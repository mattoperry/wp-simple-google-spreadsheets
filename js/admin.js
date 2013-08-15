/** simple-google-spreadsheets admin JavaScript **/

/** main class **/

var SGS = { UI: {} };

/** the css class for our body **/

SGS.UI.context = '.settings_page_simple-google-spreadsheets';

/** SGS methods **/
/** @todo -- refactor inside document ready -- these should be shorter **/

SGS.unmark_sheet_for_deletion = function( sheet_id ) {
	var  our_tr = jQuery( SGS.UI.context + ' tr#sheet_' +sheet_id );
	var  our_row = jQuery( SGS.UI.context + ' tr#sheet_' + sheet_id + ' input.row_delete');
	var  our_delete_button = jQuery( SGS.UI.context + ' tr#sheet_' + sheet_id + ' button.delete_sheet');
	var  our_undo_button = jQuery( SGS.UI.context + ' tr#sheet_' + sheet_id + ' button.undo_delete_sheet');
	our_row.attr( 'value', '0' );
	our_tr.removeClass('marked_for_deletion');
	our_delete_button.show();
	our_undo_button.hide();
}

SGS.mark_sheet_for_deletion = function( sheet_id ) {
	var  our_tr = jQuery( SGS.UI.context + ' tr#sheet_' +sheet_id );
	var  our_row = jQuery( SGS.UI.context + ' tr#sheet_' + sheet_id + ' input.row_delete');
	var  our_delete_button = jQuery( SGS.UI.context + ' tr#sheet_' + sheet_id + ' button.delete_sheet');
	var  our_undo_button = jQuery( SGS.UI.context + ' tr#sheet_' + sheet_id + ' button.undo_delete_sheet');
	our_row.attr('value', '1');
	our_tr.addClass('marked_for_deletion');
	our_delete_button.hide();
	our_undo_button.show();
}

SGS.reset_sheet_fields = function( sheet_id ) {
	var  our_fields = jQuery( SGS.UI.context + ' tr#sheet_' + sheet_id + ' input[type=text]');
	our_fields.each( function() {
		jQuery(this).val('');
	});
}

/** events **/

jQuery(document).ready(function() {

	//new sheet row
	SGS.UI.new_sheet = jQuery(SGS.UI.context + ' tr.new_sheet');

	//control buttons
	SGS.UI.cancel_add_button = jQuery( SGS.UI.context + ' button.cancel_add_sheet');
	SGS.UI.delete_button = jQuery( SGS.UI.context + ' button.delete_sheet');
	SGS.UI.undo_delete_button = jQuery( SGS.UI.context + ' button.undo_delete_sheet');
	SGS.UI.add_button = jQuery( SGS.UI.context + ' button#add_sheet');
	SGS.UI.settings_form = jQuery( SGS.UI.context + ' #wpbody-content form');	//the main settings form for our plugin

	/** BUTTONS **/

	// add sheet button
	SGS.UI.add_button.click( function( e ) {
		e.preventDefault();
		SGS.UI.new_sheet.show();
		SGS.unmark_sheet_for_deletion( SGS.UI.new_sheet.attr( 'data' ) );
		SGS.UI.add_button.hide();
	});

	//cancel add sheet button
	SGS.UI.cancel_add_button.click( function( e ) {
		e.preventDefault();
		SGS.UI.new_sheet.hide();
		SGS.mark_sheet_for_deletion( SGS.UI.new_sheet.attr( 'data' ) );
		SGS.reset_sheet_fields( SGS.UI.new_sheet.attr( 'data' ) );
		SGS.UI.add_button.show();
	});

	//delete sheet button
	SGS.UI.delete_button.click( function( e ) {
		e.preventDefault();
		SGS.mark_sheet_for_deletion( e.target.attributes.data.value );
	});

	//undo delete sheet button
	SGS.UI.undo_delete_button.click( function( e ) {
		e.preventDefault();
		SGS.unmark_sheet_for_deletion( e.target.attributes.data.value );
	});

	/** THE FORM SUMBISSION **/

	SGS.UI.settings_form.submit( function( e ) {
		//make sure there is no missing data in any non-deletion-marked text field on the form
		jQuery( SGS.UI.context + ' form tr:not(.marked_for_deletion) input[type=text]').each( function() {
			if (jQuery.trim( jQuery(this).val() ) == '' ) {
				e.preventDefault();
				alert('missing or invalid data');
			}
		});

		//delete any "marked for deletion" rows from the dom prior to submission
		jQuery( SGS.UI.context + ' form tr.marked_for_deletion').each( function() {
			jQuery(this).empty();
		});
	});

});
