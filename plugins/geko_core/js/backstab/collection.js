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
				// Add code here to modify args before passing to super
				return _this._backboneExtend.apply( Backbone.Collection, args );
			};
			
			return this;
		},
		
		// revert to the original Backbone.Collection.extend() method
		unlatchFromBackbone: function() {
			Backbone.Collection.extend = this._backboneExtend;
			return this;
		},
		
		// wrapper for Backbone.Collection.extend() which applies enhancements to events
		extend: function() {
			var args = $.makeArray( arguments );
				// Add code here to modify args before passing to super
			return Backbone.Collection.extend.apply( Backbone.Collection, args );
		}
		
	} );
	
} ).call( this );



