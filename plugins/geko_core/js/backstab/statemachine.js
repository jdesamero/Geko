/*
 * "backstab/state-machine.js"
 * https://github.com/jdesamero/Backstab
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * depends on "backstab/core.js"
 * depends on "dependencies/state-machine.js"
 * 		https://github.com/jakesgordon/javascript-state-machine
 */
 
( function() {
	
	var $ = this.jQuery;
	var Backbone = this.Backbone;
	var Backstab = this.Backstab;
	
	//
	Backstab.createConstructor( 'StateMachine', {
		
		setup: function() {
			
			var _this = this;
			
			// initial, events, callbacks, target, error
			
			StateMachine.create( {
				
				target: _this,
				
				initial: _this.initial,
				events: _this.events,
				callbacks: _this.callbacks,
				
				error: function ( event, fromstate, tostate, errorArgs, errorCode, errorMessage ) {
					
					var trigger = 'error';
					
					var state = {
						event: event,
						fromstate: fromstate,
						tostate: tostate,
						trigger: trigger,
						error: {
							args: errorArgs,
							code: errorCode,
							message: errorMessage
						}
					};
										
					this.fire( state, 'event', [ state ] );
					
					return false;
				}

			} );
			
			this.on( 'all', function() {
				
				var args = $.makeArray( arguments );
				
				// fShowMe( args );
				var method = args.shift();
				
				// alert( _this[ method ] );
				
				if ( _this[ method ] && ( 'fire' != method ) && ( 'error' != method ) ) {
					var methodArgs = args.shift();
					// what happens to leftover "args" ???
					_this[ method ].apply( _this, methodArgs );
				}
			} );
			
		},
		
		
		initial: null,
		events: null,
		callbacks: null,
		
		
		_prefix: '',
		_hold: false,
		
		
		
		hold: function() {
			this._hold = true;
		},
		
		release: function() {
			this._hold = false;
			if ( this.transition ) {
				this.transition();
			}
		},
		
		//
		formatArgs: function( args ) {
			
			var event = args.shift();
			var fromstate = args.shift();
			var tostate = args.shift();
			
			var state = {
				event: event,
				fromstate: fromstate,
				tostate: tostate,
				trigger: null
			};
			
			args.unshift( state );
			
			return args;
		},				
		
		// event, from, to, arg1, ... argn
		
		// fired before any event
		onbeforeevent: function() {
			
			var trigger = 'before';
			
			var args = this.formatArgs( $.makeArray( arguments ) );
			args[ 0 ].trigger =  trigger;
						
			this.fire( args[ 0 ], 'event', args );
		},
		
		// fired when leaving any state
		onleavestate: function() {
			
			var trigger = 'leave';
			
			var args = this.formatArgs( $.makeArray( arguments ) );
			args[ 0 ].trigger =  trigger;
			
			this.fire( args[ 0 ], 'fromstate', args );
			
			if ( this._hold ) return StateMachine.ASYNC;
		},
		
		// fired when entering any state
		onenterstate: function() {
			
			var trigger = 'enter';
			
			var args = this.formatArgs( $.makeArray( arguments ) );
			args[ 0 ].trigger =  trigger;
			
			this.fire( args[ 0 ], 'tostate', args );
		},
		
		// fired after any event
		onafterevent: function() {

			var trigger = 'after';
			
			var args = this.formatArgs( $.makeArray( arguments ) );
			args[ 0 ].trigger =  trigger;
			
			this.fire( args[ 0 ], 'event', args );
		},
		
		
		
		//
		fire: function( state, targetname, args ) {
			
			var _this = this;
			
			var trigger = state.trigger;
			var target = state[ targetname ];
			
			// fShowMe( [ state, targetname, args ] );
			// fShowMe( trigger + ':' + target );
			
			var _trigger = function( evt, args ) {
				args.unshift( evt );
				_this.trigger.apply( _this, args );
				args.shift();
			};
			
			_trigger( this._prefix + 'fire', args );
			_trigger( this._prefix + trigger, args );
			_trigger( this._prefix + trigger + ':' + target, args );
						
		}
		
	} );
	
	
} ).call( this );