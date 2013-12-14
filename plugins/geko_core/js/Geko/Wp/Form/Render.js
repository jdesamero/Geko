;( function ( $ ) {
	
	$.fn.gekoWpFormRender = function( options ) {
		
		var opts = $.extend( {
			success_div: '#successdiv'				// selector for success div
		}, options );
		
		var successDiv = $( opts.success_div );
			
		return $( this ).each( function() {
			
			var iAnimDelay = 500;
			
			var form = $( this );
			var tabPanel = form.find( 'div.geko-form-tabs' );
			var tabControls = tabPanel.find( 'ul.geko-tab-controls' );
			var tabControlLinks = tabControls.find( 'li > a' );
			
			var submitBtn = form.find( '.geko-form-submit' );
			var prevBtn = form.find( '#geko-form-prev' );
			var nextBtn = form.find( '#geko-form-next' );
			 
			var tabNum = tabControls.find( 'li' ).length;
			var curTabIdx = 0;
			var vld = opts.validation;
			var vldChk = {};
			
			var bSaveMode = false;
			
			var setButtonShow = function() {
				
				if ( curTabIdx == 0 ) prevBtn.css( 'visibility', 'hidden' );
				else prevBtn.css( 'visibility', 'visible' );
				
				if ( curTabIdx == ( tabNum - 1 ) ) {
					nextBtn.css( 'visibility', 'hidden' );
					submitBtn.css( 'visibility', 'visible' );
				} else {
					nextBtn.css( 'visibility', 'visible' );
					submitBtn.css( 'visibility', 'hidden' );
				}
			}
			
			var goToTab = function( tabidx ) {
				tabPanel.tabs( 'option', 'selected', tabidx );
			}
			
			// validate each item in a section
			var validateItem = function( form, errors, id, itm ) {
				
				var elem = null;
				var elemVal = null;
				var subRegex = /sub-([a-z0-9_-]+)/;
				
				// get validation flags
				var valFlags = itm.validation;
				
				var checkCondAncestors = function( elem ) {
					var parType = elem.getTagName();
					if (
						( ( 'option' == parType ) && elem.is( ':selected' ) ) || 
						( ( 'input' == parType ) && elem.is( ':checked' ) )
					) {
						var parUl = elem.closest( '.geko-form-sub' );
						if ( parUl.length ) {
							var sub2 = subRegex.exec( parUl.attr( 'id' ) );
							var parElem = form.find( '#' + sub2[ 1 ]  );
							if ( parElem.length ) {
								return checkCondAncestors( parElem );
							}
						}
						return true;
					} else {
						return false;
					}
				}
				
				// set up validation flags
				if ( valFlags ) {
					var sub = subRegex.exec( valFlags );
					if ( sub ) {
						
						valFlags = valFlags.replace( sub[ 0 ], 'sub' );
						valSub = sub[ 1 ];
						
						var parElem = form.find( '#' + valSub  );
						
						if ( checkCondAncestors( parElem ) ) {
							valFlags += ',sub_selected';
						}
						
					}
				}
				
				// split and filter validation flags
				var flags = valFlags.split( ',' );
				flags = flags.filter( function( n ) { return n; } );
				
				// conditions: not_required, text_type_email, text_type_url, text_type_postal_code
				var valIs = function( cond ) {
					return ( -1 != $.inArray( cond, flags ) ) ? true : false ;
				};
				
				
				// validate questions that are not subs, and subs that are selected
				if (
					( !valIs( 'sub' ) ) ||
					( valIs( 'sub' ) && valIs( 'sub_selected' ) )
				) {
					
					// get the element values
					if ( 'radio' == itm.type || 'checkbox' == itm.type ) {
						elem = form.find( 'input[name="' + id + '"]' );
						elemVal = elem.is( ':checked' );
					} else if ( 'checkbox_multi' == itm.type ) {
						elem = form.find( 'input[name="' + id + '\\[\\]"]' );
						elemVal = elem.is( ':checked' );
					} else {
						elem = form.find( '#' + id );
						elemVal = $.trim( elem.val() );
					}
					
					var bContinue = true;
					
					if ( !valIs( 'not_required' ) && !elemVal ) {
						errors.push( 'Please specify a value for question: "' + itm.question + '"' );
						bContinue = false;
					}
					
					if ( bContinue && elemVal ) {
						
						if ( valIs( 'text_type_email' ) && !form.isEmail( elemVal ) ) {
							errors.push( 'Please specify a valid email address for question: "' + itm.question + '"' );					
						}
						
						if ( valIs( 'text_type_url' ) && !form.isUrl( elemVal ) ) {
							errors.push( 'Please specify a valid URL for question: "' + itm.question + '"' );					
						}
						
						if ( valIs( 'text_type_postal_code' ) && !form.isPostalCode( elemVal ) ) {
							errors.push( 'Please specify a valid postal code for question: "' + itm.question + '"' );					
						}
					}
					
				}
				
				
				return errors;
			}
			
			prevBtn.click( function() {
				if ( curTabIdx > 0 ) {
					
					/* /
					var custom_validate = function( form, errors ) {
						
						var vlditms = vld[ curTabIdx ];
						
						$.each( vlditms, function( id, itm ) {
							errors = validateItem( form, errors, id, itm );
						} );
	
						if ( errors.length ) {
							errors.unshift( '<strong>Please correct the errors in this section before going to the previous:<\/strong>' );
						}
						
						return errors;
					}
					
					var custom_validate_success = function( form ) {
						
						// set validated flag on section
						vldChk[ curTabIdx ] = true;
						
						curTabIdx = curTabIdx - 1;
						goToTab( curTabIdx );
						window.location = '#form_top';
					}
					
					form.trigger( 'validate', [ custom_validate, custom_validate_success ] );
					/* */
					
					// no need to validate
					curTabIdx = curTabIdx - 1;
					goToTab( curTabIdx );
					window.location = '#form_top';					
				}
			} );
			
			nextBtn.click( function() {
				
				if ( curTabIdx < ( tabNum - 1 ) ) {
					
					var custom_validate = function( form, errors ) {
						
						var vlditms = vld[ curTabIdx ];
						
						$.each( vlditms, function( id, itm ) {
							errors = validateItem( form, errors, id, itm );
						} );
	
						if ( errors.length ) {
							errors.unshift( '<strong>Please correct the errors in this section before proceeding to the next:<\/strong>' );
						}
						
						return errors;
					}
					
					var custom_validate_success = function( form ) {
	
						// set validated flag on section
						vldChk[ curTabIdx ] = true;
						
						curTabIdx = curTabIdx + 1;
						goToTab( curTabIdx );
						window.location = '#form_top';
					}
					
					form.trigger( 'validate', [ custom_validate, custom_validate_success ] );
				}
			} );
			
			// add tooltip functionality to the form
			form.tooltip();
			
			form.gekoAjaxForm( {
				status: opts.status,
				process_script: opts.script.process,
				action: '&action=Gloc_Service_Form',
				form_top: '#form_top',
				validate: function( form, errors ) {
					
					if ( !opts.admin ) {						
						if ( !bSaveMode ) {
							
							// go through each section
							$.each( vld, function( tabidx, vlditms ) {
								
								$.each( vlditms, function( id, itm ) {
									errors = validateItem( form, errors, id, itm );
								} );
								
								if ( errors.length ) {
									errors.unshift( '<strong>Please correct the errors in this section:<\/strong>' );
									goToTab( tabidx );
									return false;
								}
								
							} );
						}
					} else {
						errors.push( 'Submission not allowed in admin mode!' );
					}
					
					return errors;
					
				},
				process: function( form, res, status ) {
					
					if ( status.success == parseInt( res.status ) ) {
						if ( bSaveMode ) {
							form.success( 'Form was saved successfully' );
						} else {
							// complete the survey
							form.hide();
							successDiv.show();
						}
					} else {
						form.error( 'Failed to save form. Please try again.' );
					}
					
					window.location = '#form_top';
					
					// reset
					form.find( 'input[name="subaction"]' ).val( 'submit' );
					bSaveMode = false;
				}
			} );
			
			form.find( '.geko-form-save' ).click( function() {
				form.find( 'input[name="subaction"]' ).val( 'save' );
				bSaveMode = true;
				form.submit();
			} );
			
			
			// section tabs
			tabPanel.tabs( {
				select: function( evt, ui ) {
					
					validateTabs();
					
					// set validated class where applicable
					var doThisTab = null;
					tabControlLinks.each( function( idx ) {
						var a = $( this );
						if ( vldChk[ idx ] ) {
							a.addClass( 'validated' );
						} else {
							a.removeClass( 'validated' );
							if ( null === doThisTab ) {
								doThisTab = idx;
							}
						}
					} );
					
					// do checks first
					if ( ui.index ) {
						
						var notValidIdx = null;						
						tabControlLinks.each( function( idx ) {
							if ( idx < ui.index ) {
								var a = $( this );
								if ( !a.hasClass( 'validated' ) ) {
									notValidIdx = idx;
								}
							}
						} );
						
						if ( null !== notValidIdx ) {
							var doThisTabTitle = tabControlLinks.eq( doThisTab ).html();
							doThisTabTitle = doThisTabTitle.replace( '&amp;', '&' );
							alert( 'Please complete "' + doThisTabTitle + '" before proceeding to this section.' );
							return false;
						}
						
					}
					
					curTabIdx = ui.index;
					setButtonShow();
					form.trigger( 'reset_errors' );
					
					return true;
				},
				selected: 1		// hackity-hack-hack!!!
			} );
			
			
			//// conditional logic controls
			
			var getSubs = function( elem ) {
				var parLi = elem.closest( 'li' );
				var subs = parLi.find( '> .geko-form-sub' );
				if ( subs.length > 0 ) {
					return subs;
				}
				return false;
			};
			
			var showSubs = function( subs, id, noAnim ) {
				subs.each( function() {
					var sub = $( this );
					if ( ( 'sub-' + id ) == sub.attr( 'id' ) ) {
						if ( sub.is( ':hidden' ) ) {
							if ( noAnim ) {
								sub.show();
							} else {
								sub.slideDown( iAnimDelay );
							}
						}
					} else {
						if ( !sub.is( ':hidden' ) ) {
							if ( noAnim ) {
								sub.hide();							
							} else {
								sub.slideUp( iAnimDelay );
							}
						}
					}
				} );
			};
			
			form.find( 'select' ).each( function() {
				var select = $( this );
				var subs = null;
				if ( subs = getSubs( select ) ) {
					
					select.change( function() {
						var selId = select.find( ':selected' ).attr( 'id' );
						showSubs( subs, selId );
					} );
					
					// init
					var selId = select.find( ':selected' ).attr( 'id' );
					showSubs( subs, selId, true );
				}
			} );
			
			form.find( 'input[type="radio"]' ).each( function() {
				var radio = $( this );
				var subs = null;
				if ( subs = getSubs( radio ) ) {
					
					radio.click( function() {
						var radId = radio.attr( 'id' );
						showSubs( subs, radId );
					} );
					
					// init
					if ( radio.is( ':checked' ) ) {
						showSubs( subs, radio.attr( 'id' ), true );
					}
				}
			} );
			
			
			//// init
			
			var initTabIdx = null;
			
			var validateTabs = function() {
				
				// cycle through tabs and go to "current"
				tabControlLinks.each( function( idx ) {
					
					var custom_validate = function( form, errors ) {
						
						var vlditms = vld[ idx ];
						
						$.each( vlditms, function( id, itm ) {
							errors = validateItem( form, errors, id, itm );
						} );
						
						return errors;
					}
					
					var custom_validate_success = function( form ) {
						// set validated flag on section
						vldChk[ idx ] = true;
					}
					
					var custom_validate_error = function( form ) {
						vldChk[ idx ] = false;
						if ( null === initTabIdx ) {
							initTabIdx = idx;
						}
					}
					
					form.trigger( 'validate', [ custom_validate, custom_validate_success, custom_validate_error ] );
				} );
				
			};
			
			validateTabs();
			
			
			// if value of initTabIdx is still null, then set it to the last tab
			if ( null === initTabIdx ) initTabIdx = tabNum - 1;
			
			// set the first tab
			goToTab( 0 );			// hack
			goToTab( initTabIdx );
			
			// hide prev and next buttons if not needed
			if ( 1 == tabNum ) {
				tabControls.hide();
				prevBtn.css( 'visibility', 'hidden' );
				nextBtn.css( 'visibility', 'hidden' );
			}
			
		} );
	};

} )( jQuery );