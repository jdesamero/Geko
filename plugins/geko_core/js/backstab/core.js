/*
 * "backstab/core.js"
 * https://github.com/jdesamero/Backstab
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * depends on "backstab/underscore.js"
 */

( function() {
	
	this.Backstab = {};
	
	var $ = this.jQuery;
	var Backstab = this.Backstab;
	
	//
	Backstab.extend = function( protoProps, staticProps ) {
		
		// alert( this );
		
		var parent = this;
		var child;
		
		// The constructor function for the new subclass is either defined by you
		// (the "constructor" property in your `extend` definition), or defaulted
		// by us to simply call the parent's constructor.
		if ( protoProps && _.has( protoProps, 'constructor' ) ) {
			child = protoProps.constructor;
		} else {
			child = function() {
				return parent.apply( this, arguments );
			};
		}
		
		// Add static properties to the constructor function, if supplied.
		_.extend( child, parent, staticProps );
		
		// Set the prototype chain to inherit from `parent`, without calling
		// `parent`'s constructor function.
		var Surrogate = function() {
			this.constructor = child;
		};
		
		Surrogate.prototype = parent.prototype;
		child.prototype = new Surrogate;
		
		// Add prototype properties (instance properties) to the subclass,
		// if supplied.
		if ( protoProps ) _.extend( child.prototype, protoProps );
		
		// Set a convenience property in case the parent's prototype is needed
		// later.
		child.__super__ = parent.prototype;
		
		return child;
		
	};
	
	// in case we want to add enhancements Backbone.Events in the future
	Backstab.Events = Backbone.Events;
	
	// convenience latcher for subclasses
	Backstab.latchToBackbone = function() {
		
		var _this = this;
		var args = $.makeArray( arguments );
		
		$.each( args, function( i, sub ) {
			if ( _this[ sub ] && _this[ sub ].latchToBackbone ) {
				_this[ sub ].latchToBackbone();
			}
		} );
		
		return this;
	};
	
	// Backstab constructor template
	Backstab.createConstructor = function( namespc, opts, staticProps, cons ) {
		
		//
		if ( !cons ) {
			
			cons = function() {
				
				if ( this.setup ) {
					this.setup.apply( this, arguments );
				}
				
				if ( this.initialize ) {
					this.initialize.apply( this, arguments );
				}
	
				if ( this.afterInit ) {
					this.afterInit.apply( this, arguments );
				}
			};
			
			//
			_.extend( cons.prototype, Backstab.Events, opts );
			
			cons._namespace = namespc;
			
			cons.extend = Backstab.extend;
			
			cons.latchToBackbone = function() {
				Backbone[ namespc ] = this;
				return this;
			};
		}
				
		// latch to Backstab namespace
		Backstab[ namespc ] = cons;
		// alert( 'created constructor: ' + cons._namespace );
		
		if ( staticProps ) {
			_.extend( Backstab[ namespc ], staticProps );
		}
		
		return cons;
	};
	
} ).call( this );