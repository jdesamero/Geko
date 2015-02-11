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
			
			
			var eTimerBtn = $( '<input type="button" value="Loading..." name="quiz-timer" id="quiz-timer" \/>' );
			var eTimerLoading = $( '<span class="timer-loading"><\/span>' );
			
			eTimerButtonCont
				.append( eTimerBtn )
				.append( ' ' )
				.append( eTimerLoading )
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
			
			
			
			eTimerBtn.click( function() {
				
				var eInput = $( this );
				
				eTimerLoading.show();
				
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
						
						eTimerLoading.hide();
						
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
						fSetModePause( eTimerBtn );
					} else if ( oParams.status.resume == res.status ) {
						fSetModeResume( eTimerBtn );
					}
					
					iCurTs = $.gekoTimestamp();
					iCurElapsed = res.elapsed_seconds;
					
					eTimerLoading.hide();
					
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
		
		
		//// alert user of unanswered questions
		
		// override submit button
		
		var eSubmitBtn = $( '#action-button' );
		
		// get the original click event
		eSubmitBtn.removeAttr( 'onclick' );
		
		eSubmitBtn.on( 'click', function( evt ) {
			
			var iCount = 0;
			var aMissed = [];
			
			eMainQuizDiv.find( '.watu-question' ).each( function() {
				
				iCount++;
				
				var eDiv = $( this );
				
				// question was missed!
				if ( !eDiv.find( 'input[type="radio"]:checked' ).length ) {
					aMissed.push( iCount );
				}
				
			} );
			
			if ( aMissed.length ) {
				
				if ( confirm(
					'You missed the following question%s: %s! Do you want to continue?'.printf(
						( ( aMissed.length > 1 ) ? 's' : '' ),
						aMissed.join( ', ' )
					)
				) ) {
					WatuPRO.submitResult( evt );
				}
			
			} else {
				WatuPRO.submitResult( evt );
			}
			
			return false;
			
		} );

		
		//// DEPRECATED CODE: easier to check missed questions client-side
		
		/* /
		var eSubmitLoading = $( '<span class="timer-loading"><\/span>' );
		eSubmitLoading.hide();
		
		eSubmitBtn.after( eSubmitLoading );

			eSubmitLoading.show();
			
			$.post (
				oParams.script.process,
				{
					action: 'Geko_Wp_Ext_WatuPro_Timing_Service',
					subaction: 'get_missed',
					taking_id: iTheTakingId
				}, function( res ) {
					
					var iMissedQuestions = res.missed_questions;
					
					if ( oParams.status.get_missed == res.status ) {
						
						if ( iMissedQuestions ) {
							
							if ( confirm(
								'You missed %s question%s! Do you want to continue?'.printf(
									iMissedQuestions,
									( ( iMissedQuestions > 1 ) ? 's' : '' )
								)
							) ) {
								WatuPRO.submitResult( evt );
							}
							
						} else {
							WatuPRO.submitResult( evt );
						}
						
					} else {
						alert( 'Sorry, there was an error submitting your exam. Please try again.' );
					}
					
					eSubmitLoading.hide();
					
				}, 'json'
			)
						
			return false;
		
		/* */
		
	};

} )( jQuery );