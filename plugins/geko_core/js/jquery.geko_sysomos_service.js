( function( $ ) {
	
	var fContentOkay = function( aFilter, sContent ) {
		
		var bMatch = false;
		
		$.each( aFilter, function( i, v ) {
			if ( -1 !== sContent.indexOf( v ) ) {
				bMatch = true;
				return false;
			}
		} );
		
		// content not okay if there was match
		return ( bMatch ) ? false : true ;
	};
	
	var fFormatBeat = function( oBeat, aFilter ) {
		
		var sDocId = oBeat.find( 'docid' ).text();
		var aSeq = sDocId.split( ':' );
		var sTime = oBeat.find( 'time' ).text();
		var sTitle = oBeat.find( 'title' ).text();
		var sContent =  oBeat.find( 'content' ).text();
		var sType =  oBeat.find( 'mediaType' ).text().toLowerCase();
		
		if ( fContentOkay( aFilter, sContent ) ) {
			
			return {
				'id': 'doc-%s'.printf( sDocId.replace( ':', '-' ) ),
				'seq': aSeq[ 1 ],
				'time': sTime,
				'ts': sTime.strtotime(),
				'type': sType,
				'title': sTitle,
				'content': sContent,
				'excerpt': sContent.truncate( 120, true ),
				'sentiment': oBeat.find( 'sentiment' ).text().toLowerCase()
			};
		}
		
		return null;
	}
	
	//
	$.gekoSysomosService = function( sService, oServOpts ) {
		
		var aServices = {
			
			// formatting done via Geko_Sysomos_Heartbeat PHP class
			'geko': function( options ) {

				var opts = $.extend( {
					//
				}, options );
				
				this.get = function( oGetOptions ) {
					
					var oGetOpts = $.extend( {
						'type': 'info',
						'hbid': 0,
						'callbacks': {},
						'get_params': {}
					}, oGetOptions );
					
					var fSuccessCb = ( oGetOpts.callbacks.success ) ? oGetOpts.callbacks.success : $.noop() ;
					var fFailCb = ( oGetOpts.callbacks.fail ) ? oGetOpts.callbacks.fail : $.noop() ;						
					
					var oGetBase = {
						'ajax_content': 1,
						'section': oGetOpts.type,
						'hbid': oGetOpts.hbid
					};
					
					$.get(
						opts.url,
						$.extend( oGetBase, oGetOpts.get_params ),
						fSuccessCb,		// straight through
						'json'
					).fail( fFailCb );
					
				};
				
			},
			
			// work with api.sysomos.com via transparent proxy
			'sysomos': function( options ) {
				
				var opts = $.extend( {
					'content_filter': []
				}, options );
				
				this.get = function( oGetOptions ) {
					
					var oGetOpts = $.extend( {
						'type': 'info',
						'hid': 0,
						'callbacks': {},
						'get_params': {}
					}, oGetOptions );
					
					var fSuccessCb = ( oGetOpts.callbacks.success ) ? oGetOpts.callbacks.success : $.noop() ;
					var fFailCb = ( oGetOpts.callbacks.fail ) ? oGetOpts.callbacks.fail : $.noop() ;						
					
					var sTypeFmt = oGetOpts.type.replace( '_', '' );
					
					var sUrl = '%s/%s'.printf( opts.url, sTypeFmt );
					
					var oGetBase = {
						'apiKey': opts.api_key,
						'hid': oGetOpts.hbid
					};
					
					var oGetFmt = {};
					$.each( oGetOpts.get_params, function( k, v ) {
						
						if ( 'tag' == k ) {
							
							oGetFmt.fTs = v;
						
						} else if ( 'type' == k ) {
							
							var aTypes = {
								'twitter': 't',
								'blogpost': 'b',
								'forum': 'f',
								'facebook': 'k',
								'news': 'n',
								'youtube': 'y'
							};
							
							oGetFmt.fTs = '~SOURCE~%s%%2C'.printf( aTypes[ v ] );							
						}
						
					} );
					
					
					// defaults
					if ( 'measure' == sTypeFmt ) {
						
						oGetFmt = $.extend( {
							'max': 20,
							'dRg': 7,
							'fTs': 'me'
						}, oGetFmt );
						
					} else if ( 'rsscontent' == sTypeFmt ) {
						
						oGetFmt = $.extend( {
							'startid': 0,
							'max': 120,
							'dRg': 7,
							'fTs': 'me'
						}, oGetFmt );
					}
					
					
					//
					$.get(
						sUrl,
						$.extend( oGetBase, oGetFmt ),
						function( xml ) {
							
							// format xml data
							var oXml = $( xml );
							
							var data = { status: 1 };
							
							if ( 'info' == sTypeFmt ) {
								
								var aTags = {};
								oXml.find( 'tag' ).each( function() {
									
									var oTag = $( this );
									aTags[ oTag.find( 'name' ).text() ] = oTag.find( 'displayName' ).text();
									
								} );
								
								data.name = oXml.find( 'response > name' ).text();
								data.tags = aTags;
								data.count = aTags.length;
								
							} else if ( 'measure' == sTypeFmt ) {
								
								// response > tagStats > matchCount
								// response > tag
								
								var oResp = oXml.find( 'response' ).eq( 0 );
								
								data.mentions = oResp.find( 'response > tagStats > matchCount' ).text();
								data.tag = oResp.find( 'response > tag' ).text();
								
							} else if ( 'rsscontent' == sTypeFmt ) {
								
								var aFeed = [];
								var aBeats = oXml.find( 'beatResponse > beat' );
								
								aBeats.each( function() {
									
									var oBeat = $( this );
									
									var oBeatFmt = fFormatBeat( oBeat, opts.content_filter );
									
									if ( oBeatFmt ) aFeed.push( oBeatFmt );
									
									// TO DO: sort feed
									
								} );
								
								data.feed = aFeed;
								data.filtered_count = aFeed.length;
								data.unfiltered_count = aBeats.length;
							}
							
							fSuccessCb( data );
							
						},
						'xml'
					).fail( fFailCb );
					
				};
				
			}
			
		};
		
		var oService = aServices[ sService ];
		
		if ( !oService ) {
			// default
			oService = aServices[ 'geko' ];
		}
		
		return new oService( oServOpts );
	};	

} )( jQuery );
