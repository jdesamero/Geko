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
	
	//// set up the Backstab namespace and descendants
	
	var $ = this.jQuery;
	var Backstab = this.Backstab;
	
	if ( !Backstab ) Backstab = {};
	
	$.extend( Backstab, {
		
		setNamespace: function() {
			Geko._setNamespace.apply( Backstab, arguments );
		}
		
	} );
	
	
	//// set up Base class
	
	Backstab.setNamespace( 'Base', function() {
		
		this.sharedSetup();					// from Backstab.Shared
		
		if ( this.initialize ) {
			this.initialize.apply( this, arguments );
		}
		
	} );
	
	
	// the Backbone.Model.extend can be used universally
	// Backbone.Collection.extend, Backbone.View.extend, etc. references the same method
	Backstab.Base.extend = Backbone.Model.extend;
	
	
	// mix-in Backbone.Events
	$.extend( Backstab.Base.prototype, Backbone.Events );
	
	
	//// set up Shared mix-in namespace
	
	Backstab.setNamespace( 'Shared', {
		
		data: null,
		
		sharedSetup: function() {
			
			// ensure that the data property is instance specific
			
			if ( this.data ) {
				
				// make a copy
				this.data = Object.create( this.data );
				
			} else {
				
				this.data = {};				
			}
			
		}
		
	} );
	
	
	// mix-in Backstab.Shared
	$.extend( Backstab.Base.prototype, Backstab.Shared );
	
	
	//// set global
	
	this.Backstab = Backstab;
	
	
} ).call( this );