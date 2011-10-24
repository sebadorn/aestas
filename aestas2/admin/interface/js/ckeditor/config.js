/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config ) {
	config.skin = "aestas";
	config.coreStyles_underline = { element : "span", attributes : { "style": "text-decoration: underline;"} };
	config.coreStyles_strike = { element : "del" };
	config.toolbar = [
		["Bold","Italic","Strike"],
		["NumberedList","BulletedList"],["Blockquote"],
		["Link","Unlink"],
		["Image"],["Table"],
		["JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"],
		["RemoveFormat"],["PageBreak"],
		"/",["Format"],["Underline"],["TextColor"],
		["SpecialChar"],
		["Find"],
		["PasteText","PasteFromWord"],
		["Maximize"],["Source"],
		["Undo","Redo"],["About"]
	];
	config.toolbarCanCollapse = false;
	config.width = 692;
	config.height = 240;
	config.resize_maxWidth = 0;
	config.dialog_backgroundCoverColor = "#303030";
	config.removePlugins = "scayt,menubutton,contextmenu";
	config.entities = false;
};

// Adjust HTML output format
CKEDITOR.on( 'instanceReady', function( ev ) {
	var writer = ev.editor.dataProcessor.writer; 
	// The character sequence to use for every indentation step.
	writer.indentationChars = '    ';	
 
	var dtd = CKEDITOR.dtd;
	//Elements taken as an example are: block elements (div or p), list items (li, dd) and table elements (td, tbody).
	for( var e in CKEDITOR.tools.extend( {}, dtd.$block, dtd.$listItem, dtd.$tableContent ) ) {
		ev.editor.dataProcessor.writer.setRules( e, {
			// Indicates that tag causes indentation on line breaks inside of it.
			indent: false,
			// Insert a line break before tag.
			breakBeforeOpen: true,
			// Insert a line break after tag.
			breakAfterOpen: false,
			// Insert a line break before closing tag.
			breakBeforeClose: false,
			// Insert a line break after closing tag.
			breakAfterClose: true
		} );
	}
 
	for( var e in CKEDITOR.tools.extend( {}, dtd.$list, dtd.$listItem, dtd.$tableContent ) ) {
		ev.editor.dataProcessor.writer.setRules( e, { indent: true, } );
	}

	//You can also apply the rules to the single elements.
	ev.editor.dataProcessor.writer.setRules( 'table', { indent: true } );
	ev.editor.dataProcessor.writer.setRules( 'form', { indent: true } );	
} );
