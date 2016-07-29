/**
 * Set up the isotope script and filters.
 * @copyright 2016 Robin Cornett
 */
(function ( document, $, undefined ) {
	'use strict';
	var SixTen  = {};
	var filters = {};

	SixTen.init = function () {

		$( window ).on( 'resize.stp', _doIsotope ).triggerHandler( 'resize.stp' );

		/**
		 * Filter using an unordered list.
		 */
		$( '.filter button' ).on( 'click.stp', function () {
			_doFilter( $( this ) );
		} );

		/**
		 * Filter using dropdown(s).
		 */
		$( '.filter' ).on( 'change.stpselect', function() {
			_doSelect( $( this ) );
		} );
	};

	function _doIsotope() {
		var _container = $( '.' + SixTen.params.container );
		_container.isotope( {
			itemSelector: SixTen.params.selector,
			percentPosition: true,
			masonry: {
				isAnimated: true,
				gutter: parseInt( SixTen.params.gutter )
			}
		} );
		_container.imagesLoaded( function() {
			_container.isotope( 'layout' );
		});
	}

	/**
	 * Filter using an unordered list (buttons)
	 * @param $select
	 * @returns {boolean}
	 * @private
	 */
	function _doFilter( $select ) {
		var selector = $select.attr( 'data-filter' );
		$( '.' + SixTen.params.container ).isotope( { filter: selector } );
		$select.parents( 'ul' ).find( 'button' ).removeClass( 'active' );
		$select.addClass( 'active' );
		return false;
	}

	/**
	 * Filter using a dropdown/select
	 * @param $select
	 * @returns {boolean}
	 * @private
	 */
	function _doSelect( $select ) {
		var group        = $select.attr( 'data-filter-group' );
		filters[ group ] = $select.find( ':selected' ).attr( 'data-filter-value' );

		var _selector = _combineFilters( filters );
		$( '.' + SixTen.params.container ).isotope( {
			filter: _selector
		} );

		return false;
	}

	/**
	 * Combine two select filters
	 * @param filters
	 * @returns {string}
	 * @private
	 */
	function _combineFilters( filters ) {
		var _selector = [];
		for ( var prop in filters ) {
			_selector.push( filters[ prop ] );
		}
		return _selector.join( '' );
	}

	$( document ).ready( function () {
		SixTen.params = typeof SixTenPressIsotope === 'undefined' ? '' : SixTenPressIsotope;

		if ( typeof SixTen.params !== 'undefined' ) {
			SixTen.init();
		}
	} );
})( document, jQuery );
