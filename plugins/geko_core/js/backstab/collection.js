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
		
		beforeInit: function() {
			_.mergeValues( 'data', this, arguments[ 1 ] );
		},
		
		transfer: function( oModel, oDestCollection ) {
			
			if ( this.contains( oModel ) ) {
				this.remove( oModel );
				oDestCollection.add( oModel );
			}
			
			return this;
		},
		
		createPart: function( sKey, oPartedCollection, oFindParams ) {
			
			var oPart = new this.constructor( this.where( oFindParams ) );
			
			var oModelParams = {};
			oModelParams[ sKey ] = oPart;
			_.extend( oModelParams, oFindParams );
			
			oPartedCollection.add( new Backstab.Model( oModelParams ) );
			
			return oPart;
		},
		
		findAndGet: function( sGetKey, oFindParams ) {
			return this.findWhere( oFindParams ).get( sGetKey );
		}
		
	};
	
	
	
	//
	Backstab.createConstructor( 'Collection', oOpts, null, Backbone.Collection );
	
	
	
} ).call( this );



