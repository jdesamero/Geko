/*
 * "backstab/view.js"
 * https://github.com/jdesamero/Backstab
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * depends on "backstab/core.js"
 */

( function() {
	
	var Backbone = this.Backbone;
	var Backstab = this.Backstab;
	
	//
	Backstab.createConstructor( 'View', {}, {
		
		//// properties
		
		_props: null,
		_maxLevels: 3,					// maximum number of descendant levels to traverse
		_backboneExtend: null,			// reference to the orginal Backbone.View.extend() method
		
		
		//// methods
		
		// accessors
		
		//
		setProps: function( props ) {
			this._props = props;
			return this;
		},
		
		setMaxLevels: function( maxLevels ) {
			this._maxLevels = parseInt( maxLevels );
			return this;
		},
		
		// apply enhancements to Backbone.View
		latchToBackbone: function() {
			
			var _this = this;
			
			this._backboneExtend = Backbone.View.extend;
			
			Backbone.View.extend = function() {
				var args = $.makeArray( arguments );
				if ( args[ 0 ] ) {
					args[ 0 ] = _this.modifyViewProps( args[ 0 ] );
				}
				return _this._backboneExtend.apply( Backbone.View, args );
			};
			
			return this;
		},
		
		// revert to the original Backbone.View.extend() method
		unlatchFromBackbone: function() {
			Backbone.View.extend = this._backboneExtend;
			return this;
		},
		
		// other
		
		// *obj* is first the *properties* value specified in Backbone.View.extend()
		modifyViewProps: function( obj ) {

			var _this = this;
			
			//
			var copyMethod = function( method ) {
				if ( 'array' === $.type( method ) ) {
					return method.slice( 0 );	// make a copy
				}
				return method;
			};
			
			//
			if ( obj.events && ( 'object' === $.type( obj.events ) ) ) {
	
				var evt = {}, m = null;
				
				// pass 1, expand curly braces
				$.each( obj.events, function( evtsel, method ) {
					
					var orig = evtsel;
					evtsel = _.expandCurlyShortform( evtsel );
					
					// _.showMe( evtsel );
					
					if ( evtsel != orig ) {
						obj.events[ evtsel ] = copyMethod( method );
						delete obj.events[ orig ];
					}
					
					// if ( evtsel != orig ) _.showMe( evtsel, orig );					
				} );
				
				// pass 2, find semi-colon separated event/selectors and split
				$.each( obj.events, function( evtsel, method ) {
					if ( _.contains( evtsel, ';' ) ) {
						var split = evtsel.split( ';' );
						$.each( split, function( i, v ) {
							obj.events[ $.trim( v ) ] = copyMethod( method );
						} );
						delete obj.events[ evtsel ];
					}
				} );
				
				// pass 3, apply parameters where needed
				$.each( obj.events, function( evtsel, method ) {
					
					if ( 'array' === $.type( method ) ) {
						
						var args = method;
						var methodName = args.shift();
						
						if ( 'function' === $.type( obj[ methodName ] ) ) {
							
							var wrap = function() {
								var ag = $.makeArray( arguments );
								return this[ methodName ].apply( this, ag.concat( args ) );
							};
														
							method = wrap;
						}
					}
					
					evt[ evtsel ] = method;
				} );
				
				// _.showMe( evt );
				obj.events = evt;
			}
			
			
			// make sure there is an initialize() method
			if ( !obj.initialize ) {
				obj.initialize = function() { };
			}
			
			// execute bindDelegates() after calling initialize()
			if ( 'function' === $.type( obj.initialize ) ) {
				var init = obj.initialize;
				var wrap = function() {
					init.apply( this );
					_this.bindDelegates( this );
				};
				obj.initialize = wrap;
			}
			
			return obj;
		},
		
				
		// bind delegate events to the view object
		bindDelegates: function( view ) {
			
			//
			var props = this._props;
			
			if ( 'array' !== $.type( props ) ) {
				props = _.descendantsWithMethod( view, 'on', this._maxLevels );
			}
			
			var hasEach = _.descendantsWithMethod( view, 'each', this._maxLevels );
			
			var delegate = {};
			
			// helpers
			
			//
			var resolveTarget = function( elem, sel ) {
				if ( sel ) {
					var subtgt = elem.find( sel );
					if ( subtgt.length > 0 ) return subtgt;
				}
				return elem;	
			};
			
			//
			var getDelegateSelector = function( prop, evt ) {
				if (
					( delegate[ prop ] ) && 
					( typeof delegate[ prop ][ evt ] !== 'undefined' )
				) {
					return delegate[ prop ][ evt ];
				};
				return null;
			};
			
			//
			if (  view.events ) {
				$.each( view.events, function( evtsel, method ) {
					
					var func = null;
					
					if ( 'function' === $.type( method ) ) {
						func = method;
					} else if (
						( 'string' === $.type( method ) ) && 
						( 'function' === $.type( view[ method ] ) )
					) {
						func = view[ method ];
					}
					
					var prop = _.beginsWith( evtsel, props );
					
					if ( func && prop ) {
						
						if ( !delegate[ prop ] ) delegate[ prop ] = {};
						
						var sel = '', part1 = '', part2 = '', pos = null, evt = null, on = false;
						
						part1 = prop + ':';
						part2 = $.trim( evtsel.substring( part1.length ) );
						pos = part2.indexOf( ' ' );
						if ( -1 != pos ) {
							sel = $.trim( part2.substring( pos ) );
							part2 = $.trim( part2.substring( 0, pos ) );
						}
						
						delegate[ prop ][ part2 ] = sel;
						
						evt = $.trim( part1 + part2 );
						
						var target = resolveTarget( view.$el, sel );
											
						if ( evt ) {
							func = _.bind( func, view );
							var hasEachProp = _.beginsWith( evt, hasEach );
							if ( hasEachProp && ( ( hasEachProp + ':initialize' ) == evt ) ) {
								target.on( evt, func );
							}
						}
					}
				} );
			}
			
			// _.showMe( delegate, props );
			
			$.each( props, function( i, prop ) {
				
				var propObj = _.descendant( view, prop );
				
				if ( propObj && propObj.on ) {
					
					// alert( prop );
					
					// delegate to master element (default), or sub-elements
					propObj.on( 'all', function() {
						
						var args = $.makeArray( arguments );
						var evt = args.shift();
						var event = prop + ':' + evt;
						var sel = getDelegateSelector( prop, evt );
						
						if ( null !== sel ) {
							sel = delegate[ prop ][ evt ];
							var target = resolveTarget( view.$el, sel );
							target.trigger( event, args );
						}
						
					} );
					
					// trigger now
					if ( _.contains( hasEach, prop ) ) {
						propObj.each( function() {
							var args2 = $.makeArray( arguments );
							var evt = 'initialize';
							var sel = getDelegateSelector( prop, evt );
							if ( null !== sel ) {
								var target = resolveTarget( view.$el, sel );
								target.trigger( prop + ':' + evt, args2 );
							}
							// _.showMe( args2 );
						} );
					}
				}
			} );
			
		},
		
		// wrapper for Backbone.View.extend() which applies enhancements to events
		extend: function() {
			var args = $.makeArray( arguments );
			if ( args[ 0 ] ) {
				args[ 0 ] = this.modifyViewProps( args[ 0 ] );
			}
			return Backbone.View.extend.apply( Backbone.View, args );
		}
		
	} );
	
	
} ).call( this );