/**
 * Set up the isotope script and filters.
 * @copyright 2016-2019 Robin Cornett
 */
; ( function ( document, $, undefined ) {
	'use strict';
	var SixTen = {},
		filters = {},
		qsRegex;

	SixTen.init = function () {
		var _container = $( '.' + SixTen.params.container );
		_container.imagesLoaded( function () {
			$.each( $( 'select.filter' ), function () {
				$( this ).val( 'all' );
			} );

			_container.isotope( SixTen.params.isotopeRules );

			if ( SixTen.params.infinite ) {
				var _navSelector = SixTen.params.navigation,
					instance = _container.data( 'isotope' );
				_container.infiniteScroll( {
					path: _navSelector + ' ' + SixTen.params.link,
					append: '.' + SixTen.params.container + ' ' + SixTen.params.selector,
					outlayer: instance,
					hideNav: _navSelector,
				},
					function ( newItems ) {
						var _newItems = $( newItems ).css( { opacity: 0 } );
						_newItems.imagesLoaded( function () {
							_container.isotope( 'appended', _newItems );
							_newItems.animate( { opacity: 1 } );
						} );
					}
				);
			}
			$( window ).on( 'resize.stp', _doIsotope ).triggerHandler( 'resize.stp' );
		} );

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
	function _doIsotope () {
		var _container = $( '.' + SixTen.params.container );
		_container.imagesLoaded( function () {
			_container.isotope( 'layout' );
			$( SixTen.params.selector ).animate( { opacity: 1 } );
		} );
	}

	/**
	 * Filter using an unordered list (buttons)
	 * @param $select
	 * @returns {boolean}
	 * @private
	 */
	function _doFilter ( $select ) {
		var _container = $( '.' + SixTen.params.container ),
			selector = $select.attr( 'data-filter' );
		$( _container ).isotope( { filter: selector } );
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
	function _doSelect ( $select ) {
		var _container = $( '.' + SixTen.params.container ),
			selector = _getSelect( $select );
		_container.isotope( {
			filter: selector
		} );

		return false;
	}

	/**
	 * Get the select class for the filter.
	 * @param $select
	 * @return {string}
	 * @private
	 */
	function _getSelect ( $select ) {
		var group = $select.attr( 'data-filter-group' );
		filters[ group ] = $select.find( ':selected' ).attr( 'data-filter-value' );

		return _combineFilters( filters );
	}

	/**
	 * Combine two select filters
	 * @param filters
	 * @returns {string}
	 * @private
	 */
	function _combineFilters ( filters ) {
		var _selector = [];
		for ( var prop in filters ) {
			_selector.push( filters[ prop ] );
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
	function _debounce ( func, wait, immediate ) {
		var timeout;
		return function () {
			var context = this, args = arguments;
			var later = function () {
				timeout = null;
				if ( !immediate ) {
					func.apply( context, args );
				}
			};
			var callNow = immediate && !timeout;
			clearTimeout( timeout );
			timeout = setTimeout( later, wait );
			if ( callNow ) {
				func.apply( context, args );
			}
		};
	}

	SixTen.params = typeof SixTenPressIsotope === 'undefined' ? '' : SixTenPressIsotope;

	if ( typeof SixTen.params !== 'undefined' ) {
		SixTen.init();
	}
} )( document, jQuery );
