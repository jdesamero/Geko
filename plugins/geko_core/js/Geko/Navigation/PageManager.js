;( function ( $ ) {
	
	//
	String.prototype.htmlEntities = function() {
		return this.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
	}
	
	$.fn.extend( {
		cssValue: function( prop ) {
			var val = this.css( prop );
			if ( !val ) {
				return null;
			} else {
				return parseInt( val.replace( 'px', '' ) );
			}
		},
		offsetCss: function( prop, offsetValue ) {
			var val = this.cssValue( prop );
			val = val + offsetValue;
			this.css( prop, val + 'px' );
			return this;
		},
		targetLi: function() {
			return this.parent().parent();
		},
		selValue: function( val ) {
			if ( val ) {
				this.val( val );
			} else {
				this.find( 'option' ).removeAttr( 'selected' );
			}
		}
	} );
		
	
	//
	var GekoNavigationPageManager = function() {
		
		var _this = this;
		
		
		this.oPlugins = {};
		
		
		// register plugins
		this.registerPlugin = function( plugin ) {
			_this.oPlugins[ plugin.name ] = plugin;
		};
		
		
		
		// initialize
		this.init = function( options ) {
			
			// 
			
			var opts = $.extend( {
				// ...
			}, options );
			
			
			//// assignment
			
			var aNav = opts.nav_data;
			var aManage = opts.mgmt_data;
			var iDefaultTypeIdx = opts.default_idx;
			
			
			
			//// element references
			
			var navIf = $( '#' + opts.opts.form_id );
			var navIfTrash = navIf.find( '.' + opts.opts.trash );
			var navIfList = navIf.find( '.' + opts.opts.list );
			var navIfTmpl = navIf.find( '.' + opts.opts.template );
			
			var navDlg = $( '#' + opts.opts.dialog );
			
			
			var elemsPlugin = {
				navIf: navIf,
				navIfTrash: navIfTrash,
				navIfList: navIfList,
				navIfTmpl: navIfTmpl,
				navDlg: navDlg
			};
			
			
			//// variable declarations
			
			
			// prep the type management data
			$.each( aManage, function( i ) {
				
				aManage[ i ].__opts = opts.opts;							// general options
				aManage[ i ].__elems = elemsPlugin;							// references to $ elements
				
			} );
			
			
			
			//// convenience class for calculating indentation
			
			//
			var Indentation = function() {
				
				this.getNegativeOffset = function() {
					return -opts.opts.indent_width;
				}
		
				this.getPositiveOffset = function() {
					return opts.opts.indent_width;
				}
				
				// currently unused
				this.getMinWidth = function() {
					return opts.opts.max_width - ( opts.opts.indent_width * opts.opts.indent_levels );
				}
				
				this.getIndentLevels = function( elemWidth ) {
					return ( opts.opts.max_width - elemWidth ) / opts.opts.indent_width;
				}
				
			};
			
			var oIndent = new Indentation;
			
			
			var sTypeCssClasses = '';
			$.each( aManage, function( i ) {
				sTypeCssClasses += 'type-' + i + ' ';
			} );
			
			
			
			//
			var fTriggerPlugins = function() {
				
				var args = [];
				$.each( arguments, function() { args.push( this ); } );
				var sEvent = args.shift();
				
				$.each( aManage, function() {
					
					var mgmt = this;
					var aPlugins = [ _this.oPlugins[ this.__class ] ];
					var dep = '';
					
					while ( aPlugins[ 0 ].depends ) {
						dep = aPlugins[ 0 ].depends;
						aPlugins.unshift( _this.oPlugins[ dep ] );
					}
					
					$.each( aPlugins, function() {
						
						var fPluginEvent = this[ sEvent ];
						if ( fPluginEvent ) fPluginEvent.apply( mgmt, args );
						
					} );
					
				} );
				
			};
			
			
			
			//// Init ------------------------------------------------------------------------------
			
			// disable/enable parts of the interface
			
			//
			if ( opts.opts.remove_drag_template ) {
				// remove the drag item template
				navIf.find( '.' + opts.opts.prefix + 'tmpl_outer' ).remove();
			} else {
				// make the drag template div draggable
				navIf.find( '.' + opts.opts.prefix + 'tmpl_outer' ).draggable( {
					handle: '.' + opts.opts.prefix + 'drag_handle'
				} );			
			}
			
			//
			if ( opts.opts.remove_outdent ) {
				navIfTmpl.find( 'a.outdent' ).remove();
			}
			
			//
			if ( opts.opts.remove_indent ) {
				navIfTmpl.find( 'a.indent' ).remove();
			}
			
			//
			if ( opts.opts.remove_options ) {
				navIfTmpl.find( 'a.options' ).remove();
			}
			
			//
			if ( opts.opts.remove_remove ) {
				navIfTmpl.find( 'a.remove' ).remove();
			}
			
			//
			if ( opts.opts.remove_go_to_link ) {
				navIfTmpl.find( 'a.link' ).remove();
			}
			
			//
			if ( opts.opts.remove_toggle_visibility ) {
				navIfTmpl.find( 'a.visibility' ).remove();
			}
			
			//
			if ( opts.opts.remove_trash ) {
				navIf.find( '.' + opts.opts.trash ).remove();
			}
			
			//
			if ( opts.opts.disable_default_params ) {
			
				navDlg.find( '#' + opts.opts.prefix + 'type' ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				navDlg.find( '.opt-common label[for=' + opts.opts.prefix + 'type]' ).css( 'color', 'gray' );
				
				navDlg.find( '#' + opts.opts.prefix + 'target' ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				navDlg.find( '.opt-common label[for=' + opts.opts.prefix + 'target]' ).css( 'color', 'gray' );
				
				navDlg.find( '#' + opts.opts.prefix + 'css_class' ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				navDlg.find( '.opt-common label[for=' + opts.opts.prefix + 'css_class]' ).css( 'color', 'gray' );
				
				navDlg.find( '#' + opts.opts.prefix + 'inactive' ).attr( 'disabled', 'disabled' );
				navDlg.find( '.opt-common label[for=' + opts.opts.prefix + 'inactive]' ).css( 'color', 'gray' );
				
				navDlg.find( '#' + opts.opts.prefix + 'hide' ).attr( 'disabled', 'disabled' );
				navDlg.find( '.opt-common label[for=' + opts.opts.prefix + 'hide]' ).css( 'color', 'gray' );
			
			}
			
			fTriggerPlugins( 'init' );
			
			
			//// Nav Interface Stuff ---------------------------------------------------------------
			
			//// functions
						
			//
			var fIndent = function() {
				
				var li = $( this ).targetLi();
				var nav_params = li.data( 'nav_params' );
								
				if ( nav_params.indent < opts.opts.indent_levels ) {
					
					var prev_nav_params = li.prev().data( 'nav_params' );
					if ( prev_nav_params && ( nav_params.indent <= prev_nav_params.indent ) ) {
						nav_params.indent++;
						li.data( 'nav_params', nav_params ).trigger( 'update' );
					} else {
						alert( 'Cannot indent' );
					}
					
				} else {
					alert( 'Maximum indentation reached.' );
				}
				
				return false;
			};
			
			//
			var fOutdent = function() {
				
				var li = $( this ).targetLi();
				var nav_params = li.data( 'nav_params' );
				
				if ( nav_params.indent > 0 ) {
					var next_nav_params = li.next().data( 'nav_params' );
					if ( ( null == next_nav_params ) || ( nav_params.indent >= next_nav_params.indent ) ) {
						nav_params.indent--;
						li.data( 'nav_params', nav_params ).trigger( 'update' );
					} else {
						alert( 'Cannot outdent' );
					}
				} else {
					alert( 'Minimum indentation reached.' );				
				}
				
				return false;
			};
			
			//
			var fItemTitle = function() {
				var li = $( this ).targetLi();
				navDlg.data( 'selected_li', li ).dialog( 'open' );
				return false;
			};
			
			//
			var fOptions = function() {
				var li = $( this ).targetLi();
				navDlg.data( 'selected_li', li ).dialog( 'open' );
				return false;
			};
			
			//
			var fVisibility = function() {
				var li = $( this ).targetLi();
				var nav_params = li.data( 'nav_params' );
				if ( nav_params.hide ) {
					nav_params.hide = false;
				} else {
					nav_params.hide = true;				
				}
				li.data( 'nav_params', nav_params ).trigger( 'update' );
				return false;
			};
			
			//
			var fRemove = function() {
				
				var li = $( this ).targetLi();
				
				if ( confirm( 'Are you sure you want to remove this item?' ) ) {
					li.remove();
				}
				
				return false;
			};
			
			//
			var fUpdate = function() {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				// update the ui
				$( this ).find( 'span.item_title' ).css(
					'width', opts.opts.inner_span_width + 'px'
				).offsetCss(
					'width', oIndent.getNegativeOffset() * nav_params.indent
				).find( 'a' ).html( nav_params.label );
				
				$( this ).find( 'div.item_icon' ).removeClass(
					sTypeCssClasses
				).addClass(
					'type-' + nav_params.type
				);
				
				$( this ).css(
					'width', opts.opts.max_width + 'px'
				).css(
					'margin-left', '5px'
				).offsetCss(
					'width', oIndent.getNegativeOffset() * nav_params.indent
				).offsetCss(
					'margin-left', oIndent.getPositiveOffset() * nav_params.indent
				);
				
				if ( nav_params.hide ) {
					$( this ).find( 'a.visibility img' ).removeClass( 'vbl' ).addClass( 'hdn' );
				} else {
					$( this ).find( 'a.visibility img' ).removeClass( 'hdn' ).addClass( 'vbl' );
				}
				
				$( this ).data( 'nav_params', nav_params );
				
			};
			
			
			// add functionality to a cloned <li>
			var fSetupLi = function( li, bPartial ) {
				
				if ( !bPartial ) {
					li.find( 'a.indent' ).click( fIndent );
					li.find( 'a.outdent' ).click( fOutdent );
					li.find( 'a.remove' ).click( fRemove );				
				}
				
				li.find( 'span.item_title a' ).click( fItemTitle );
				li.find( 'a.options' ).click( fOptions );
				li.find( 'a.visibility' ).click( fVisibility );
				
				li.bind( 'update', fUpdate );
				
				fTriggerPlugins( 'setup_li', li );
				
				return li;
			}
						
			//
			var mergeDefaultParams = function( typeIdx, obj ) {
				
				if ( null == typeIdx ) {
					typeIdx = iDefaultTypeIdx;
					// alert( iDefaultTypeIdx );
				}
	
				if ( null == obj ) {
					obj = {};
				}
				
				// make a copy of default params
				var nav_params = $.extend( true, {}, aManage[ typeIdx ].default_params );
				
				// merge
				return $.extend( true, nav_params, obj );
			}
	
			
			
			//// pre-init stuff
						
			// populate sortable
			$.each( aNav, function( i, nav_params ) {
				
				nav_params.item_idx = i;
				nav_params = mergeDefaultParams( nav_params.type, nav_params );
				
				var clonedLi = navIfTmpl.clone( true );
				
				clonedLi.removeClass(
					'ui-state-highlight ' + opts.opts.template
				).addClass(
					'ui-state-default'
				).data(
					'nav_params', nav_params
				)
				
				fSetupLi( clonedLi );			// add functionality to buttons
				
				clonedLi.appendTo( navIfList );
				
			} );
			
			// add (partial) functionality to buttons on the template
			fSetupLi( navIfTmpl, true );
			
			
			
			
			//// assign functionality to elements
			
			//
			if ( !opts.opts.remove_trash ) {	
				navIfTrash.sortable( {
					revert: true
				} );
			}
			
			
			//
			if ( !opts.opts.remove_sortable ) {
				navIfList.sortable( {
					revert: true,
					connectWith: navIfTrash,
					remove: function( event, ui ) {
						
						var li = ui.item;
						
						if ( confirm( 'Are you sure you want to remove this item?' ) ) {
							li.remove();
						} else {
							return false;
						}
					},
					update: function( event, ui ) {
						
						// prepare the cloned template li
						var li = ui.item;
						
						if ( li.hasClass( opts.opts.template ) ) li.removeClass( opts.opts.template );
						if ( !li.data( 'nav_params' ) ) {
							
							var tmpl_nav_params = navIfTmpl.data( 'nav_params' );
							var nav_params = $.extend( true, {},
								( tmpl_nav_params ) ?
									tmpl_nav_params :
									mergeDefaultParams()
							);
							
							li.data( 'nav_params', nav_params );
							
							fSetupLi( li );			// add functionality to buttons
						}
					}
				} );
			}
			
			//
			if ( !opts.opts.remove_sortable ) {
				navIfTmpl.draggable( {
					connectToSortable: navIfList,
					helper: 'clone',
					revert: 'invalid'
				} );
			}
			
			
			//
			navIf.submit(function() {
				
				var aSaveNav = new Array();
				
				navIfList.find( 'li' ).each( function( i ) {
					aSaveNav.push( $( this ).data( 'nav_params' ) );
				} );
				
				$( '#' + opts.opts.result_field ).val( $.toJSON( aSaveNav ) );
				
				return true;
				
			} );
	
			
			
			//// Dialog Stuff ----------------------------------------------------------------------
						
			navDlg.dialog( {
				bgiframe: true,
				autoOpen: false,
				height: opts.opts.dialog_height,
				width: opts.opts.dialog_width,
				modal: true,
				buttons: {
					Update: function() {
						
						var selectedLi = $( this ).data( 'selected_li' );
						var nav_params = selectedLi.data( 'nav_params' );
						
						nav_params.type = $( this ).find( '#' + opts.opts.prefix + 'type' ).val();
						nav_params.label = $( this ).find( '#' + opts.opts.prefix + 'label' ).val();
						nav_params.title = $( this ).find( '#' + opts.opts.prefix + 'title' ).val();
						nav_params.target = $( this ).find( '#' + opts.opts.prefix + 'target' ).val();
						nav_params.css_class = $( this ).find( '#' + opts.opts.prefix + 'css_class' ).val();
						nav_params.inactive = $( this ).find( '#' + opts.opts.prefix + 'inactive' ).attr( 'checked' );
						nav_params.hide = $( this ).find( '#' + opts.opts.prefix + 'hide' ).attr( 'checked' );
						
						selectedLi.trigger( 'pre_update' );
						
						selectedLi.data( 'nav_params_copy', nav_params );			// set copy so that changes are not reverted
						
						selectedLi.trigger( 'update' );
						
						$( this ).dialog( 'close' );
						
					},
					Cancel: function() {
						
						$( this ).dialog( 'close' );
						
					}
				},
				open: function() {
					
					var selectedLi = $( this ).data( 'selected_li' );
					var nav_params = selectedLi.data( 'nav_params' );
					
					selectedLi.data( 'nav_params_copy' , $.extend( true, {}, nav_params ) );		// make a copy, in case reverting
					
					if ( !selectedLi.data( 'nav_params_prev' ) ) {
						selectedLi.data( 'nav_params_prev' , $.extend( true, {}, nav_params ) );
					}
					
					// trigger reset first then trigger open
					$( this ).trigger( 'reset' );
					$( this ).trigger( 'open' );
					
				},
				close: function() {
					
					// revert
					var selectedLi = $( this ).data( 'selected_li' );
					var nav_params_copy = selectedLi.data( 'nav_params_copy' );
					
					$( this ).trigger( 'close' );
					
					selectedLi.data( 'nav_params', nav_params_copy );			// either keep or revert the data
					
					// more cleanup stuff
					$( this ).find( '.opt-common label[for=' + opts.opts.prefix + 'label]' ).html( 'Label' );
					$( this ).find( '.opt-common label[for=' + opts.opts.prefix + 'title]' ).html( 'Title' );
					$( this ).find( '.opt-common label[for=' + opts.opts.prefix + 'target]' ).html( 'Target' );
					$( this ).find( '.opt-common label[for=' + opts.opts.prefix + 'css_class]' ).html( 'Class' );
					$( this ).find( '.opt-common label[for=' + opts.opts.prefix + 'inactive]' ).html( 'Force Inactive' );
					$( this ).find( '.opt-common label[for=' + opts.opts.prefix + 'hide]' ).html( 'Hide' );
					
				}
			} );		
			
			
			//
			navDlg.bind( 'open', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( '.opt-group' ).hide();
				$( this ).find( '.opt-group.opt-' + nav_params.type ).show();
				
			} );
			
			//
			navDlg.bind( 'reset', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( '#' + opts.opts.prefix + 'type' ).val( nav_params.type );
				$( this ).find( '#' + opts.opts.prefix + 'label' ).val( ( nav_params.label ) ? nav_params.label : '' );
				$( this ).find( '#' + opts.opts.prefix + 'title' ).val( ( nav_params.title ) ? nav_params.title : '' );
				$( this ).find( '#' + opts.opts.prefix + 'target' ).val( ( nav_params.target ) ? nav_params.target : '' );
				$( this ).find( '#' + opts.opts.prefix + 'css_class' ).val( ( nav_params.css_class ) ? nav_params.css_class : '' );
				$( this ).find( '#' + opts.opts.prefix + 'inactive' ).attr( 'checked', ( nav_params.inactive ) ? true : false );
				$( this ).find( '#' + opts.opts.prefix + 'hide' ).attr( 'checked', ( nav_params.hide ) ? true : false );
				
			} );
			
			//
			navDlg.find( '#' + opts.opts.prefix + 'type' ).change( function() {
	
				var selectedLi = navDlg.data( 'selected_li' );
				var nav_params = selectedLi.data( 'nav_params' );
				
				var nav_params_prev = $.extend( true, {}, nav_params );
				selectedLi.data( 'nav_params_prev', nav_params_prev );
				
				nav_params.type = $( this ).val();
				
				if ( selectedLi.parent().hasClass( opts.opts.prefix + 'template' ) ) {
					iDefaultTypeIdx = nav_params.type;
				}
				
				nav_params = mergeDefaultParams( nav_params.type, nav_params );
				
				selectedLi.data( 'nav_params', nav_params );
				
				// trigger type change event, then open event on the dialog
				navDlg.trigger( 'type_change' );
				navDlg.trigger( 'open' );
				
			} );
			
			
			// testing
			
			$( '#test' ).click( function() {
				
				/* /
				var aSaveNav = new Array();
				
				navIfList.find( 'li' ).each(function(i) {
					aSaveNav.push( $( this ).data( 'nav_params' ) );
				} );
				/* */
				
				// $.sygerDebug( 'aSaveNav', aSaveNav, ' ', 2 );
				// alert( $.toJSON(aSaveNav) );
				
				// $( '#' + opts.opts.result_field ).val( $.toJSON(aSaveNav) );
				
				// alert( $( '#' + opts.opts.prefix + 'template li' ).html() );
				// alert($.debug);
				
				// $.sygerDebug( 'aNav', aNav, ' ', 2 );
				// $.sygerDebug( 'document', document, ' ', 1 );
				
				// $.sygerDebug( 'dialog', navDlg.html().htmlEntities() );
				// $.sygerDebug( 'aManage', aManage, ' ', 2, true );
				
			} );
			
			
			
			
			//// Setup -----------------------------------------------------------------------------
			
			navIf.find( 'ul, li' ).disableSelection();
			
			// populate the draggable template
			navIfTmpl.data( 'nav_params', mergeDefaultParams() );
			
			
			// update loaded nav elems
			navIfTmpl.trigger( 'update' );
			navIfList.find( 'li' ).trigger( 'update' );
			
			navDlg.find( '.opt-group' ).hide();
			
			fTriggerPlugins( 'setup' );
			
			
			// navDlg.trigger( 'open' );
			// alert( 'aaa' );
			
			// !!! TO DO:
			// [ ?php $this->outputHookJs( 'js' ); ? ]
			
			/* /
			var s = '';
			$.each( _this.oPlugins, function( i ) {
				s += i + '; ';
			} );
			alert( s );
			/* */
			
		};
		
	};
	
	
	$.extend( {
		gekoNavigationPageManager: new GekoNavigationPageManager()
	} );
	
} )( jQuery );