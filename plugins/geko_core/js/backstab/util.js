/*
 * "backstab/underscore.js"
 * https://github.com/jdesamero/Backstab
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * Enhancements to "Underscore.js" for use by Backstab
 */

( function() {
	
	var _ = this._;
	var $ = this.jQuery;
	var Backstab = this.Backstab;
	
	
	
	//// main
	
	Backstab.setNamespace( 'Util', {
		
		//// additions
		
		consoleError: function( msg ) {
			console.error( msg );
		},

		consoleLog: function( msg ) {
			console.log( msg );
		},
		
		showMe: function() {
			var args = $.makeArray( arguments );
			alert( Backstab.Util.stringify.apply( this, args ) );
		},
		
		beginsWith: function( haystack, needle, greedy ) {
				
			if ( 'string' !== $.type( haystack ) ) {
				Backstab.Util.consoleError( 'Backstab Error: Backstab.Util.beginsWith( haystack, needle ): invalid first parameter (haystack) provided!' );			
			}
			
			if ( 'string' === $.type( needle ) ) {
				
				// return "true" if needle begins with string, otherwise return "false"
				return ( haystack.substring( 0, needle.length ) === needle ) ? true : false;
			
			} else if ( 'array' === $.type( needle ) ) {
				
				// if *haystack* begins with any of the values in *needle* array, return the matching value
				// otherwise, return "false"
				var len = needle.length;
				var greedyRes = '';
				
				for ( var i = 0; i < len; i++ ) {
					if ( Backstab.Util.beginsWith( haystack, needle[ i ] ) ) {
						if ( greedy ) {
							if ( needle[ i ].length > greedyRes.length ) {
								greedyRes = needle[ i ];
							}
						} else {
							return needle[ i ];
						}
					}
				}
				
				if ( greedyRes ) {
					return greedyRes;
				}
				
				return false;	
			}
			
			Backstab.Util.consoleError( 'Backstab Error: Backstab.Util.beginsWith( haystack, needle ): invalid second parameter (needle) provided!' );
			
			return null;
		},
		
		expandCurlyShortform: function( evtsel ) {
			
			var curlyRegex = /([^ ]+?)\{([^\{\}]+?)\}([^;\{\}]*)/g;
			
			if ( Backstab.Util.contains( evtsel, '{' ) ) {
				
				var regs = false, exp = '', subs = [];
				
				// pass 1.1
				while( regs = curlyRegex.exec( evtsel ) ) {
					// Backstab.Util.showMe( regs );
					exp = '';
					subs = regs[ 2 ].split( ';' );
					$.each( subs, function( i, v ) {
						if ( exp ) exp += '; ';
						exp += '%s%s%s'.printf( regs[ 1 ], $.trim( v ), regs[ 3 ] );
					} );
					evtsel = evtsel.replace( regs[ 0 ], exp );

					// Backstab.Util.showMe( evtsel );
				}
				
				// Backstab.Util.showMe( evtsel );
				
				// do this recursively
				evtsel = Backstab.Util.expandCurlyShortform( evtsel );
			}
			
			return evtsel;
		},
		
		// var obj = { some: { sub: { prop: 'foo' } } };
		// Backstab.Util.descendant( obj, 'some.sub.prop' );		// returns 'foo'
		// Backstab.Util.descendant( obj, '' );						// returns obj
		
		descendant: function( obj, sPath ) {
			
			sPath = $.trim( sPath );
			if ( !sPath ) return obj;
			
			var aPath = sPath.split( '.' );
			var bFail = false;
			$.each( aPath, function( i, prop ) {
				if ( ( 'object' === $.type( obj ) ) && obj[ prop ] ) {
					obj = obj[ prop ];
				} else {
					bFail = true;
					return false;
				}
			} );
			
			return ( bFail ) ? false : obj ;		
		},
		
		objectType: function( val ) {
			
			if ( 'object' === $.type( val ) ) {
				var ret = Object.prototype.toString.call( val );
				return ret.replace( '[object ', '' ).replace( ']', '' );
			}
			
			return false;
		},
		
		// get the keys of descendants that has the given method name
		// format is "prop.sub1.sub2.etc..." and traversal is determined by maxLevels
		
		descendantsWithMethod: function( subject, method, maxLevels ) {
			
			var match = [];
			var seen = [];
			
			var getMatches = function( obj, path, level ) {
				
				if ( maxLevels && ( level >= maxLevels ) ) return;
				
				$.each( obj, function( name, val ) {
					
					// if ( Backstab.Util.contains( [ 'el', '$el', 'options', '_byId' ], name ) ) return;
					// if ( Backstab.Util.contains( [ 'el' ], name ) ) return;
					
					if ( val && val[ method ] && ( 'function' === $.type( val[ method ] ) ) ) {
						match.push( '%s%s'.printf( path, name ) );
						// console.log( Backstab.Util.stringify( method, level, '%s%s'.printf( path, name ) ) );
					}
					
					if (
						( 'Object' === Backstab.Util.objectType( val ) ) && 
						( seen.indexOf( val ) == -1 )
					) {
						// console.log( '%s -> %s'.printf( name, Object.prototype.toString.call( val ) ) );
						seen.push( val );
						getMatches( val, '%s%s.'.printf( path, name ), level + 1 );
						// console.log( Backstab.Util.stringify( method, level + 1, '%s%s.'.printf( path, name ), val ) );
					}
				} );
			};
			
			getMatches( subject, '', 0 );
			
			return match;
		},
		
		stringify: function() {
				
			var args = $.makeArray( arguments );
			var obj = ( args.length > 1 ) ? args : args[ 0 ] ;
			var seen = [];
			
			return JSON.stringify( obj, function( key, val ) {
				
				if ( typeof val == 'object' ) {
					var idx = seen.indexOf( val );
					if ( idx >= 0 ) return '[Cyclic Object #%s]'.printf( idx );
					seen.push( val );
				}
				
				return val;
			} );
		},
		
		mergeValues: function( sKey, oTarget, oParams ) {
				
			if ( oParams && oParams[ sKey ] ) {
				
				if ( oTarget[ sKey ] ) {
					_.extend( oTarget[ sKey ], oParams[ sKey ] );						
				} else {
					oTarget[ sKey ] = oParams[ sKey ];
				}
			}
		},
		
		//// overrides
		
		contains: function( subject, value ) {
			
			if ( ( 'string' === $.type( subject ) ) && ( 'string' === $.type( value ) ) ) {
				return ( subject.indexOf( value ) !== -1 ) ? true : false ;
			}
			
			return _.contains( subject, value );
		}
		
		
	} );
	
	
} ).call( this );