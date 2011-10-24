/*
* jQuery for the MEDIA section.
*/
$( document ).ready( function() {



	/* Add file to upload */
	$( "input.add" ).click(function () {
		var i = $( "input[name='files']" ).val();
		i++;
		$( "input[name='files']" ).val( i );
		$( "#step-1 div" ).append( '<br /><input name="ae_upload_' + i + '" type="file" />' );
	} );



	/* Adding tags. */
	function addNewTag( newTag ) {

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
		if(newTag === "" || newTag === null)
			return false;

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
				if(newTag != oldTag)
					i--;
			}


			// If a list does not exist: Now's a good time to create it.
			if($("div.tags ul").length == 0)
				$("div.tags input[type='button']").after("<ul></ul>");


			// Append new tag to the list
			$("div.tags ul").append("\n<li><img src=\"interface/img/remove.png\" alt=\"remove\" \/><input name=\"tags_js[]\" type=\"hidden\" value=\""+newTag+"\" />"+newTag+"</li>");
			$("div.tags input[type='text']").val("");

		}
	}


	// In case [Enter] is hit add tag
	$("div.tags input[type='text']").keypress(function(key) {
		if(key.which == 13)
			addNewTag($("div.tags input[type='text']").val());
	});


	// In case the button is hit add tag
	$("div.tags input[type='button']").click(function() {
		addNewTag($("div.tags input[type='text']").val());
	});


	// Removing tags
	$("div.tags li img").live("click", function() {
		$(this).parent().remove();
	});



	/* Upload conflict: Rename */
	$(".conflictfile input[type='text']").hide();

	if($(".conflictfile input[value='rename']").is(":checked"))
		$(".conflictfile input[type='text']").show();

	$(".conflictfile input[type='radio']").click(function() {

		var id = $(this).attr("name");
		id = id.replace('conflict_', '');

		if($(this).val() == "rename")
			$(".conflictfile input[name='rename_"+id+"']").slideDown("fast");
		else
			$(".conflictfile input[name='rename_"+id+"']").slideUp("normal");

	});


});
