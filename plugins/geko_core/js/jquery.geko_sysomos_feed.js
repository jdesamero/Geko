( function( $ ) {
	
	
	$.fn.gekoSysomosFeed = function( options ) {
		
		var opts = $.extend( {
			'load_min': 2000,
			'load_max': 4000,
			'update_delay': 20000,
			'fade_delay': 500,
			'item_limit': 10
		}, options );
		
		
		//
		return this.each( function() {
			
			var oService = opts.service;
			
			var mainCont = $( this );
			
			var sourceUl = mainCont.find( '> div > ul' );
			var loadingDiv = mainCont.find( '.loading' );
			
			
			var loadMin = opts.load_min;
			var loadMax = opts.load_max;
			var updateDelay = opts.update_delay;
			var fadeDelay = opts.fade_delay;
			var itemLimit = opts.item_limit;
			
			
			var iHbId = mainCont.attr( 'data-hbid' );
			
			
			//
			var getFeed = function() {
				
				oService.get( {
					'type': 'rss_content',
					'hbid': iHbId,
					'callbacks': {
						
						'success': function( data ) {

							loadingDiv.fadeOut( fadeDelay, function() {
								sourceUl.fadeIn( fadeDelay );
							} );
							
							if ( 1 == parseInt( data.status ) ) {
								
								// alert( data.unfiltered_count + ':' + data.filtered_count );
								
								var feed = data.feed;
								
								var appendFeed = function() {
									if ( feed.length > 0 ) {
										var item = feed.pop();
										var html = sourceUl.find( '.row-tmpl' ).tmpl( [ item ] );
										html.hide();
										sourceUl.prepend( html );
										html.fadeIn( fadeDelay, function() {
											setTimeout( appendFeed, $.gekoRandomInt( loadMin, loadMax ) );
										} );
									} else {
										
										sourceUl.find( 'li' ).each( function( i ) {
											var li = $( this );
											if ( i > itemLimit ) li.remove();
										} );
										
										setTimeout( getFeed, updateDelay );
									}
								};
								
								appendFeed();
							}
							
						},
						
						'fail': function() {
							setTimeout( getFeed, updateDelay );
						}
						
					}
				} );
				
			}
			
			getFeed();
			
			
		} );
		
	};	

} )( jQuery );
