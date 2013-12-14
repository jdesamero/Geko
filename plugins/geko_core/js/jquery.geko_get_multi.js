//// work with multiple ajax get requests
//// by Joel Desamero

;(function ($) {
	
	$.extend({
		getMulti: function( aGetList, fGetUrlCb, fReqCb, fDoneCb ) {
			
			var iDoneCount = 0;
			var iListLen = aGetList.length;
			var i = 0;
								
			var execGet = function( i, sGetUrl ) {
				$.get( sGetUrl, function( res ) {
					fReqCb( i, res, aGetList[ i ] );
					iDoneCount++;						// advance the done counter
					
					// only execute the "done" callback function if all the requests have
					// fully completed
					if ( ( iDoneCount == iListLen ) && fDoneCb ) fDoneCb();
				});
			}
			
			// it is assumed that aGetList member is a string representing the get request
			// in case it is not, a callback function can be specified as to how to retrive
			// the request url
			if ( !fGetUrlCb ) {
				fGetUrlCb = function( mListItem ) {
					return mListItem;
				}
			}
			
			// execute the requests
			for ( i = 0; i < iListLen; i++ ) {
				execGet( i, fGetUrlCb( aGetList[ i ] ) );
			}
			
		}
	});
	
})(jQuery);

/* /

//// usage 1

var xml_list = [ <xml file 1>, <xml file 2>, ... ];

$.getMulti(
	xml_list,
	null,
	function ( i, xml ) {
		// ops to do for each request
	},
	function () {
		// op to perform once all requests are completed
	}
);



//// usage 2

var xml_list = [
	{ file: <xml file 1>, param: 'foo' },
	{ file: <xml file 2>, param: 'bar' },
	...
];

$.getMulti(
	xml_list,
	function( item ) {
		return item.file;
	},
	function ( i, xml, item ) {
		// ops to do for each request
		
		// do something with "item"
		// eg: alert( item.param );
	},
	function () {
		// op to perform once all requests are completed
	}
);

/* */

