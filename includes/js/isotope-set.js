/**
 * Set up the isotope script and filters.
 * @copyright 2016 Robin Cornett
 */
;(function ( document, $, undefined ) {
	'use strict';
	var SixTen  = {};
	var filters = {};
	var qsRegex;

	SixTen.init = function () {

		var _container = $( '.' + SixTen.params.container );
		_container.imagesLoaded( function () {
			_container.isotope( SixTen.params.isotopeRules );
		} );

		var _function = _doIsotope;
		if ( SixTen.params.infinite ) {
			_function = _doInfiniteScroll;
		}
		$( window ).on( 'resize.stp', _function ).triggerHandler( 'resize.stp' );

		/**
		 * Filter using an unordered list.
		 */
		$( '.filter button' ).on( 'click.stp', function () {
			_doFilter( $( this ) );
		} );

		/**
		 * Filter using dropdown(s).
		 */
		$( '.filter' ).on( 'change.stpselect', function () {
			_doSelect( $( this ) );
		} );

		/**
		 * Filter by search input.
		 */
		var _quickSearch = $( '.isotope-search' ).keyup( _debounce( function () {
			qsRegex = new RegExp( _quickSearch.val(), 'gi' );
			_container.isotope( {
				filter: function () {
					return qsRegex ? $( this ).text().match( qsRegex ) : true;
				}
			} );
		}, 250 ) );
	};

	/**
	 * Do the isotope functions.
	 * @private
	 */
	function _doIsotope() {
		var _container = $( '.' + SixTen.params.container );
		_container.imagesLoaded( function () {
			_container.isotope( 'layout' );
			$( SixTen.params.selector ).animate( {opacity: 1} );
		} );
	}

	/**
	 * Do infinite scroll
	 * @private
	 */
	function _doInfiniteScroll() {
		var _container   = $( '.' + SixTen.params.container ),
			_navSelector = SixTen.params.navigation;
		$( _navSelector ).css( 'display', 'none' );
		_container.infinitescroll( {
				navSelector: _navSelector,
				nextSelector: _navSelector + ' ' + SixTen.params.link,
				itemSelector: '.' + SixTen.params.container + ' ' + SixTen.params.selector,
				loading: {
					finishedMsg: SixTen.params.finished,
					img: SixTen.params.loading,
					msgText: '<em>' + SixTen.params.msg + '</em>',
					speed: 'fast'
				}
			},
			function ( newItems ) {
				var _newItems = $( newItems ).css( {opacity: 0} );
				_newItems.imagesLoaded( function () {
					_container.isotope( 'appended', _newItems );
					_newItems.animate( {opacity: 1} );
				} );
			}
		);
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
		_debounce( function () {
			var stylesheet = 'sixtenpress-isotope-dynamic';
			$( '#' + stylesheet ).remove();
			if ( _selector ) {
				var item = '.' + SixTen.params.container + ' ' + SixTen.params.selector;
				$( 'head' ).append( '<style id="' + stylesheet + '" type="text/css">' + item + ':not(' + _selector + ') { display: none; }</style>' );
			}
		}, 400 );

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
			_selector.push( filters[prop] );
		}
		return _selector.join( '' );
	}

	/**
	 * Delay action after resize
	 * @param func
	 * @param wait
	 * @param immediate
	 * @returns {Function}
	 * @private
	 */
	function _debounce( func, wait, immediate ) {
		var timeout;
		return function() {
			var context = this, args = arguments;
			var later   = function () {
				timeout = null;
				if ( ! immediate ) {
					func.apply( context, args );
				}
			};
			var callNow = immediate && ! timeout;
			clearTimeout( timeout );
			timeout = setTimeout( later, wait );
			if ( callNow ) {
				func.apply( context, args );
			}
		};
	}

	$( document ).ready( function () {
		SixTen.params = typeof SixTenPressIsotope === 'undefined' ? '' : SixTenPressIsotope;

		if ( typeof SixTen.params !== 'undefined' ) {
			SixTen.init();
		}
	} );
})( document, jQuery );
