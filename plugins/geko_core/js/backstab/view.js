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
	
	var $ = this.jQuery;
	var Backbone = this.Backbone;
	var Backstab = this.Backstab;
	
	
	var getTarget = function( elem, params, prop ) {
		
		var sel = null, fcont = null, target = null, _target = null, defer = null;
		
		if ( params && params[ prop ] ) {
			
			var propParams = params[ prop ];
			
			if ( propParams.selector ) {
				sel = propParams.selector;
			}
			
			if ( propParams.contents ) {
				fcont = propParams.contents;
			}

			if ( propParams.defer ) {
				defer = propParams.defer;
			}
		}
		
		// find target
		
		if ( sel ) {
			
			target = elem.find( sel );
			if ( target.length > 0 ) _target = target;
			
		} else {
			
			// try these selectors
			var sels = [ '.%s'.printf( prop ), '#%s'.printf( prop ) ];
			
			$.each( sels, function( i, sel2 ) {
				target = elem.find( sel2 );
				if ( target.length > 0 ) {
					_target = target;
					return false;
				}
			} );
			
		}
		
		return [ _target, fcont, defer ];
	};
	
	var setTargetValue = function( target, val, cb ) {
		
		if ( target ) {
			
			if ( cb ) {
				
				cb.call( this, target, val );
				
			} else {
				
				var tag = target.prop( 'tagName' ).toLowerCase();
				
				if ( -1 !== $.inArray( tag, [ 'input', 'textarea', 'select' ] ) ) {
					target.val( val );
				} else {
					target.html( val );						
				}
			}
		}
		
	};
	
	var getTargetValue = function( target ) {
		
		var tag = target.prop( 'tagName' ).toLowerCase();
		
		// TO DO!!!!!!
		if ( -1 !== $.inArray( tag, [ 'input', 'textarea', 'select' ] ) ) {
			return target.val();
		}
		
		return target.html();
	};

	
	
	
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
					
					if ( evtsel != orig ) {
						obj.events[ evtsel ] = copyMethod( method );
						delete obj.events[ orig ];
					}
					
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
				
				obj.events = evt;
			}
			
			
			
			// make sure there is an initialize() method
			if ( !obj.initialize ) {
				obj.initialize = function() { };
			}
			
			// execute bindDelegates() after calling initialize()
			if ( 'function' === $.type( obj.initialize ) ) {
				
				var init = obj.initialize;
				var initWrap = function() {
					
					var oArg1 = arguments[ 0 ];
					if ( oArg1 && oArg1.data ) {
						this.data = oArg1.data;
					}
					
					var res = init.apply( this, arguments );
					_this.bindDelegates( this );
					
					return res;
				};
				
				obj.initialize = initWrap;
			}
			
			
			obj._ensureElement = function() {
				
				if ( !this.el && this.createElement ) {
					
					var eElem = this.createElement();
					
					if ( 'object' == $.type( eElem ) ) {
						this.el = this.createElement();
					}
				}
				
				return Backbone.View.prototype._ensureElement.apply( this, arguments );
			};
			
			obj.delegateEvents = function() {
				return Backbone.View.prototype.delegateEvents.apply( this, arguments );
			};
			
			obj.extractModelValues = function( oModel, eElem, oParams ) {
				
				var _this = this;
				
				if ( !oModel ) oModel = this.model;
				if ( !eElem ) eElem = this.$el;
				if ( !oParams ) oParams = {};
				
				// ---------------------------------------------------------------------------------
				
				if ( oModel && eElem ) {
					
					var data;
					
					if ( oModel.toJSON ) {
						data = oModel.toJSON();
					} else {
						data = oModel;
					}
					
					$.each( data, function( prop, val ) {
						
						var sTargetProp = prop;
						if ( _this.elementPrefix ) {
							sTargetProp = '%s%s'.printf( _this.elementPrefix, prop );
						}
						
						var res = getTarget( eElem, oParams, sTargetProp );
						var _target = res[ 0 ];
						var fcont = res[ 1 ];
						var defer = res[ 2 ];
						
						if ( defer ) {
							
							if ( defer.contents ) {
								defer.contents.call( _this, _target, val );
							}
							
							var funtil = defer.until;
							
							if ( funtil ) {
								var itvl, deferTil = function() {
									if ( funtil() ) {
										setTargetValue.call( _this, _target, val, fcont );
										clearInterval( itvl );
									}
								};
								
								itvl = setInterval( deferTil, 100 );
							}
							
						} else {
							setTargetValue.call( _this, _target, val, fcont );
						}
						
					} );
				}						
				
			};
			
			obj.getModelDataFromElem = function( oModel, eElem, oParams ) {
				
				var _this = this;
				
				if ( !oModel ) oModel = this.model;
				if ( !eElem ) eElem = this.$el;
				if ( !oParams ) oParams = {};
				
				// ---------------------------------------------------------------------------------
				
				var ret = {};
				var data = {};
				var oFormat;
				
				if ( oModel.extractFields ) {
					
					$.each( oModel.extractFields, function( i, v ) {
						data[ v ] = null;
					} );
					
				} else if ( oModel.toJSON ) {
					data = oModel.toJSON();
				} else {
					data = oModel;
				}
				
				if ( oModel.fieldFormats ) {
					oFormat = oModel.fieldFormats;
				}
				
				$.each( data, function( prop, oldval ) {
					
					var sTargetProp = prop;
					if ( _this.elementPrefix ) {
						sTargetProp = '%s%s'.printf( _this.elementPrefix, prop );
					}
					
					var res = getTarget( eElem, oParams, sTargetProp );
					var _target = res[ 0 ];
					var fcont = res[ 1 ];
					
					// populate
					if ( _target ) {
						
						var mValue = ( fcont ) ? fcont( _target ) : getTargetValue( _target ) ;
						
						// apply formatting
						if ( oFormat && oFormat[ prop ] ) {
							
							var sProp = oFormat[ prop ];
							
							if ( ( 'int' === sProp ) || ( 'number' === sProp ) ) {
								mValue = parseInt( mValue );
							} else if ( 'float' === sProp ) {
								mValue = parseFloat( mValue );							
							} else if ( 'boolean' === sProp ) {
								mValue = ( mValue ) ? true : false ;
							}
							
						}
						
						ret[ prop ] = mValue;
					}
					
				} );
				
				return ret;
			};
			
			obj.setModelValues = function( oModel, eElem, oParams, oFields ) {

				if ( !oModel ) oModel = this.model;
				if ( !eElem ) eElem = this.$el;
				if ( !oParams ) oParams = {};
				if ( !oFields ) oFields = oModel;
				
				// ---------------------------------------------------------------------------------
				
				oModel.set( this.getModelDataFromElem( oFields, eElem, oParams ) );
			};
			
			
			return obj;
		},
		
				
		// bind delegate events to the view object
		bindDelegates: function( view ) {
			
			//
			var props = this._props;
			
			if ( 'array' !== $.type( props ) ) {
				
				// find view descendants with an "on()" method so we can trigger them from the "events" hash
				props = _.descendantsWithMethod( view, 'on', this._maxLevels );
				
				// magic handler for view instance
				props.push( 'this' );
			}
			
			// find view descendants with an "each()" method so we can iterate through members when initializing
			var hasEach = _.descendantsWithMethod( view, 'each', this._maxLevels );
			
			var delegate = {};
			
			//// helpers
			
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
			
			// go through the "events" hash and bind the view event to the matching descendants
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
						
						part1 = '%s:'.printf( prop );
						part2 = $.trim( evtsel.substring( part1.length ) );
						pos = part2.indexOf( ' ' );
						
						if ( -1 != pos ) {
							sel = $.trim( part2.substring( pos ) );
							part2 = $.trim( part2.substring( 0, pos ) );
						}
						
						delegate[ prop ][ part2 ] = sel;
						
						evt = $.trim( '%s%s'.printf( part1, part2 ) );
						
						
						bJqueryElem = false;
						bBind = false;
						
						var mainTarget = null;
						
						if ( 'this' === prop ) {
							
							mainTarget = view;
							evt = part2;
							bBind = true;
							
						} else if ( view[ prop ] && view[ prop ].jquery ) {
							
							mainTarget = view[ prop ];
							evt = part2;
							bJqueryElem = true;
							
						} else {
							
							// default target
							mainTarget = view.$el;
						}
						
						
						var target = resolveTarget( mainTarget, sel );
											
						if ( evt ) {
							
							func = _.bind( func, view );
							
							var hasEachProp = _.beginsWith( evt, hasEach );
							if (
								( hasEachProp && ( ( '%s:initialize'.printf( hasEachProp ) ) == evt ) ) || 
								( bJqueryElem )
							) {
								target.on( evt, func );
							}
							
							if ( bBind ) {
								target.bind( evt, func );
							}
							
						}
					}
				} );
			}
			
			// set listener
			$.each( props, function( i, prop ) {
				
				var propObj = _.descendant( view, prop );
				
				if ( propObj && propObj.on ) {
					
					// delegate to the corresponding element
					// IMPORTANT!!! use 'click :first', instead of just 'click'
					// http://japhr.blogspot.ca/2011/09/event-propagation-in-backbonejs.html
					
					propObj.on( 'all', function() {
												
						var args = $.makeArray( arguments );
						var evt = args.shift();
						var event = '%s:%s'.printf( prop, evt );
						var sel = getDelegateSelector( prop, evt );
						
						if ( null !== sel ) {
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
								target.trigger( '%s:%s'.printf( prop, evt ), args2 );
							}
							
						} );
					}
					
				}
			} );
			
		},
		
		// wrapper for Backbone.View.extend() which applies enhancements to events
		extend: function() {
			
			var args = $.makeArray( arguments );
			
			if ( !args[ 0 ] ) {
				args[ 0 ] = {};
			}
			
			args[ 0 ] = this.modifyViewProps( args[ 0 ] );
			
			return Backbone.View.extend.apply( Backbone.View, args );
		}
		
	} );
	
	
} ).call( this );