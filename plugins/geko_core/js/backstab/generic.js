/*
 * "backstab/foo.js"
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
	Backstab.setNamespace( 'Generic', Backstab.Base.extend( {
		
		params: null,
		
		constructor: function( oParams ) {
			
			this.params = {};				// do this for instance
			
			if ( oParams ) {
				$.extend( this.params, oParams );
			}
			
			Backstab.Base.apply( this, arguments );
		},
		
		'get': function( sKey ) {
			return this.params[ sKey ];
		},
		
		'set': function() {
			
			var _this = this;
			
			var iArgLen = arguments.length;
			
			if ( 2 === iArgLen ) {
				
				// assume sKey and mMember were provided
				var sKey = arguments[ 0 ];
				var mValue = arguments[ 1 ];
				
				this.params[ sKey ] = mValue;
				
			} else {
				
				// assume hash of members were provided
				
				var oMembers = arguments[ 0 ];
				
				$.each( oMembers, function( k, v ) {
					_this.set( k, v );
				} );
				
			}
			
			return this;
		},
		
		unset: function( sKey ) {
			
			if ( this.params[ sKey ] ) {
				delete this.params[ sKey ];
			}
			
			return this;
		}
		
	} ) );
	
	
} ).call( this );

