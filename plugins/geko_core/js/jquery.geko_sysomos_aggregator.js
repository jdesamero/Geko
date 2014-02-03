( function( $ ) {
	
	//
	$.gekoSysomosAggregator = function( aHbIds, options ) {
		
		var _this = this;
		
		var opts = $.extend( {
			
			'list_update_delay': 1000 * 60 * 10,
			'item_update_delay': 1000 * 60 * 2
			
		}, options );
		
		
		
		var bStartMentions = false;
		
		var iListUpdateDelay = opts.list_update_delay;
		var iItemUpdateDelay = opts.item_update_delay;
		
		
		var oService = opts.service;
		
		
		
		//// properties
		
		// should follow the same structure as sysomos:measure
		
		
		var oAggregations = {};
		
		var aIterate = [];
		var aMentionsQueue = [];
		
		var oAggHandlers = {
			
			'tag': function( oQueueItem, fGetNextMentions ) {
				
				var oTag = oQueueItem.tag;
				
				oService.get( {
					'type': 'measure',
					'hbid': oTag.hbid,
					'callbacks': {
						
						'success': function( data ) {
							
							if ( 1 == parseInt( data.status ) ) {
								
								oTag.mentions = parseInt( data.mentions );
								
								fGetNextMentions();
								
							} else {
								setTimeout( fGetNextMentions, iItemUpdateDelay );
							}
							
						},
						
						'fail': function() {
							setTimeout( fGetNextMentions, iItemUpdateDelay );
						}
						
					},
					'get_params': {
						'tag': oQueueItem.tag_key
					}
				} );
				
			},
			
			'heartbeat': function( oQueueItem, fGetNextMentions ) {
				
				var oTag = oQueueItem.tag;
				
				var oTagAgg = oAggregations[ 'tag' ];
				
				oTag.mentions = 0;
				
				$.each( oTagAgg.tags, function( k, v ) {
					
					// add it up
					if ( oQueueItem.tag_key == v.hbid ) {
						oTag.mentions += v.mentions;
					}
					
				} );
				
				fGetNextMentions();
				
			}
			
		};
		
		var fInitAggregation = function( sKey ) {
			oAggregations[ sKey ] = {
				'name': '',
				'tags': {},
				'count': 0
			};
		}
		
		
		fInitAggregation( 'tag' );
		fInitAggregation( 'heartbeat' );
		
		
		var fGetNextMentions = function() {
			
			if ( 0 == aMentionsQueue.length ) {
				// take a break
				setTimeout( fGetMentions, iItemUpdateDelay );
			} else {
				// do next one immediately
				fGetMentions();
			}
			
		};
		
		//
		var fGetMentions = function() {
			
			if ( 0 == aMentionsQueue.length ) {
				
				// populate the queue
				$.each( oAggregations, function( sAggKey, oAgg ) {
					
					$.each( oAgg.tags, function( sTagKey, oTag ) {
						
						aMentionsQueue.push( {
							'agg_key': sAggKey,
							'tag_key': sTagKey,
							'tag': oTag
						} );
						
					} );
					
				} );
				
			}
			
			
			var oQueueItem = aMentionsQueue.shift();
			
			var fQueueHandler = oAggHandlers[ oQueueItem.agg_key ];
			
			fQueueHandler( oQueueItem, fGetNextMentions );
			
		};
		
		
		
		// gather information from each heartbeat
		var fGetHeartbeat = function() {
			
			if ( 0 == aIterate.length ) {
				aIterate = aHbIds.slice();		// make a copy
			}
			
			var iCurHbId = aIterate.shift();
			
			oService.get( {
				'hbid': iCurHbId,
				'callbacks': {
					
					'success': function( data ) {
						
						if ( 1 == parseInt( data.status ) ) {
							
							var oTag = oAggregations.tag;
							var oHeartbeat = oAggregations.heartbeat;
							
							//// assign
							
							var aTags = data.tags;
							
							if ( !oHeartbeat.tags[ iCurHbId ] ) {
								oHeartbeat.tags[ iCurHbId ] = {
									title: data.name,
									mentions: 0
								};
								oHeartbeat.count++;
							}
							
							$.each( aTags, function( k, v ) {
								
								if ( !oTag.tags[ k ] ) {
									
									oTag.tags[ k ] = {
										title: v,
										mentions: 0,
										hbid: iCurHbId,
										hb_title: data.name
									};
									
									oTag.count++;
								}
								
							} );
							
							
							//// custom handlers ???
							
							
							if ( 0 == aIterate.length ) {
								
								// start getting mentions
								if ( !bStartMentions ) {
									
									fGetMentions();
									
									bStartMentions = true;
								}
								
								// take a break
								setTimeout( fGetHeartbeat, iListUpdateDelay );
																
							} else {
								// get next immediately
								fGetHeartbeat();
							}
							
						} else {
							setTimeout( fGetHeartbeat, iListUpdateDelay );
						}
						
					},
					
					'fail': function() {
						setTimeout( fGetHeartbeat, iListUpdateDelay );
					}
					
				}
			} );
			
		};
		
				
		//// methods
		
		this.init = function() {
			
			fGetHeartbeat();		// init
			
			return _this;
		}
		
		this.getAggregation = function( sKey ) {
			
			if ( !sKey ) sKey = 'tag';
			
			return oAggregations[ sKey ];
		}
		
	};	

} )( jQuery );
