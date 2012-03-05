$( function() {

	// Hide all elements with CSS class "hideonload".
	$( ".hideonload" ).hide();
	$( ".hideifnojs" ).show();

	// Use jQuery indent plugin on everything with the CSS class "indent".
	$( ".indent" ).indent();

	// Tab sections
	$( ".tabs li" ).click( function() {
		var showTabPanel = $( this ).data( "tabTrigger" );
		// Set "active" class for tabs
		$( this ).parent( ".tabs" ).children( "li" ).removeClass( "active" );
		$( this ).addClass( "active" );
		// Only show chosen tab panel
		$( this ).parents( ".tabsection" ).children( ".tabpanel" ).hide();
		$( ".tabpanel[data-tab-panel='" + showTabPanel + "']" ).show();
	} );

} );