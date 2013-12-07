;(function ($) {
	
	$.fn.gekoEventCalendar = function( options ) {
		
		var opts = $.extend( {
			cal_loading_class: 'cal_loading'
		}, options );
		
		
		var calMon = $( this );
		
		
		
		//// month stuff
		
		// tooltip for event
		var callQtip = function( mons, dp ) {
			
			var countDays = 0;
			
			if ( mons.length ) {
			
				calMon.find( 'tbody td a.ui-state-enabled' ).each(function() {
					
					var day = parseInt( $( this ).html() );
					var date = new Date( dp.selectedYear, dp.selectedMonth, day );
					
					$( this ).qtip( {
						content: {
							title: {
								text: date.format( 'mediumDate' ),
								button: 'Close'
							},
							prerender: true,
							url: opts.service_url,
							data: {
								year: dp.selectedYear,
								mon: dp.selectedMonth,
								day: day
							},
							method: 'get'
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
							beforeContentUpdate: function ( ret ) {
								if ( $.trim( ret ) ) {
									
									// assemble output string
									var parsed = $.parseJSON( ret );
									
									if ( ret.vars ) ret = ret.vars;
									
									var output = '';
									
									$.each( parsed , function() {
										output += '<div class="cal-event"><p class="title"><a href="' + this.url + '">' + this.title + '<\/a><\/p>'
										if ( this.range ) output += '<p class="date">' + this.range + '<\/p>';
										output += '<p>' + this.excerpt + '<\/p>';
										
										var cats = '';
										
										$.each( this.cats , function() {
											if ( '' != cats ) cats += ', ';
											cats += '<a href="' + this.url + '">' + this.title + '<\/a>';
										} );
										
										output += '<p><strong>Categories:<\/strong> ' + cats + '<\/p><\/div>';
										
									} );
									
									// hide the "loading..." if needed
									countDays++;
									if ( countDays == mons.length ) {
										calendarMonthLoaded();
									}
									
									return output;
									
								} else {
									return false;
								}
							},
							onPositionUpdate: function() {
								$( 'div.qtip' ).hide();
							}
						}
					} );
				} );
			
			}
			
		}
		
		// prepare the months
		var prepMons = function( mons, dp ) {
					
			calMon.find( 'tbody td a.ui-state-default' ).each( function() {
				
				var day = parseInt( $( this ).html() );
				var par = $( this ).parent();
				
				if ( -1 == $.inArray( day, $.makeArray( mons ) ) ) {
					par.addClass( 'ui-state-disabled ui-datepicker-unselectable' );
					$( this ).addClass( 'ui-state-disabled' );
				} else {
					$( this ).addClass( 'ui-state-enabled' );					
				}
			} );
			
			// there are days to be clicked
			if ( mons.length ) {
				
				// HACK!!!: remove the onclick from the calendar td
				calMon.find( 'tbody td' ).each( function() {
					$( this ).removeAttr( 'onclick' );
				} );
				
				calMon.find( 'tbody td a.ui-state-default' ).click( function() {
					return false;
				} );
				
				//
				setTimeout( function () {
					callQtip( mons, dp );
				}, 10 );
				
				// $.sygerDebug( 'days', aDays );
			} else {
				
				calendarMonthLoaded();
				
			}
			
		};
		
		//
		var calendarMonthLoaded = function() {
			
			calMon.find( 'tbody td a.ui-state-default' ).each( function() {
				var par = $( this ).parent();
				par.addClass( 'ui-state-loaded' );
				$( this ).addClass( 'ui-state-loaded' );
			} );
			
			calMon.find( 'tbody td span.ui-state-default' ).each( function() {
				$( this ).addClass( 'ui-state-loaded' );
			} );
			
			calMon.find( '.' + opts.cal_loading_class ).hide();
		
		}
		
		
		// load the date picker
		$.getJSON(
			opts.service_url,
			function( bounds ) {
				
				if ( bounds.vars ) bounds = bounds.vars;
				
				calMon.datepicker( {
					minDate: new Date( bounds.min.year, bounds.min.mon, bounds.min.day ),
					maxDate: new Date( bounds.max.year, bounds.max.mon, bounds.max.day ),
					nextText: '>>',
					prevText: '<<',
					dayNamesMin: [ 'S', 'M', 'T', 'W', 'T', 'F', 'S' ],
					onChangeMonthYear: function (year, mon, dp) {
						
						if ( !calMon.find( '.' + opts.cal_loading_class ).length ) {
							calMon.prepend( '<div class="' + opts.cal_loading_class + '"><\/div>' );
						} else {
							calMon.find( '.' + opts.cal_loading_class ).show();
						}
						
						$.getJSON(
							opts.service_url,
							{
								year: dp.selectedYear,
								mon: dp.selectedMonth
							},
							function( mons ) {
								
								if ( mons.vars ) mons = mons.vars;
								
								// $.sygerDebug( 'data', data, '   ', 2 );
								// $.sygerDebug( 'dp', dp );
								prepMons( mons, dp );		// activate tooltips again
							}
						);
						
					}
				} );
				
				// trigger onChangeMonthYear event to populate datepicker
				$.datepicker._notifyChange( calMon.data( 'datepicker' ) );
				
			}
		);
		
		
		//// testing
		
		$( '#foo_test' ).click( function() {
			
			alert(
				$( '.ui-datepicker-calendar' ).innerWidth() + ' ' + 
				$( '.ui-datepicker-calendar' ).width() + ' ' + 
				$( '.ui-datepicker-calendar' ).outerWidth()
			);
			
		} );
				
		return this;		
	};
	
} )( jQuery );
