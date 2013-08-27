/*
 * "backstab/dispatcher.js"
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
	Backstab.createConstructor( 'Dispatcher' );

	// create a global instance
	var Dispatcher = Backstab.Dispatcher.extend();
	Backstab.Dispatcher.global = new Dispatcher();
	
	
} ).call( this );
