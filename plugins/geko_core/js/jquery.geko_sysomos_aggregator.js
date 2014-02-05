( function( $ ) {
	
	//
	$.gekoSysomosAggregator = function( aHbIds, options ) {
		
		var _this = this;
		
		var opts = $.extend( {
			
			'list_update_delay': 1000 * 60 * 10,
			'agg_update_delay': 1000 * 60 * 2
			
		}, options );
		
		
		
		var bStartMentions = false;
		
		var iListUpdateDelay = opts.list_update_delay;
		var iAggUpdateDelay = opts.agg_update_delay;
		
		
		var oService = opts.service;
		
		
		var fGetKey = function( sUnhashed ) {
			sUnhashed = new String( sUnhashed );
			return sUnhashed.md5();
		};
		
		
		
		//// properties
		
		// should follow the same structure as sysomos:measure
		
		
		var oAggregations = {};
		
		// called when initializing the list
		var oAggInit = {};
		
		// called when getting mentions
		var oAggMentions = {
			
			'tag': function( oQueueItem, fGetNextMentions ) {
				
				var aTags = oQueueItem.agg.tags;
				
				var iTagCount = oQueueItem.agg.count;
				
				var fScheduleNext = function() {
					if ( 0 == iTagCount ) {
						fGetNextMentions();
					}
				}
				
				$.each( aTags, function( sTagKey, oTag ) {
					
					oService.get( {
						'type': 'measure',
						'hbid': oTag.hbid,
						'callbacks': {
							
							'success': function( data ) {
								
								if ( 1 == parseInt( data.status ) ) {
									
									oTag.prev_mentions = oTag.mentions;
									oTag.mentions = parseInt( data.mentions );
									
								}
								
								// decrement
								iTagCount--;
								
								fScheduleNext();
							},
							
							'fail': function() {

								// decrement
								iTagCount--;
								
								fScheduleNext();
							}
							
						},
						'get_params': {
							'tag': oTag.tag
						}
					} );
					
				} );
				
			},
			
			'heartbeat': function( oQueueItem, fGetNextMentions ) {
				
				var aTags = oQueueItem.agg.tags;
				
				var oTagAgg = oAggregations[ 'tag' ];
				
				// reset tags
				$.each( aTags, function( iHbId, oTag ) {
					oTag.prev_mentions = oTag.mentions;
					oTag.mentions = 0;
				} );
				
				// start aggregation
				$.each( oTagAgg.tags, function( k, v ) {
					
					var sTagKey = fGetKey( v.hbid );
					// var sTagKey = '%d:%s'.printf( v.hbid, v.tag );
					
					var oTag = aTags[ sTagKey ];

					oTag.mentions += v.mentions;
					
				} );
				
				fGetNextMentions();
			}
			
		};
		
		
		
		
		var fInitAggregation = function( sKey, options ) {
			
			oAggregations[ sKey ] = {
				'name': '',
				'tags': {},
				'count': 0
			};
			
			if ( options ) {
				
				// init callback
				if ( options.init ) {
					oAggInit[ sKey ] = options.init;
				}
				
				// mentions callback
				if ( options.mentions ) {
					oAggMentions[ sKey ] = options.mentions;
				}				
			}
			
		};
		
				
		
		
		//
		var aMentionsQueue = [];
		
		var fGetNextMentions = function() {
			
			if ( 0 == aMentionsQueue.length ) {
				// take a break
				setTimeout( fGetMentions, iAggUpdateDelay );
			} else {
				// do next one immediately
				fGetMentions();
			}
			
		};
		
		var fGetNextMentions = function() {
			if ( 0 == aMentionsQueue.length ) {
				setTimeout( fGetMentions, iAggUpdateDelay );
			} else {
				fGetMentions();		// do next immediately
			}
		};
		
		var fGetMentions = function() {
			
			if ( 0 == aMentionsQueue.length ) {

				// populate the queue
				$.each( oAggregations, function( sAggKey, oAgg ) {
					
					aMentionsQueue.push( {
						'agg_key': sAggKey,
						'agg': oAgg
					} );
					
				} );
			
			}
			
			var oQueueItem = aMentionsQueue.shift();
			
			var fQueueHandler = oAggMentions[ oQueueItem.agg_key ];
			
			if ( fQueueHandler ) {
				fQueueHandler.call( _this, oQueueItem, fGetNextMentions );
			} else {
				fGetNextMentions();
			}
			
		};
		
		
		
		// gather information from each heartbeat
		var fGetHeartbeat = function() {
						
			var iHbCount = aHbIds.length;

			var fScheduleNext = function() {
				
				// take a break when done, then do it again
				if ( 0 == iHbCount ) {
					
					setTimeout( fGetHeartbeat, iListUpdateDelay );
					
					if ( !bStartMentions ) {
						
						fGetMentions();		// start polling mentions
						
						bStartMentions = true;
					}
				}
			};
			
			$.each( aHbIds, function ( i, iCurHbId ) {
				
				oService.get( {
					'hbid': iCurHbId,
					'callbacks': {
						
						'success': function( data ) {
							
							if ( 1 == parseInt( data.status ) ) {
								
								var oTag = oAggregations.tag;
								var oHeartbeat = oAggregations.heartbeat;
								
								//// assign
								
								var aTags = data.tags;
								
								var sHbKey = fGetKey( iCurHbId );
								// var sHbKey = iCurHbId;
								
								if ( !oHeartbeat.tags[ sHbKey ] ) {
									
									oHeartbeat.tags[ sHbKey ] = {
										title: data.name,
										mentions: 0,
										prev_mentions: 0,
										hbid: iCurHbId
									};
									
									oHeartbeat.count++;
								}
								
								$.each( aTags, function( k, v ) {
									
									var sTagKey = fGetKey( '%d:%s'.printf( iCurHbId, k ) );
									// var sTagKey = '%d:%s'.printf( iCurHbId, k );
									
									if ( !oTag.tags[ sTagKey ] ) {
										
										oTag.tags[ sTagKey ] = {
											title: v,
											tag: k,
											mentions: 0,
											prev_mentions: 0,
											hbid: iCurHbId,
											hb_title: data.name
										};
										
										oTag.count++;
									}
									
								} );
								
								//// other init handlers
								
								$.each( oAggInit, function( sKey, fInit ) {
									
									fInit.call( _this, oAggregations[ sKey ] );
									
								} );
								
							}
							
							
							// decrement by 1
							iHbCount--;
							
							fScheduleNext();
						},
						
						'fail': function() {
							
							// decrement by 1 as well, so we don't lose count
							iHbCount--;
							
							fScheduleNext();
						}
						
					}
				} );
				
			} );
			
			
		};
		
				
		//// methods
		
		
		// default aggregations
		fInitAggregation( 'tag' );
		fInitAggregation( 'heartbeat' );
		
		
		//
		this.init = function() {
			
			fGetHeartbeat();		// init
			
			return _this;
		};
		
		//
		this.getAggregation = function( sKey ) {
			
			if ( !sKey ) sKey = 'tag';
			
			return oAggregations[ sKey ];
		};
		
		// create a custom aggregation
		this.setAggregation = function( sKey, options ) {
			
			fInitAggregation( sKey, options );
			
			return _this;
		};
		
	};	

} )( jQuery );
