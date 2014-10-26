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
	
	//// added functions
	
	var funclist = {
		
		//// additions

		consoleError: {
			func: function( msg ) { console.error( msg ); }
		},

		consoleLog: {
			func: function( msg ) { console.log( msg ); }
		},
		
		showMe: {
			func: function() {
				var args = $.makeArray( arguments );
				alert( _.stringify.apply( this, args ) );
			}
		},
		
		beginsWith: {
			func: function( haystack, needle, greedy ) {
				
				if ( 'string' !== $.type( haystack ) ) {
					_.consoleError( 'Backstab Error: _.beginsWith( haystack, needle ): invalid first parameter (haystack) provided!' );			
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
						if ( _.beginsWith( haystack, needle[ i ] ) ) {
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
				
				_.consoleError( 'Backstab Error: _.beginsWith( haystack, needle ): invalid second parameter (needle) provided!' );
				
				return null;
			}
		},
		
		expandCurlyShortform: {
			func: function( evtsel ) {
				
				var curlyRegex = /([^ ]+?)\{([^\{\}]+?)\}([^;\{\}]*)/g;
				
				if ( _.contains( evtsel, '{' ) ) {
					
					var regs = false, exp = '', subs = [];
					
					// pass 1.1
					while( regs = curlyRegex.exec( evtsel ) ) {
						// _.showMe( regs );
						exp = '';
						subs = regs[ 2 ].split( ';' );
						$.each( subs, function( i, v ) {
							if ( exp ) exp += '; ';
							exp += '%s%s%s'.printf( regs[ 1 ], $.trim( v ), regs[ 3 ] );
						} );
						evtsel = evtsel.replace( regs[ 0 ], exp );
	
						// _.showMe( evtsel );
					}
					
					// _.showMe( evtsel );
					
					// do this recursively
					evtsel = _.expandCurlyShortform( evtsel );
				}
				
				return evtsel;
			}
		},
		
		// var obj = { some: { sub: { prop: 'foo' } } };
		// _.descendant( obj, 'some.sub.prop' );		// returns 'foo'
		// _.descendant( obj, '' );						// returns obj
		
		descendant: {
			func: function( obj, sPath ) {
				
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
			}
		},
		
		objectType: {
			func: function( val ) {
				if ( 'object' === $.type( val ) ) {
					var ret = Object.prototype.toString.call( val );
					return ret.replace( '[object ', '' ).replace( ']', '' );
				}
				return false;
			}
		},
		
		// get the keys of descendants that has the given method name
		// format is "prop.sub1.sub2.etc..." and traversal is determined by maxLevels
		
		descendantsWithMethod: {
			func: function( subject, method, maxLevels ) {
				
				var match = [];
				var seen = [];
				
				var getMatches = function( obj, path, level ) {
					
					if ( maxLevels && ( level >= maxLevels ) ) return;
					
					$.each( obj, function( name, val ) {
						
						// if ( _.contains( [ 'el', '$el', 'options', '_byId' ], name ) ) return;
						// if ( _.contains( [ 'el' ], name ) ) return;
						
						if ( val && val[ method ] && ( 'function' === $.type( val[ method ] ) ) ) {
							match.push( '%s%s'.printf( path, name ) );
							// console.log( _.stringify( method, level, '%s%s'.printf( path, name ) ) );
						}
						
						if (
							( 'Object' === _.objectType( val ) ) && 
							( seen.indexOf( val ) == -1 )
						) {
							// console.log( '%s -> %s'.printf( name, Object.prototype.toString.call( val ) ) );
							seen.push( val );
							getMatches( val, '%s%s.'.printf( path, name ), level + 1 );
							// console.log( _.stringify( method, level + 1, '%s%s.'.printf( path, name ), val ) );
						}
					} );
				};
				
				getMatches( subject, '', 0 );
				
				return match;
			}
		},
		
		stringify: {
			func: function() {
				
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
			}
		},
		
		mergeValues: {
			func: function( sKey, oTarget, oParams ) {
				
				if ( oParams && oParams[ sKey ] ) {
					
					if ( oTarget[ sKey ] ) {
						_.extend( oTarget[ sKey ], oParams[ sKey ] );						
					} else {
						oTarget[ sKey ] = oParams[ sKey ];
					}
				}
			}
		},
		
		//// overrides
		
		containsOrig: {
			init: function() {
				_.containsOrig = _.contains;
			},
			func: function( subject, value ) {
				if ( ( 'string' === $.type( subject ) ) && ( 'string' === $.type( value ) ) ) {
					return ( subject.indexOf( value ) !== -1 ) ? true : false ;
				}
				return _.containsOrig( subject, value );
			}		
		}
		
	};
	
	// load function list
	$.each( funclist, function( funcName, v ) {

		if ( !_[ funcName ] ) {
			
			if ( v.init ) v.init();
			_[ funcName ] = v.func;
			_[ funcName ].backstab = true;		// check
			
		} else {
			
			if ( !_[ funcName ].backstab ) {
				_.consoleError( 'Backstab Error: conflict with _.%s()!'.printf( funcName ) );
			}		
		}
		
	} );
	
} ).call( this );