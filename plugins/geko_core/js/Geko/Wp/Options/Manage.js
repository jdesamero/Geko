;( function ( $ ) {
	
	$.gekoWpOptionsManage = function( options ) {
		
		var opts = $.extend( {
			
		}, options );
		
		//// vars
		
		var subject = opts.mng.subject;
		
		//// do checks
		if ( !subject ) {
			return this;
		}
		
		//// continue
		
		var subject_lc = subject.toLowerCase();
		var display_mode = opts.mng.display_mode;
		var type = opts.mng.type;
		var edit_form_id = opts.mng.edit_form_id;
		
		// standard close button
		var dialogCancelButton = function() {
			var dialog = $( this );
			dialog.dialog( 'close' );
		};
		
		if ( ( 'add' == display_mode ) || ( 'edit' == display_mode ) ) {
			
			var submitBtn = $( '#' + edit_form_id + ' input[name="submit"]' );
			
			var exportFlagFld = $( 'input[name="export_' + type + '"]' );
			var duplicateFlagFld = $( 'input[name="duplicate_' + type + '"]' );
			
			// reset other buttons
			submitBtn.click( function( evt, caller ) {
				if ( !caller ) {
					
					// only reset these values if there was no caller
					exportFlagFld.val( '' );
					duplicateFlagFld.val( '' );
					
					// reset
					$.each( opts.custom_actions, function( k, v ) {
						if ( ( 'edit' == v.mode ) && v.button && v.hidden_field ) {
							$( 'input[name="' + v.hidden_field.name + '"]' ).val( '' );
						}
					} );
				}
			} );
			
			//// click
			$.each( opts.custom_actions, function( k, v ) {
				if ( ( 'edit' == v.mode ) && v.button && v.hidden_field ) {
					$( '#' + v.button.id ).click( function() {
						$( 'input[name="' + v.hidden_field.name + '"]' ).val( 1 );
						submitBtn.trigger( 'click', [ k ] );
					} );
				}
			} );
			
			
			//// export
			if ( opts.can[ 'export' ] ) {
				
				$( '#export_' + type + '_btn' ).click( function() {
					exportFlagFld.val( 1 );
					submitBtn.trigger( 'click', [ 'export' ] );
				} );
				
			}
			
			//// duplicate
			if ( opts.can.duplicate ) {
				
				var dupFormDlg = $( '#duplicate_' + type );
				
				dupFormDlg.dialog( {
					title: 'Duplicate ' + subject,
					autoOpen: false,
					modal: true,
					buttons: {
						Duplicate: function() {
							var dialog = $( this );
							duplicateFlagFld.val( 1 );
							$( 'input[name="duplicate_' + type + '_title"]' ).val( dialog.find( '#duplicate_' + type + '_title' ).val() );
							dialog.dialog( 'close' );
							submitBtn.trigger( 'click', [ 'duplicate' ] );
						},
						Cancel: dialogCancelButton
					},
					width: 500
				} );
				
				$( '#duplicate_' + type + '_btn' ).click( function() {
					dupFormDlg.dialog( 'open' );
				} );
				
			}
			
			//// restore
			if ( opts.can.restore ) {
				
				var restoreDlg = $( '#restore_' + type + '_form' );
				
				restoreDlg.dialog( {
					title: 'Restore ' + subject,
					autoOpen: false,
					modal: true,
					buttons: {
						Restore: function() {
							var dialog = $( this );
							dialog.find( 'form' ).submit();
						},
						Cancel: dialogCancelButton
					},
					width: 500
				} );
				
				$( '#restore_' + type + '_btn' ).click( function() {
					restoreDlg.dialog( 'open' );
				} );
				
			}
			
			
			//// js params
			if ( opts.js_params ) {
				
				//// row template
				if ( opts.js_params.row_template ) {
					
					$.each( opts.js_params.row_template, function( k, v ) {
						
						var rtElem = $( v.group_sel );
						
						var rowTmplParams = {
							group_name: v.group_name,
							row_container_sel: v.row_container_sel,
							row_sel: v.row_sel,
							row_template_sel: v.row_template_sel,
							add_row_sel: v.add_row_sel,
							del_row_sel: v.del_row_sel
						};
						
						if ( v.submit_func ) rowTmplParams.submit_func = v.submit_func;
						
						if ( v.sortable ) {
							
							var tbody = rtElem.find( v.sortable.sort_sel );
							
							tbody.sortable();
							
							if ( !rowTmplParams.submit_func ) {
								rowTmplParams.submit_func = function( tmpl ) {
									var elem = $( this );
									elem.find( v.sortable.rank_sel ).each( function( i ) {
										$( this ).val( i );
									} );
									tmpl.remove();
								}
							}
							
							var sortCols = rtElem.find( v.sortable.col_sel );
							
							if ( sortCols.length ) {
								
								sortCols.each( function() {
									
									var th = $( this );
									var temp = $( '<div><\/div>' );
									temp.addClass( th.attr( 'class' ) );
									temp.removeClass( v.sortable.col_class );
									
									var cmp = temp.attr( 'class' );
									var fld = $.trim( cmp.replace( v.sortable.col_pfx, '' ) );
									
									if ( ( fld != cmp ) && fld ) {
										
										var title = th.html();
										th.html( '<a href="#">' + title + '<\/a>' );
					
										var sortBtn = th.find( 'a' );
										sortBtn.click( function() {
											
											var tr = tbody.children( 'tr' );
											tr.detach().sort( function( a, b ) {
												
												var valA = $( a ).find( '.' + v.sortable.fld_pfx + fld ).val().toLowerCase();
												var valB = $( b ).find( '.' + v.sortable.fld_pfx + fld ).val().toLowerCase();
												
												return ( valA > valB ) ? 1 : -1 ;
											} );
											
											tbody.append( tr );
											
											return false;
										} );
										
									}
									
								} );
								
							}
							
						}
						
						if ( v.toggle_column ) {
							
							$( v.toggle_column.btn_sel ).click( function() {
								
								var colsel = $( this ).attr( 'id' ).replace( v.toggle_column.id_pfx, '' );
								var label = $( this ).html();
								
								if ( !$( this ).hasClass( 'show' ) ) {
									$( this ).addClass( 'show' );
									$( '.' + v.toggle_column.col_pfx + colsel ).show();
									$( this ).html( label.replace( 'Show', 'Hide' ) );
								} else {
									$( this ).removeClass( 'show' );
									$( '.' + v.toggle_column.col_pfx + colsel ).hide();
									$( this ).html( label.replace( 'Hide', 'Show' ) );
								}
								
								return false;
							} );
							
						}
						
						rtElem.gekoRowTemplate( rowTmplParams );
					} );
				}
				
				//// conditional toggle
				if ( opts.js_params.conditional_toggle ) {
					
					$.each( opts.js_params.conditional_toggle, function( k, v ) {
						
						var ctElem = $( v.group_sel );
						var widget = ctElem.find( '#' + v.widget_id );
						
						var updateFields = function() {
							
							var descSpan = widget.closest( v.widget_cont_sel ).find( v.desc_sel );
							if ( !descSpan.data( 'default_html' ) ) {
								descSpan.data( 'default_html', descSpan.html() );
							}
							
							ctElem.find( v.cond_sel ).hide();
							
							var curTypeId = parseInt( widget.val() );
							var match = false;
							
							$.each( v.conditions, function( j, u ) {
								if ( u.val == curTypeId ) {
									match = true;
									ctElem.find( '.' + j ).show();
									descSpan.html( u.desc );
								}
							} );
							
							if ( !match ) {
								descSpan.html( descSpan.data( 'default_html' ) );			
							}
							
						}
						
						updateFields();
						
						widget.change( function() {
							updateFields();
						} );
						
					} );
					
				}
				
			}
			
			
		} else if ( 'list' == display_mode ) {
		
			//// delete entity
			$( 'a.submitdelete' ).click( function () {
				return ( confirm( 'Are you sure you want to delete this ' + subject_lc + '?' ) );
			} );
			
			//// import
			if ( opts.can[ 'import' ] ) {

				var importDlg = $( '#import_' + type + '_form' );
				
				importDlg.dialog( {
					title: 'Import ' + subject,
					autoOpen: false,
					modal: true,
					buttons: {
						Import: function() {
							var dialog = $( this );
							dialog.find( 'form' ).submit();
						},
						Cancel: dialogCancelButton
					},
					width: 500
				} );
				
				$( '#import_' + type + '_btn' ).click( function() {
					importDlg.dialog( 'open' );
				} );
				
			}
			
		}
		
		return this;
	};
	
} )( jQuery );