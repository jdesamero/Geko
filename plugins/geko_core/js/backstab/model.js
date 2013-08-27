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
		
		populateElem: function( elem ) {
			
			var model = this;
			
			if ( model && elem ) {
				var data = model.toJSON();
				$.each( data, function( prop, val ) {
					
					// try these selectors
					var sels = [ '.' + prop, '#' + prop ];
					
					$.each( sels, function( i, sel ) {
						var target = elem.find( sel );
						if ( target.length > 0 ) {
							var tag = target.prop( 'tagName' ).toLowerCase();
							if ( 'input' == tag ) {
								target.val( val );
							} else {
								target.html( val );						
							}
							return false;
						}
					} );
					
				} );
			}		
		}
		
	} );
	
} ).call( this );

/*
 * reciprocal functionality for model.populateElem( $el )
 */
;( function ( $ ) {
	
	$.fn.extractData = function( model ) {
		
		var elem = $( this );
		var ret = {};
		
		var data;
		if ( model.toJSON ) data = model.toJSON();
		else data = model;
		
		$.each( data, function( prop, oldval ) {
			
			var newval = null;
			
			// try these selectors
			var sels = [ '.' + prop, '#' + prop ];
			
			$.each( sels, function( i, sel ) {
				var target = elem.find( sel );
				if ( target.length > 0 ) {
					var tag = target.prop( 'tagName' ).toLowerCase();
					if ( 'input' == tag ) {
						newval = target.val();
					} else {
						newval = target.html();						
					}
					ret[ prop ] = newval;
					return false;
				}
			} );
			
		} );
		
		return ret;	
	};
	
	$.fn.populateModel = function( model ) {
		model.set( $( this ).extractData( model ) );
	};
	
} )( jQuery );


