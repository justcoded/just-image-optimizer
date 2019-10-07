'use strict';

jQuery( document ).ready( function () {
	var lazyLoadInstance = new LazyLoad( {
		elements_selector: ".lazy"
	} );

	if ( lazyLoadInstance ) {
		lazyLoadInstance.update();
	}
} );
