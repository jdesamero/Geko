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
	
	//
	Backstab.createConstructor( 'Foo', {
				
		setup: function( opts ) {
			this.options = $.extend( {}, opts );
			return this;
		},
		
		foo: function( msg ) {
			
			var alrt = 'Calling Foo!!!';
			alrt += ' (this.options.foo -> %s)'.printf( _.stringify( this.options.foo ) );
			alrt += ' (msg -> %s)'.printf( _.stringify( msg ) );
			
			alert( alrt );
			
			this.trigger( 'foo', this, msg );
			return this;
		},
		
		baz: function( msg ) {
			this.trigger( 'baz', this, msg );
			return 'Calling baz: %s %s'.printf( this.options.baz, msg );
		}
		
	} );
	
} ).call( this );

