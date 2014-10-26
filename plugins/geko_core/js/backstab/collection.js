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
	
	
	
	// add new methods and properties
	var oOpts = {
		
		setup: function() {
			_.mergeValues( 'data', this, arguments[ 1 ] );
		},
		
		transfer: function( oModel, oDestCollection ) {
			
			if ( this.contains( oModel ) ) {
				this.remove( oModel );
				oDestCollection.add( oModel );
			}
			
			return this;
		}

	};
	
	
	
	//
	Backstab.createConstructor( 'Collection', oOpts, null, Backbone.Collection );
	
	
	
} ).call( this );



