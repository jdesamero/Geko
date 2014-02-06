( function( $ ) {

	
	$.fn.gekoSysomosTopTen = function( options ) {
		
		var opts = $.extend( {
			
			'update_delay': 20000,
			'sort_refresh': 5000,
			'sort_anim': 1500,
			'fade_delay': 500,
			'rebuild_delay': ( 1000 * 60 * 2 ),
			'rebuild_stagger': 20,
			
			'format_name': null,
			'format_tag': null,
			
			'title_sel': null
			
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
			var getTags = function() {
				
				oService.get( {
					
					'success': function( data ) {
						
						if ( 1 == parseInt( data.status ) ) {
							
							// apply format_name() callback if specified
							var sName = data.name;
							if ( opts.format_name ) {
								sName = opts.format_name( sName );
							}
							
							// if title selector was supplied, then populate
							if ( opts.title_sel ) {
								mainCont.find( opts.title_sel ).html( sName );
							}
							
							var aTags = data.tags;
							tagCount = data.count;
							
							var aTagsFmt = $.map( aTags, function( v, k ) {
								
								var oTag =  {
									id: k,
									mentions: 0
								};
								
								// merge params
								if ( 'object' === $.type( v ) ) {
									oTag = $.extend( oTag, v );
								} else {
									oTag.title = v;
								}
								
								// apply format_tag() callback if specified
								if ( opts.format_tag ) {
									oTag = opts.format_tag( oTag, sName );
								}
								
								return oTag;
							} );
							
							loadingDiv.fadeOut( fadeDelay, function() {
								
								sourceUl.find( '.row-tmpl' ).tmpl( aTagsFmt ).appendTo( sourceUl );
								setRank();
								
								sourceUl.fadeIn( fadeDelay );
								
								sourceUl.find( 'li' ).each( function() {
									var li = $( this );
									li.bind( 'update', function() {
										
										var tag = li.attr( 'data-tag' );
										
										var getMentions = function() {
											
											oService.get( {
												
												'type': 'measure',
												
												'success': function( data2 ) {

													if ( 1 == parseInt( data2.status ) ) {
														
														var curMentions = parseInt( li.find( 'div.mentions' ).text() );
														var newMentions = data2.mentions;
														var loadMin = 100, loadMax = 1000;
														
														if ( 0 == curMentions ) {
															if ( newMentions > 100 ) {
																curMentions = newMentions - 100;
															} else {
																curMentions = newMentions;
															}
														}
														
														if ( ( newMentions - curMentions ) >= 100 ) {
															// make results load faster
															loadMin = 0;
															loadMax = 100;
														}
														
														var updateTimer = null;
														
														var updateMentions = function() {
															
															if ( curMentions == newMentions ) {
																
																if ( updateTimer ) {
																	clearTimeout( updateTimer );
																	updateTimer = null;
																}
																
																li.addClass( 'updated' );
																if ( tagCount == sourceUl.find( 'li.updated' ).length ) {
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
												},
													
												'get_params': {
													'hbid': iHbId,
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
							setTimeout( getTags, updateDelay );
						}
						
					},
					
					'fail': function() {
						setTimeout( getTags, updateDelay );
					},
					
					'get_params': {
						'hbid': iHbId
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
					getTags();
					setTimeout( rebuild, getRebuildDelay() );
				} );
			};
			
			rebuild();
			
		} );
		
	};	

} )( jQuery );
