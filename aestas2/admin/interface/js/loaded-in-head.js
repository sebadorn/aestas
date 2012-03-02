// Add a CSS rule to hide all elements with the class "hideonload".
// At the end of the page load, the rule is nullified again.
// Prevents flickering on page load.

// Also adds a CSS rule to show all elements with the class "hideifnojs".

var headEle = document.getElementsByTagName( "head" )[0],
	css = ".hideonload { display: none; } .hideifnojs { visibility: visible !important; }";

styleEle = document.createElement( "style" );
styleEle.type = "text/css";

headEle.appendChild( styleEle );

if( styleEle.styleSheet ) {
	styleEle.styleSheet.cssText = css;
}
else {
	styleEle.appendChild( document.createTextNode( css ) );
}