// **************************************************************************
// Copyright 2007 - 2009 Tavs Dokkedahl
// Contact: http://www.jslab.dk/contact.php
//
// This file is part of the JSLab Standard Library (JSL) Program.
//
// JSL is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// any later version.
//
// JSL is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ***************************************************************************

// Parse a string to a date. Modelled after the PHP function strtotime      
Date.parse =
  function(s,rd) {
    // If s is not specified
    if (!s)
      return new Date();
    // Trim s
    s = s.replace(/^\s+|\s+$/g,"");
    // If no reference date is provided the reference is now
    if (!rd)
      rd = new Date();
    // Get keywords used for relative parsing
    var t = '';
    for(var p in Date.parse.keywords)
      t += p + '|';
    t = t.substring(0, t.length-1);
    var rgx = new RegExp(t,'i');
    // Determine to parse absolute or relative
    if (rgx.test(s))
      return Date.parse.relative(s, rd);
    else
      return Date.parse.absolute(s, rd);
  };

// Parse absolute date
Date.parse.absolute =
  function(s, rd) {
    var r = null, y = null, m = null, d = null, h = null, mi = null, se = null, ms = null;
    // Date format 1972-09-24, 72-9-24, 72-09-24
    if (r = s.match(/^(\d{4}|\d{2})-(\d{1,2})-(\d{1,2})/)) {
      // Year
      y = r[1] * 1;
      // Month - JS months is zero based
      m = r[2] * 1 - 1;
      // Date
      d = r[3] * 1;
    }
    // US date format 9/24/72 (m/d/y)
    else if (r = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4}|\d{2})/)) {
      // Month
      m = r[1] * 1 - 1;
      // Date
      d = r[2] * 1;
      // Year
      y = r[3] * 1;
    }
    // Month written litteraly
    // Date format 4 September 1972, 24 Sept 72, 24 Sep 72, 24-sep-72
    else if (r = s.match(/^(\d{1,2})(\s*|-)([a-z]{3,})((\s+|-)(\d{4}|\d{2}))?/i)) {
      // Date
      d = r[1] * 1;
      // Month
      var rgx = new RegExp('^' + r[3],'i');
      for(var i=0; i<11; i++) {
        if (rgx.test(Date.nameOfMonths[i])) {
          m = i;
          break;
        }
      }
      if (m === null)
        throw new Error('Date.parse: Unknown month specified litteraly');
      // Year
      if (r[6])
        y = r[6] * 1;
    }
    // Check date
    // Check year
    if (y < 100)
      y = y > 68 && y < 100 ? 1900 + y : 2000 + y;
    else if (y && (y < 1972 || y > 2068))
      throw new Error('Date.parse: Year out of range. Valid input is 1972 through 2068');
    // Check month
    if (m && (m < 0 || m > 11))
      throw new Error('Date.parse: Month out of range. Valid input is 01 through 12');
    // Check date - don't check for 28/29 in feb etc. Just overflow dates
    if (d && (d < 1 || d > 31))
      throw new Error('Date.parse: Date out of range. Valid input is 01 through 31');
    // Set date
    if (y && (m !== null) && d )
      rd.setFullYear(y, m, d);
    // ***
    // Parse time
    // ***
    // Old regex used to detect timezone - contains am/pm error
    //if (r = s.match(/(\d{1,2})\:(\d{1,2})(?:(?:\:(\d{1,2})(?:\.(\d{1,3}))?)(?:(am|pm)?))?(?:([\+-])(\d{2})\:?(\d{2}))?/)) {
    if (r = s.match(/(\d{1,2})\:(\d{1,2})(?:(?:\:(\d{1,2})(?:\.(\d{1,3}))?)?(?:\s*(am|pm)?))?/)) {
      /*
      Timezone
      TZ sign is r[6]
      TZ hour is r[7]
      TZ minutes is r[8]
      It doesn't make sense to adjust timezones as the you can't change timezone with JS
      */
      // Hour
      h = r[1] * 1;
      // If am/pm is specified
      if (r[5]) {
        if (h < 1 || h > 12)
          throw new Error('Date.parse: Hour out of range (using am/pm). Valid input is 1 through 12');
        // If am
        if (r[5] == 'am') {
          if (h == 12)
            h = 0;
        }
        // If pm
        else {
          if (h != 12)
            h = h + 12;
        }
      }
      else {
        if (h > 24)
          throw new Error('Date.parse: Hour out of range. Valid input is 00 through 23');
      }
      // Minute
      if (r[2]) {
        mi = r[2] * 1;
        if (mi > 59)
          throw new Error('Date.parse: Minute out of range. Valid input is 00 through 59');
      }
      // Seconds
      if (r[3]) {
        se = r[3] * 1;
        if (se > 59)
          throw new Error('Date.parse: Seconds out of range. Valid input is 00 through 59');
      }
      // Msecs
      if (r[4]) {
        // For whatever reason the multiplication becomes slightly incorrect and have to be ceiled.
        ms = Math.ceil(('1.' + r[4]) * 1000) - 1000;
      }
    }
    // Set time
    if (h !== null)
      rd.setHours(h);
    if (mi !== null)
      rd.setMinutes(mi);
    if (se !== null)
      rd.setSeconds(se);
    if (ms !== null)
      rd.setMilliseconds(ms);
    return rd;
  };

// Parse relative date
Date.parse.relative =
  function(s,rd) {
    // If relative date is given as a single word - ie. now, today, tomorrow, yesterday, fortnight
    if (/^now|today|tomorrow|fortnight|yesterday$/.test(s)) {
      rd.setDate(rd.getDate() + Date.parse.keywords[s]);
    }
    else {
      var mod;
      var p = /(last|this|next|first|third|fourth|fifth|sixth|seventh|eighth|ninth|tenth|eleventh|twelfth|(?:[\+-]?\d+))\s+([a-z]+)(?:\s+(ago))?/g;
      while((r = p.exec(s)) != null) {
        // r[1] is relative number or word
        // r[2] is time interval
        // r[3] is optional 'ago'
        // If modifier is not a number 'ago' does not apply
        if (/(?:[\+-]?\d+)/.test(r[1]))
          mod = !r[3] ? parseInt(r[1]) : -1 * parseInt(r[1]);
        else
          mod = Date.parse.keywords[r[1]];
        // Remove plural s and convert to lower case
        r[2] = r[2].replace(/s$/,'').toLowerCase();
        // Switch on interval
        switch(r[2]) {
          case 'year':
            rd.setFullYear(rd.getFullYear() + mod);
            break;
          case 'month':
            rd.setMonth(rd.getMonth() + mod);
            break;
          case 'week':
            rd.setDate(rd.getDate() + mod * 7);
            break;
          case 'day':
            rd.setDate(rd.getDate() + mod);
            break;
          case 'hour':
            rd.setHours(rd.getHours() + mod);
            break;
          case 'minute':
            rd.setMinutes(rd.getMinutes() + mod);
            break;
          case 'second':
            rd.setSeconds(rd.getSeconds() + mod);
            break;
          default:
            // Check for weekdays
            var rgx = new RegExp('^' + r[2],'i');
            for(var i=0; i<7; i++) {
              if (rgx.test(Date.nameOfDays[i]))
                break;
            }
            // If weekday exists
            if (i < 7) {
              var d = rd.getISODay() - 1;
              // If weekday is in the future
              if (i > d)
                rd.setDate(rd.getDate() + (i - d) + ((mod - 1) * 7));
              else
                rd.setDate(rd.getDate() + (i - d) + ((mod - 1) * 7) + 7);
            }
            else
              throw new Error('Date.parse: Unknown keyword in input');
            break;
        }
      }
    }
    return rd;
  };

// Valid keywords in input string
Date.parse.keywords =
  {
    // Absolute. Numbers are offsets in days
    now: 0,
    today: 0,
    tomorrow: 1,
    fortnight: 14,
    yesterday: -1,
    // Relative
    last: -1,
    'this': 1,
    next: 1,
    // Ordinal numbers
    first: 1,
    third: 3,
    fourth: 4,
    fifth: 5,
    sixth: 6,
    seventh: 7,
    eighth: 8,
    ninth: 9,
    tenth: 10,
    eleventh: 11,
    twelfth: 12,
    // Intervals
    second: null,
    minute: null,
    hour: null,
    day: null,
    week: null,
    month: null,
    year: null
  };
  
  
  