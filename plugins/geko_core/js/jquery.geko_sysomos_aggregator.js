( function( $ ) {
	
	//
	var Aggregator = function( aHbIds, options ) {
		
		var _this = this;
		
		var opts = $.extend( {
			
			'list_update_delay': 1000 * 60 * 10,
			'item_update_delay': 1000 * 60 * 2
			
		}, options );
		
		
		
		var bStartMentions = false;
		
		var iListUpdateDelay = opts.list_update_delay;
		var iItemUpdateDelay = opts.item_update_delay;
		
		
		var oService = opts.service;
		
		
		
		var fGetKey = function( sUnhashed ) {
			sUnhashed = new String( sUnhashed );
			return sUnhashed.md5();
		};

		
		
		//// properties
		
		// should follow the same structure as sysomos:measure
		
		
		var oAggregations = {};
		
		var aIterate = [];
		var aMentionsQueue = [];
		
		// called when initializing the list
		var oAggInit = {};
		
		// filter tags
		var oAggFilter = {};
		
		// called when getting mentions
		var oAggMentions = {
			
			'tag': function( oQueueItem, fGetNextMentions ) {
				
				var oTag = oQueueItem.tag;
				
				oService.get( {
					
					'type': 'measure',

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
					},
					
					'get_params': {
						'tag': oTag.tag,
						'hbid': oTag.hbid
					}
					
				} );
				
			},
			
			'heartbeat': function( oQueueItem, fGetNextMentions ) {
				
				var oTag = oQueueItem.tag;
				
				var oTagAgg = oAggregations[ 'tag' ];
				
				oTag.mentions = 0;
				
				$.each( oTagAgg.tags, function( k, v ) {
					
					// add it up
					if ( oQueueItem.tag_key == fGetKey( v.hbid ) ) {
						oTag.mentions += v.mentions;
					}
					
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

				// filter callback
				if ( options.filter ) {
					oAggFilter[ sKey ] = options.filter;
				}
			}
			
		};
		
		
		
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
					
					// only if there is a handler
					if ( oAggMentions[ sAggKey ] ) {
						
						$.each( oAgg.tags, function( sTagKey, oTag ) {
							
							aMentionsQueue.push( {
								'agg_key': sAggKey,
								'tag_key': sTagKey,
								'tag': oTag
							} );
							
						} );
					}
					
					var fFilterHandler = oAggFilter[ sAggKey ];
					if ( fFilterHandler ) {
						fFilterHandler.call( _this, oAgg );
					}
					
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
			
			if ( 0 == aIterate.length ) {
				aIterate = aHbIds.slice();		// make a copy
			}
			
			var iCurHbId = aIterate.shift();
			
			oService.get( {
			
				'success': function( data ) {
					
					if ( 1 == parseInt( data.status ) ) {
						
						var oTag = oAggregations.tag;
						var oHeartbeat = oAggregations.heartbeat;
						
						//// assign
						
						var aTags = data.tags;
						
						var sHbKey = fGetKey( iCurHbId );
						// var sHbKey = iCurHbId;
						
						if ( !oHeartbeat.tags[ sHbKey ] ) {
							
							// each heartbeat is a tag
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
								
								// tags are aggregated for each request
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
				},
				
				'get_params': {
					'hbid': iCurHbId
				}
				
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
		
		// if bStartMentions is true, then initialization is complete
		this.initComplete = function() {
			return bStartMentions;
		};
		
		//
		this.getAggregation = function( sKey ) {
			
			if ( !sKey ) sKey = 'tag';
			
			
			var oRes = oAggregations[ sKey ];
			oRes.foo = sKey;
			
			return oRes;
		};
		
		// create a custom aggregation
		this.setAggregation = function( sKey, options ) {
			
			fInitAggregation( sKey, options );
			
			return _this;
		};
		
		
	};
	
	
	//
	$.gekoSysomosAggregator = function( aHbIds, options ) {
		
		return new Aggregator( aHbIds, options );
	};
	
	
} )( jQuery );
