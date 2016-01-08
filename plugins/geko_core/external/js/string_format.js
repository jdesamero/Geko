(function( window ) {

	//// add commas to a numeric value
	
	var addCommas = function( nStr ) {
		nStr += '';
		x = nStr.split( '.' );
		x1 = x[ 0 ];
		x2 = x.length > 1 ? '.' + x[ 1 ] : '';
		var rgx = /(\d+)(\d{3})/;
		while ( rgx.test( x1 ) ) {
			x1 = x1.replace( rgx, '$1' + ',' + '$2' );
		}
		return x1 + x2;
	}
	
	// for convenience...
	String.prototype.addCommas = function() {
		return addCommas( this );
	};
	
	//// convert line breaks to <br /> and vice-versa
	
	var br2nl = function( val ) {
		return val.replace( /\<br \/>/g, '\n' ).replace( /\<br>/g, '\n' );
	}
	
	var nl2br = function( val ) {
		return val.replace( /\n/g, '<br \/>' );
	}
	
	// for convenience...
	String.prototype.br2nl = function() {
		return br2nl( this );
	}
	
	String.prototype.nl2br = function() {
		return nl2br( this );
	}
	
	//// convert string to slug
	
	var convertToSlug = function( value ) {
		return value.toLowerCase().replace( /[^\w ]+/g, '' ).replace( / +/g, '-' );
	}
	
	String.prototype.convertToSlug = function() {
		return convertToSlug( this );
	}
	
	
	
	//// truncate string
	
	// http://stackoverflow.com/questions/1199352/smart-way-to-shorten-long-strings-with-javascript
	
	String.prototype.truncate =
	 function(n,useWordBoundary){
		 var toLong = this.length>n,
			 s_ = toLong ? this.substr(0,n-1) : this;
		 s_ = useWordBoundary && toLong ? s_.substr(0,s_.lastIndexOf(' ')) : s_;
		 return  toLong ? s_ + '&hellip;' : s_;
	  };
	
	
	
	//// sprintf implementation
	
	// http://westhoffswelt.de/blog/0051_sprintf.js_an_almost_feature_complete_javascript_implementation_of_the_standard_c_sprintf_function.html
	
	/**
	 * Copyright (c) 2010 Jakob Westhoff
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 * 
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 * 
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 */

    var sprintf = function( format ) {
        // Check for format definition
        if ( typeof format != 'string' ) {
            throw "sprintf: The first arguments need to be a valid format string.";
        }
        
        /**
         * Define the regex to match a formating string
         * The regex consists of the following parts:
         * percent sign to indicate the start
         * (optional) sign specifier
         * (optional) padding specifier
         * (optional) alignment specifier
         * (optional) width specifier
         * (optional) precision specifier
         * type specifier:
         *  % - literal percent sign
         *  b - binary number
         *  c - ASCII character represented by the given value
         *  d - signed decimal number
         *  f - floating point value
         *  o - octal number
         *  s - string
         *  x - hexadecimal number (lowercase characters)
         *  X - hexadecimal number (uppercase characters)
         */
        var r = new RegExp( /%(\+)?([0 ]|'(.))?(-)?([0-9]+)?(\.([0-9]+))?([%bcdfosxX])/g );

        /**
         * Each format string is splitted into the following parts:
         * 0: Full format string
         * 1: sign specifier (+)
         * 2: padding specifier (0/<space>/'<any char>)
         * 3: if the padding character starts with a ' this will be the real 
         *    padding character
         * 4: alignment specifier
         * 5: width specifier
         * 6: precision specifier including the dot
         * 7: precision specifier without the dot
         * 8: type specifier
         */
        var parts      = [];
        var paramIndex = 1;
        while ( part = r.exec( format ) ) {
            // Check if an input value has been provided, for the current
            // format string (no argument needed for %%)
            if ( ( paramIndex >= arguments.length ) && ( part[8] != '%' ) ) {
                throw "sprintf: At least one argument was missing.";
            }

            parts[parts.length] = {
                /* beginning of the part in the string */
                begin: part.index,
                /* end of the part in the string */
                end: part.index + part[0].length,
                /* force sign */
                sign: ( part[1] == '+' ),
                /* is the given data negative */
                negative: ( parseFloat( arguments[paramIndex] ) < 0 ) ? true : false,
                /* padding character (default: <space>) */
                padding: ( part[2] == undefined )
                         ? ( ' ' ) /* default */
                         : ( ( part[2].substring( 0, 1 ) == "'" ) 
                             ? ( part[3] ) /* use special char */
                             : ( part[2] ) /* use normal <space> or zero */
                           ),
                /* should the output be aligned left?*/
                alignLeft: ( part[4] == '-' ),
                /* width specifier (number or false) */
                width: ( part[5] != undefined ) ? part[5] : false,
                /* precision specifier (number or false) */
                precision: ( part[7] != undefined ) ? part[7] : false,
                /* type specifier */
                type: part[8],
                /* the given data associated with this part converted to a string */
                data: ( part[8] != '%' ) ? String ( arguments[paramIndex++] ) : false
            };
        }

        var newString = "";
        var start = 0;
        // Generate our new formated string
        for( var i=0; i<parts.length; ++i ) {
            // Add first unformated string part
            newString += format.substring( start, parts[i].begin );
            
            // Mark the new string start
            start = parts[i].end;

            // Create the appropriate preformat substitution
            // This substitution is only the correct type conversion. All the
            // different options and flags haven't been applied to it at this
            // point
            var preSubstitution = "";
            switch ( parts[i].type ) {
                case '%':
                    preSubstitution = "%";
                break;
                case 'b':
                    preSubstitution = Math.abs( parseInt( parts[i].data ) ).toString( 2 );
                break;
                case 'c':
                    preSubstitution = String.fromCharCode( Math.abs( parseInt( parts[i].data ) ) );
                break;
                case 'd':
                    preSubstitution = String( Math.abs( parseInt( parts[i].data ) ) );
                break;
                case 'f':
                    preSubstitution = ( parts[i].precision == false )
                                      ? ( String( ( Math.abs( parseFloat( parts[i].data ) ) ) ) )
                                      : ( Math.abs( parseFloat( parts[i].data ) ).toFixed( parts[i].precision ) );
                break;
                case 'o':
                    preSubstitution = Math.abs( parseInt( parts[i].data ) ).toString( 8 );
                break;
                case 's':
                    preSubstitution = parts[i].data.substring( 0, parts[i].precision ? parts[i].precision : parts[i].data.length ); /* Cut if precision is defined */
                break;
                case 'x':
                    preSubstitution = Math.abs( parseInt( parts[i].data ) ).toString( 16 ).toLowerCase();
                break;
                case 'X':
                    preSubstitution = Math.abs( parseInt( parts[i].data ) ).toString( 16 ).toUpperCase();
                break;
                default:
                    throw 'sprintf: Unknown type "' + parts[i].type + '" detected. This should never happen. Maybe the regex is wrong.';
            }

            // The % character is a special type and does not need further processing
            if ( parts[i].type ==  "%" ) {
                newString += preSubstitution;
                continue;
            }

            // Modify the preSubstitution by taking sign, padding and width
            // into account

            // Pad the string based on the given width
            if ( parts[i].width != false ) {
                // Padding needed?
                if ( parts[i].width > preSubstitution.length ) 
                {
                    var origLength = preSubstitution.length;
                    for( var j = 0; j < parts[i].width - origLength; ++j ) 
                    {
                        preSubstitution = ( parts[i].alignLeft == true ) 
                                          ? ( preSubstitution + parts[i].padding )
                                          : ( parts[i].padding + preSubstitution );
                    }
                }
            }

            // Add a sign symbol if neccessary or enforced, but only if we are
            // not handling a string
            if ( parts[i].type == 'b' 
              || parts[i].type == 'd' 
              || parts[i].type == 'o' 
              || parts[i].type == 'f' 
              || parts[i].type == 'x' 
              || parts[i].type == 'X' ) {
                if ( parts[i].negative == true ) {
                    preSubstitution = "-" + preSubstitution;
                }
                else if ( parts[i].sign == true ) {
                    preSubstitution = "+" + preSubstitution;
                }
            }

            // Add the substitution to the new string
            newString += preSubstitution;
        }

        // Add the last part of the given format string, which may still be there
        newString += format.substring( start, format.length );

        return newString;
    };

    // Register the new sprintf function as a global function, as well as a
    // method to the String object.
    window.sprintf = sprintf;
    String.prototype.printf = function() {
        var newArguments = Array.prototype.slice.call( arguments );
        newArguments.unshift( String( this ) );
        return sprintf.apply( undefined, newArguments );
    }


	//// http://phpjs.org/functions/strtotime/
	
	//
	function strtotime(text, now) {
	  // discuss at: http://phpjs.org/functions/strtotime/
	  // version: 1109.2016
	  // original by: Caio Ariede (http://caioariede.com)
	  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // improved by: Caio Ariede (http://caioariede.com)
	  // improved by: A. Mat’as Quezada (http://amatiasq.com)
	  // improved by: preuter
	  // improved by: Brett Zamir (http://brett-zamir.me)
	  // improved by: Mirko Faber
	  // input by: David
	  // bugfixed by: Wagner B. Soares
	  // bugfixed by: Artur Tchernychev
	  // note: Examples all have a fixed timestamp to prevent tests to fail because of variable time(zones)
	  // example 1: strtotime('+1 day', 1129633200);
	  // returns 1: 1129719600
	  // example 2: strtotime('+1 week 2 days 4 hours 2 seconds', 1129633200);
	  // returns 2: 1130425202
	  // example 3: strtotime('last month', 1129633200);
	  // returns 3: 1127041200
	  // example 4: strtotime('2009-05-04 08:30:00 GMT');
	  // returns 4: 1241425800
	
	  var parsed, match, today, year, date, days, ranges, len, times, regex, i, fail = false;
	
	  if (!text) {
		return fail;
	  }
	
	  // Unecessary spaces
	  text = text.replace(/^\s+|\s+$/g, '')
		.replace(/\s{2,}/g, ' ')
		.replace(/[\t\r\n]/g, '')
		.toLowerCase();
	
	  // in contrast to php, js Date.parse function interprets:
	  // dates given as yyyy-mm-dd as in timezone: UTC,
	  // dates with "." or "-" as MDY instead of DMY
	  // dates with two-digit years differently
	  // etc...etc...
	  // ...therefore we manually parse lots of common date formats
	  match = text.match(
		/^(\d{1,4})([\-\.\/\:])(\d{1,2})([\-\.\/\:])(\d{1,4})(?:\s(\d{1,2}):(\d{2})?:?(\d{2})?)?(?:\s([A-Z]+)?)?$/);
	
	  if (match && match[2] === match[4]) {
		if (match[1] > 1901) {
		  switch (match[2]) {
			case '-':
			  { // YYYY-M-D
				if (match[3] > 12 || match[5] > 31) {
				  return fail;
				}
	
				return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
				  match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
			  }
			case '.':
			  { // YYYY.M.D is not parsed by strtotime()
				return fail;
			  }
			case '/':
			  { // YYYY/M/D
				if (match[3] > 12 || match[5] > 31) {
				  return fail;
				}
	
				return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
				  match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
			  }
		  }
		} else if (match[5] > 1901) {
		  switch (match[2]) {
			case '-':
			  { // D-M-YYYY
				if (match[3] > 12 || match[1] > 31) {
				  return fail;
				}
	
				return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
				  match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
			  }
			case '.':
			  { // D.M.YYYY
				if (match[3] > 12 || match[1] > 31) {
				  return fail;
				}
	
				return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
				  match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
			  }
			case '/':
			  { // M/D/YYYY
				if (match[1] > 12 || match[3] > 31) {
				  return fail;
				}
	
				return new Date(match[5], parseInt(match[1], 10) - 1, match[3],
				  match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
			  }
		  }
		} else {
		  switch (match[2]) {
			case '-':
			  { // YY-M-D
				if (match[3] > 12 || match[5] > 31 || (match[1] < 70 && match[1] > 38)) {
				  return fail;
				}
	
				year = match[1] >= 0 && match[1] <= 38 ? +match[1] + 2000 : match[1];
				return new Date(year, parseInt(match[3], 10) - 1, match[5],
				  match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
			  }
			case '.':
			  { // D.M.YY or H.MM.SS
				if (match[5] >= 70) { // D.M.YY
				  if (match[3] > 12 || match[1] > 31) {
					return fail;
				  }
	
				  return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
					match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
				}
				if (match[5] < 60 && !match[6]) { // H.MM.SS
				  if (match[1] > 23 || match[3] > 59) {
					return fail;
				  }
	
				  today = new Date();
				  return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
					match[1] || 0, match[3] || 0, match[5] || 0, match[9] || 0) / 1000;
				}
	
				return fail; // invalid format, cannot be parsed
			  }
			case '/':
			  { // M/D/YY
				if (match[1] > 12 || match[3] > 31 || (match[5] < 70 && match[5] > 38)) {
				  return fail;
				}
	
				year = match[5] >= 0 && match[5] <= 38 ? +match[5] + 2000 : match[5];
				return new Date(year, parseInt(match[1], 10) - 1, match[3],
				  match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
			  }
			case ':':
			  { // HH:MM:SS
				if (match[1] > 23 || match[3] > 59 || match[5] > 59) {
				  return fail;
				}
	
				today = new Date();
				return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
				  match[1] || 0, match[3] || 0, match[5] || 0) / 1000;
			  }
		  }
		}
	  }
	
	  // other formats and "now" should be parsed by Date.parse()
	  if (text === 'now') {
		return now === null || isNaN(now) ? new Date()
		  .getTime() / 1000 | 0 : now | 0;
	  }
	  if (!isNaN(parsed = Date.parse(text))) {
		return parsed / 1000 | 0;
	  }
	
	  date = now ? new Date(now * 1000) : new Date();
	  days = {
		'sun': 0,
		'mon': 1,
		'tue': 2,
		'wed': 3,
		'thu': 4,
		'fri': 5,
		'sat': 6
	  };
	  ranges = {
		'yea': 'FullYear',
		'mon': 'Month',
		'day': 'Date',
		'hou': 'Hours',
		'min': 'Minutes',
		'sec': 'Seconds'
	  };
	
	  function lastNext(type, range, modifier) {
		var diff, day = days[range];
	
		if (typeof day !== 'undefined') {
		  diff = day - date.getDay();
	
		  if (diff === 0) {
			diff = 7 * modifier;
		  } else if (diff > 0 && type === 'last') {
			diff -= 7;
		  } else if (diff < 0 && type === 'next') {
			diff += 7;
		  }
	
		  date.setDate(date.getDate() + diff);
		}
	  }
	
	  function process(val) {
		var splt = val.split(' '), // Todo: Reconcile this with regex using \s, taking into account browser issues with split and regexes
		  type = splt[0],
		  range = splt[1].substring(0, 3),
		  typeIsNumber = /\d+/.test(type),
		  ago = splt[2] === 'ago',
		  num = (type === 'last' ? -1 : 1) * (ago ? -1 : 1);
	
		if (typeIsNumber) {
		  num *= parseInt(type, 10);
		}
	
		if (ranges.hasOwnProperty(range) && !splt[1].match(/^mon(day|\.)?$/i)) {
		  return date['set' + ranges[range]](date['get' + ranges[range]]() + num);
		}
	
		if (range === 'wee') {
		  return date.setDate(date.getDate() + (num * 7));
		}
	
		if (type === 'next' || type === 'last') {
		  lastNext(type, range, num);
		} else if (!typeIsNumber) {
		  return false;
		}
	
		return true;
	  }
	
	  times = '(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec' +
		'|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?' +
		'|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)';
	  regex = '([+-]?\\d+\\s' + times + '|' + '(last|next)\\s' + times + ')(\\sago)?';
	
	  match = text.match(new RegExp(regex, 'gi'));
	  if (!match) {
		return fail;
	  }
	
	  for (i = 0, len = match.length; i < len; i++) {
		if (!process(match[i])) {
		  return fail;
		}
	  }
	
	  // ECMAScript 5 only
	  // if (!match.every(process))
	  // return false;
	
	  return (date.getTime() / 1000);
	};

	String.prototype.strtotime = function( now ) {
		return strtotime( this, now );
	};

	
	
	
	// http://stackoverflow.com/questions/4878756/javascript-how-to-capitalize-first-letter-of-each-word-like-a-2-word-city
	
	var ucwords = function( str ) {
		return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	};
	
	String.prototype.ucwords = function() {
		return ucwords( this );
	};
	
	
	// http://stackoverflow.com/questions/1026069/capitalize-the-first-letter-of-string-in-javascript
	
	var ucfirst = function( str ) {
		return str.charAt( 0 ).toUpperCase() + str.slice( 1 );
	};
	
	String.prototype.ucfirst = function() {
		return ucfirst( this );
	};
	
	
	
	
	
	//// https://github.com/kvz/phpjs/blob/master/functions/xml/utf8_encode.js
	
	//
	function utf8_encode(argString) {
	  // discuss at: http://phpjs.org/functions/utf8_encode/
	  // original by: Webtoolkit.info (http://www.webtoolkit.info/)
	  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // improved by: sowberry
	  // improved by: Jack
	  // improved by: Yves Sucaet
	  // improved by: kirilloid
	  // bugfixed by: Onno Marsman
	  // bugfixed by: Onno Marsman
	  // bugfixed by: Ulrich
	  // bugfixed by: Rafal Kukawski
	  // bugfixed by: kirilloid
	  // example 1: utf8_encode('Kevin van Zonneveld');
	  // returns 1: 'Kevin van Zonneveld'
	
	  if (argString === null || typeof argString === 'undefined') {
		return '';
	  }
	
	  var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
	  var utftext = '',
		start, end, stringl = 0;
	
	  start = end = 0;
	  stringl = string.length;
	  for (var n = 0; n < stringl; n++) {
		var c1 = string.charCodeAt(n);
		var enc = null;
	
		if (c1 < 128) {
		  end++;
		} else if (c1 > 127 && c1 < 2048) {
		  enc = String.fromCharCode(
			(c1 >> 6) | 192, (c1 & 63) | 128
		  );
		} else if (c1 & 0xF800 != 0xD800) {
		  enc = String.fromCharCode(
			(c1 >> 12) | 224, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
		  );
		} else { // surrogate pairs
		  if (c1 & 0xFC00 != 0xD800) {
			throw new RangeError('Unmatched trail surrogate at ' + n);
		  }
		  var c2 = string.charCodeAt(++n);
		  if (c2 & 0xFC00 != 0xDC00) {
			throw new RangeError('Unmatched lead surrogate at ' + (n - 1));
		  }
		  c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
		  enc = String.fromCharCode(
			(c1 >> 18) | 240, ((c1 >> 12) & 63) | 128, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
		  );
		}
		if (enc !== null) {
		  if (end > start) {
			utftext += string.slice(start, end);
		  }
		  utftext += enc;
		  start = end = n + 1;
		}
	  }
	
	  if (end > start) {
		utftext += string.slice(start, stringl);
	  }
	
	  return utftext;
	}
	
	String.prototype.utf8_encode = function() {
		return utf8_encode( this );
	};

	
	
	
	
	
	
	
	
	//// https://raw.github.com/kvz/phpjs/master/functions/strings/md5.js
	
	//
	function md5(str) {
	  //  discuss at: http://phpjs.org/functions/md5/
	  // original by: Webtoolkit.info (http://www.webtoolkit.info/)
	  // improved by: Michael White (http://getsprink.com)
	  // improved by: Jack
	  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  //    input by: Brett Zamir (http://brett-zamir.me)
	  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  //  depends on: utf8_encode
	  //   example 1: md5('Kevin van Zonneveld');
	  //   returns 1: '6e658d4bfcb59cc13f96c14450ac40b9'
	
	  var xl;
	
	  var rotateLeft = function(lValue, iShiftBits) {
		return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
	  };
	
	  var addUnsigned = function(lX, lY) {
		var lX4, lY4, lX8, lY8, lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);
		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
		  return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
		  if (lResult & 0x40000000) {
			return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
		  } else {
			return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
		  }
		} else {
		  return (lResult ^ lX8 ^ lY8);
		}
	  };
	
	  var _F = function(x, y, z) {
		return (x & y) | ((~x) & z);
	  };
	  var _G = function(x, y, z) {
		return (x & z) | (y & (~z));
	  };
	  var _H = function(x, y, z) {
		return (x ^ y ^ z);
	  };
	  var _I = function(x, y, z) {
		return (y ^ (x | (~z)));
	  };
	
	  var _FF = function(a, b, c, d, x, s, ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_F(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	  };
	
	  var _GG = function(a, b, c, d, x, s, ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_G(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	  };
	
	  var _HH = function(a, b, c, d, x, s, ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_H(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	  };
	
	  var _II = function(a, b, c, d, x, s, ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_I(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	  };
	
	  var convertToWordArray = function(str) {
		var lWordCount;
		var lMessageLength = str.length;
		var lNumberOfWords_temp1 = lMessageLength + 8;
		var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
		var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
		var lWordArray = new Array(lNumberOfWords - 1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while (lByteCount < lMessageLength) {
		  lWordCount = (lByteCount - (lByteCount % 4)) / 4;
		  lBytePosition = (lByteCount % 4) * 8;
		  lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount) << lBytePosition));
		  lByteCount++;
		}
		lWordCount = (lByteCount - (lByteCount % 4)) / 4;
		lBytePosition = (lByteCount % 4) * 8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
		lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
		lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
		return lWordArray;
	  };
	
	  var wordToHex = function(lValue) {
		var wordToHexValue = '',
		  wordToHexValue_temp = '',
		  lByte, lCount;
		for (lCount = 0; lCount <= 3; lCount++) {
		  lByte = (lValue >>> (lCount * 8)) & 255;
		  wordToHexValue_temp = '0' + lByte.toString(16);
		  wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length - 2, 2);
		}
		return wordToHexValue;
	  };
	
	  var x = [],
		k, AA, BB, CC, DD, a, b, c, d, S11 = 7,
		S12 = 12,
		S13 = 17,
		S14 = 22,
		S21 = 5,
		S22 = 9,
		S23 = 14,
		S24 = 20,
		S31 = 4,
		S32 = 11,
		S33 = 16,
		S34 = 23,
		S41 = 6,
		S42 = 10,
		S43 = 15,
		S44 = 21;
	
	  str = utf8_encode(str);
	  x = convertToWordArray(str);
	  a = 0x67452301;
	  b = 0xEFCDAB89;
	  c = 0x98BADCFE;
	  d = 0x10325476;
	
	  xl = x.length;
	  for (k = 0; k < xl; k += 16) {
		AA = a;
		BB = b;
		CC = c;
		DD = d;
		a = _FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
		d = _FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
		c = _FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
		b = _FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
		a = _FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
		d = _FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
		c = _FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
		b = _FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
		a = _FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
		d = _FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
		c = _FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
		b = _FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
		a = _FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
		d = _FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
		c = _FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
		b = _FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
		a = _GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
		d = _GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
		c = _GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
		b = _GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
		a = _GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
		d = _GG(d, a, b, c, x[k + 10], S22, 0x2441453);
		c = _GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
		b = _GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
		a = _GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
		d = _GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
		c = _GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
		b = _GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
		a = _GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
		d = _GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
		c = _GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
		b = _GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
		a = _HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
		d = _HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
		c = _HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
		b = _HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
		a = _HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
		d = _HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
		c = _HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
		b = _HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
		a = _HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
		d = _HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
		c = _HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
		b = _HH(b, c, d, a, x[k + 6], S34, 0x4881D05);
		a = _HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
		d = _HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
		c = _HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
		b = _HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
		a = _II(a, b, c, d, x[k + 0], S41, 0xF4292244);
		d = _II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
		c = _II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
		b = _II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
		a = _II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
		d = _II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
		c = _II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
		b = _II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
		a = _II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
		d = _II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
		c = _II(c, d, a, b, x[k + 6], S43, 0xA3014314);
		b = _II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
		a = _II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
		d = _II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
		c = _II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
		b = _II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
		a = addUnsigned(a, AA);
		b = addUnsigned(b, BB);
		c = addUnsigned(c, CC);
		d = addUnsigned(d, DD);
	  }
	
	  var temp = wordToHex(a) + wordToHex(b) + wordToHex(c) + wordToHex(d);
	
	  return temp.toLowerCase();
	}
	
	String.prototype.md5 = function() {
		return md5( this );
	};
	


	var base64_decode = function(data) {
	  //  discuss at: http://phpjs.org/functions/base64_decode/
	  // original by: Tyler Akins (http://rumkin.com)
	  // improved by: Thunder.m
	  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  //    input by: Aman Gupta
	  //    input by: Brett Zamir (http://brett-zamir.me)
	  // bugfixed by: Onno Marsman
	  // bugfixed by: Pellentesque Malesuada
	  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  //   example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
	  //   returns 1: 'Kevin van Zonneveld'
	  //   example 2: base64_decode('YQ===');
	  //   returns 2: 'a'
	
	  var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	  var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
		ac = 0,
		dec = '',
		tmp_arr = [];
	
	  if (!data) {
		return data;
	  }
	
	  data += '';
	
	  do { // unpack four hexets into three octets using index points in b64
		h1 = b64.indexOf(data.charAt(i++));
		h2 = b64.indexOf(data.charAt(i++));
		h3 = b64.indexOf(data.charAt(i++));
		h4 = b64.indexOf(data.charAt(i++));
	
		bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;
	
		o1 = bits >> 16 & 0xff;
		o2 = bits >> 8 & 0xff;
		o3 = bits & 0xff;
	
		if (h3 == 64) {
		  tmp_arr[ac++] = String.fromCharCode(o1);
		} else if (h4 == 64) {
		  tmp_arr[ac++] = String.fromCharCode(o1, o2);
		} else {
		  tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
		}
	  } while (i < data.length);
	
	  dec = tmp_arr.join('');
	
	  return dec.replace(/\0+$/, '');
	}

	
	String.prototype.base64_decode = function() {
		return base64_decode( this );
	};	
	
	
})( window );