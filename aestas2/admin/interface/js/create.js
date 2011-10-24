/*
* jQuery for the CREATE section.
*/
$( document ).ready( function() {


	/* Switch through extended options */

	$("#ext_options div").hide();

	if($("#create h1").text() == "Add Post" || $("#create h1").text() == "Edit Post") {
		$("#ext_options div.categories").show();
	}

	else if($("#create h1").text() == "Add Page" || $("#create h1").text() == "Edit Page" || $("#create h1").text() == "File Info") {
		$("#ext_options div.tags").show();
	}

	else if($("#create h1").text() == "Add Category" || $("#create h1").text() == "Edit Category") {

		if($("#ext_options div.parent").length > 0) {
			$("#ext_options div.parent").show();
		}
		else {
			$("#ext_options div.permalink").show();
		}

	}


	$("#ext_nav li").click(function() {

		$("#ext_nav li").each(function() {
			$(this).removeClass("active");
		});

		$(this).addClass("active");
		var toShow = $(this).text().toLowerCase().replace(/ /, ""); // Class name of panel to show

		$("#ext_options div").each(function() {
			if($(this).attr("class") != toShow)
				$(this).slideUp();
		});

		$("#ext_options div."+toShow).slideDown();

	});



	/* Hitting [Enter] while being in an input or select shall do nothing. */
	/* Except in the search. */

	$( "input[type='text'], input[type='password'], select" ).keypress( function( key ) {
		if( $( this ).closest( "form" ).attr( "class" ) != "search" && key.which == 13 ) {
			return false;
		}
	} );



	/* Adding tags. */

	function addNewTag(newTag) {

		// If multiple tags are seperated with semicolons
		newTag = jQuery.trim(newTag);
		var sliceHere = newTag.lastIndexOf(";");

		if(sliceHere != -1) {
			var newNewTag = newTag.slice(0, sliceHere);
			newNewTag = jQuery.trim(newNewTag);
			newTag = newTag.slice(sliceHere+1, newTag.length);
			newTag = jQuery.trim(newTag);

			addNewTag(newNewTag); // Recursive, sweet.
		}

		// Stopping empty strings
		if(newTag === "" || newTag === null) {
			return false;
		}

		// Checking if tag already exists
		alreadyExistent = false;
		$("div.tags li").each(function() {
			if(newTag == $(this).text()) {
				alreadyExistent = true;
				return;
			}
		});


		// It does not exist, so let's add it!
		if(alreadyExistent === false) {

			// Replacing some unwanted chars
			var beforeReplace = new Array('"', '<', '>');
			var afterReplace = new Array('&quot;', '&lt;', '&gt;');
			var oldTag;

			for(var i = 0; i < beforeReplace.length; i++) {
				oldTag = newTag;
				newTag = newTag.replace(beforeReplace[i], afterReplace[i]);
				if(newTag != oldTag) {
					i--;
				}
			}


			// If a list does not exist: Now's a good time to create it.
			if($("div.tags ul").length == 0) {
				$("div.tags input[type='button']").after("<ul></ul>");
			}


			// Append new tag to the list
			$("div.tags ul").append("\n<li><img src=\"interface/img/remove.png\" alt=\"remove\" \/><input name=\"tags_js[]\" type=\"hidden\" value=\""+newTag+"\" />"+newTag+"</li>");
			$("div.tags input[type='text']").val("");

		}
	}


	// In case [Enter] is hit add tag

	$("div.tags input[type='text']").keypress(function(key) {
		if(key.which == 13) {
			addNewTag($("div.tags input[type='text']").val());
		}
	});


	// In case the button is hit add tag

	$("div.tags input[type='button']").click(function() {
		addNewTag($("div.tags input[type='text']").val());
	});


	// Removing tags
	$("div.tags li img").live("click", function() {
		$(this).parent().remove();
	});



	/* Adding trackbacks */

	function addTrack(newTrack) {

		// If multiple tags are seperated with white space
		newTrack = jQuery.trim(newTrack);
		var sliceHere = newTrack.lastIndexOf(" ");

		if(sliceHere != -1) {
			var newNewTrack = newTrack.slice(0, sliceHere);
			newNewTrack = jQuery.trim(newNewTrack);
			newTrack = newTrack.slice(sliceHere+1, newTrack.length);
			newTrack = jQuery.trim(newTrack);

			addTrack(newNewTrack); // Recursive, sweet.
		}


		// Stopping empty strings
		if(newTrack === "" || newTrack === null) {
			return false;
		}

		// Add protocol if missing
		if(newTrack.indexOf("http://") != 0 && newTrack.indexOf("https://") != 0) {
			newTrack = "http://"+newTrack;
		}

		// Checking if track already exists
		alreadyExistent = false;
		$("div.tracks li").each(function() {
			if(newTrack == $(this).text()) {
				alreadyExistent = true;
				return;
			}
		});


		// It does not exist, so let's add it!
		if(alreadyExistent === false) {

			// Removing unwanted chars
			var beforeReplace = new Array('"', '<', '>');
			var oldTrack;

			for(var i = 0; i < beforeReplace.length; i++) {
				oldTrack = newTrack;
				newTrack = newTrack.replace(beforeReplace[i], '');
				if(newTrack != oldTrack) {
					i--;
				}
			}


			// If a list does not exist: Now's a good time to create it.
			if($("div.tracks ul").length == 0) {
				$("div.tracks input[type='button']").after("<ul></ul>");
			}


			// Append new trackback to the list
			$("div.tracks ul").append("\n<li><img src=\"interface/img/remove.png\" alt=\"remove\" \/><input name=\"tracks_js[]\" type=\"hidden\" value=\""+newTrack+"\" />"+newTrack+"</li>");
			$("div.tracks input[type='text']").val("");

		}

	}


	// In case [Enter] is hit add trackback

	$("div.tracks input[type='text']").keypress(function(key) {
		if(key.which == 13) {
			addTrack($("div.tracks input[type='text']").val());
		}
	});


	// In case the button is hit add trackback

	$("div.tracks input[type='button']").click(function() {
		addTrack($("div.tracks input[type='text']").val());
	});


	// Removing tracks

	$("div.tracks li img").live("click", function() {
		$(this).parent().remove();
	});



	/* Password in cleartext or not */

	$("div.protect input[type='text']").hide();

	$("div.protect #cleartext").click(function() {
		if($(this).is(":checked")) {
			$("div.protect input[type='text']").show();
			$("div.protect input[name='protect']").hide();
		}
		else {
			$("div.protect input[type='text']").hide();
			$("div.protect input[name='protect']").show();
		}
	});


	$("div.protect input[type='text']").keyup(function() {
		$("div.protect input[name='protect']").val($(this).val());
	});

	$("div.protect input[name='protect']").keyup(function() {
		$("div.protect input[type='text']").val($(this).val());
	});



	/* Security check */

	function securityCheck() {
		var passw = $("div.protect input[name='protect']").val();
		var sLevel = 0;
		var result = "";


		if(passw == "") {
			$("div.protect p").text("Just type your password in here.");
			return;
		}

		if(passw.length >= 6) {
			sLevel++;
		}

		if(sLevel > 0 && passw.match(/[a-z]/) && passw.match(/[0-9]/)) {
			sLevel++;
		}

		if(sLevel > 1 && (passw.match(/[A-Z_]/) || passw.match(/\W/))) {
			sLevel++;
		}

		if(sLevel < 2) {
			if(passw.match(/^(0)?123456(7)?$/)
			|| passw.match(/^sex$/)
			|| passw.match(/^love$/)
			|| passw.match(/^god$/)
			|| passw.match(/^family$/)
			|| passw.match(/^asd$/)
			|| passw.match(/^qwert$/)) {
				sLevel = -1;
			}
		}

		switch(sLevel) {
			case 0: result = "Insecure. A password should be longer than 6 characters and contain numbers and letters, also capitals are fine."; break;
			case 1: result = "Not good. A password should mix numbers and letters, also capitals are fine."; break;
			case 2: result = "That is a good one. You could also add capital letters."; break;
			case 3: result = "Great. You made a good choice."; break;
			case -1: result = "Seriously, DO NOT use this password! Way too insecure."; break;
			default: result = "Sorry, I was not able to analyse the password.";
		}

		$("div.protect p").text(result);
	}


	$("div.protect input[type='button']").click(function() {
		securityCheck();
	});

	$("div.protect input").keypress(function(key) {
		if(key.which == 13) {
			securityCheck();
		}
	});



	/* Suggestion for permalink */

	$("#title input[name='title']").keyup(function() {
		var perma = $("#title input[name='title']").val();

		perma = perma.toLowerCase();
		perma = perma.replace(/ä/g, "ae").replace(/ö/g, "oe").replace(/ü/g, "ue").replace(/ß/g, "ss");
		perma = perma.replace(/[^a-z0-9 ]/g, "").replace(/\s/g, "-").replace(/-+/g, "-");
		perma = perma.replace(/-$/, "");
		perma = perma.replace(/^-/, "");

		$(".sug span").text(perma);
	});



	/* Validate user-suggested permalink */

	function validatePermalink() {
		var url = $( "div.permalink input[name='permalink']" ).val();
		url = $.trim( url );

		// Stopping empty strings
		if( url === "" || url === null ) {
			return false;
		}

		// Reform
		url = url.toLowerCase();
		url = url.replace( /ä/g, "ae" ).replace( /ö/g, "oe" ).replace( /ü/g, "ue" ).replace( /ß/g, "ss" );
		url = url.replace( /[^a-z0-9-]/g, "" ).replace( /\s/g, "-" );

		// Start of URL
		suggestion = $( "div.permalink .sug" ).text();
		suggestion = $.trim( suggestion );
		url = suggestion.slice( 12, $( "div.permalink .sug" ).text().length ) + url;
		var withoutSpan = $( "div.permalink .sug span" ).text();
		url = url.replace( withoutSpan, "" );

		// Add protocol if missing
		if( url.indexOf( "http://" ) != 0 && url.indexOf( "https://" ) != 0 ) {
			url = "http://" + url;
		}

		$( "div.permalink #permalink" ).text(url);
	}


	// In case [Enter] is hit validate Permalink

	$("div.permalink input[type='text']").keypress(function(key) {
		if(key.which == 13) {
			validatePermalink();
		}
	});


	// In case the button is hit validate permalink

	$("div.permalink input[type='button']").click(function() {
		validatePermalink();
	});



	/* Show and hide options for a manually set date. */

	if($("#date input[value='imm']").length == 0 || $("#date input[value='imm']").is(":checked")) {
		$("#date .manually").hide();
	}

	$("#date input[name='date']").click(function() {
		if($(this).val() == "set") {
			$("#date .manually").slideDown("fast");
		}
		else {
			$("#date .manually").slideUp("normal");
		}
	});



	/* Show and hide option for date when to expire */

	if(!$("#date input[name='expires']").is(":checked")) {
		$("#date .expires_set").hide();
	}

	$("#date input[name='expires']").click(function() {
		if($(this).is(":checked")) {
			$("#date .expires_set").slideDown("fast");
		}
		else {
			$("#date .expires_set").slideUp("normal");
		}
	});



	/* Show and hide options for a manually set date. */

	$( "#date input[name='change_date']" ).click( function() {
		if( $( this ).is( ":checked" ) ) {
			$("#date .manually").slideDown( "fast" );
		}
		else {
			$( "#date .manually" ).slideUp( "normal" );
		}
	} );



	/* Edit comment: Show and hide options if a registered user */

	if( !$( "#precontent div div input" ).is( ":checked" ) ) {
		$( "#precontent div div select" ).hide();
	}

	$( "#precontent div div input" ).click( function() {
		$( "#precontent div div select" ).slideToggle( "fast" );
	} );



	/* Edit Post: Existing Tags */

	if( $( ".tags input[name='tags']" ).val() != "" ) {
		addNewTag( $( ".tags input[name='tags']" ).val() );
	}



	/* Edit Post: Existing Tags */

	if( $( ".tracks input[name='tracks']" ).val() != "" ) {
		addTrack( $( ".tracks input[name='tracks']" ).val() );
	}



	/* Edit Post: Existing Permalink */

	if( $( ".permalink input[name='permalink']" ).val() != "" ) {
		validatePermalink( $( ".permalink input[name='permalink']" ).val() );
	}



	/* Edit Post: Suggestion for permalink */

	if( $( "#title input[name='title']" ).val() != '' ) {
		var perma = $( "#title input[name='title']" ).val();

		perma = perma.toLowerCase();
		perma = perma.replace( /ä/g, "ae" ).replace( /ö/g, "oe" ).replace( /ü/g, "ue" ).replace( /ß/g, "ss" );
		perma = perma.replace( /[^a-z0-9 ]/g, "" ).replace( /\s/g, "-" ).replace( /-+/g, "-" );
		perma = perma.replace( /-$/, "" );
		perma = perma.replace( /^-/, "" );

		$( ".sug span" ).text( perma );
	}


} );
