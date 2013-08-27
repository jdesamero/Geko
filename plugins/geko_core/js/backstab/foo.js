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
			alrt += ' (this.options.foo -> ' + _.stringify( this.options.foo ) + ')';
			alrt += ' (msg -> ' + _.stringify( msg ) + ')';
			
			alert( alrt );
			
			this.trigger( 'foo', this, msg );
			return this;
		},
		
		baz: function( msg ) {
			this.trigger( 'baz', this, msg );
			return 'Calling baz: ' + this.options.baz + ' ' + msg;
		}
		
	} );
	
} ).call( this );