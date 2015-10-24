(function( $ ) {
	'use strict';

	//Javascript GET cookie parameter
	var $_GET = {};
	document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
	    function decode(s) {
	        return decodeURIComponent(s.split("+").join(" "));
	    }

	    $_GET[decode(arguments[1])] = decode(arguments[2]);
	});

	// Get time var defined in woo backend
	var $time = parseInt(gens_raf.timee);
	//If raf is set, add cookie.
	if( typeof $_GET["raf"] !== 'undefined' && $_GET["raf"] !== null ){
		//console.log(window.location.hostname);
		cookie.set("gens_raf",$_GET["raf"],{ expires: $time, path:'/' });
	}

})( jQuery );
