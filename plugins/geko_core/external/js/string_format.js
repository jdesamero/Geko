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
	

})( window );