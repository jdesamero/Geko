/* Includes:
 * $.toJSON, $.evalJSON, $.secureEvalJSON, $.quoteString
 * $.fn.outerHTML, $.fn.gekoGetAsHtml, $.fn.getTagName, $.fn.setFormElemVal, $.fn.getFormElemVal
 * $.fn.resetSelOpts, $.fn.showSelOpts
 */

/*
 * jQuery JSON Plugin
 * version: 2.1 (2009-08-14)
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * Brantley Harris wrote this plugin. It is based somewhat on the JSON.org 
 * website's http://www.json.org/json2.js, which proclaims:
 * "NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.", a sentiment that
 * I uphold.
 *
 * It is also influenced heavily by MochiKit's serializeJSON, which is 
 * copyrighted 2005 by Bob Ippolito.
 */
 
( function( $ ) {
    
    /** jQuery.toJSON( json-serializble )
        Converts the given argument into a JSON respresentation.

        If an object has a "toJSON" function, that will be used to get the representation.
        Non-integer/string keys are skipped in the object, as are keys that point to a function.

        json-serializble:
            The *thing* to be converted.
     **/
    $.toJSON = function(o)
    {
        if (typeof(JSON) == 'object' && JSON.stringify)
            return JSON.stringify(o);
        
        var type = typeof(o);
    
        if (o === null)
            return "null";
    
        if (type == "undefined")
            return undefined;
        
        if (type == "number" || type == "boolean")
            return o + "";
    
        if (type == "string")
            return $.quoteString(o);
    
        if (type == 'object')
        {
            if (typeof o.toJSON == "function") 
                return $.toJSON( o.toJSON() );
            
            if (o.constructor === Date)
            {
                var month = o.getUTCMonth() + 1;
                if (month < 10) month = '0' + month;

                var day = o.getUTCDate();
                if (day < 10) day = '0' + day;

                var year = o.getUTCFullYear();
                
                var hours = o.getUTCHours();
                if (hours < 10) hours = '0' + hours;
                
                var minutes = o.getUTCMinutes();
                if (minutes < 10) minutes = '0' + minutes;
                
                var seconds = o.getUTCSeconds();
                if (seconds < 10) seconds = '0' + seconds;
                
                var milli = o.getUTCMilliseconds();
                if (milli < 100) milli = '0' + milli;
                if (milli < 10) milli = '0' + milli;

                return '"' + year + '-' + month + '-' + day + 'T' +
                             hours + ':' + minutes + ':' + seconds + 
                             '.' + milli + 'Z"'; 
            }

            if (o.constructor === Array) 
            {
                var ret = [];
                for (var i = 0; i < o.length; i++)
                    ret.push( $.toJSON(o[i]) || "null" );

                return "[" + ret.join(",") + "]";
            }
        
            var pairs = [];
            for (var k in o) {
                var name;
                var type = typeof k;

                if (type == "number")
                    name = '"' + k + '"';
                else if (type == "string")
                    name = $.quoteString(k);
                else
                    continue;  //skip non-string or number keys
            
                if (typeof o[k] == "function") 
                    continue;  //skip pairs where the value is a function.
            
                var val = $.toJSON(o[k]);
            
                pairs.push(name + ":" + val);
            }

            return "{" + pairs.join(", ") + "}";
        }
    };

    /** jQuery.evalJSON(src)
        Evaluates a given piece of json source.
     **/
    $.evalJSON = function(src)
    {
        if (typeof(JSON) == 'object' && JSON.parse)
            return JSON.parse(src);
        return eval("(" + src + ")");
    };
    
    /** jQuery.secureEvalJSON(src)
        Evals JSON in a way that is *more* secure.
    **/
    $.secureEvalJSON = function(src)
    {
        if (typeof(JSON) == 'object' && JSON.parse)
            return JSON.parse(src);
        
        var filtered = src;
        filtered = filtered.replace(/\\["\\\/bfnrtu]/g, '@');
        filtered = filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']');
        filtered = filtered.replace(/(?:^|:|,)(?:\s*\[)+/g, '');
        
        if (/^[\],:{}\s]*$/.test(filtered))
            return eval("(" + src + ")");
        else
            throw new SyntaxError("Error parsing JSON, source is not valid.");
    };

    /** jQuery.quoteString(string)
        Returns a string-repr of a string, escaping quotes intelligently.  
        Mostly a support function for toJSON.
    
        Examples:
            >>> jQuery.quoteString("apple")
            "apple"
        
            >>> jQuery.quoteString('"Where are we going?", she asked.')
            "\"Where are we going?\", she asked."
     **/
    $.quoteString = function(string)
    {
        if (string.match(_escapeable))
        {
            return '"' + string.replace(_escapeable, function (a) 
            {
                var c = _meta[a];
                if (typeof c === 'string') return c;
                c = a.charCodeAt();
                return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
            }) + '"';
        }
        return '"' + string + '"';
    };
    
    var _escapeable = /["\\\x00-\x1f\x7f-\x9f]/g;
    
    var _meta = {
        '\b': '\\b',
        '\t': '\\t',
        '\n': '\\n',
        '\f': '\\f',
        '\r': '\\r',
        '"' : '\\"',
        '\\': '\\\\'
    };
    
    
    
    ////// more miscellaneous stuff added by Joel
    
    var assertBool = function( mValue ) {
    	if ( 'boolean' == ( typeof mValue ) ) {
    		return mValue;
    	} else if ( 'number' == ( typeof mValue ) ) {
    		return ( mValue ) ? true : false ;
    	} else if ( 'string' == ( typeof mValue ) ) {
    		return ( parseInt( mValue ) ) ? true : false ;
    	}
    	
    	return mValue;
    }
    
    //// node/html
    
    // get full HTML of node, unlike $( $this ).html()
	$.fn.outerHTML = function() {
		if ( this.length > 0 ) {
			return $( '<div>' ).append( this.eq( 0 ).clone() ).html();
		}
		return '';
	};
	
	// extract html from nodes
	$.fn.gekoGetAsHtml = function( options ) {
	
		var opts = $.extend( {
			inner: false,
			content: false
		}, options );
		
		// if content mode, inner is automatically true
		if ( opts.content ) opts.inner = true;
				
		var elem = $( this );
		
		// HACKS!!! do this dynamically
		elem.removeClass( 'ui-tab-template' );
		
		var html = ( opts.inner ) ? elem.html() : elem.outerHTML();
		
		// removal
		if ( opts.content ) {
			elem.html( '' );		
		} else {
			elem.remove();
		}
		
		// HACKS!!! replace curly braces
		html = html.replace( '%7B', '{' ).replace( '%7D', '}' );
		
		return html;
	};
	
	// get tag name
	$.fn.getTagName = function() {
		if ( this.length > 0 ) {
			return this.eq( 0 ).prop( 'tagName' ).toLowerCase();
		}
		return '';
	}
	
	// set form element value
	$.fn.setFormElemVal = function( val ) {
		return this.each( function() {
			
			var elem = $( this );
			var tag = elem.getTagName();
			var type = elem.attr( 'type' );
			
			if ( 'input' == tag ) {
				if ( ( 'checkbox' == type ) || ( 'radio' == type ) ) {
					if ( assertBool( val ) ) {
						elem.attr( 'checked', 'checked' );
					} else {
						elem.removeAttr( 'checked' );
					}
				} else {
					elem.val( val );
				}
			} else if ( 'option' == tag ) {
				if ( assertBool( val ) ) {
					elem.attr( 'selected', 'selected' );
				} else {
					elem.removeAttr( 'selected' );
				}
			} else if ( ( 'select' == tag ) || ( 'textarea' == tag ) ) {
				elem.val( val );
			} else {
				elem.html( val );
			}
			
		} );
	}

	// get form element value
	$.fn.getFormElemVal = function() {
		
		var elem = this;
		var tag = elem.getTagName();
		var type = elem.attr( 'type' );
		
		if ( 'input' == tag ) {
			if ( ( 'checkbox' == type ) || ( 'radio' == type ) ) {
				return ( elem.is( ':checked' ) ) ? elem.val() : '';
			}
			return elem.val();
		} else if ( 'option' == tag ) {
			return ( elem.is( ':selected' ) ) ? elem.val() : '';
		} else if ( ( 'select' == tag ) || ( 'textarea' == tag ) ) {
			return elem.val();
		}
		
		return elem.html();
	}
	
	//// select options
	
	//
	$.fn.resetSelOpts = function() {
		
		return this.each( function() {
			var elem = $( this );
			if ( 'select' == elem.getTagName() ) {
				if ( elem.data( 'selectHtml' ) ) {
					elem.data( 'selectHtml', null );
				}
			}
		} );
	};
	
	//
	$.fn.showSelOpts = function( sSelector ) {
		
		return this.each( function() {
			
			var eElem = $( this );
			
			if ( 'select' == eElem.getTagName() ) {
				
				if ( !eElem.data( 'sSelectHtml' ) ) {
					var sSelectHtml = eElem.outerHTML();
					eElem.data( 'sSelectHtml', sSelectHtml );
				}
				
				var eTemp = $( eElem.data( 'sSelectHtml' ) );
				var sCurVal = eElem.val();
				
				eElem.html( '' );
				eTemp.find( sSelector ).each( function() {
					eElem.append( $( this ) );
				} );
				
				eElem.val( sCurVal );
			}
		} );
		
	};
	
	
	
	//// class manipulation
	
	$.fn.ternaryClass = function( bCond, sTrueClass, sFalseClass ) {
		
		return this.each( function() {
			
			var eElem = $( this );
			
			eElem.removeClass( '%s %s'.printf( sTrueClass, sFalseClass ) );
			
			if ( bCond ) {
				eElem.addClass( sTrueClass );
			} else {
				eElem.addClass( sFalseClass );			
			}
			
		} );
		
	};
	
	$.fn.showHideElem = function( sShowSelector, sHideSelector ) {
		
		return this.each( function() {
			
			var eElem = $( this );
			
			eElem.find( sShowSelector ).show();
			eElem.find( sHideSelector ).hide();
			
		} );
		
	};
	
	
	
	
	//// value formatting shortcuts
	
	$.fn.intVal = function() {
		
		var eElem = $( this );
		var mRes = parseInt( $.trim( eElem.val() ) );
		
		if ( ( 'number' === $.type( mRes ) ) && ( !isNaN( mRes ) ) ) {
			return mRes;
		}
		
		return 0;
	};
	
	$.fn.floatVal = function() {
		
		var eElem = $( this );
		var mRes = parseFloat( $.trim( eElem.val() ) );
		
		if ( ( 'number' === $.type( mRes ) ) && ( !isNaN( mRes ) ) ) {
			return mRes;
		}
		
		return 0;
	};
	
	
	$.fn.boolVal = function() {
		
		var eElem = $( this );
		var sRes = $.trim( eElem.val() ).toLowerCase();
		var iRes = parseInt( sRes );
		
		// try to be a smarty pants
		if (
			( 0 !== iRes ) && 
			( '' !== sRes ) && 
			( 'false' !== sRes ) && 
			( 'no' !== sRes )
		) {
			return true;
		}
		
		return false;
	};
	
	
	// attempt to set val() of element to JSON string if mValue supports it
	$.fn.jsonVal = function( mValue ) {
		
		return this.each( function() {
			
			var eElem = $( this );
			var mSetVal;
			
			if ( JSON && JSON.stringify && mValue.toJSON ) {
				mSetVal = JSON.stringify( mValue.toJSON() );
			} else {
				mSetVal = mValue;
			}
			
			eElem.val( mSetVal );
			
		} );
	};
	
	
	//// math functions
	
	$.gekoRandomInt = function( min, max ) {
		return Math.floor( Math.random() * ( max - min + 1 ) ) + min;
	};
	
	$.gekoRadToDeg = function( radians ) {
		return radians * 180 / Math.PI;
	};
	
	$.gekoDegToRad = function( degrees ) {
		return degrees * Math.PI / 180;
	};
	
	$.gekoMath = {
		
		vals: {
			
			e: Math.E,
			pi: Math.PI,
			sqrt2: Math.SQRT2,
			sqrt1_2: Math.SQRT1_2,
			ln2: Math.LN2,
			ln10: Math.LN10,
			log2e: Math.LOG2E,
			log10e: Math.LOG10E,
			
			abs: Math.abs,
			acos: Math.acos,
			asin: Math.asin,
			atan: Math.atan,
			atan2: Math.atan2,
			ceil: Math.ceil,
			cos: Math.cos,
			exp: Math.exp,
			floor: Math.floor,
			log: Math.log,
			max: Math.max,
			min: Math.min,
			pow: Math.pow,
			random: Math.random,
			round: Math.round,
			sin: Math.sin,
			sqrt: Math.sqrt,
			tan: Math.tan,
			
			pi1_2: ( Math.PI / 2 ),
			radians: ( Math.PI / 180 ),
			degrees: ( 180 / Math.PI ),
			tau: ( Math.PI * 2 ),
			e2: ( Math.E * Math.E ),
			
			sqr: function( x ) { return x * x; },
			cube: function( x ) { return x * x * x; },
			sgn: function( x ) { return x > 0 ? 1 : x < 0 ? -1 : 0; },
			randInt: function( min, max ) { return Math.floor( Math.random() * ( max - min + 1 ) ) + min; },
			degToRad: function( deg ) { return radians * 180 / Math.PI; },
			radToDeg: function( rad ) { return degrees * Math.PI / 180; },
			
			sinh: function( x ) { return ( ( x = Math.exp( x ) ) - 1 / x) / 2; },
			cosh: function( x ) { return ( ( x = Math.exp( x ) ) + 1 / x) / 2; },
			tanh: function( x ) { return ( ( x = Math.exp( 2 * x ) ) - 1) / ( x + 1 ); }
			
		},
		
		load: function () {
			
			var sOut = 'var __gm = jQuery.gekoMath.vals';
			
			for ( var k in jQuery.gekoMath.vals ) {
				sOut += ', %s = __gm.%s'.printf( k, k );
			}
			
			return sOut + ';';
		}
		
	};
	
	
	
	
	//// browser detect
	
	var matched, browser;
	
	// Use of jQuery.browser is frowned upon.
	// More details: http://api.jquery.com/jQuery.browser
	// jQuery.uaMatch maintained for back-compat
	jQuery.uaMatch = function( ua ) {
		ua = ua.toLowerCase();
	
		var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
			/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
			/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
			/(msie) ([\w.]+)/.exec( ua ) ||
			ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
			[];
	
		return {
			browser: match[ 1 ] || "",
			version: match[ 2 ] || "0"
		};
	};
	
	matched = jQuery.uaMatch( navigator.userAgent );
	browser = {};
	
	if ( matched.browser ) {
		browser[ matched.browser ] = true;
		browser.version = matched.version;
	}
	
	// Chrome is Webkit, but Webkit is also Safari.
	if ( browser.chrome ) {
		browser.webkit = true;
	} else if ( browser.webkit ) {
		browser.safari = true;
	}
	
	jQuery.browser = browser;
	
	
	
	//// decode html entities
	
	// http://stackoverflow.com/questions/5796718/html-entity-decode
	
	var decodeEntities = (function() {
		
		// this prevents any overhead from creating the object each time
		var element = document.createElement('div');
		
		function decodeHTMLEntities (str) {
			
			if(str && typeof str === 'string') {
				// strip script/html tags
				str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
				str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
				element.innerHTML = str;
				str = element.textContent;
				element.textContent = '';
			}
			
			return str;
		}
		
		return decodeHTMLEntities;
		
	})();
	
	jQuery.decodeEntities = decodeEntities;
	
	
	
	//// basic observer/listener implementation
	
	$.fn.gekoObserver = function() {

		// var iNumArgs = arguments.length;
		
		var sOptsKey = 'geko_observer_options';
		var sObsKey = 'geko_observers';
		
		var mArg0 = arguments[ 0 ];
		var mArg1 = arguments[ 1 ];
		
		return this.each( function() {
			
			var eElem = $( this );
						
			if ( 'string' === $.type( mArg0 ) ) {

				var opts = eElem.data( sOptsKey );
				
				if ( 'register' === mArg0 ) {
					
					if ( !eElem.data( sObsKey ) ) {
						eElem.data( sObsKey, [] );
					}
					
					if ( -1 === $.inArray( mArg1, eElem.data( sObsKey ) ) ) {
						eElem.data( sObsKey ).push( mArg1 );
					}
					
				} else if ( 'unregister' === mArg0 ) {
					
					if ( eElem.data( sObsKey ) ) {
						
						var iUnregIdx = $.inArray( mArg1, eElem.data( sObsKey ) );
						
						if ( -1 !== iUnregIdx ) {
							eElem.data( sObsKey ).splice( iUnregIdx, 1 );
						}
					}
					
				}
				
			} else {
				
				//// default, initialize
				
				var opts = $.extend( {
					prefix: null,
					events: null
				}, mArg0 );
				
				var eElem = $( this );
				
				if ( opts.events ) {
					
					eElem.on( opts.events, function() {
						
						var args = $.makeArray( arguments );
						var evt = args.shift();
						
						var eThis = $( this );
						
						var aObs = eThis.data( sObsKey );
						
						$.each( aObs, function( i, v ) {
							
							var sTriggerEvent = evt.type;
							
							if ( opts.prefix ) {
								sTriggerEvent = '%s:%s'.printf( opts.prefix, sTriggerEvent );
							}
							
							v.trigger( sTriggerEvent, args );
							
						} );
						
					} );
				}
				
				eElem.data( sOptsKey, opts );
				
			}
			
		} );
		
		
	};
	
	
	//// some basic date/time utilities
	
	// client-side version of PHP time()
	
	$.gekoTimestamp = function() {
		return Math.floor( new Date().getTime() / 1000 );
	};
	
	
	$.gekoFormatDdHsMmSs = function( iSecNum, bIncludeZeroed ) {
		
		var iDays   = Math.floor( iSecNum / 86400 );
		var iHours   = Math.floor( ( iSecNum - ( iDays * 86400 ) ) / 3600 );
		var iMins = Math.floor( ( iSecNum - ( iDays * 86400 ) - ( iHours * 3600 ) ) / 60 );
		var iSecs = iSecNum - ( iDays * 86400 ) - ( iHours * 3600 ) - ( iMins * 60 );
		
		var sOut = '%02d:%02d'.printf( iMins, iSecs );
		
		if ( iHours || bIncludeZeroed ) {
			sOut = '%02d:%s'.printf( iHours, sOut );
		}

		if ( iDays || bIncludeZeroed ) {
			sOut = '%02d:%s'.printf( iDays, sOut );
		}
		
		return sOut;
	};
	
	
	
	//// some basic input validation functions
	
	// email
	
	var rEmail = /^[a-z0-9\._-]+@([a-z0-9_-]+\.)+[a-z]{2,6}$/i;
	
	$.gekoValidateEmail = function( sEmail ) {
		return rEmail.test( sEmail );
	};
	
	
	// credit card
	
	// https://gist.github.com/DiegoSalazar/4075533
	// Luhn algorithm in Javascript. Check valid credit card numbers 
	
	var rCreditCard = /[^0-9-\s]+/;
	var rCcDigit = /\D/g;
	
	$.gekoValidateCreditCard = function( value ) {
		
		// accept only digits, dashes or spaces
		if ( rCreditCard.test( value ) ) return false;
		
		// The Luhn Algorithm. It's so pretty.
		var nCheck = 0, nDigit = 0, bEven = false;
		value = value.replace( rCcDigit, '' );
		
		for ( var n = value.length - 1; n >= 0; n-- ) {
			
			var cDigit = value.charAt( n ),
			nDigit = parseInt( cDigit, 10 );
			
			if ( bEven ) {
				if ( (nDigit *= 2 ) > 9 ) nDigit -= 9;
			}
		
			nCheck += nDigit;
			bEven = !bEven;
		}
		
		return ( nCheck % 10 ) == 0;	
	};
	
	var rCvc = /^[0-9]{3,4}$/;
	
	$.gekoValidateCvc = function( cvcValue ) {
		return rCvc.test( cvcValue );
	};
	
	var rCcExpiry = /^[0-9]{2}\/[0-9]{2}$/;
	var rCcExpiryClean = /[^0-9\/]/g;
	
	$.gekoValidateCcExpiry = function( ccExpiryValue ) {
		
		var ccNormExpVal = ccExpiryValue.replace( rCcExpiryClean, '' );
		
		return rCcExpiry.test( ccNormExpVal );
	};
	
	
	var oPostalRegex = {
		'CA': /^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/,
		'US': /(^\d{5}$)|(^\d{5}-\d{4}$)/
	};
	
	$.gekoValidatePostalCode = function( sValue, sCountryCode ) {
		
		if ( !sCountryCode ) sCountryCode = 'CA';		// default
		sCountryCode = sCountryCode.toUpperCase();
		
		var oRegex = oPostalRegex[ sCountryCode ];
		
		if ( oRegex ) {
			return oRegex.test( sValue );
		}
		
		return false;
	};
	
	
	
	
	// prevent IE 9 from crapping out
	if ( !( window.console && console.log ) ) {
		window.console = {
			log: function(){},
			debug: function(){},
			info: function(){},
			warn: function(){},
			error: function(){}
		};
	}
	
	
} )( jQuery );