;( function ( $ ) {
	
	// eMainQuizDiv -> the main quiz div
	// eTimerDisplayCont -> element where timer display gets inserted
	// eTimerButtonCont -> element where timer button gets inserted
	
	$.gekoWpExtWatuProTiming = function( oParams, eMainQuizDiv, eTimerDisplayCont, eTimerButtonCont ) {
		
		var iTheTakingId = null;		// a reference to the taking id, if set by fActivateTimer
		
		
		var eHeaderElapsedDiv = $( '<div class="elapsed">Elapsed Time: <\/div>' );
		var eElapsedTimeSpan = $( '<span class="time">Calculating...<\/span>' );
		var eElapsedPausedSpan = $( '<span class="paused">Paused<\/span>' );
		var eActivateNoticeDiv = $( '<div class="activate-notice">(NOTE: Timer is activated after first question is answered.)<\/div>' );
		
		eHeaderElapsedDiv.hide();
		eElapsedPausedSpan.hide();
		eActivateNoticeDiv.hide();
		
		
		eHeaderElapsedDiv
			.append( eElapsedTimeSpan )
			.append( eElapsedPausedSpan )
		;
		
		//
		eTimerDisplayCont
			.append( eHeaderElapsedDiv )
			.append( eActivateNoticeDiv )
		;
		
		
		
		var fActivateTimer = function( iTakingId ) {
			
			iTheTakingId = iTakingId;
			
			eActivateNoticeDiv.hide();
			eHeaderElapsedDiv.show();
			
			
			var eBtn = $( '<input type="button" value="Loading..." name="quiz-timer" id="quiz-timer" \/>' );
			var eLoading = $( '<span class="timer-loading"><\/span>' );
			
			eTimerButtonCont
				.append( eBtn )
				.append( ' ' )
				.append( eLoading )
			;
			
			
			var iCurTs = 0;
			var iCurElapsed = 0;
			var bTimerRunning = false;
			
			// set timer in motion
			var fRunTimer = function() {
				
				if ( iCurElapsed && iCurTs ) {
					
					var iShowTime = iCurElapsed;
					
					if ( bTimerRunning ) {
						iShowTime = iShowTime + ( $.gekoTimestamp() - iCurTs );
					}
					
					eElapsedTimeSpan.html( $.gekoFormatDdHsMmSs( iShowTime ) );
				}
				
				setTimeout( function() {
					fRunTimer();
				}, 500 );
			};
			
			fRunTimer();
			
			
			
			//// set mode functions
			
			// pause
			var fSetModePause = function( eInput ) {

				bTimerRunning = false;
				
				eInput
					.removeClass( 'mode-pause' )
					.addClass( 'mode-resume' )
					.val( 'Resume Timer' )
				;		

				eElapsedPausedSpan.show();
			};
			
			// resume
			var fSetModeResume = function( eInput ) {
				
				bTimerRunning = true;
				
				eInput
					.removeClass( 'mode-resume' )
					.addClass( 'mode-pause' )
					.val( 'Pause Timer' )
				;
				
				eElapsedPausedSpan.hide();
			};
			
			
			
			eBtn.click( function() {
				
				var eInput = $( this );
				
				eLoading.show();
				
				$.post (
					oParams.script.process,
					{
						action: 'Geko_Wp_Ext_WatuPro_Timing_Service',
						subaction: 'toggle_timing',
						taking_id: iTakingId
					}, function( res ) {
						
						if ( oParams.status.paused == res.status ) {
							fSetModePause( eInput );
						} else if ( oParams.status.resume == res.status ) {
							fSetModeResume( eInput );
						}
						
						iCurTs = $.gekoTimestamp();
						iCurElapsed = res.elapsed_seconds;
						
						eLoading.hide();
						
					}, 'json'
				)
				
				
			} );
			
			// init button
			$.post (
				oParams.script.process,
				{
					action: 'Geko_Wp_Ext_WatuPro_Timing_Service',
					subaction: 'get_status',
					taking_id: iTakingId
				}, function( res ) {
					
					if ( oParams.status.paused == res.status ) {
						fSetModePause( eBtn );
					} else if ( oParams.status.resume == res.status ) {
						fSetModeResume( eBtn );
					}
					
					iCurTs = $.gekoTimestamp();
					iCurElapsed = res.elapsed_seconds;
					
					eLoading.hide();
					
				}, 'json'
			)
			
		};
		
		
		
		// add pause/resume, only if there is a valid taking_id
		
		if ( oParams.taking_id ) {
			
			fActivateTimer( oParams.taking_id );
			
		} else {
						
			if ( oParams.user_id ) {
				eActivateNoticeDiv.show();
			}
			
			var bAjaxTaking = false;
			
			// init
			$( document ).ajaxSuccess( function( evt, xhr, settings ) {
				
				if ( !bAjaxTaking ) {

					if ( -1 != settings.data.indexOf( 'exam_id' ) ) {
						
						$.post (
							oParams.script.process,
							{
								action: 'Geko_Wp_Ext_WatuPro_Timing_Service',
								subaction: 'get_taking_id',
								exam_id: oParams.exam_id
							}, function( res ) {
								
								if ( oParams.status.taking_id == res.status ) {
									fActivateTimer( res.taking_id );
									bAjaxTaking = true;
								}
								
							}, 'json'
						)
						
					}
					
				}
				
			} );

		}
		
		
		//// handle redirect of completed test

		//
		$( document ).ajaxSend( function( evt, jqxhr, settings ) {
			
			// submit button was clicked
			if ( -1 != settings.data.indexOf( 'action=watupro_submit' ) ) {
				
				eMainQuizDiv.hide();
				eMainQuizDiv.after( '<div>Submitting Your Test... <span class="timer-loading"><\/span><\/div>' );
				
			}
			
		} );
		
		//
		$( document ).ajaxSuccess( function( evt, xhr, settings ) {
			
			// submit button was clicked
			if ( -1 != xhr.responseText.indexOf( 'startOutput' ) ) {
				window.location = '%s/exam-results/exam-review/?id=%d'.printf( oParams.script.url, iTheTakingId );
			}
			
		} );
		
		
	};

} )( jQuery );