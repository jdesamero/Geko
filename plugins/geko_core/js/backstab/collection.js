/*
 * "backstab/collection.js"
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
	
	//
	Backstab.createConstructor( 'Collection', {}, {
				
		// apply enhancements to Backbone.Collection
		latchToBackbone: function() {
			
			var _this = this;
			
			this._backboneExtend = Backbone.Collection.extend;
			
			Backbone.Collection.extend = function() {
				
				var args = $.makeArray( arguments );
				
				if ( args[ 0 ] ) {
					args[ 0 ] = _this.modifyCollectionProps( args[ 0 ] );
				}
				
				return _this._backboneExtend.apply( Backbone.Collection, args );
			};
			
			return this;
		},
		
		// revert to the original Backbone.Collection.extend() method
		unlatchFromBackbone: function() {
			Backbone.Collection.extend = this._backboneExtend;
			return this;
		},
		
		
		//
		modifyCollectionProps: function( obj ) {
			
			var _this = this;
			
			// make sure there is an initialize() method
			if ( !obj.initialize ) {
				obj.initialize = function() { };
			}
			
			
			// execute bindDelegates() after calling initialize()
			if ( 'function' === $.type( obj.initialize ) ) {
				
				var init = obj.initialize;
				var initWrap = function() {
					
					var oArg2 = arguments[ 1 ];
					if ( oArg2 && oArg2.data ) {
						this.data = oArg2.data;
					}
					
					var res = init.apply( this, arguments );
					
					return res;
				};
				
				obj.initialize = initWrap;
			}
			
			return obj;
		},
		
		
		// wrapper for Backbone.Collection.extend() which applies enhancements to events
		extend: function() {
			
			var args = $.makeArray( arguments );
			
			if ( !args[ 0 ] ) {
				args[ 0 ] = {};
			}
			
			args[ 0 ] = this.modifyCollectionProps( args[ 0 ] );
			
			return Backbone.Collection.extend.apply( Backbone.Collection, args );
		}
		
	} );
	
	
	/* ------------------------------------------------------------------------------------------ */
	
	// collection bindings
	
	_.extend( Backbone.Collection.prototype, {
		
		transfer: function( oModel, oDestCollection ) {
			
			if ( this.contains( oModel ) ) {
				this.remove( oModel );
				oDestCollection.add( oModel );
			}
			
			return this;
		}
		
	} );
	
	
} ).call( this );



