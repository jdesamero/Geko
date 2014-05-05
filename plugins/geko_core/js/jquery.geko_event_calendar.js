;( function( $ ) {
	
	$.fn.gekoEventCalendar = function( options ) {
		
		var opts = $.extend( {
			
			cal_loading_class: 'cal_loading',
			success_status: 1,
			
			get_month_cb: null,
			tooltip_cb: null,
			click_day_cb: null,
			event_list_cb: null
			
		}, options );
		
		
		//
		var fSetMonth = function( eCal, iYr, iMon ) {
			
			var oTrack = eCal.data( 'track' );
			var sTrackIdx = '%d-%d'.printf( iYr, iMon );
			
			var oTrackMon = oTrack[ sTrackIdx ];
			
			var aEvents = oTrackMon.events;
			var aData = oTrackMon.data;
			
			
			var sCalMonSel = 'td[data-month="%d"]'.printf( parseInt( iMon - 1 ) );
			
			eCal.find( sCalMonSel ).each( function() {
				
				var eTd = $( this );
				var eA = eTd.find( 'a' );
				
				// disable default behaviour				
				eA.click( function( e ) {
					e.stopPropagation();
					return false;
				} );
				
				eTd.attr( 'data-day', eA.text() );
				
			} );
			
			$.each( aEvents, function( iDay, aEvt ) {
				
				var sCalDaySel = 'td[data-day="%d"]'.printf( iDay );
				
				eCal.find( sCalDaySel ).each( function() {
					
					var eTd = $( this );
					var eA = eTd.find( 'a' );
					
					eTd.addClass( 'has-event' );
					eA.addClass( 'has-event' );
					
					eA.data( 'events', aEvt );
					eA.data( 'data', aData );
					
					
					// tooltips
					if ( opts.tooltip_cb ) {
						
						var sOut = '';
						
						$.each( aEvt, function( i, v ) {
							var d = aData[ v ];
							sOut += opts.tooltip_cb.call( eCal, d );
						} );
						
						
						if ( $.fn.qtip ) {
							
							var oDate = new Date( iYr, parseInt( iMon - 1 ), iDay );
							
							eA.qtip( {
								content: {
									title: {
										text: oDate.format( 'mediumDate' ),
										button: 'Close'
									},
									text: sOut
								},
								show: 'click',
								hide: 'unfocus',
								style: {
									tip: 'topRight',
									border: {
										width: 3,
										radius: 8
									}
								},
								position: {
									corner: {
										target: 'bottomLeft',
										tooltip: 'topRight'
									}
								},
								api: {
									onPositionUpdate: function() {
										$( 'div.qtip' ).hide();
									}
								}
							} );
						
						} else if ( $.fn.tooltip ) {
									
							eTd.tooltip( {
								
								items: 'a',
								content: function() {
									return sOut;
								}
								
							} );
							
						}
						
					}
						
					
					//
					if ( opts.click_day_cb ) {
						opts.click_day_cb.cal( eCal, eA, iYr, iMon, iDay );
					}
					
				} );
				
			} );
			
			
			// callbacks
			
			if ( opts.event_list_cb ) {
				
				var eEvtList = $( '.event-list' );
				
				eEvtList.find( '.evt_item' ).remove();
				
				var eTmpl = eEvtList.find( '.evt_tmpl' );
				var eNone = eEvtList.find( '.evt_none' );
				
				eNone.hide();
				
				if ( aData.length ) {
					
					$.each( aData, function( i, v ) {
						
						var eEvtItem = eTmpl.clone();
						
						eEvtItem.removeClass( 'evt_tmpl' );
						eEvtItem.addClass( 'evt_item' );
						
						
						opts.event_list_cb.call( eCal, eEvtList, eEvtItem, v );
						
						
						eEvtList.append( eEvtItem );
						
					} );
					
				} else {
					
					eNone.show();
					
				}
								
			}
			
			if ( opts.get_month_cb ) {
				opts.get_month_cb.call( eCal, iYr, iMon, aEvents, aData );
			}
			
		}
		
		
		// get month data via ajax if not already cached
		var fGetMonth = function( eCal, iYr, iMon ) {
			
			var oTrack;
			
			if ( eCal.data( 'track' ) ) {
				oTrack = eCal.data( 'track' );
			} else {
				oTrack = {};
				eCal.data( 'track', oTrack );
			}
			
			var sTrackIdx = '%d-%d'.printf( iYr, iMon );

			var sCalLoadSel = '.%s'.printf( opts.cal_loading_class );
			var eLoad = eCal.find( sCalLoadSel );
			
			eLoad.show();
			
			if ( !oTrack[ sTrackIdx ] ) {
				
				$.get(
					opts.service_url,
					{
						yr: iYr,
						mon: iMon
					},
					function( oRes ) {
						
						if ( opts.success_status == oRes.status ) {
							
							oTrack[ sTrackIdx ] = {
								events: oRes.events,
								data: oRes.data
							};
							
							eCal.data( 'track', oTrack );
							
							fSetMonth( eCal, iYr, iMon );
							
						}
						
						eLoad.hide();
					},
					'json'
				);
				
			} else {
				
				setTimeout( function() {

					fSetMonth( eCal, iYr, iMon );
					eLoad.hide();
					
				}, 10 );
				
			}
			

			
		};
		
		
		//
		return this.each( function() {
			
			var eCalendar = $( this );
			
			var sCalLoadSel = '.%s'.printf( opts.cal_loading_class );
			var eLoad = eCalendar.find( sCalLoadSel );
			
			if ( !eLoad.length ) {
				eCalendar.prepend( '<div class="%s"><\/div>'.printf( opts.cal_loading_class ) );
			}
			
			
			$.get(
				opts.service_url,
				function( oRes ) {
					
					if ( opts.success_status == oRes.status ) {
						
						var aMin = oRes.range.min;
						var aMax = oRes.range.max;
						
						eCalendar.datepicker( {
							
							dateFormat: 'yy-mm-dd',
							
							minDate: new Date( aMin.year, aMin.mon, aMin.day ),
							maxDate: new Date( aMax.year, aMax.mon, aMax.day ),
							
							onChangeMonthYear: function( iYr, iMon ) {
								// console.log( 'Making changes... : %d - %d'.printf( iYr, iMon ) );
								fGetMonth( eCalendar, iYr, iMon );
							},
							
							onSelect: function( sDate ) {
								
								var aDate = sDate.split( '-' );
								fGetMonth( eCalendar, parseInt( aDate[ 0 ] ), parseInt( aDate[ 1 ] ) );
								
							}
							
						} );
						
						// init
						// console.log( eCalendar.datepicker( 'getDate' ) );
						oDate = eCalendar.datepicker( 'getDate' );
						fGetMonth( eCalendar, oDate.getFullYear(), oDate.getMonth() + 1 );
						
						
					}
					
				},
				'json'
			);
			
			
		} );
		
	};
	
} )( jQuery );
