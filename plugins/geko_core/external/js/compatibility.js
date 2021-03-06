// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
if ( !Array.prototype.filter ) {
	
	Array.prototype.filter = function( fun /*, thisp*/ ) {
		
		'use strict';
		
		if ( this == null ) throw new TypeError();
		
		var t = Object( this ),
			len = t.length >>> 0,
			res, thisp, i, val;
		
		if ( typeof fun !== 'function' ) throw new TypeError();
		
		res = [];
		thisp = arguments[ 1 ];
		
		for ( i = 0; i < len; i++ ) {
			if ( i in t ) {
				val = t[ i ]; // in case fun mutates this
				if ( fun.call( thisp, val, i, t ) ) {
					res.push( val );
				}
			}
		}
		
		return res;
	};
}


// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/indexOf
if ( !Array.prototype.indexOf ) {

	Array.prototype.indexOf = function ( searchElement /*, fromIndex */ ) {
		
		'use strict';
		
		if ( this == null ) throw new TypeError();
		
		var n, k, t = Object( this ),
			len = t.length >>> 0;
		
		if ( len === 0 ) return -1;
		
		n = 0;
		
		if ( arguments.length > 1 ) {
			n = Number( arguments[ 1 ] );
			if ( n != n ) {		// shortcut for verifying if it's NaN
				n = 0;
			} else if ( n != 0 && n != Infinity && n != -Infinity ) {
				n = ( n > 0 || -1 ) * Math.floor( Math.abs( n ) );
			}
		}
		
		if ( n >= len ) return -1;
		
		for ( k = n >= 0 ? n : Math.max( len - Math.abs( n ), 0 ); k < len; k++ ) {
			if ( k in t && t[ k ] === searchElement ) {
				return k;
			}
		}
		
		return -1;
	};
}

