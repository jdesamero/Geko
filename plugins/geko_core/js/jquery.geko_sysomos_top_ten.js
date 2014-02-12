( function( $ ) {

	
	$.fn.gekoSysomosTopTen = function( options ) {
		
		var opts = $.extend( {
			
			'update_delay': 20000,								// update mentions every 20 seconds
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
			
			var holderUl = $( '<ul></ul>' );				// temporary holder
			holderUl.hide();
			
			sourceUl.after( holderUl );
			
			
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
			
			
			// mention lookup
			var oMnLookup = {
				'locked': false,
				'tags': {},
				'init': false
			};
			
			var fLoadLookup = function( aTagsFmt ) {

				// lock and load lookup object
				if ( !oMnLookup.locked ) {
				
					oMnLookup.locked = true;
					
					$.each( aTagsFmt, function( k, v ) {
						oMnLookup.tags[ v.id ] = v.mentions;
					} );
					
					oMnLookup.locked = false;
					
				} else {
					console.log( 'Already Locked!' );
				}
				
			};
			
			
			//
			var fFormatTag = function( v, k ) {
				
				var oTag =  { id: k };
				
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
			};
			
			
			
			// refresh
			var fRefreshLookup = function( bInit ) {
				
				if ( bInit ) {
					
					// service just called, poll later
					setTimeout( fRefreshLookup, updateDelay );
					
				} else {
	
					oService.get( {
						
						'success': function( data ) {
							
							if ( 1 == parseInt( data.status ) ) {
	
								var aTags = data.tags;
								
								var aTagsFmt = $.map( aTags, fFormatTag );
								
								fLoadLookup( aTagsFmt );
								
							}
							
							setTimeout( fRefreshLookup, updateDelay );
							
						},
						
						'fail': function() {
							setTimeout( fRefreshLookup, updateDelay );
						},
						
						'get_params': {
							'hbid': iHbId
						}
						
					} );
				}
				
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

							var bHasMentionData = data.has_mention_data;
							
							var aTags = data.tags;
							var aTagsFmt = $.map( aTags, fFormatTag );
							
							if ( bHasMentionData ) {
								
								fLoadLookup( aTagsFmt );
								
								if ( !oMnLookup.init ) {
									fRefreshLookup( true );
									oMnLookup.init = true;
								}							
							}
							
							//
							loadingDiv.fadeOut( fadeDelay, function() {
								
								$.each( aTagsFmt, function( i, oTag ) {
									
									var eLi = holderUl.find( 'li[data-tag="%s"]'.printf( oTag.id ) )
									
									if ( 0 == eLi.length ) {
										
										// create new
										oTag.mentions = 0;
										
										var eNewLi = sourceUl.find( '.row-tmpl' ).tmpl( oTag );
										
										eNewLi.bind( 'update', function() {
											
											var li = $( this );
											
											var updateTimer = null;
											
											var tag = li.attr( 'data-tag' );
											
											var getMentions = function() {
												
												///
												var fSetMention = function( newMentions ) {

													if ( updateTimer ) {
														clearTimeout( updateTimer );
														updateTimer = null;
													}
													
													var curMentions = parseInt( li.find( 'div.mentions' ).text() );
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
													
													
													// update until current mentions is caught up with new mentions
													var updateMentions = function() {
														
														if ( curMentions == newMentions ) {
															
															if ( updateTimer ) {
																clearTimeout( updateTimer );
																updateTimer = null;
															}
															
															li.addClass( 'updated' );
															
															// wait until everyone has been updated
															if ( sourceUl.find( 'li' ).length == sourceUl.find( 'li.updated' ).length ) {
																
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
													
												};			// end fSetMention
												
												
												// -------------------------------------------------
												
												if ( bHasMentionData ) {
													
													fSetMention( oMnLookup.tags[ tag ] );
													
												} else {
													
													// poll individually
													
													oService.get( {
														
														'type': 'measure',
														
														'success': function( data2 ) {
		
															if ( 1 == parseInt( data2.status ) ) {
																
																fSetMention( data2.mentions );
																
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
												
												// -------------------------------------------------
												
											}
											
											getMentions();
											
										} );
										
										sourceUl.append( eNewLi );
										
										// console.log( 'New :' + oTag.id );
										
									} else {
										
										// exists, reset and do other updates
										// re-append to source ul
										
										eLi.removeAttr( 'style' );
										eLi.css( { position: 'relative' } );
										eLi.removeData( 'h' );
										eLi.find( 'div.mentions' ).html( 0 );
										
										sourceUl.append( eLi );
										
										// console.log( 'Existing :' + oTag.id );
									}
									
								} );
								
								
								setRank();
								
								sourceUl.fadeIn( fadeDelay );
								
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
					
					// move existing li's to holder
					holderUl.append( sourceUl.find( 'li' ) );
					
					
					// remove all nodes for a hard rebuild
					// sourceUl.find( 'li' ).remove();
					
					loadingDiv.fadeIn( fadeDelay );
					
					
					
					getTags();
					
					setTimeout( rebuild, getRebuildDelay() );
				} );
			};
			
			rebuild();
			
		} );
		
	};	

} )( jQuery );
