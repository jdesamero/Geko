( function( $ ) {

	
	$.fn.gekoSysomosTopTen = function( options ) {
		
		var opts = $.extend( {
			
			'update_delay': 20000,								// update mentions every 20 seconds
			'sort_refresh': 1000,
			'sort_anim': 1500,
			'fade_delay': 500,
			'rebuild_delay': ( 1000 * 60 * 2 ),
			'rebuild_stagger': 20,
			
			'drama_group': 1,
			'drama_delay': 500,
			
			'line_height': null,
			
			'format_name': null,
			'format_tag': null,
			
			'title_sel': null,
			
			'rank_after_sort': false
			
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
			
			var iDramaGroup = opts.drama_group;
			var iDramaDelay = opts.drama_delay;
			
			var iLineHeight = opts.line_height;
			
			
			
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
								
								sourceUl.data( 'lock_update', true );
								
								$.each( aTagsFmt, function( i, oTag ) {
									
									var eAppendLi = null;
									
									var eLi = holderUl.find( 'li[data-tag="%s"]'.printf( oTag.id ) )
									
									if ( 0 == eLi.length ) {
										
										// create new
										oTag.mentions = 0;
										
										var eNewLi = sourceUl.find( '.row-tmpl' ).tmpl( oTag );
										
										eNewLi.bind( 'update', function() {
											
											var li = $( this );
											
											var updateTimer = null;
											
											var tag = li.attr( 'data-tag' );
											
											//
											var getMentions = function() {
																								
												//
												var fSetMention = function( newMentions ) {
													
													if ( updateTimer ) {
														clearTimeout( updateTimer );
														updateTimer = null;
													}
													
													var curMentions = parseInt( li.find( 'div.mentions' ).text() );
													var loadMin = 100, loadMax = 1000;
													
													if ( ( 0 == curMentions ) && ( newMentions > 100 ) ) {
														curMentions = newMentions - 100;
													}
													
													if ( ( newMentions - curMentions ) >= 100 ) {
														// make results load faster
														loadMin = 0;
														loadMax = 100;
													}
													
													
													// update until current mentions is caught up with new mentions
													var updateMentions = function() {
														
														if ( curMentions == newMentions ) {
															
															// mentions equalized, so unlock
															li.data( 'lock_mention', false )
															
															if ( updateTimer ) {
																clearTimeout( updateTimer );
																updateTimer = null;
															}
															
															li.addClass( 'updated' );
															
															// wait until everyone has been updated
															if ( sourceUl.find( 'li' ).length == sourceUl.find( 'li.updated' ).length ) {
																
																sourceUl.find( 'li.updated' ).removeClass( 'updated' );
																
																sourceUl.data( 'lock_update', false );
																
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
												
												var bLockDrama = li.data( 'lock_drama' );
												
												var fDoDrama = function() {
													
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
												};
												
												if ( bLockDrama ) {
													var iDramaGroup = li.data( 'drama_group' );
													setTimeout( fDoDrama, iDramaGroup * iDramaDelay );
													li.data( 'lock_drama', false );
												} else {
													fDoDrama();
												}
												
												// -------------------------------------------------
												
											}
											
											// only initiate if mention lock is not set
											if ( !li.data( 'lock_mention' ) ) {
												li.data( 'lock_mention', true );
												getMentions();
											}

										} );
										
										eAppendLi = eNewLi;
										
									} else {
										
										// exists, reset and do other updates
										// re-append to source ul
										
										eLi.removeAttr( 'style' );
										eLi.css( { position: 'relative' } );
										eLi.removeData( 'h' );
										eLi.find( 'div.mentions' ).html( 0 );
										
										eAppendLi = eLi;
										
									}
									
									//
									eAppendLi.data( 'lock_drama', true );
									eAppendLi.data( 'drama_group', parseInt( i / iDramaGroup ) );
									
									sourceUl.append( eAppendLi );
									
								} );
								
								
								setRank();
								
								sourceUl.fadeIn( fadeDelay );
								
								sourceUl.find( 'li' ).trigger( 'update' );
								
								sourceUl.data( 'lock_rebuild', false );
								
								
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
				
				
			};
			
			
			// re-sort every [sortRefresh] seconds
			var reSortList = function() {
				
				var srcLi = sourceUl.find( 'li' );
				var iNumLis = srcLi.length;
				
				if ( ( 0 !== iNumLis ) && ( !sourceUl.data( 'lock_rebuild' ) ) ) {
					
					sourceUl.data( 'lock_sort', true );
					
					sourceUl.css( {
						position: 'relative',
						height: sourceUl.height(),
						display: 'block'
					} );
					
					
					var iLnH = null;
					
					if ( iLineHeight ) {
						iLnH = iLineHeight;			// force static line height
					}
					
					
					//
					srcLi.each( function( i ) {
						
						var iY = null;
						var li = $( this );
						
						if ( !iLnH ) {
							
							iY = li.position().top;
							if ( i === 1 ) {
								iLnH = iY;
							}
							
						} else {
	
							iY = i * iLnH;
						}
						
						li.data( 'h', iY );
						
					} );
					
					
					//
					srcLi.tsort( 'div.mentions', {
						order: 'desc'
					} ).each( function( i ) {
						
						var li = $( this );
						var iFr = li.data( 'h' );
						var iTo = i * iLnH;
						
						li.css( {
							position: 'absolute',
							top: iFr
						} ).animate( { top: iTo }, sortAnim, 'swing', function() {
							
							if ( i == ( iNumLis - 1 ) ) {
								
								if ( opts.rank_after_sort ) {
									setRank();
								}
								
								sourceUl.data( 'lock_sort', false );
								
								setTimeout( reSortList, sortRefresh );
							}
													
						} );
						
					} );
					
					if ( !opts.rank_after_sort ) {
						setRank();
					}								
					
				} else {
					
					// try again in half a second
					setTimeout( reSortList, 500 );
				}
				
			};
			
			reSortList();
			
			
			// rebuild list by querying new data
			var rebuild = function() {
				
				if ( !sourceUl.data( 'lock_sort' ) && !sourceUl.data( 'lock_update' ) ) {
					
					sourceUl.data( 'lock_rebuild', true );
					
					sourceUl.fadeOut( fadeDelay, function() {
						
						// move existing li's to holder
						holderUl.append( sourceUl.find( 'li' ) );
						
						// remove all nodes for a hard rebuild
						// sourceUl.find( 'li' ).remove();
						
						loadingDiv.fadeIn( fadeDelay, function() {
							
							getTags();
							
							setTimeout( rebuild, getRebuildDelay() );
							
						} );
						
					} );
					
				} else {
					
					// try again in half a second
					setTimeout( rebuild, 500 );					
				}
			};
			
			rebuild();
			
			
		} );
		
	};	

} )( jQuery );