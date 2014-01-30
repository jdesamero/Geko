( function( $ ) {

	
	$.fn.gekoSysomosTopTen = function( options ) {
		
		var opts = $.extend( {
			'update_delay': 20000,
			'sort_refresh': 5000,
			'sort_anim': 1500,
			'fade_delay': 500,
			'rebuild_delay': ( 1000 * 60 * 2 ),
			'rebuild_stagger': 20
		}, options );
		
		
		//
		return this.each( function() {
			
			var oService = opts.service;
			
			var mainCont = $( this );
			
			var sourceUl = mainCont.find( '> div > ul' );
			var loadingDiv = mainCont.find( '.loading' );
			
			var setRank = function() {
				var i = 0;
				sourceUl.find( 'li' ).each( function() {
					i++;
					var li = $( this );
					li.find( 'div.rank' ).html( i );
				} );
			};
			
			// var aTickers = [];
			var aTags = [];
			
			var updateDelay = opts.update_delay;
			var sortRefresh = opts.sort_refresh;
			var sortAnim = opts.sort_anim;
			var fadeDelay = opts.fade_delay;
			
			var rebuildDelay = opts.rebuild_delay;
			var rebuildStagger = opts.rebuild_stagger;
			
			
			var iHbId = mainCont.attr( 'data-hbid' );
			
			
			//
			var getRebuildDelay = function() {
				
				var calcRebuildDelay;
				
				if ( rebuildStagger ) {
					var halfway = parseInt( rebuildStagger / 2 );
					calcRebuildDelay = ( rebuildDelay - ( 1000 * halfway ) ) + ( 1000 * $.gekoRandomInt( 0, rebuildStagger ) );
				} else {
					calcRebuildDelay = rebuildDelay;
				}
				
				// console.log( calcRebuildDelay );
				
				return calcRebuildDelay;
			};
			
			
			//
			var getTickers = function() {
				
				oService.get( {
					'hbid': iHbId,
					'callbacks': {
						
						'success': function( data ) {
							
							if ( 1 == parseInt( data.status ) ) {
								
								// aTickers = data.tickers;
								aTags = data.tags;
								tickerCount = data.count;
								
								var tickers = $.map( aTags, function( v, k ) {
									return {
										id: k,
										symbol: v,
										title: v,
										mentions: 0
									}
								} );
								
								loadingDiv.fadeOut( fadeDelay, function() {
									
									sourceUl.find( '.row-tmpl' ).tmpl( tickers ).appendTo( sourceUl );
									setRank();
									
									sourceUl.fadeIn( fadeDelay );
									
									sourceUl.find( 'li' ).each( function() {
										var li = $( this );
										li.bind( 'update', function() {
											
											var tag = li.attr( 'data-tag' );
											// var tag = aTickers[ symbol ].tag;
											
											var getMentions = function() {
												
												oService.get( {
													'type': 'measure',
													'hbid': iHbId,
													'callbacks': {
														
														'success': function( data2 ) {

															if ( 1 == parseInt( data2.status ) ) {
															
																var curMentions = parseInt( li.find( 'div.mentions' ).text() );
																var newMentions = data2.mentions;
																var loadMin = 100, loadMax = 1000;
																
																if ( 0 == curMentions ) {
																	curMentions = newMentions - 100;
																}
																
																if ( ( newMentions - curMentions ) >= 100 ) {
																	// make results load faster
																	loadMin = 0;
																	loadMax = 100;
																}
																
																var updateMentions = function() {
																	if ( curMentions == newMentions ) {
																		li.addClass( 'updated' );
																		if ( tickerCount == sourceUl.find( 'li.updated' ).length ) {
																			sourceUl.find( 'li.updated' ).removeClass( 'updated' );
																			setTimeout( function() {
																				sourceUl.find( 'li' ).trigger( 'update' );
																			}, updateDelay );
																		}								
																	} else {
																		
																		if ( curMentions < newMentions ) curMentions++;
																		else curMentions--;
																		
																		li.find( 'div.mentions' ).html( curMentions );
																		updateTimer = setTimeout( updateMentions, $.gekoRandomInt( loadMin, loadMax ) );
																	}
																};
																
																updateMentions();
																
															} else {
																setTimeout( getMentions, updateDelay );
															}
															
														},
														
														'fail': function() {
															setTimeout( getMentions, updateDelay );
														}
														
													},
													'get_params': {
														'tag': tag
													}
												} );
													
											}
											
											getMentions();
											
										} );
									} );
									
									sourceUl.find( 'li' ).trigger( 'update' );
									
								} );
								
							} else {
								setTimeout( getTickers, updateDelay );
							}
							
						},
						
						'fail': function() {
							setTimeout( getTickers, updateDelay );
						}
						
					}
				} );
				
				
			}
			
			// re-sort every [sortRefresh] seconds
			var reSortList = function() {

				sourceUl.css( {
					position: 'relative',
					height: sourceUl.height(),
					display: 'block'
				} );
				
				var srcLi = sourceUl.find( 'li' );
				
				var iLnH;
				
				srcLi.each( function( i ) {
					var li = $( this );
					var iY = li.position().top;
					li.data( 'h', iY );
					if ( i === 1 ) iLnH = iY;
				} );
				
				srcLi.tsort( 'div.mentions', {
					order: 'desc'
				} ).each( function( i ) {
					var li = $( this );
					var iFr = li.data( 'h' );
					var iTo = i * iLnH;
					li.css( { position: 'absolute', top: iFr } ).animate( { top: iTo }, sortAnim );
				} );
				
				setRank();
				setTimeout( reSortList, sortRefresh );
			}
			
			reSortList();
			
			var rebuild = function() {
				sourceUl.fadeOut( fadeDelay, function() {

					loadingDiv.fadeIn( fadeDelay );

					sourceUl.find( 'li' ).remove();
					getTickers();
					setTimeout( rebuild, getRebuildDelay() );
				} );
			};
			
			rebuild();
			
		} );
		
	};	

} )( jQuery );
