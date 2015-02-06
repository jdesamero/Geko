;( function ( $ ) {

	$.gekoWpExtWatuProTiming = function( oParams, eHeaderDisplayDiv ) {
		
		
		var eHeaderElapsedDiv = $( '<div class="elapsed">Elapsed Time: <\/div>' );
		var eElapsedTimeSpan = $( '<span class="time">Calculating...<\/span>' );
		var eElapsedPausedSpan = $( '<span class="paused">Paused<\/span>' );
		var eActivateNoticeDiv = $( '<div class="activate-notice">(NOTE: Timer is activated after first question is answered.)<\/div>' );
		
		eHeaderElapsedDiv.hide();
		eElapsedPausedSpan.hide();
		eActivateNoticeDiv.hide();
		
		eHeaderDisplayDiv;
		
		eHeaderElapsedDiv
			.append( eElapsedTimeSpan )
			.append( eElapsedPausedSpan )
		;
		
		// Temporary!!!
		eHeaderDisplayDiv
			.append( eHeaderQuestionDiv )
			.append( eHeaderElapsedDiv )
			.append( eActivateNoticeDiv )
		;
		
		
		
		var fActivateTimer = function( iTakingId ) {
			
			eHeaderElapsedDiv.show();
			
			//
			eBtnRow.prepend( '<td nowrap="nowrap"><input type="button" value="Loading..." name="quiz-timer" id="quiz-timer" \/> <span class="timer-loading"></span><\/td>' );
			
			
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
			
			
			var eBtn = eBtnRow.find( '#quiz-timer' );
			var eLoading = eBtnRow.find( 'span.timer-loading' );
			
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
			
			var iNumQuestions = eQuizForm.find( '.watupro-qnum-info' ).length;
			
			eHeaderQuestionDiv.html( 'Question 1 of %d'.printf( iNumQuestions ) );
			
			if ( oParams.taking_id ) {
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
		
		
	};

} )( jQuery );