window.addEventListener( "DOMContentLoaded", function() {

	var d = document;
	var tagInput = d.getElementById( "add-tags" ),
	    tagList = d.getElementById( "tag-listing" );
	var tags = new Array();

	tagInput.addEventListener( "keydown", triggerTagAdding );


	/**
	 * Calls addTags().
	 * @param {Event} e
	 */
	function triggerTagAdding( e ) {
		// Enter key only
		if( e.keyCode != 13 ) { return; }
		// Don't submit form
		e.preventDefault();

		addTags();
	}


	/**
	 * Removes tags from the input field and
	 * adds them to an HTML list.
	 */
	function addTags() {
		var tagsFromInput = tagInput.value.split( ";" );
		var liFragment = d.createDocumentFragment(),
		    li;
		var i;

		tags = mergeArrays( tags, tagsFromInput );
		tags.sort();

		// Remove all tags
		while( tagList.hasChildNodes() ) {
    		tagList.removeChild( tagList.lastChild );
		}

		// Add tags including new ones in sorted order
		for( i = 0; i < tags.length; i++ ) {
			li = d.createElement( "li" );
			li.className = "icon icon-tag";
			li.title = "click to remove";
			li.addEventListener( "click", removeTag );
			li.appendChild( d.createTextNode( tags[i] ) );

			liFragment.appendChild( li );
		}

		tagList.appendChild( liFragment );
		tagInput.value = "";
	}


	/**
	 * Remove a tag from the HTML list.
	 * @param {Event} e
	 */
	function removeTag( e ) {
		var value = e.target.textContent;
		var index = tags.indexOf( value );

		tags.splice( index, 1 );
		e.target.parentNode.removeChild( e.target );
	}


	/**
	 * Merge to arrays to an array with unique elements.
	 * @param  {Array} a First array. Has to be unique already.
	 * @param  {Array} b Second array.
	 * @return {Array}   Merged array with unique elements.
	 */
	function mergeArrays( a, b ) {
		var merged = a.slice( 0 );
		var i, element;

		for( i = 0; i < b.length; i++ ) {
			element = b[i].trim();
			if( element != "" && merged.indexOf( element ) == -1 ) {
				merged[merged.length] = element;
			}
		}

		return merged;
	}


	// In case we edit a post that already has tags.
	// Change those into an HTML list on page load.
	addTags();

} );