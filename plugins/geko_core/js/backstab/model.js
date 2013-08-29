/*
 * "backstab/model.js"
 * https://github.com/jdesamero/Backstab
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * depends on "backstab/core.js"
 */

( function() {
	
	var getTarget = function( elem, params, prop ) {
		
		var sel = null, fcont = null, target = null, _target = null;
		
		if ( params && params[ prop ] ) {
			
			var propParams = params[ prop ];
			
			if ( propParams.selector ) {
				sel = propParams.selector;
			}
			
			if ( propParams.contents ) {
				fcont = propParams.contents;
			}
		}
		
		// find target
		
		if ( sel ) {
			
			target = elem.find( sel );
			if ( target.length > 0 ) _target = target;
			
		} else {

			// try these selectors
			var sels = [ '.' + prop, '#' + prop ];
			
			$.each( sels, function( i, sel2 ) {
				target = elem.find( sel2 );
				if ( target.length > 0 ) {
					_target = target;
					return false;
				}
			} );
			
		}
		
		return [ _target, fcont ];
	};
	
	var Backbone = this.Backbone;
	var Backstab = this.Backstab;
	
	//
	Backstab.createConstructor( 'Model', {}, {
				
		// apply enhancements to Backbone.Model
		latchToBackbone: function() {
			
			var _this = this;
			
			this._backboneExtend = Backbone.Model.extend;
			
			Backbone.Model.extend = function() {
				var args = $.makeArray( arguments );
				// Add code here to modify args before passing to super
				return _this._backboneExtend.apply( Backbone.Model, args );
			};
			
			return this;
		},
		
		// revert to the original Backbone.Model.extend() method
		unlatchFromBackbone: function() {
			Backbone.Model.extend = this._backboneExtend;
			return this;
		},
		
		// wrapper for Backbone.Model.extend() which applies enhancements to events
		extend: function() {
			var args = $.makeArray( arguments );
				// Add code here to modify args before passing to super
			return Backbone.Model.extend.apply( Backbone.Model, args );
		}
		
	} );
	
	/* ------------------------------------------------------------------------------------------ */
	
	// model bindings
	
	_.extend( Backbone.Model.prototype, {
		
		populateElem: function( elem, params ) {
			
			var model = this;

			var setTargetValue = function( target, val ) {
				
				var tag = target.prop( 'tagName' ).toLowerCase();
				
				// TO DO!!!!!!
				if ( 'input' == tag ) {
					target.val( val );
				} else {
					target.html( val );						
				}
			};
			
			// -------------------------------------------------------------------------------------
			
			if ( model && elem ) {
				var data = model.toJSON();
				$.each( data, function( prop, val ) {
					
					var res = getTarget( elem, params, prop );
					var _target = res[ 0 ];
					var fcont = res[ 1 ];
					
					// populate
					if ( _target ) {
						if ( fcont ) fcont( _target, val );
						else setTargetValue( _target, val );
					}
					
				} );
			}		
		}
		
	} );
	
	
	
	/*
	 * reciprocal functionality for model.populateElem( $el )
	 */
	;( function ( $ ) {
		
		$.fn.extractData = function( model, params ) {
			
			var getTargetValue = function( target ) {
				
				var tag = target.prop( 'tagName' ).toLowerCase();
				
				// TO DO!!!!!!
				if ( 'input' == tag ) {
					return target.val();
				}
				
				return target.html();
			};
			
			// -----------------------------------------------------------------------------------------
			
			var elem = $( this );
			var ret = {};
			
			var data;
			if ( model.toJSON ) data = model.toJSON();
			else data = model;
			
			$.each( data, function( prop, oldval ) {
				
				var res = getTarget( elem, params, prop );
				var _target = res[ 0 ];
				var fcont = res[ 1 ];
				
				// populate
				if ( _target ) {
					if ( fcont ) ret[ prop ] = fcont( _target );
					else ret[ prop ] = getTargetValue( _target );
				}
				
			} );
			
			return ret;	
		};
		
		$.fn.populateModel = function( model ) {
			model.set( $( this ).extractData( model ) );
		};
		
	} )( this.jQuery );
	
	
	
} ).call( this );



