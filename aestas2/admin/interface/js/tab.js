window.addEventListener( "DOMContentLoaded", function() {

	var d = document;
	var tabTrigger = d.querySelectorAll( "[data-tab-trigger]" );
	var tabPanel = d.querySelectorAll( "[data-tab-panel]" );
	var i;

	for( i = 0; i < tabTrigger.length; i++ ) {
		tabTrigger[i].addEventListener( "click", togglePanel );
	}
	console.log(tabTrigger);


	/**
	 * Hides all panel except for the now activated one.
	 * @param  {Event} e
	 */
	function togglePanel( e ) {
		var d = document;
		var targetPanelName = e.target.getAttribute( "data-tab-trigger" );
		var i, cn;

		for( i = 0; i < tabTrigger.length; i++ ) {
			// Hide all
			tabTrigger[i].className = "";
			tabPanel[i].className = tabPanel[i].className.replace( " active", "" );

			// Show the one
			if( tabPanel[i].getAttribute( "data-tab-panel" ) == targetPanelName ) {
				tabPanel[i].className = tabPanel[i].className + " active";
			}
		}

		// Used trigger
		e.target.className = "active";
	}

} );