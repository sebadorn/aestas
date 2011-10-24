/*
* jQuery for the STATS section.
*/
$( document ).ready( function() {


	$( "#referrer table" ).hide();
	$( "caption" ).hide();

	$( "#referrer table:first" ).fadeIn();

	function hideAllRefTables() {
		$( "#referrer table" ).fadeOut();
		$( "ul.change li" ).removeClass( "active" );
	}

	$( "ul.change li.topsearch" ).click( function() {
		hideAllRefTables();
		$( "table.topsearch" ).fadeIn();
		$( "ul.change li.topsearch" ).addClass( "active" );
	} );

	$( "ul.change li.recentsearch" ).click( function() {
		hideAllRefTables();
		$( "table.recentsearch" ).fadeIn();
		$( "ul.change li.recentsearch" ).addClass( "active" );
	} );

	$( "ul.change li.topref" ).click( function() {
		hideAllRefTables();
		$( "table.topref" ).fadeIn();
		$( "ul.change li.topref" ).addClass( "active" );
	} );

	$( "ul.change li.recentref" ).click( function() {
		hideAllRefTables();
		$( "table.recentref" ).fadeIn();
		$( "ul.change li.recentref" ).addClass( "active" );
	} );

} );