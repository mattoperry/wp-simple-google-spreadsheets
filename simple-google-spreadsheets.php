<?php
/**
 * Plugin Name: Simple Google Spreadsheets
 * Plugin URI: https://github.com/mattoperry/simple-google-spreadsheets
 * Integrates live data from Google spreadsheets into your WordPress templates.
 * Author: Matt Perry
 * Author URI: http://www.stkywll.com
 * Version: 0.1
 */

/** Include the PEAR JSON library.  We'll use it instead of internal JSON because a)  we don't really need to do much except decode and b) we want this to work even in situations without native JSON support (like old PHP or times when JSON is disabled.) **/
if ( !class_exists( 'Services_JSON' ) ) {
	include_once( plugin_dir_path(__FILE__) . 'lib/Services_JSON.php' );
}


/***
 *
 * Simple Google Spreadsheets
 *
 * @package   Simple_Google_Spreadsheets
 * @author    Matt Perry mattoperry@gmail.com
 * @license   GPL-2.0+
 * @link      http://stkywll.com
 * @copyright Matt Perry
 *
 * @wordpress-plugin
 * Plugin Name: Simple Google Spreadsheets
 * Plugin URI:  http://stkywll.com
 * Description: Integrates live data from Google spreadsheets into your WordPress templates.
 * Version:     1.0.0
 * Author:      Matt Perry
 * Author URI:  http://stkywll.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-simple-google-spreadsheets.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Simple_Google_Spreadsheets', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Simple_Google_Spreadsheets', 'deactivate' ) );

$SGS = Simple_Google_Spreadsheets::get_instance();

?>