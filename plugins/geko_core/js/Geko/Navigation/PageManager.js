/*
 * "geko_core/js/Geko/Navigation/PageManager.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

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
			this.css( prop, '%spx'.printf( val ) );
			
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
		
		
		
		// load
		this.load = function( oParams ) {
			
			var oGetParams = oParams.get_params;
			
			$.get(
				oParams.script.process,
				oGetParams,
				function ( res ) {
					
					var oData = res.data;
					
					_this.init( oData, oParams.script, oGetParams );
					
				},
				'json'
			);
			
		};
		
		
		// initialize
		this.init = function( options, oScript, oGetParams ) {
			
			// 
			
			var opts = $.extend( {
				// ...
			}, options );
			
			
			
			//// assignment
			
			var aNav = opts.nav_data;
			var aManage = opts.mgmt_data;
			var oOpts = opts.opts;
			var iDefaultTypeIdx = opts.default_idx;
			
			
			var sPrefix = oOpts.prefix;
			
			
			
			//// element references
			
			var eNavIf = $( '#%s'.printf( oOpts.form_id ) );
			var eNavIfTrash = eNavIf.find( '.%s'.printf( oOpts.trash ) );
			var eNavIfList = eNavIf.find( '.%s'.printf( oOpts.list ) );
			var eNavIfTmpl = eNavIf.find( '.%s'.printf( oOpts.template ) );
			
			var eNavDlg = $( '#%s'.printf( oOpts.dialog ) );
			
			
			var elemsPlugin = {
				navIf: eNavIf,
				navIfTrash: eNavIfTrash,
				navIfList: eNavIfList,
				navIfTmpl: eNavIfTmpl,
				navDlg: eNavDlg
			};
			
			
			//// variable declarations
			
			
			// prep the type management data
			$.each( aManage, function( i ) {
				
				aManage[ i ].__opts = oOpts;							// general options
				aManage[ i ].__elems = elemsPlugin;							// references to $ elements
				
			} );
			
			
			
			//// convenience class for calculating indentation
			
			//
			var Indentation = function() {
				
				this.getNegativeOffset = function() {
					return -oOpts.indent_width;
				}
		
				this.getPositiveOffset = function() {
					return oOpts.indent_width;
				}
				
				// currently unused
				this.getMinWidth = function() {
					return oOpts.max_width - ( oOpts.indent_width * oOpts.indent_levels );
				}
				
				this.getIndentLevels = function( elemWidth ) {
					return ( oOpts.max_width - elemWidth ) / oOpts.indent_width;
				}
				
			};
			
			var oIndent = new Indentation;
			
			
			var sTypeCssClasses = '';
			$.each( aManage, function( i ) {
				sTypeCssClasses += 'type-%d '.printf( i );
			} );
			
			
			
			//
			var cTriggerPlugins = function() {
				
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
						
						var cPluginEvent = this[ sEvent ];
						if ( cPluginEvent ) cPluginEvent.apply( mgmt, args );
						
					} );
					
				} );
				
			};
			
			
			
			//// Init ------------------------------------------------------------------------------
			
			// disable/enable parts of the interface
			
			//
			if ( oOpts.remove_drag_template ) {
				// remove the drag item template
				eNavIf.find( '.%stmpl_outer'.printf( sPrefix ) ).remove();
			} else {
				// make the drag template div draggable
				eNavIf.find( '.%stmpl_outer'.printf( sPrefix ) ).draggable( {
					handle: '.%sdrag_handle'.printf( sPrefix )
				} );			
			}
			
			//
			if ( oOpts.remove_outdent ) {
				eNavIfTmpl.find( 'a.outdent' ).remove();
			}
			
			//
			if ( oOpts.remove_indent ) {
				eNavIfTmpl.find( 'a.indent' ).remove();
			}
			
			//
			if ( oOpts.remove_options ) {
				eNavIfTmpl.find( 'a.options' ).remove();
			}
			
			//
			if ( oOpts.remove_remove ) {
				eNavIfTmpl.find( 'a.remove' ).remove();
			}
			
			//
			if ( oOpts.remove_go_to_link ) {
				eNavIfTmpl.find( 'a.link' ).remove();
			}
			
			//
			if ( oOpts.remove_toggle_visibility ) {
				eNavIfTmpl.find( 'a.visibility' ).remove();
			}
			
			//
			if ( oOpts.remove_trash ) {
				eNavIf.find( '.%s'.printf( oOpts.trash ) ).remove();
			}
			
			//
			if ( oOpts.disable_default_params ) {
			
				eNavDlg.find( '#%stype'.printf( sPrefix ) ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( '.opt-common label[for=%stype]'.printf( sPrefix ) ).css( 'color', 'gray' );
				
				eNavDlg.find( '#%starget'.printf( sPrefix ) ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( '.opt-common label[for=%starget]'.printf( sPrefix ) ).css( 'color', 'gray' );
				
				eNavDlg.find( '#%scss_class'.printf( sPrefix ) ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( '.opt-common label[for=%scss_class]'.printf( sPrefix ) ).css( 'color', 'gray' );
				
				eNavDlg.find( '#%sinactive'.printf( sPrefix ) ).attr( 'disabled', 'disabled' );
				eNavDlg.find( '.opt-common label[for=%sinactive]'.printf( sPrefix ) ).css( 'color', 'gray' );
				
				eNavDlg.find( '#%shide'.printf( sPrefix ) ).attr( 'disabled', 'disabled' );
				eNavDlg.find( '.opt-common label[for=%shide]'.printf( sPrefix ) ).css( 'color', 'gray' );
			
			}
			
			cTriggerPlugins( 'init' );
			
			
			//// Nav Interface Stuff ---------------------------------------------------------------
			
			//// functions
			
			//
			var cIndent = function() {
				
				var eLi = $( this ).targetLi();
				var nav_params = eLi.data( 'nav_params' );
								
				if ( nav_params.indent < oOpts.indent_levels ) {
					
					var prev_nav_params = eLi.prev().data( 'nav_params' );
					if ( prev_nav_params && ( nav_params.indent <= prev_nav_params.indent ) ) {
						nav_params.indent++;
						eLi.data( 'nav_params', nav_params ).trigger( 'update' );
					} else {
						alert( 'Cannot indent' );
					}
					
				} else {
					alert( 'Maximum indentation reached.' );
				}
				
				return false;
			};
			
			//
			var cOutdent = function() {
				
				var eLi = $( this ).targetLi();
				var nav_params = eLi.data( 'nav_params' );
				
				if ( nav_params.indent > 0 ) {
					var next_nav_params = eLi.next().data( 'nav_params' );
					if ( ( null == next_nav_params ) || ( nav_params.indent >= next_nav_params.indent ) ) {
						nav_params.indent--;
						eLi.data( 'nav_params', nav_params ).trigger( 'update' );
					} else {
						alert( 'Cannot outdent' );
					}
				} else {
					alert( 'Minimum indentation reached.' );				
				}
				
				return false;
			};
			
			//
			var cItemTitle = function() {
				var eLi = $( this ).targetLi();
				eNavDlg.data( 'selected_li', eLi ).dialog( 'open' );
				return false;
			};
			
			//
			var cOptions = function() {
				var eLi = $( this ).targetLi();
				eNavDlg.data( 'selected_li', eLi ).dialog( 'open' );
				return false;
			};
			
			//
			var cVisibility = function() {
				var eLi = $( this ).targetLi();
				var nav_params = eLi.data( 'nav_params' );
				if ( nav_params.hide ) {
					nav_params.hide = false;
				} else {
					nav_params.hide = true;				
				}
				eLi.data( 'nav_params', nav_params ).trigger( 'update' );
				return false;
			};
			
			//
			var cRemove = function() {
				
				var eLi = $( this ).targetLi();
				
				if ( confirm( 'Are you sure you want to remove this item?' ) ) {
					eLi.remove();
				}
				
				return false;
			};
			
			//
			var cUpdate = function() {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				// update the ui
				$( this ).find( 'span.item_title' ).css(
					'width', '%spx'.printf( oOpts.inner_span_width )
				).offsetCss(
					'width', oIndent.getNegativeOffset() * nav_params.indent
				).find( 'a' ).html( nav_params.label );
				
				$( this ).find( 'div.item_icon' ).removeClass(
					sTypeCssClasses
				).addClass(
					'type-%s'.printf( nav_params.type )
				);
				
				$( this ).css(
					'width', '%spx'.printf( oOpts.max_width )
				).css(
					'margin-left', '5px'
				).offsetCss(
					'width', oIndent.getNegativeOffset() * nav_params.indent
				).offsetCss(
					'margin-left', oIndent.getPositiveOffset() * nav_params.indent
				);
				
				if ( nav_params.hide ) {
					$( this ).find( 'a.visibility' ).removeClass( 'vbl' ).addClass( 'hdn' );
				} else {
					$( this ).find( 'a.visibility' ).removeClass( 'hdn' ).addClass( 'vbl' );
				}
				
				$( this ).data( 'nav_params', nav_params );
				
			};
			
			
			// add functionality to a cloned <li>
			var cSetupLi = function( eLi, bPartial ) {
				
				if ( !bPartial ) {
					eLi.find( 'a.indent' ).click( cIndent );
					eLi.find( 'a.outdent' ).click( cOutdent );
					eLi.find( 'a.remove' ).click( cRemove );				
				}
				
				eLi.find( 'span.item_title a' ).click( cItemTitle );
				eLi.find( 'a.options' ).click( cOptions );
				eLi.find( 'a.visibility' ).click( cVisibility );
				
				eLi.bind( 'update', cUpdate );
				
				cTriggerPlugins( 'setup_li', eLi );
				
				return eLi;
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
				
				var eClonedLi = eNavIfTmpl.clone( true );
				
				eClonedLi.removeClass(
					'ui-state-highlight %s'.printf( oOpts.template )
				).addClass(
					'ui-state-default'
				).data(
					'nav_params', nav_params
				)
				
				cSetupLi( eClonedLi );			// add functionality to buttons
				
				eClonedLi.appendTo( eNavIfList );
				
			} );
			
			// add (partial) functionality to buttons on the template
			cSetupLi( eNavIfTmpl, true );
			
			
			
			
			//// assign functionality to elements
			
			//
			if ( !oOpts.remove_trash ) {	
				eNavIfTrash.sortable( {
					revert: true
				} );
			}
			
			
			//
			if ( !oOpts.remove_sortable ) {
				eNavIfList.sortable( {
					revert: true,
					connectWith: eNavIfTrash,
					remove: function( event, ui ) {
						
						var eLi = ui.item;
						
						if ( confirm( 'Are you sure you want to remove this item?' ) ) {
							eLi.remove();
						} else {
							return false;
						}
					},
					update: function( event, ui ) {
						
						// prepare the cloned template li
						var eLi = ui.item;
						
						if ( eLi.hasClass( oOpts.template ) ) eLi.removeClass( oOpts.template );
						if ( !eLi.data( 'nav_params' ) ) {
							
							var tmpl_nav_params = eNavIfTmpl.data( 'nav_params' );
							var nav_params = $.extend( true, {},
								( tmpl_nav_params ) ?
									tmpl_nav_params :
									mergeDefaultParams()
							);
							
							eLi.data( 'nav_params', nav_params );
							
							cSetupLi( eLi );			// add functionality to buttons
						}
					}
				} );
			}
			
			//
			if ( !oOpts.remove_sortable ) {
				eNavIfTmpl.draggable( {
					connectToSortable: eNavIfList,
					helper: 'clone',
					revert: 'invalid'
				} );
			}
			
			
			//
			eNavIf.submit( function() {
				
				var aSaveNav = new Array();
				
				eNavIfList.find( 'li' ).each( function( i ) {
					aSaveNav.push( $( this ).data( 'nav_params' ) );
				} );
				
				$( '#%s'.printf( oOpts.result_field ) ).val( $.toJSON( aSaveNav ) );
				
				$.post(
					oScript.process,
					'%s&_service=%s&_action=save_data'.printf( eNavIf.serialize(), oGetParams[ '_service' ] ),
					function() {
						window.location = oScript.curpage;
					},
					'json'
				);
				
				return false;
				
			} );
	
			
			
			//// Dialog Stuff ----------------------------------------------------------------------
						
			eNavDlg.dialog( {
				bgiframe: true,
				autoOpen: false,
				height: oOpts.dialog_height,
				width: oOpts.dialog_width,
				modal: true,
				buttons: {
					Update: function() {
						
						var eSelectedLi = $( this ).data( 'selected_li' );
						var nav_params = eSelectedLi.data( 'nav_params' );
						
						nav_params.type = $( this ).find( '#%stype'.printf( sPrefix ) ).val();
						nav_params.label = $( this ).find( '#%slabel'.printf( sPrefix ) ).val();
						nav_params.title = $( this ).find( '#%stitle'.printf( sPrefix ) ).val();
						nav_params.target = $( this ).find( '#%starget'.printf( sPrefix ) ).val();
						nav_params.css_class = $( this ).find( '#%scss_class'.printf( sPrefix ) ).val();
						nav_params.inactive = $( this ).find( '#%sinactive'.printf( sPrefix ) ).attr( 'checked' );
						nav_params.hide = $( this ).find( '#%shide'.printf( sPrefix ) ).attr( 'checked' );
						
						eSelectedLi.trigger( 'pre_update' );
						
						eSelectedLi.data( 'nav_params_copy', nav_params );			// set copy so that changes are not reverted
						
						eSelectedLi.trigger( 'update' );
						
						$( this ).dialog( 'close' );
						
					},
					Cancel: function() {
						
						$( this ).dialog( 'close' );
						
					}
				},
				open: function() {
					
					var eSelectedLi = $( this ).data( 'selected_li' );
					var nav_params = eSelectedLi.data( 'nav_params' );
					
					eSelectedLi.data( 'nav_params_copy' , $.extend( true, {}, nav_params ) );		// make a copy, in case reverting
					
					if ( !eSelectedLi.data( 'nav_params_prev' ) ) {
						eSelectedLi.data( 'nav_params_prev' , $.extend( true, {}, nav_params ) );
					}
					
					// trigger reset first then trigger open
					$( this ).trigger( 'reset' );
					$( this ).trigger( 'open' );
					
				},
				close: function() {
					
					// revert
					var eSelectedLi = $( this ).data( 'selected_li' );
					var nav_params_copy = eSelectedLi.data( 'nav_params_copy' );
					
					$( this ).trigger( 'close' );
					
					eSelectedLi.data( 'nav_params', nav_params_copy );			// either keep or revert the data
					
					// more cleanup stuff
					$( this ).find( '.opt-common label[for=%slabel]'.printf( sPrefix ) ).html( 'Label' );
					$( this ).find( '.opt-common label[for=%stitle]'.printf( sPrefix ) ).html( 'Title' );
					$( this ).find( '.opt-common label[for=%starget]'.printf( sPrefix ) ).html( 'Target' );
					$( this ).find( '.opt-common label[for=%scss_class]'.printf( sPrefix ) ).html( 'Class' );
					$( this ).find( '.opt-common label[for=%sinactive]'.printf( sPrefix ) ).html( 'Force Inactive' );
					$( this ).find( '.opt-common label[for=%shide]'.printf( sPrefix ) ).html( 'Hide' );
					
				}
			} );		
			
			
			//
			eNavDlg.bind( 'open', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( '.opt-group' ).hide();
				$( this ).find( '.opt-group.opt-' + nav_params.type ).show();
				
			} );
			
			//
			eNavDlg.bind( 'reset', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( '#%stype'.printf( sPrefix ) ).val( nav_params.type );
				$( this ).find( '#%slabel'.printf( sPrefix ) ).val( ( nav_params.label ) ? nav_params.label : '' );
				$( this ).find( '#%stitle'.printf( sPrefix ) ).val( ( nav_params.title ) ? nav_params.title : '' );
				$( this ).find( '#%starget'.printf( sPrefix ) ).val( ( nav_params.target ) ? nav_params.target : '' );
				$( this ).find( '#%scss_class'.printf( sPrefix ) ).val( ( nav_params.css_class ) ? nav_params.css_class : '' );
				$( this ).find( '#%sinactive'.printf( sPrefix ) ).attr( 'checked', ( nav_params.inactive ) ? true : false );
				$( this ).find( '#%shide'.printf( sPrefix ) ).attr( 'checked', ( nav_params.hide ) ? true : false );
				
			} );
			
			//
			eNavDlg.find( '#%stype'.printf( sPrefix ) ).change( function() {
	
				var eSelectedLi = eNavDlg.data( 'selected_li' );
				var nav_params = eSelectedLi.data( 'nav_params' );
				
				var nav_params_prev = $.extend( true, {}, nav_params );
				eSelectedLi.data( 'nav_params_prev', nav_params_prev );
				
				nav_params.type = $( this ).val();
				
				if ( eSelectedLi.parent().hasClass( '%stemplate'.printf( sPrefix ) ) ) {
					iDefaultTypeIdx = nav_params.type;
				}
				
				nav_params = mergeDefaultParams( nav_params.type, nav_params );
				
				eSelectedLi.data( 'nav_params', nav_params );
				
				// trigger type change event, then open event on the dialog
				eNavDlg.trigger( 'type_change' );
				eNavDlg.trigger( 'open' );
				
			} );
			
			
			// testing
			
			$( '#test' ).click( function() {
				
				/* /
				var aSaveNav = new Array();
				
				eNavIfList.find( 'li' ).each(function(i) {
					aSaveNav.push( $( this ).data( 'nav_params' ) );
				} );
				/* */
				
				// $.sygerDebug( 'aSaveNav', aSaveNav, ' ', 2 );
				// alert( $.toJSON(aSaveNav) );
				
				// $( '#%s'.printf( oOpts.result_field ) ).val( $.toJSON(aSaveNav) );
				
				// alert( $( '#%stemplate li'.printf( sPrefix ) ).html() );
				// alert($.debug);
				
				// $.sygerDebug( 'aNav', aNav, ' ', 2 );
				// $.sygerDebug( 'document', document, ' ', 1 );
				
				// $.sygerDebug( 'dialog', eNavDlg.html().htmlEntities() );
				// $.sygerDebug( 'aManage', aManage, ' ', 2, true );
				
			} );
			
			
			
			
			//// Setup -----------------------------------------------------------------------------
			
			eNavIf.find( 'ul, li' ).disableSelection();
			
			// populate the draggable template
			eNavIfTmpl.data( 'nav_params', mergeDefaultParams() );
			
			
			// update loaded nav elems
			eNavIfTmpl.trigger( 'update' );
			eNavIfList.find( 'li' ).trigger( 'update' );
			
			eNavDlg.find( '.opt-group' ).hide();
			
			cTriggerPlugins( 'setup' );
			
			
			// eNavDlg.trigger( 'open' );
			// alert( 'aaa' );
			
			// !!! TO DO:
			// [ ?php $this->outputHookJs( 'js' ); ? ]
			
			/* /
			var s = '';
			$.each( _this.oPlugins, function( i ) {
				s += '%d; '.printf( i );
			} );
			alert( s );
			/* */
			
			
			eNavIf.find( '.gnp_ld' ).hide();
			eNavIf.find( '.gnp_main' ).css( 'visibility', 'visible' );
						
		};
		
	};
	
	
	$.extend( {
		gekoNavigationPageManager: new GekoNavigationPageManager()
	} );
	
} )( jQuery );