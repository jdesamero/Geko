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
	
	var $ = this.jQuery;
	var Backbone = this.Backbone;
	var Backstab = this.Backstab;
	
	
	
	//// main
	
	Backstab.setNamespace( 'Dispatcher', Backstab.Base.extend( {
		
	} ) );
	
	Backstab.Dispatcher.global = new Backstab.Dispatcher();
	
	
	
} ).call( this );
