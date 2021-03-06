<?php
/**
 * Simple Google Spreadsheets
 *
 * @package   Simple_Google_Spreadsheets
 * @author    Matt Perry
 * @license   GPL-2.0+
 * @link      http://stkywll.com
 * @copyright 2013 Matt Perry
 */

/**
 * Simple Google Spreadsheets Class
 *
 * @package Simple_Google_Spreadsheets
 * @author  Matt Perry
 */
class Simple_Google_Spreadsheets {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 * @var     string
	 */
	protected $version = '1.0.0';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * shortname for the plugin
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	protected $shortname = 'simple-google-spreadsheets';

	/**
	*
	* JSON objet
	*
	* @since  	1.0.0
	* @var 		string
	*
	**/

	protected $json = null;

	/**
	*
	* available worksheets list
	*
	* @since  	1.0.0
	* @var 		array
	*
	**/

	protected $sheets = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Define custom functionality. Read more about actions and filters: http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		//add_action( 'TODO', array( $this, 'action_method_name' ) );
		//add_filter( 'TODO', array( $this, 'filter_method_name' ) );

		//set up the settings for the plugin
		//add_settings_field( $this->$shortname.'_sheets', 'Worksheets', array($this, 'bla'), $this->$plugin_screen_hook_suffix );

		$this->json = new Services_JSON;
		add_action('admin_init', array($this, 'admin_init'));
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
	}

	/**
	 * Init the admin screen.
	 *
	 * @since    1.0.0
	 */

	public function admin_init() {
		register_setting( 'simple-google-spreadsheets', 'simple-google-spreadsheets_sheets' );
	}

	public function admin_settings_sanitize( $val ) {
		return $val;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->shortname ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->shortname ) {
			wp_enqueue_style( $this->shortname .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->shortname ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->shortname ) {
			wp_enqueue_script( $this->shortname . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->shortname = add_options_page(
			'Simple Google Spreadsheets Settings',
			'Google Spreadsheets',
			'edit_others_posts',
			'simple-google-spreadsheets',
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( plugin_dir_path(__FILE__) . 'admin.php' );
	}


	/** retrieve a particular spreadsheet **/

	public function get_data( $worksheet_name ) {

		//@todo cache and retrieval layer here right?

		/** lets see if we have this sheet in cache already **/
		$ret = $this->fetch_from_cache( $worksheet_name );
		if ( $ret ) return $ret;

		/** not in cache -- begin the process of fetching it from Google **/
		$sheet = $this->get_sheet_data( $worksheet_name );

		$url = "http://spreadsheets.google.com/feeds/cells/{$sheet['ID']}/{$sheet['worksheet']}/public/values?alt=json";	//note -- no https

		$args = array(
			'method'      =>    'GET',
			'timeout'     =>    10,
			'redirection' =>    2,
			'httpversion' =>    '1.0',
			'blocking'    =>    true
		);

		$response = wp_remote_get( $url, $args );
		
		if( is_wp_error( $response ) ) {
		   	
		   	return false;

		} else {

			$j_response = $this->json->decode( wp_remote_retrieve_body( $response ) );	

			//@todo make this optional -- currently this all only works if there are row and column headers -- we should allow for plain old integer indexing too
			$worksheet_args['col_names'] = true;
			$worksheet_args['row_names'] = true;

			$data = array();
			$data_labels = array();
			$cols = array();

			foreach ( $j_response->feed->entry as $cell ) {

				$c = $cell->{'gs$cell'};
				$t = $cell->{'title'};

				$title = $t->{'title'};

				//record the indexed data -- this builds an array where the incdeces are the cel titles .. like C1, D15, CC14 etc ...
				$data[$title] = $c->{'$t'};

				//now build the array which assumes that the first item in both rows and columns are headings -- note that we assume here tha both have titles
				//thus it's not possible for just column or just row titles, but noth or neither are required.

				// if we're in the first column, adopt the value here as the row name
				if ( $c->col == 1 ) {
					$row_index == $c->{'$t'};
					continue;
				}
					
				// if we're in the first row, build the list of column names
				if ( $c->row == 1 ) {
					$cols[$c->col] = $c->{'$t'};
				}else{
					//otherwise record the contents of the cel f'reel
					$data_labels[$row_index][$cols[$c->col]] = $c->{'$t'};
				}				
			}

			$ret = array( 'no_labels' => $data, 'labels' => $data_labels );

			if ( $ret ) {
				//cache it before we return it
				$this->cache_sheet( $sheet['name'], $ret, $sheet['refresh']);
			}

			return $ret;
		}
	}

	/** helper function -- given a sheet name, get its meta data from the option **/

	public function get_sheet_data( $worksheet_name ) {

		$sheets = get_option( 'simple-google-spreadsheets_sheets' );
		$sheet = false;

		foreach ( $sheets as $s ) {
			if ( $s['name'] == $worksheet_name ) {
				$sheet = $s;
				break;
			}
		}

		return $sheet;
	}

	/** fetch some data from the spreadsheet
	@param str $name -- the name of the spreadsheet
	@param mixed $arg -- an argument sepcifying what data to retrieve.  Acts accorind to the following logic:
	-- if a string, it will be treated as a cell title, usually of the form B1, S12 , CC14 etc ...
	-- if an array, it will be treated as a tupple of the form (row, column)  if either row or column is null, we try to fetch the other ... 
	-- if both are specified, then the cell contents -- if found -- are fetched
	@param bool $echo -- if true, the reults are echoed, otherwise returned
	**/

	public function fetch( $name=null, $arg=null, $echo=true ) {		

		/** validate the spreadsheet name -- get the data **/

		$data = $this->get_data( $name );

		if ( !$data ) {
			return false;
		}

		if ( is_array( $arg ) && isset( $arg[0] ) && isset( $arg[1] ) )  {
			$ret = ( isset( $data_labels[$arg[0]][$arg[1]] ) ) ? $data_labels[$arg[0]][$arg[1]] : false;
		}elseif( is_array( $arg ) && isset( $arg[0] ) ) {
			$ret = ( isset( $data_labels[$arg[0]] ) ) ? $data_labels[$arg[0]] : false;
		}elseif( is_array( $arg ) && isset( $arg[1] ) ) {
			$ret = $this->get_column( $data_labels, $arg[1] );
		}elseif( is_string($arg) ) {
			$ret = $this->data[$arg];
		}

		if ( $echo && !is_array( $ret ) ) {
			echo $ret;
		}else{
			return $ret;
		}
	}

	private function get_column( $data, $col ) {

		$ret = array();

		foreach ( $data as $row ) {
			if ( isset( $row[$col] ) ) {
				$ret[] = $row[$col];	
			}
		}

		return $ret;

	}

	/** caching **/

	//puts a sheet into cache using a transient
	protected function cache_sheet( $worksheet_name=false, $data=false, $refresh=false ) {
		
		if ( !$worksheet_name || !$data || !$refresh ) return false;
		return set_transient( "simple-google-spreadsheets_{$worksheet_name}", $data, $refresh);
	}

	//retrieves a sheet from cache
	protected function fetch_from_cache( $worksheet_name ) {
		return get_transient( "simple-google-spreadsheets_{$worksheet_name}" );
	}

}