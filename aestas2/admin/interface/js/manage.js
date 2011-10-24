/*
* jQuery for the MANAGE section.
*/
$( document ).ready( function() {

	var class_selected = "selected";
	var ctrl = false;


	/* Set variable if ctrl-key is pressed. */
	$( document ).keydown( function( event ) {
		ctrl = ( event.keyCode == 17 ) ? true : false;
	} ).keyup( function() {
		ctrl = false;
	} );


	/* Change class when selecting or de-selecting something. */
	$( ".check input[type='checkbox']" ).click( function() {
		entry = $( this ).parent().parent();
		entry.toggleClass( class_selected );
	} );


	function ctrl_select() {
		if( ctrl ) {
			cid = $( this ).attr( "id" );
			chkbox = $( "input:checkbox[value='" + cid + "']" );
			if( chkbox.is( ":checked" ) ) {
				chkbox.attr( "checked", false );
			}
			else {
				chkbox.attr( "checked", true );
			}
			$( this ).toggleClass( class_selected );
		}
	}

	/* Select entries by pressing ctrl and click. */
	$( "#manage > form tr" ).click( ctrl_select );
	$( "#manage > form li" ).click( ctrl_select );


	/* Tabs for comments */
	$( "#comments .tabs li" ).click( function() {
		$( this ).parent().find( "li" ).attr( "class", "" );
		$( this ).attr( "class", "active" );
		tabText = $( this ).text().toLowerCase();
		hideTabs = $( this ).parent().parent().find( "div.tab" );
		hideTabs.slideUp();
		showTab = $( this ).parent().parent().find( "div.tab-" + tabText );
		showTab.slideDown();
	} );


} );
