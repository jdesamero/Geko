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
	
	
	// target is an element or view
	var resolveTarget = function( target, sel ) {
		
		if ( 'this' === sel ) {
			return target;
		}
		
		// check if target is view
		if ( target.$el ) {
			target = target.$el;		// target is now the $el of the view
		}
		
		if ( sel && ( 'function' === $.type( target.find ) ) ) {
			var subTarget = target.find( sel );
			if ( subTarget.length > 0 ) return subTarget;
		}
		
		return target;	
	};
	
	//
	var getDelegateSelector = function( delegate, prop, evt ) {
		
		if (
			( delegate[ prop ] ) && 
			( typeof delegate[ prop ][ evt ] !== 'undefined' )
		) {
			return delegate[ prop ][ evt ];
		};
		
		return null;
	};

	
	
	
	
	
	// add new methods and properties
	var oOpts = {
		
		setup: function() {
			_.mergeValues( 'data', this, arguments[ 0 ] );
		},
		
		_ensureElement: function() {
			
			if ( !this.el && this.createElement ) {
				
				var eElem = this.createElement();
				
				if ( 'object' == $.type( eElem ) ) {
					this.el = this.createElement();
				}
			}
			
			return Backbone.View.prototype._ensureElement.apply( this, arguments );
		},
		
		delegateEvents: function() {
			return Backbone.View.prototype.delegateEvents.apply( this, arguments );
		},
		
		extractModelValues: function( oModel, eElem, oParams ) {
			
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
			
		},
		
		getModelDataFromElem: function( oModel, eElem, oParams ) {
			
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
		},
		
		setModelValues: function( oModel, eElem, oParams, oFields ) {

			if ( !oModel ) oModel = this.model;
			if ( !eElem ) eElem = this.$el;
			if ( !oParams ) oParams = {};
			if ( !oFields ) oFields = oModel;
			
			// ---------------------------------------------------------------------------------
			
			oModel.set( this.getModelDataFromElem( oFields, eElem, oParams ) );
		}
		
	};
	
	
	// static properties	
	var oStaticProps = {
		
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
		
		
		// other
		
		// *obj* is first the *properties* value specified in Backbone.View.extend()
		modifyProps: function( obj ) {

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
					
					_.mergeValues( 'data', this, arguments[ 0 ] );
					
					var res = init.apply( this, arguments );
					_this.bindDelegates( this );
					
					return res;
				};
				
				obj.initialize = initWrap;
			}
			
			
			return obj;
		},
		
				
		// bind delegate events to the view object
		bindDelegates: function( view ) {
			
			//
			var props = this._props;
			
			if ( 'array' !== $.type( props ) ) {
				// find view descendants with an "on()" method so we can trigger them from the "events" hash
				props = _.descendantsWithMethod( view, 'on', this._maxLevels );
			}
			
			// find view descendants with an "each()" method so we can iterate through members when initializing
			var hasEach = _.descendantsWithMethod( view, 'each', this._maxLevels );
			
			var oDelegate = {};
			
			
			// go through the "events" hash and bind the view event to the matching descendants
			if ( view.events ) {
				
				$.each( view.events, function( evtsel, method ) {
										
					var func = null;
					
					//// obtain the event handler
					
					if ( 'function' === $.type( method ) ) {
						func = method;
					} else if (
						( 'string' === $.type( method ) ) && 
						( 'function' === $.type( view[ method ] ) )
					) {
						func = view[ method ];
					}
					
					//// parse the event/selector
					
					var evt = null, sel = '';
					
					var iPos = evtsel.indexOf( ' ' );
					
					if ( -1 !== iPos ) {
						evt = $.trim( evtsel.substring( 0, iPos ) );
						sel = $.trim( evtsel.substring( iPos ) );
					} else {
						// selector was empty
						evt = evtsel;
					}
					
					
					// look at the event and see if there is a matching view property
					var prop = _.beginsWith( evt, props, true );
					
					// selector "this" is a special case, referring to the view itself
					if ( !prop && ( 'this' === sel ) ) {
						prop = 'this';
					}
					
					
					if ( func && prop ) {
						
						if ( !oDelegate[ prop ] ) oDelegate[ prop ] = {};
						
						//// extract the actual event name
						
						var sMatch = '%s:'.printf( prop );
						var iPos2 = evt.indexOf( sMatch );
						
						var sDelegateEvent = '';
						
						if ( -1 !== iPos2 ) {
							
							// match was found
							sDelegateEvent = evt.substring( sMatch.length );
						} else {
							
							// event has no prefix, "this" (the view) is probably the target
							sDelegateEvent = evt;
						}
						
						oDelegate[ prop ][ sDelegateEvent ] = sel;
						
						
						//// resolve the main target
						
						var mainTarget = view.$el;			// default target
						
						
						if ( 'this' === sel ) {
							
							mainTarget = view;
							
							
							//// re-wrap so that event bound to backbone object behaves similar to jQuery
							
							var funcWrap = func;
							
							func = function() {
								
								var ag = $.makeArray( arguments );
								var firstAg = ag[ 0 ];
								
								if ( 'array' !== $.type( firstAg ) ) {
									firstAg = [];
								}
								
								// http://api.jquery.com/category/events/event-object/
								// target: The DOM element (backbone object) that initiated the event.
								// relatedTarget: The other DOM element (backbone object) involved in the event, if any
								
								// parse evt as follows:
								// 	  'reveal'					= { type: 'reveal' }
								// 	  'collection:add'			= { type: 'add', namespace: 'collection' }
								//	  'model:change:status'		= { type: 'change:status', namespace: 'model' }
								//	  'data.foo:doStuff'		= { type: 'doStuff', namespace: 'data.foo' }
								
								var sType = '', sNamespace = null;
								var iColonPos = evt.indexOf( ':' );
								
								if ( -1 !== iColonPos ) {
									sNamespace = $.trim( evt.substring( 0, iColonPos ) );
									sType = $.trim( evt.substring( iColonPos + 1 ) );
								} else {
									sType = evt;
								}
								
								var oBackboneEvent = {
									type: sType,
									stopPropagation: function() {}
								};
								
								if ( sNamespace ) {
									oBackboneEvent[ 'namespace' ] = sNamespace;
								}
								
								// check for a "trigger event" object, which usually is the last argument
								var oCheck = firstAg[ firstAg.length - 1 ];
								
								if ( oCheck && oCheck.backstabTriggerEvent ) {
									
									// remove the event tracker from the argument list
									oCheck = firstAg.pop();
									
									// remove our hacky flag and merge the rest with our made up event object
									delete oCheck.backstabTriggerEvent;
									_.extend( oBackboneEvent, oCheck );
								}
								
								firstAg.unshift( oBackboneEvent );
								
								return funcWrap.apply( view, firstAg );
							};
							
							
						} else {
							
							// main target is a jQuery element
							
							if ( view[ prop ] && view[ prop ].jquery ) {
								
								mainTarget = view[ prop ];
								evt = sDelegateEvent;
							}
							
							func = _.bind( func, view );
							
						}
						
						// resolve the target to trigger the event on
						
						var target = resolveTarget( mainTarget, sel );
						
						// ensure target has an "on" method
						if ( 'function' === $.type( target.on ) ) {
							target.on( evt, func );
						}
						
						
					}
					
				} );
			}
			
			
			// set listener
			$.each( props, function( i, prop ) {
				
				var propObj = _.descendant( view, prop );
				
				if ( propObj && propObj.on && !propObj.jquery ) {
					
					// delegate to the corresponding element
					
					propObj.on( 'all', function() {
												
						var args = $.makeArray( arguments );
						var evt = args.shift();
						var event = '%s:%s'.printf( prop, evt );
						var sel = getDelegateSelector( oDelegate, prop, evt );
						
						// track objects pertinent to event being triggered
						var oTriggerEvent = {
							backstabTriggerEvent: true,				// hacky way to identify this object
							target: propObj
						};
						
						// append our event tracker object to the end of the argument list
						args.push( oTriggerEvent );
						
						var target = resolveTarget( view, sel );
						target.trigger( event, args );
									
					} );
					
					// trigger now
					if ( _.contains( hasEach, prop ) ) {
						
						propObj.each( function() {
							
							var args2 = $.makeArray( arguments );
							var evt = 'initialize';
							var sel = getDelegateSelector( oDelegate, prop, evt );

							// track objects pertinent to event being triggered
							var oTriggerEvent = {
								backstabTriggerEvent: true,				// hacky way to identify this object
								targetParent: propObj
							};
							
							if ( args2[ 0 ] ) {
								oTriggerEvent.target = args2[ 0 ];
							}
							
							// append our event tracker object to the end of the argument list
							args2.push( oTriggerEvent );
							
							var target = resolveTarget( view, sel );
							target.trigger( '%s:%s'.printf( prop, evt ), args2 );
							
						} );
					}
					
				}
			} );
			
		}
		
	};
	
	
	
	//
	Backstab.createConstructor( 'View', oOpts, oStaticProps, Backbone.View );
	
	
} ).call( this );