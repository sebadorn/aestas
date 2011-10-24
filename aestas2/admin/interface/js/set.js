/*
* jQuery for the SET section.
*/
$( document ).ready( function() {



	/* Hide and show extended settings for gravatars. */

	if( $( "#grav" ).is( ":checked" ) === false ) {
		$( "#avatars .extended" ).hide();
	}

	$( "#grav" ).click( function() {
		$( "#avatars .extended" ).slideToggle( "normal" );
	} );



	/* Permalinks: Hide custom fields */

	$( "#set input[name|='custom']" ).hide();
	$( "#set p[class|='patterns']" ).hide();


	// Show those with customized permalinks

	$( "#set input[value='custom']" ).each( function() {
		if( $( this ).is( ":checked" ) ) {

			var what = $(this).attr( "name" );
			what = what.split( "-" );

			$( "#set input[name='custom-" + what[1] + "']" ).show();
			$( "#set p[class~='patterns-" + what[1] + "']" ).show();

		}
	} );


	$( "#set input[type='radio']" ).click( function() {

		var what = $( this ).attr( "name" );
		what = what.split( "-" );

		if( $( this ).attr( "value" ) == "custom" ) {
			$( "#set input[name='custom-" + what[1] + "']" ).slideDown();
			$( "#set p[class~='patterns-" + what[1] + "']" ).slideDown();
		}
		else {
			$( "#set input[name='custom-" + what[1] + "']" ).slideUp();
			$( "#set p[class~='patterns-" + what[1] + "']" ).slideUp();
		}

	} );



} );
