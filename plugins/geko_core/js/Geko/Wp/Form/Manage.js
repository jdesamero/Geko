;( function ( $ ) {

	////// vars
	
	var iAnimDelay = 300;
	var iDialogWidth = 500;
	
	
	
	// if value is not an array, then wrap it in one
	var arrayWrap = function( val ) {
		if ( !val ) return [ val ];
		var type = typeof( val );
		if ( ( type == 'object' ) && ( val.constructor === Array ) ) {
			// already an array
			return val;
		}
		return [ val ];
	}
	
	var pushVal = function( curval, pushval ) {
		curval = arrayWrap( curval );
		curval.push( pushval );
		return curval;
	};
	
	var ContentHelper = function( options, content ) {
		
		var opts = $.extend( {
			prefix: '',
			serialize_fields: [],
			new_values_cb: function() { }
		}, options );
		
		var _this = this;
		
		var getPrefix = function( elem ) {
			var tag = elem.getTagName();
			var prefix = ( ( 'default_elem' == elem.attr( 'id' ) ) || ( 'tr' == tag ) ) ?
				'.' : ( '#' + opts.prefix );
			return prefix;
		};
		
		this._isEmpty = false;
		this._newIndex = '';
		
		if ( !content ) {
			content = {};
			this._isEmpty = true;
		}
		
		// inherit field properties
		$.each( content, function( key, value ) {
			_this[ key ] = value;
		} );
		
		
		this.isEmpty = function() {
			return this._isEmpty;
		};
		
		this.setNewIndex = function( sIndex ) {
			this._newIndex = sIndex;
			return this;
		};
		
		this.setNewValues = function() {
			opts.new_values_cb.call( this );
			return this;
		};
		
		this.loadFormValues = function( elem ) {
			var prefix = getPrefix( elem );
			$.each( opts.fields, function( i, key ) {
				// load only if element exists
				var e = elem.find( prefix + key );
				if ( e.length > 0 ) e.setFormElemVal( _this[ key ] );
			} );
			
			return this;
		};
		
		this.unloadFormValues = function( elem ) {
			var prefix = getPrefix( elem );
			$.each( opts.fields, function( i, key ) {
				// unload only if element exists
				var e = elem.find( prefix + key );
				if ( e.length > 0 ) _this[ key ] = e.getFormElemVal();
			} );
			return this;
		};
		
		this.loadMetaValues = function( elem, context ) {
			
			// this.fmitmmv is Hard coded!!!!!!!!!!!!!
			
			var aMetaContexts = ContentHelper.meta_contexts;
			var aTypeHandlers = ContentHelper.type_handlers;
			
			var mvh = aMetaContexts[ context ];
			
			// re-arrange item meta values
			var fmitmmv = ( this.fmitmmv ) ? this.fmitmmv : [];
			var oMetaVal = {};
			$.each( fmitmmv, function( i, meta ) {
				var lang_id = meta.lang_id;
				var slug = meta.slug;
				if ( !oMetaVal[ lang_id ] ) oMetaVal[ lang_id ] = {};
				if ( oMetaVal[ lang_id ][ slug ] ) {
					var val = oMetaVal[ lang_id ][ slug ];
					oMetaVal[ lang_id ][ slug ] = pushVal( val, meta.value );
				} else {
					oMetaVal[ lang_id ][ slug ] = meta.value;
				}
			} );
			
			$.each( mvh.metadata, function( lang_id, lang_meta ) {
				// go through each language tab
				var langtab = elem.find( '#' + mvh.tab_pfx + '-' + lang_id );
				var langval = oMetaVal[ lang_id ];
				$.each( lang_meta, function( key, metadata ) {
					var typh = aTypeHandlers[ metadata.item_type ];
					typh.setFieldValue( langtab, key, 'meta_data_', ( ( langval ) ? langval[ key ] : '' ), metadata.fmmv );
				} );
			} );
			
			return this;
		};
		
		// context_id, fmitm_id, fmitmval_idx, fmsec_id, lang_id, slug, value
		
		this.unloadMetaValues = function( elem, context ) {
			
			// this.fmitmmv is Hard coded!!!!!!!!!!!!!
			
			var aMetaContexts = ContentHelper.meta_contexts;
			var aTypeHandlers = ContentHelper.type_handlers;
			
			var mvh = aMetaContexts[ context ];
			
			var fmitmmv = [];
			
			$.each( mvh.metadata, function( lang_id, lang_meta ) {
				// go through each language tab
				var langtab = elem.find( '#' + mvh.tab_pfx + '-' + lang_id );
				$.each( lang_meta, function( key, metadata ) {
					var typh = aTypeHandlers[ metadata.item_type ];
					var value = typh.getFieldValue( langtab, key, 'meta_data_', metadata.fmmv );
					value = arrayWrap( value );
					$.each( value, function( i, v ) {
						
						var meta = {
							context_id: mvh.context_id,
							fmitm_id: ( _this.fmitm_id ) ? _this.fmitm_id : 0,
							fmitmval_idx: ( _this.fmitmval_idx ) ? _this.fmitmval_idx : 0,
							fmsec_id: ( _this.fmsec_id ) ? _this.fmsec_id : 0,
							lang_id: lang_id,
							slug: key,
							value: v
						};
						
						fmitmmv.push( meta );
					} );
				} );
			} );
			
			this.fmitmmv = fmitmmv;
			
			return this;
		};
		
		this.loadSerialize = function( oRow ) {
			
			$.each( opts.fields, function( i, key ) {
				oRow[ key ] = _this[ key ];
			} );
			
			$.each( opts.serialize_fields, function( i, key ) {
				oRow[ key ] = _this[ key ];
			} );
			
			return oRow;
		}
		
		this.getRawContent = function( bAllFields ) {
			
			var oRet = {};
			
			$.each( opts.fields, function( i, key ) {
				oRet[ key ] = _this[ key ];
			} );
			
			if ( bAllFields ) {
				$.each( opts.serialize_fields, function( i, key ) {
					oRet[ key ] = _this[ key ];
				} );
			}
			
			// Hard coded !!!!!!!!!!!!!!!
			if ( this.fmitmmv ) oRet.fmitmmv = this.fmitmmv;
			
			return oRet;
		}
		
	}
	
	
	// aka widgets
	var itemValIdxCounter = 1;
	var ItemType = function( options ) {
		
		var opts = $.extend( {
			type: 'widget',
			getField: null
		}, options );
		
		var _this = this;
		
		this._dbType = null;
		this._fieldHtml = '';
		
		//
		this.init = function( dbType, fieldTemplates ) {
			
			// inherit field properties
			this._dbType = dbType;
			$.each( dbType, function( key, value ) {
				_this[ key ] = value;
			} );
			
			// output field
			this._fieldHtml = '<div>' + fieldTemplates.find( '.fields > .' + opts.type ).html() + '<\/div>';
			
			// TO DO: add type specific form validation
			
			// value manager
			var vmh;
			
			if ( 'checkbox' == dbType.slug ) {
				vmh = fieldTemplates.find( '.values .checkbox' ).html();				
			} else if ( parseInt( dbType.has_multiple_response ) ) {
				vmh = fieldTemplates.find( '.values .has_multiple_responses' ).html();
			} else if ( parseInt( dbType.has_multiple_values ) ) {
				vmh = fieldTemplates.find( '.values .has_multiple_values' ).html();			
			} else {
				vmh = fieldTemplates.find( '.values .default' ).html();			
			}
			
			this._valueManagerHtml = '<div>' + vmh + '<\/div>';
			
			// validation manager
			var vld;
			
			vld = fieldTemplates.find( '.validation > .all' ).html();
			
			var vldElem = fieldTemplates.find( '.validation > .' + opts.type );
			if ( vldElem.length ) {
				vld += vldElem.html();
			}
			
			this._validationManagerHtml = '<div>' + vld + '<\/div>';
			
			return this;
		};
		
		//
		this.getField = function( label, name, id, value, params ) {

			// default behaviour
			
			if ( !id ) id = name;
			
			var field = $( this._fieldHtml );
			
			var labelElem = field.find( 'label.main' );
			labelElem.attr( 'for', id ).html( label ).removeClass( 'main' );
			
			var widget = field.find( '.widget' );
			widget.attr( 'id', id ).attr( 'name', name ).removeClass( 'widget' );
			
			if ( opts.getField ) {
				return opts.getField.call( this, field, label, name, id, value, params );
			}
			
			return field;
			
		};
		
		//
		this.setFieldValue = function( elem, slug, prefix, value, params ) {
			
			if ( opts.setFieldValue ) {
				return opts.setFieldValue.call( this, elem, slug, prefix, value, params );
			}
			
			// default behaviour
			var e = elem.find( '#' + prefix + slug );
			if ( e.length > 0 ) e.setFormElemVal( value );
			
			return elem;
		};
		
		//
		this.getFieldValue = function( elem, slug, prefix, params ) {

			if ( opts.getFieldValue ) {
				return opts.getFieldValue.call( this, elem, slug, prefix, params );
			}
			
			// default behaviour
			var e = elem.find( '#' + prefix + slug );
			if ( e.length > 0 ) return e.getFormElemVal();
			
			return null;
		};
		
		//
		this.loadValueManager = function( aValues, oItemValContHlp ) {
						
			if ( !aValues ) aValues = [];
			
			var valMng = $( this._valueManagerHtml );
			
			if ( parseInt( this._dbType.has_multiple_values ) ) {
				
				// create manager for radio, checkbox_multi, select, and select_multi types
				var table = valMng.find( 'table' );
				
				var toggleTable = function( row ) {
					if ( table.find( 'tbody tr' ).length ) {
						table.show();
						if ( row ) {
							row.fadeIn( iAnimDelay ).css( 'display', 'table-row' );
						}
					} else {
						table.hide();
					}
				};
				
				// load
				var rowHtml = valMng.find( 'tr.multi' ).gekoGetAsHtml();
				
				var addRow = function( content ) {
					
					// wrap content
					content = new ContentHelper( oItemValContHlp, content );
					
					if ( content.isEmpty() ) {
						content.setNewIndex( '_' + itemValIdxCounter ).setNewValues();
						itemValIdxCounter++;
					}
					
					var row = $( rowHtml ).removeClass( 'multi' );
					row.find( 'a.geko-form-remove-item' ).click( function() {
						if ( confirm( 'Are you sure you want to remove this item?' ) ) {
							$( this ).closest( 'tr' ).fadeOut( iAnimDelay, function() {
								$( this ).remove();
								toggleTable();
							} );
						}
						return false;
					} );
					
					content.loadFormValues( row );
					
					row.data( 'content', content );
					
					row.hide();
					valMng.find( 'tbody' ).append( row );
					
					toggleTable( row );
				};
				
				$.each( aValues, function( i, content ) {
					addRow( content );
				} );
				
				valMng.find( 'button.add' ).click( function() {
					addRow();
					return false;
				} );

				if ( !parseInt( this._dbType.has_multiple_response ) ) {
					valMng.find( 'button.remove_default' ).click( function() {
						table.find( 'input.is_default' ).removeAttr( 'checked' );
						return false;
					} );				
				}
				
				// init
				toggleTable();
				valMng.find( 'tbody' ).sortable();
				
			} else {
				
				var field = valMng.find( '#default_elem' );
				field.find( '.widget' ).removeClass( 'widget' );
				
				var content;
				if ( aValues && aValues[ 0 ] ) content = aValues[ 0 ];
				
				content = new ContentHelper( oItemValContHlp, content );
				if ( content.isEmpty() ) content.setNewValues();
				
				content.loadFormValues( field );
				field.data( 'content', content );
				
			}
			
			return valMng;
			
		};
		
		//
		this.unloadValueManager = function( valMng ) {
			
			var aValues = [];
			
			if ( parseInt( this._dbType.has_multiple_values ) ) {
				
				valMng.find( 'tbody tr' ).each( function() {
					
					var row = $( this );
					var content = row.data( 'content' );
					
					content.unloadFormValues( row );
					
					aValues.push( content.getRawContent( true ) );
					
				} );
				
			} else {
				
				var field = valMng.find( '#default_elem' );
				var content = field.data( 'content' );
				
				content.unloadFormValues( field );
				
				aValues.push( content.getRawContent( true ) );
				
			}
			
			return aValues;
		};
		
		//
		this.loadSerialize = function( aContent ) {
		
		};
		
		
		
		//
		this.loadValidationManager = function( content ) {
			
			var vldMng = $( this._validationManagerHtml );
			
			// HACKISH!!!
			var flags = [];
			
			if ( content.validation ) {
				flags = content.validation.split( ',' );
			}
			
			$.each( flags, function( i, v ) {
				var input = vldMng.find( '#vld_' + v );
				if ( input.length ) {
					input.attr( 'checked', 'checked' );
				}
			} );
			
			vldMng.find( '.unchk' ).click( function() {
				var button = $( this );
				var target = button.attr( 'id' ).replace( 'unchk_', 'vld_' );
				vldMng.find( 'input[name="' + target + '"]' ).removeAttr( 'checked' );
				return false;
			} );
			
			return vldMng;
			
		};
		
		//
		this.unloadValidationManager = function( vldMng ) {
			
			var res = '';
			
			vldMng.find( '.vld' ).each( function() {
				var input = $( this );
				if ( input.is( ':checked' ) ) {
					if ( res ) res += ',';
					res += input.attr( 'id' ).replace( 'vld_', '' );
				}
			} );
			
			return res;
		}
		
	}
	
	// meta value database fields
	// context_id, fmitm_id, fmitmval_idx, fmsec_id, lang_id, slug, value
	
	//
	var ItemMetaContext = function( options ) {
		
		var opts = $.extend( {
			load: null,
			unload: null
		}, options );
		
		var _this = this;
		this.context_id = opts.context_id;
		this.tab_pfx = opts.tab_pfx;
		this.values = {};
		this.metadata = {};
		
		// register to hash
		opts.hash[ this.context_id ] = this;
		
		this.load = function( fmitmmv ) {
			if ( opts.load ) {
				opts.load.call( this, fmitmmv );
			}
			return this;
		};
		
		this.unload = function( content ) {
			if ( opts.unload ) {
				content = opts.unload.call( this, content );
			}
			return content;
		};
		
		// group meta data by language, then by slug
		this.loadmeta = function( metadata ) {
			var lang_id = metadata.lang_id;
			if ( !this.metadata[ lang_id ] ) this.metadata[ lang_id ] = {};
			this.metadata[ lang_id ][ metadata.slug ] = metadata;
			return this;
		};
		
	};
	
	
	
	// main
	$.gekoWpFormManage = function( options ) {
		
		var opts = $.extend( {
			// ...
		}, options );
		
		
		if ( opts.unsupported_browser ) {
			alert( 'Sorry, "' + opts.unsupported_browser + '" is currently not a supported browser. Please use a different browser.' );
		}
				
		
		////// standard functions
		
		var dialogResetError = function( dialog ) {
			dialog.find( 'input, textarea, select, label' ).removeClass( 'error' );		
		}
		
		var dialogValidate = function( dialog ) {
			dialogResetError( dialog );
			var errorMsg = dialog.data( 'error_msg' );
			if ( errorMsg && errorMsg.length ) {
				var msg = '';
				$.each( errorMsg, function( i, v ) {
					dialog.find( '#' + v[ 1 ] + ', label[for="' + v[ 1 ] + '"]' ).addClass( 'error' );
					if ( msg ) msg += "\n";
					msg += v[ 0 ];
				} );
				alert( msg );
			} else {
				dialog.dialog( 'close' );		
			}		
		}
		
		var dialogAddButton = function() {
			var dialog = $( this );
			dialog.trigger( 'add' );
			dialogValidate( dialog );
		};
		
		var dialogSaveButton = function() {
			var dialog = $( this );
			dialog.trigger( 'save' );
			dialogValidate( dialog );
		};
		
		var dialogMoveButton = function() {
			var dialog = $( this );
			dialog.trigger( 'move' );
			dialog.dialog( 'close' );
		};
		
		var dialogCancelButton =  function() {
			var dialog = $( this );
			dialog.dialog( 'close' );
		};
		
		var dialogClose = function() {
			var dialog = $( this );
			dialogResetError( dialog );		
		}
		
		var tabAdd = function( event, ui ) {
			
			var tabs = $( this );
			var panel = $( ui.panel );
			
			var content = tabs.tabs( 'option', 'tabContent' );
			
			tabs.trigger( 'tab_panel', [ panel, content ] );
			
			panel.data( 'content', content );
			
			tabs.tabs( 'option', 'tabContent', null );	// reset
		};
				
		var setupLangTabs = function( tab, context ) {
			
			// get default fieldset contents
			var mvh = aMetaContexts[ context ];
			
			var context_id = mvh.context_id;
			var tab_pfx = mvh.tab_pfx;
			
			var defFieldsHtml = tab.find( 'fieldset' ).gekoGetAsHtml( { content: true } );		
			var langPanelHtml = tab.find( '.ui-tabs-panel-template' ).gekoGetAsHtml( { inner: true } );
			var langTabHtml = tab.find( '.ui-tab-template' ).gekoGetAsHtml();
			
			tab.tabs( {
				tabTemplate: langTabHtml,
				add: tabAdd
			} );
			
			tab.bind( 'tab_panel', function( evt, panel, content ) {
				
				panel.append( langPanelHtml );
				
				if ( parseInt( content.is_default ) ) {
					panel.find( 'fieldset' ).html( defFieldsHtml );
				}
				
				var lang_id = panel.attr( 'id' ).replace( tab_pfx + '-', '' );
				
				$.each( aMetaData, function( i, metadata ) {
					if (
						( context_id == parseInt( metadata.context_id ) ) && 
						( metadata.lang_id == lang_id )
					) {
						
						var typh = aTypeHandlers[ metadata.item_type ];
						
						panel.find( 'fieldset' ).append( typh.getField(
							metadata.name, 'meta_data_' + metadata.slug, null, null, metadata.fmmv
						) );
						
					}
				} )
				
			} );
			
		};
		
		var showErrorMsg = function( msg ) {
			
			window.location = '#heading_top';

			var curError = editForm.parent().find( '#notice' );
			
			var showMsg = function() {

				var elem = $( '<div class="error below-h2" id="notice"><p>' + msg + '<\/p><\/div>' );
				elem.hide();
				
				editForm.before( elem );
				elem.fadeIn( iAnimDelay );

				if ( curError.length ) {
					curError.remove();
				}
			}
			
			if ( curError.length ) {
				curError.fadeOut( iAnimDelay, showMsg );
			} else {
				showMsg();
			}
			
		};
		
		var setDlgParent = function( dialog, pfx ) {
			
			var dlgParent = dialog.parent();
			
			dlgParent.find( '.ui-dialog-buttonpane .ui-button' ).each( function() {
				var name = $( this ).find( 'span' ).html().convertToSlug();
				$( this ).attr( 'id', pfx + name );
			} );
			
			return dlgParent;
		};
		
		
		
		
		////// handlers
		
		// item types
		
		var selectGetField = function( field, label, name, id, value, params ) {
			var select = field.find( 'select' );
			params = ( params ) ? params : [] ;
			$.each( params, function( i, v ) {
				var option = $( '<option><\/option>' );
				option.attr( 'value', v.slug );
				option.html( v.label );
				option.setFormElemVal( v.is_default );
				select.append( option );
			} );
			return field;
		};
		
		var radioCheckboxMultiSetFieldValue = function( elem, slug, prefix, value, params ) {
			value = arrayWrap( value );
			$.each( params, function( i, v ) {
				var input = elem.find( '#' + prefix + slug + '-' + v.slug );
				if ( -1 != $.inArray( input.val(), value ) ) {
					input.attr( 'checked', 'checked' );
				} else {
					input.removeAttr( 'checked' );						
				}
			} );
			return elem;
		};
		
		var radioCheckboxMultiGetFieldValue = function( elem, slug, prefix, params ) {
			var value = [];
			$.each( params, function( i, v ) {
				var input = elem.find( '#' + prefix + slug + '-' + v.slug );
				if ( input.is( ':checked' ) ) value.push( input.val() );
			} );
			return value;
		};
		
		var aTypeHandlers = {
			text: new ItemType( {
				type: 'text'
			} ),
			textarea: new ItemType( {
				type: 'textarea'			
			} ),
			radio: new ItemType( {
				type: 'radio',
				getField: function( field, label, name, id, value, params ) {
					var rowHtml = field.find( 'div.row' ).gekoGetAsHtml();
					params = ( params ) ? params : [] ;
					$.each( params, function( i, v ) {
						
						var row = $( rowHtml );
						row.removeClass( 'row' );
						
						var label = row.find( 'label.sub' );
						label.html( v.label );
						label.attr( 'for', id + '-' + v.slug );
						
						var input = row.find( 'input' );
						input.val( v.slug );
						input.attr( 'name', name );
						input.attr( 'id', id + '-' + v.slug );
						input.setFormElemVal( v.is_default );
						
						field.find( '.multiple' ).append( row );
						
					} );
					return field;
				},
				setFieldValue: radioCheckboxMultiSetFieldValue,
				getFieldValue: radioCheckboxMultiGetFieldValue
			} ),
			checkbox: new ItemType( {
				type: 'checkbox',
				getFieldValue: function( elem, slug, prefix, params ) {
					var input = elem.find( '#' + prefix + slug );
					return ( input.is( ':checked' ) ) ? 1 : 0;
				}
			} ),
			checkbox_multi: new ItemType( {
				type: 'checkbox_multi',
				getField: function( field, label, name, id, value, params ) {
					var rowHtml = field.find( 'div.row' ).gekoGetAsHtml();
					params = ( params ) ? params : [] ;
					$.each( params, function( i, v ) {
					
						var row = $( rowHtml );
						row.removeClass( 'row' );
						
						var label = row.find( 'label.sub' );
						label.html( v.label );
						label.attr( 'for', id + '-' + v.slug );
						
						var input = row.find( 'input' );
						input.val( v.slug );
						input.attr( 'name', name );
						input.attr( 'id', id + '-' + v.slug );
						input.setFormElemVal( v.is_default );
						
						field.find( '.multiple' ).append( row );
						
					} );
					return field;
				},
				setFieldValue: radioCheckboxMultiSetFieldValue,
				getFieldValue: radioCheckboxMultiGetFieldValue
			} ),
			select: new ItemType( {
				type: 'select',
				getField: selectGetField
			} ),
			select_multi: new ItemType( {
				type: 'select_multi',
				getField: selectGetField,
				setFieldValue: function( elem, slug, prefix, value, params ) {
					value = arrayWrap( value );
					$.each( params, function( i, v ) {
						var option = elem.find( '#' + prefix + slug + ' option[value="' + v.slug + '"]' );
						if ( -1 != $.inArray( option.val(), value ) ) {
							option.attr( 'selected', 'selected' );
						} else {
							option.removeAttr( 'selected' );						
						}
					} );
					return elem;
				}
			} )
		};
		
		// meta values
		
		var aMetaContextHash = {};
		var aMetaContexts = {
			question: new ItemMetaContext( {
				context_id: 0,
				tab_pfx: 'itemlang',
				hash: aMetaContextHash,
				load: function( fmitmmv ) {
					
					var fmitm_id = fmitmmv.fmitm_id;
					if ( !this.values[ fmitm_id ] ) this.values[ fmitm_id ] = [];
					this.values[ fmitm_id ].push( fmitmmv );
					
				},
				unload: function( item ) {
					
					var fmitm_id = item.fmitm_id;
					if ( this.values[ fmitm_id ] ) {
						item.fmitmmv = this.values[ fmitm_id ];
					} else {
						item.fmitmmv = [];
					}
					
					return item;
				}
			} ),
			choice: new ItemMetaContext( {
				context_id: 1,
				tab_pfx: 'itmvallang',
				hash: aMetaContextHash,
				load: function( fmitmmv ) {
					
					var fmitm_id = fmitmmv.fmitm_id;
					var fmitmval_idx = fmitmmv.fmitmval_idx;
					var key = fmitm_id + ':' + fmitmval_idx;
					
					if ( !this.values[ key ] ) this.values[ key ] = [];
					this.values[ key ].push( fmitmmv );
					
				},
				unload: function( fmitmval ) {
					
					var fmitm_id = fmitmval.fmitm_id;
					var fmitmval_idx = fmitmval.fmitmval_idx;
					var key = fmitm_id + ':' + fmitmval_idx;
					
					if ( this.values[ key ] ) {
						fmitmval.fmitmmv = this.values[ key ];
					} else {
						fmitmval.fmitmmv = [];
					}
					
					return fmitmval;
				}
			} ),
			section: new ItemMetaContext( {
				context_id: 2,
				tab_pfx: 'seclang',
				hash: aMetaContextHash,
				load: function( fmitmmv ) {
					
					var fmsec_id = fmitmmv.fmsec_id;
					if ( !this.values[ fmsec_id ] ) this.values[ fmsec_id ] = [];
					this.values[ fmsec_id ].push( fmitmmv );
					
				},
				unload: function( section ) {
					
					var fmsec_id = section.fmsec_id;
					if ( this.values[ fmsec_id ] ) {
						section.fmitmmv = this.values[ fmsec_id ];
					} else {
						section.fmitmmv = [];
					}
					
					return section;
				}
			} )
		};
		
		// HACKISH!!!
		ContentHelper.meta_context_hash = aMetaContextHash;
		ContentHelper.meta_contexts = aMetaContexts;
		ContentHelper.type_handlers = aTypeHandlers;
		
		
		
		////// styling hacks
		
		var setSizes = function() {
			$( '.geko-form-item .label, .geko-form-value .label' ).each( function() {
				var span = $( this );
				var li = span.closest( 'li' ); 
				li.load( function() {
					var liWdt = li.innerWidth();
					var icon = li.find( '.geko-form-icon' );
					var iconWdt = icon.outerWidth();
					if ( li.hasClass( 'geko-form-value' ) ) {
						// one icon
						span.width( liWdt - ( ( iconWdt + 12 ) * 1 ) );			
					} else {
						// three icons
						span.width( liWdt - ( ( iconWdt + 12 ) * 3 ) );
					}
				} );
			} );
		};
		
		
		
		
		
		////// load database values
		
		var aLangs = ( opts.langs );
		var aTypes = opts.values.fmitmtyp;
		var aSections = opts.values.fmsec;
		var aItems = ( opts.values.fmitm ) ? opts.values.fmitm : [];
		var aItemVals = ( opts.values.fmitmval ) ? opts.values.fmitmval : [];
		var aMetaData = ( opts.values.fmmd ) ? opts.values.fmmd : [];
		var aMetaVals = ( opts.values.fmmv ) ? opts.values.fmmv : [];
		var aItemMetaVals = ( opts.values.fmitmmv ) ? opts.values.fmitmmv : [];


		////// initialize elements
		
		var secTabs = $( '#form_editor' );
		
		var addIconHtml = secTabs.find( '.ui-tabs-panel-header a.geko-form-add-item' ).gekoGetAsHtml();
		var formValueHtml = secTabs.find( '.geko-form-values-main .geko-form-value' ).gekoGetAsHtml();
		var formValueMainHtml = secTabs.find( '.geko-form-values-main' ).gekoGetAsHtml();
		var formItemHtml = secTabs.find( '.geko-form-item' ).gekoGetAsHtml();
		var formItemsHtml = secTabs.find( '.geko-form-items' ).gekoGetAsHtml();
		var secPanelHtml = secTabs.find( '.ui-tabs-panel-template' ).gekoGetAsHtml( { inner: true } );
		var sectionTabHtml = secTabs.find( '.ui-tab-template' ).gekoGetAsHtml();
		
		var metaLangTabs = $( '#meta_data_editor' );
		
		var addMetaIconHtml = metaLangTabs.find( '.ui-tabs-panel-header a.geko-form-add-item' ).gekoGetAsHtml();
		var metaItemHtml = metaLangTabs.find( '.geko-form-item' ).gekoGetAsHtml();
		var metaItemsHtml = metaLangTabs.find( '.geko-form-items' ).gekoGetAsHtml();
		var metaPanelHtml = metaLangTabs.find( '.ui-tabs-panel-template' ).gekoGetAsHtml( { inner: true } );
		var metaLangTabHtml = metaLangTabs.find( '.ui-tab-template' ).gekoGetAsHtml();
		
		var fieldTemplates = $( '#dialog_field_templates' );


		
		
		
		
		
		
		
		
		////// group and assign

		// group item meta values into contexts
		$.each( aItemMetaVals, function( i, fmitmmv ) {
			aMetaContextHash[ fmitmmv.context_id ].load( fmitmmv );
		} );
		
		// bind database type values to the hard-coded stuff
		$.each( aTypes, function( i, dbType ) {
			var typh = aTypeHandlers[ dbType.slug ];
			typh.init( dbType, fieldTemplates );
		} );
		
		
		// assign section meta values
		$.each( aSections, function( i, section ) {
			aSections[ i ] = aMetaContexts[ 'section' ].unload( section );
		} );

		
		// group item values
		var oItemValsFmt = {};
		$.each( aItemVals, function( i, itemval ) {
			itemval = aMetaContexts[ 'choice' ].unload( itemval );
			var fmitm_id = itemval.fmitm_id;
			if ( !oItemValsFmt[ fmitm_id ] ) oItemValsFmt[ fmitm_id ] = [];
			oItemValsFmt[ fmitm_id ].push( itemval );
		} );
		
		// assign item values to items
		$.each( aItems, function( i, item ) {
			
			var fmitm_id = item.fmitm_id;
			aItems[ i ].fmitmval = ( oItemValsFmt[ fmitm_id ] ) ? oItemValsFmt[ fmitm_id ] : [] ;
			
			// assign question meta values while we're at it
			aItems[ i ] = aMetaContexts[ 'question' ].unload( item );
			
		} );
		
		// group meta values
		var oMetaValsFmt = {};
		$.each( aMetaVals, function( i, metaval ) {
			var fmmd_id = metaval.fmmd_id;
			if ( !oMetaValsFmt[ fmmd_id ] ) oMetaValsFmt[ fmmd_id ] = [];
			oMetaValsFmt[ fmmd_id ].push( metaval );
		} );
		
		// assign meta values to meta data
		$.each( aMetaData, function( i, metadata ) {
			
			var fmmd_id = metadata.fmmd_id;
			aMetaData[ i ].fmmv = ( oMetaValsFmt[ fmmd_id ] ) ? oMetaValsFmt[ fmmd_id ] : [] ;
			
			// assign meta data to context handler while we're at it
			aMetaContextHash[ metadata.context_id ].loadmeta( metadata );
			
		} );
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		////// data submission
		
		var editForm = $( '#editform' );
		editForm.find( '#fmitmmv' ).data( 'content', [] );
		
		
		
		
		
		////// section stuff
		
		// dialog
		
		var secDlg = $( '#edit_form_section' );
				
		secDlg.dialog( {
			autoOpen: false,
			modal: true,
			close: dialogClose,
			buttons: {
				Add: dialogAddButton,
				Save: dialogSaveButton,
				Cancel: dialogCancelButton
			},
			width: iDialogWidth
		} );
		
		var secDlgParent = setDlgParent( secDlg, 'section-' );
		
		
		var secDlgLangTabs = secDlg.find( '#edit_form_section_lang' );
		setupLangTabs( secDlgLangTabs, 'section' );
		
		
		
		
		// tabs
		
		secTabs.tabs( {
			tabTemplate: sectionTabHtml,
			add: tabAdd
		} );
		
		secTabs.bind( 'tab_panel', function( evt, panel, content ) {
			
			panel.append( secPanelHtml );
			panel.find( '.ui-tabs-panel-header div.description' ).html( content.description );
			
			var headerIcons = panel.find( '.ui-tabs-panel-header div.icons' );
							
			//// edit and delete buttons
			
			var edit = headerIcons.find( 'a.geko-form-edit-section' );
			edit.click( function() {
				
				content.loadFormValues( secDlg );
				content.loadMetaValues( secDlg, 'section' );
				
				secDlgParent.find( '#section-save' ).show();
				secDlgParent.find( '#section-add' ).hide();
				
				secDlgLangTabs.tabs( { active: 0 } );
				
				secDlg.dialog( { title: 'Edit Section' } );
				secDlg.data( 'content', content );
				secDlg.data( 'panel', panel );
				secDlg.dialog( 'open' );
				
				return false;
			} );
			
			var del = headerIcons.find( 'a.geko-form-remove-section' );
			del.click( function() {
				removeSection( content );
				return false;
			} );
			
		} );
		
		
		
		// add
		
		var oSecContHlp = {
			prefix: 'section_',
			fields: [ 'title', 'slug', 'description' ]
		};
		
		//
		var sectionCounter = 1;
		var addSection = function( content ) {
			
			// wrap content
			content = new ContentHelper( oSecContHlp, content );
			
			var sectionId = '';

			if ( !content.fmsec_id ) {
				// issue new pseudo id
				content.fmsec_id = '_' + sectionCounter;
				sectionCounter++;
			}
			
			content.tab_id = 'section-' + content.fmsec_id;
			
			secTabs.tabs( 'option', 'tabContent', content );
			secTabs.tabs( 'add', '#' + content.tab_id, content.title );
			
		}
		
		var secValidate = function( dialog ) {
			
			var errorMsg = [];

			var pfx = oSecContHlp.prefix;
			
			var title = $.trim( dialog.find( '#' + pfx + 'title' ).val() );
			var slug = $.trim( dialog.find( '#' + pfx + 'slug' ).val() );
			
			// gather existing slugs
			
			var panel = dialog.data( 'panel' );
			var panelId = null;
			if ( panel ) panelId = panel.attr( 'id' );
			
			var slugs = [];
			editForm.find( '.ui-tabs-panel' ).each( function() {
				var comparePanel = $( this );
				if ( !panelId || ( panelId && ( comparePanel.attr( 'id' ) != panelId ) ) ) {
					var compareCont = comparePanel.data( 'content' );
					if ( compareCont.slug ) {
						slugs.push( compareCont.slug.toLowerCase() );
					}
				}
			} );
			
			// do checks
			if ( !title ) {
				errorMsg.push( [ 'Title cannot be blank.', pfx + 'title' ] );
			}
			
			if ( !slug ) {
				errorMsg.push( [ 'Code cannot be blank.', pfx + 'slug' ] );
			} else {
				if ( -1 != $.inArray( slug.toLowerCase(), slugs ) ) {
					errorMsg.push( [ 'Code already taken, please use a different one.', pfx + 'slug' ] );
				}
			}
			
			dialog.data( 'error_msg', errorMsg );
			
			// return true if there are errors
			return ( errorMsg.length ) ? true : false ;
		};
		
		// add item
		secDlg.bind( 'add', function( evt ) {
			
			var dialog = $( this );
			
			if ( !secValidate( dialog ) ) {
				
				var content = dialog.data( 'content' );
				
				content.unloadFormValues( dialog );
				content.unloadMetaValues( dialog, 'section' );
			
				addSection( content.getRawContent() );
			}
		} );
		
		
		// edit item
		secDlg.bind( 'save', function() {
			
			var dialog = $( this );
			
			if ( !secValidate( dialog ) ) {
				
				var content = dialog.data( 'content' );
				
				content.unloadFormValues( dialog );
				content.unloadMetaValues( dialog, 'section' );
			
				secTabs.find( 'a[href="#' + content.tab_id + '"]' ).html( content.title );
				secTabs.find( '#' + content.tab_id + ' .ui-tabs-panel-header div.description' ).html( content.description );
			}
		} );

		
		// remove
		var removeSection = function( section ) {
			
			var tp = secTabs.find( '#' + section.tab_id );
			
			if ( tp.find( '.geko-form-item' ).length ) {
				alert( 'There are items associated with this section. Please remove the items, or move them to a different section first.' );
				return false;
			}
			
			if ( confirm( 'Are you sure you want to remove this section?' ) ) {
				
				// section.tab_id
				tp.fadeOut( iAnimDelay );
				secTabs.find( 'a[href="#' + section.tab_id + '"]' ).parent().fadeOut( iAnimDelay, function() {
					$( this ).remove();
					tp.remove();
				} );
				
				// tabs.tabs( 'refresh' );
				
				secTabs.find( 'a.ui-tab' )[ 0 ].click();
				
			}
			
		}
		
		// make sortable
		secTabs.find( '.ui-tabs-nav' ).sortable( {
			axis: 'x',
			cancel: '.ui-not-sortable'
		} );
		
		// open add tab dialog
		secTabs.find( '.add_menu' ).click( function() {
			
			secDlg.find( '#section_title' ).val( '' );
			secDlg.find( '#section_description' ).val( '' );
			
			secDlgParent.find( '#section-add' ).show();
			secDlgParent.find( '#section-save' ).hide();
			
			secDlgLangTabs.tabs( { active: 0 } );
			
			var content = new ContentHelper( oSecContHlp );
			content.loadFormValues( secDlg );
			content.loadMetaValues( secDlg, 'section' );
			
			secDlg.dialog( { title: 'Add Section' } );
			secDlg.data( 'content', content );
			secDlg.dialog( 'open' );
			
		} );
		
		// serialize and submit
		editForm.submit( function( evt ) {
				
			var form = $( this );
			var oSections = {};
			var secRank = 0;
			
			var errorMsg = null;		// TO DO!!!!!!!!!
			
			// iterate through tabs to maintain rank
			secTabs.find( 'a.ui-tab' ).each( function() {
				
				var a = $( this );
				var panelId = a.attr( 'href' );
				
				var panel = secTabs.find( panelId );
				var content = panel.data( 'content' );
				
				var oSection = {
					rank: secRank
				};
				
				form.trigger( 'serialize_section', [ content ] );
				
				oSections[ content.fmsec_id ] = content.loadSerialize( oSection );
				secRank++;
								
			} );
			
			form.find( '#fmsec' ).val( $.toJSON( oSections ) );
			
			// TO DO!!!!!!!!!
			// errorMsg = 'This is a test...';
			if ( errorMsg ) {
				
				showErrorMsg( errorMsg );
				
				evt.stopPropagation();
				return false;
			}
			
		} );
		
		
		
				
		
		
		////// item stuff
		
		secTabs.bind( 'tab_panel', function( evt, panel, content ) {
				
			var panelBody = panel.find( '.ui-tabs-panel-body' );
			
			var fis = $( formItemsHtml );
			
			var aSubItems = [];
			
			// go through items
			$.each( aItems, function( i, item ) {
				if ( content.fmsec_id == item.fmsec_id ) {
					if (
						parseInt( item.parent_itmvalidx_id ) && 
						parseInt( item.parent_itm_id )
					) {
						// track the sub items
						aSubItems.push( item );
					} else {
						fis.append( addFormItem( item ) );
					}
				}
			} );
			
			panelBody.append( fis );
			
			// go through sub-items
			var appendSubItems = function( sitms ) {
				if ( sitms.length > 0 ) {
					var sitms2 = [];
					$.each( sitms, function( i, item ) {
		
						var id = item.parent_itm_id + '_' + item.parent_itmvalidx_id;
						var sel = '#itemval-' + id + ' > .geko-form-sub-items > .geko-form-items';
						
						var parItm = panelBody.find( sel );
						if ( 1 == parItm.length ) {
							parItm.append( addFormItem( item ) );
						} else {
							sitms2.push( item );
						}
						
					} );
					appendSubItems( sitms2 );
				}
			};
			
			appendSubItems( aSubItems );
			
			panel.find( '.geko-form-items' ).sortable();
			
		} );
		
		
		
		// edit item dialog
		
		var itemDlg = $( '#edit_form_item' );
		
		itemDlg.dialog( {
			autoOpen: false,
			modal: true,
			close: dialogClose,
			buttons: {
				Add: dialogAddButton,
				Save: dialogSaveButton,
				Cancel: dialogCancelButton
			},
			width: 600
		} );
		
		var itemDlgParent = setDlgParent( itemDlg, 'item-' );
		
		
		var itemDlgLangTabs = itemDlg.find( '#edit_form_item_lang' );
		setupLangTabs( itemDlgLangTabs, 'question' );
		
		
		
		
		// move item dialog

		var mvItemDlg = $( '#move_form_item' );
		
		mvItemDlg.dialog( {
			autoOpen: false,
			modal: true,
			close: dialogClose,
			buttons: {
				Move: dialogMoveButton,
				Cancel: dialogCancelButton
			},
			width: iDialogWidth
		} );
		
		
		var oItemContHlp = {
			prefix: 'item_',
			fields: [ 'title', 'slug', 'help', 'css', 'validation' ],
			serialize_fields: [ 'fmitmtyp_id', 'parent_itm_id', 'parent_itmvalidx_id' ]
		};
		
		var oItemValContHlp = {
			prefix: 'itemval_',
			fields: [ 'label', 'slug', 'help', 'is_default' ],
			serialize_fields: [ 'fmitm_id', 'fmitmval_idx' ]		
		}
		
		// add form item
		var setFormItemId = function( content ) {
			
			if ( typeof setFormItemId.counter == 'undefined' ) {
				setFormItemId.counter = 1;
			}
			
			if ( !content.fmitm_id ) {
				// provide new pseudo id
				content.fmitm_id = '_' + setFormItemId.counter;
				setFormItemId.counter++;
			}
			
			return content;
		};
		
		var addFormItem = function( content ) {
			
			// wrap content
			content = new ContentHelper( oItemContHlp, content );
			
			// item value content helper
			var itmvalhlp = $.extend( {
				new_values_cb: function() {
					this.fmitm_id = content.fmitm_id;
					this.fmitmval_idx = this._newIndex;
					this.label = '';
					this.slug = '';
					this.help = '';
				}
			}, oItemValContHlp );
			
			var fi = $( formItemHtml );
			
			content = setFormItemId( content );
			
			content.li_id = 'item-' + content.fmitm_id;
			
			fi.attr( 'id', content.li_id );
			
			fi.find( '.label' ).html( content.title );
			fi.find( '.geko-form-icon' ).addClass( 'geko-form-icon-' + content.item_type );
			
			fi.find( '.geko-form-item-options' ).attr(
				'title', 'Options for "' + content.title + '"'
			).click( function() {
				
				var typh = aTypeHandlers[ content.item_type ];
				
				itemDlg.dialog( { title: 'Edit ' + typh.name + ' Item' } );
				
				//// options
				
				// tab button
				itemDlg.find( 'a[href="#item_options_tab"]' ).html( typh.name + ' Options' );
				
				// tab panel
				itemDlg.find( '#item_options_tab fieldset' ).html( '' ).append(
					typh.loadValueManager( content.fmitmval, itmvalhlp )
				);
				
				//// validation

				// tab panel
				itemDlg.find( '#item_validation_tab fieldset' ).html( '' ).append(
					typh.loadValidationManager( content )
				);
				
				//
				content.loadFormValues( itemDlg );
				content.loadMetaValues( itemDlg, 'question' );
				
				itemDlgParent.find( '#item-save' ).show();
				itemDlgParent.find( '#item-add' ).hide();
				
				itemDlgLangTabs.tabs( { active: 0 } );
				
				itemDlg.data( 'content', content );
				itemDlg.dialog( 'open' );
				
				return false;
				
			} );
			
			fi.find( '.geko-form-remove-item' ).click( function() {
				if ( confirm( 'Are you sure you want to remove this item?' ) ) {
					fi.fadeOut( iAnimDelay, function() {
						$( this ).remove();
					} );
				}
				return false;
			} );
			
			fi.find( '.geko-form-move-item' ).click( function() {
				
				var section = $( this ).closest( '.ui-tabs-panel' );
				
				var secSel = mvItemDlg.find( '#item_section' );
				secSel.html( '' );
				
				secTabs.find( 'a.ui-tab' ).each( function() {
					var a = $( this );
					if ( ( '#' + section.attr( 'id' ) ) != a.attr( 'href' ) ) {
						secSel.append( '<option value="' + a.attr( 'href' ).replace( '#', '' ) + '">' + a.html() + '</option>' );
					}
				} );
				
				mvItemDlg.data( 'content', content );
				mvItemDlg.dialog( 'open' );
				
				return false;
			} );
			
			var expCh = fi.find( '.geko-form-expand-choices' );
			
			fi.data( 'content', content );
			fi.data( 'item_value_helper', itmvalhlp );
			
			// initialize item values
			var typh = aTypeHandlers[ content.item_type ];
			
			if ( parseInt( typh.has_multiple_values ) ) {
				
				fi.append( formValueMainHtml );
				
				var fv_ul = fi.find( 'ul.geko-form-values' );
				
				fv_ul.bind( 'load', loadItemValues );
				fv_ul.bind( 'sync', syncItemValues );
				fv_ul.trigger( 'load' );
				
				fv_ul.sortable( {
					update: function() {
						$( this ).trigger( 'sync' );
					}
				} );
				
				// show/hide choices
				expCh.click( function() {
					var a = $( this );
					var span = a.find( 'span' );
					if ( span.hasClass( 'geko-form-icon-hide-items' ) ) {
						fv_ul.slideUp( iAnimDelay, function() {
							span.removeClass( 'geko-form-icon-hide-items' );
							span.addClass( 'geko-form-icon-show-items' );
						} );
					} else {
						fv_ul.slideDown( iAnimDelay, function() {
							span.addClass( 'geko-form-icon-hide-items' );
							span.removeClass( 'geko-form-icon-show-items' );
						} );
					}
					return false;
				} );
				
				// set-up
				if ( parseInt( content.hide_subs ) ) {
					expCh.find( 'span' ).addClass( 'geko-form-icon-show-items' );
					fv_ul.hide();
				} else {
					expCh.find( 'span' ).addClass( 'geko-form-icon-hide-items' );				
				}
				
			} else {
			
				// not needed for these types of questions
				expCh.hide();
			}
			
			return fi;
			
		}
		
		var itemValidate = function( dialog ) {

			var errorMsg = [];

			var pfx = oItemContHlp.prefix;

			var title = $.trim( dialog.find( '#' + pfx + 'title' ).val() );
			var slug = $.trim( dialog.find( '#' + pfx + 'slug' ).val() );
						
			var content = dialog.data( 'content' );
			
			var slugs = [];
			secTabs.find( 'li.geko-form-item' ).each( function() {
				var li = $( this );
				var compareCont = li.data( 'content' );
				if (
					( li.attr( 'id' ) != content.li_id ) && 
					( compareCont.slug )
				) {
					slugs.push( compareCont.slug.toLowerCase() );
				}
			} );
			
			// do checks
			if ( !title ) {
				errorMsg.push( [ 'Title cannot be blank.', pfx + 'title' ] );
			}
			
			if ( !slug ) {
				errorMsg.push( [ 'Code cannot be blank.', pfx + 'slug' ] );
			} else {
				if ( -1 != $.inArray( slug.toLowerCase(), slugs ) ) {
					errorMsg.push( [ 'Code already taken, please use a different one.', pfx + 'slug' ] );
				}
			}
			
			dialog.data( 'error_msg', errorMsg );
			
			// return true if there are errors
			return ( errorMsg.length ) ? true : false ;
		};
		
		
		// add item
		itemDlg.bind( 'add', function() {

			var dialog = $( this );
			
			if ( !itemValidate( dialog ) ) {
				
				var content = dialog.data( 'content' );
				content.unloadFormValues( dialog );
				content.unloadMetaValues( dialog, 'question' );
				
				var typh = aTypeHandlers[ content.item_type ];
				content.fmitmval = typh.unloadValueManager( dialog.find( '#item_options_tab' ) );
				content.validation = typh.unloadValidationManager( dialog.find( '#item_validation_tab' ) );
				
				var ai = dialog.data( 'ai' );
				var appendItemDiv = dialog.data( 'append_item_div' );
				
				var insertFormItem = function() {
					
					var fis = addFormItem( content );
					
					fis.hide();
					
					var sel = '';
					if ( appendItemDiv.hasClass( 'ui-tabs-panel' ) ) {
						sel = '> div > .geko-form-items';
					} else {
						sel = '> .geko-form-items';					
					}
					
					appendItemDiv.find( sel ).append( fis );
					fis.fadeIn( iAnimDelay );
					
					setSizes();
				
				}
				
				if ( content.parent_itm_id ) {
					var expItm = ai.closest( 'li' ).find( '> a.geko-form-expand-items' );
					if ( expItm.find( 'span' ).hasClass( 'geko-form-icon-show-items' ) ) {
						expItm.trigger( 'click', [ insertFormItem ] );
					} else {
						insertFormItem();			// call directly
					}
				} else {
					insertFormItem();			// call directly
				}
				
			}
			
		} );
		
		// edit item
		itemDlg.bind( 'save', function() {
			
			var dialog = $( this );
			
			if ( !itemValidate( dialog ) ) {
				
				var content = dialog.data( 'content' );
				content.unloadFormValues( dialog );
				content.unloadMetaValues( dialog, 'question' );
				
				var fi = secTabs.find( '#' + content.li_id );
				fi.find( 'span.label' ).first().html( content.title );
				
				var typh = aTypeHandlers[ content.item_type ];
				content.fmitmval = typh.unloadValueManager( dialog.find( '#item_options_tab' ) );
				content.validation = typh.unloadValidationManager( dialog.find( '#item_validation_tab' ) );
				
				var fv_ul = fi.find( '> .geko-form-values-main > ul.geko-form-values' );
				
				if ( fv_ul.length ) {
					fv_ul.trigger( 'load' );
				}
			}
		} );
		
		// move item
		mvItemDlg.bind( 'move', function() {
			
			var dialog = $( this );

			var content = dialog.data( 'content' );
			var itmSec = dialog.find( '#item_section' ).val();
			var items = secTabs.find( '#' + itmSec + ' .geko-form-items' );
			items.append( secTabs.find( '#' + content.li_id ) );
			
		} );
		
		// serialize and submit
		editForm.submit( function() {
				
			var form = $( this );
			var oItems = {};
			var itemRank = 0;
			
			secTabs.find( 'li.geko-form-item' ).each( function() {
				
				var li = $( this );
				var secDiv = li.closest( '.ui-tabs-panel' );

				var fmsec_id = secDiv.attr( 'id' ).replace( 'section-', '' );
				
				var content = li.data( 'content' );
				
				// show/hide subs
				var subFlag = li.find( '> .geko-form-expand-choices > span' );
				var hideSubs = subFlag.hasClass( 'geko-form-icon-show-items' ) ? 1 : 0 ;
				
				var oItem = {
					fmsec_id: fmsec_id,
					rank: itemRank,
					hide_subs: hideSubs
				}
				
				form.trigger( 'serialize_item', [ content ] );
				
				oItems[ content.fmitm_id ] = content.loadSerialize( oItem );
				itemRank++;
				
			} );
			
			form.find( '#fmitm' ).val( $.toJSON( oItems ) );
			
		} );
		
		
		
		
		
		
		////// item value stuff
		
		// edit item dialog
		
		var itemValDlg = $( '#edit_form_value' );
		
		itemValDlg.dialog( {
			autoOpen: false,
			modal: true,
			close: dialogClose,
			buttons: {
				Save: dialogSaveButton,
				Cancel: dialogCancelButton
			},
			width: 600
		} );
		
		var itemValDlgLangTabs = itemValDlg.find( '#edit_form_itemval_lang' );
		setupLangTabs( itemValDlgLangTabs, 'choice' );
		
		
		var addFormItemValue = function( content, oItemValContHlp, bHasSubs ) {
			
			// wrap content
			content = new ContentHelper( oItemValContHlp, content );
			
			var fv = $( formValueHtml );
						
			fv.find( '.geko-form-item-options' ).click( function() {
				
				var cont2 = fv.data( 'content' );
				
				itemValDlg.dialog( { title: 'Edit Choice' } );
				
				//
				cont2.loadFormValues( itemValDlg );
				cont2.loadMetaValues( itemValDlg, 'choice' );
				
				itemValDlg.data( 'content', cont2 );
				itemValDlg.dialog( 'open' );
				
				return false;
			} );
			
			
			//// widgets
			
			var expWdg = fv.find( '.geko-form-expand-widgets' );
			var widgets = fv.find( '.icons' );
			var fsub = fv.find( '.geko-form-sub-items' );
			
			if ( bHasSubs ) {
				
				fsub.append( formItemsHtml );
				
				addWidgets( widgets, fsub );
				
				// show or hide
				if ( !parseInt( content.show_widgets ) ) {
					widgets.hide();
					expWdg.find( 'span' ).addClass( 'geko-form-icon-show-widgets' );
				} else {
					expWdg.find( 'span' ).addClass( 'geko-form-icon-hide-widgets' );					
				}
				
				// show/hide widgets
				expWdg.click( function() {
					var a = $( this );
					var span = a.find( 'span' );
					if ( span.hasClass( 'geko-form-icon-hide-widgets' ) ) {
						widgets.effect( 'blind', { direction: 'right', mode: 'hide' }, iAnimDelay, function() {
							span.removeClass( 'geko-form-icon-hide-widgets' );
							span.addClass( 'geko-form-icon-show-widgets' );
						} );
					} else {
						widgets.effect( 'blind', { direction: 'right', mode: 'show' }, iAnimDelay, function() {
							span.addClass( 'geko-form-icon-hide-widgets' );
							span.removeClass( 'geko-form-icon-show-widgets' );
						} );
					}
					return false;
				} );
			
			} else {
				expWdg.hide();
				widgets.hide();
				fsub.hide();
			}
			
			//// items
								
			var expItm = fv.find( '.geko-form-expand-items' );

			if ( bHasSubs ) {

				// show or hide
				if ( parseInt( content.hide_items ) ) {
					fsub.hide();
					expItm.find( 'span' ).addClass( 'geko-form-icon-show-items' );
				} else {
					expItm.find( 'span' ).addClass( 'geko-form-icon-hide-items' );					
				}
				
				// show/hide items
				expItm.click( function( evt, expItmCb ) {
					var a = $( this );
					var span = a.find( 'span' );
					if ( span.hasClass( 'geko-form-icon-hide-items' ) ) {
						fsub.slideUp( iAnimDelay, function() {
							span.removeClass( 'geko-form-icon-hide-items' );
							span.addClass( 'geko-form-icon-show-items' );
						} );
					} else {
						fsub.slideDown( iAnimDelay, function() {
							span.addClass( 'geko-form-icon-hide-items' );
							span.removeClass( 'geko-form-icon-show-items' );
							if ( expItmCb ) expItmCb();		// invoke callback, if given
						} );
					}
					return false;
				} );
			
			} else {
				expItm.hide();
			}
			
			// set content
			fv.data( 'content', content );
			
			// set form value with given content
			fv.bind( 'refresh', function() {
				
				var cont2 = fv.data( 'content' );
				
				cont2.li_id = 'itemval-' + cont2.fmitm_id + '_' + cont2.fmitmval_idx;
				
				fv.attr( 'id', cont2.li_id );
				
				fv.find( '> span.label' ).html( cont2.label );
				
				fv.find( '> .geko-form-item-options' ).attr(
					'title', 'Options for "' + cont2.label + '"'
				);
			} );
			
			fv.trigger( 'refresh' );	// init
			
			return fv;
		}
		
		
		var loadItemValues = function() {
			
			var fv_ul = $( this );
			var fi = fv_ul.closest( 'li' );
			
			var parCont = fi.data( 'content' );
			var oItemValContHlp = fi.data( 'item_value_helper' );
			
			// store any existing nodes
			var tmpUl = $( '<ul><\/ul>' );
			
			if ( fv_ul.find( '> li' ).length ) {
				tmpUl.append( fv_ul.find( '> li' ) );
			}
						
			if ( parCont.fmitmval ) {

				//
				var typh = aTypeHandlers[ parCont.item_type ];
				var bHasSubs = parseInt( typh._dbType.has_choice_subs ) ? true : false ;
				
				$.each( parCont.fmitmval, function( i, content ) {
					
					// check for existing node
					var contChk = new ContentHelper( oItemValContHlp, content );
					var liId = 'itemval-' + contChk.fmitm_id + '_' + contChk.fmitmval_idx;
					
					var liChk = tmpUl.find( '#' + liId );
					
					if ( 1 == liChk.length ) {
						// update content for existing node
						liChk.data( 'content', contChk );
						liChk.trigger( 'refresh' );
						fv_ul.append( liChk );
					} else {
						// create new node
						var fv = addFormItemValue( content, oItemValContHlp, bHasSubs );
						fv_ul.append( fv );				
					}
					
				} );
			}
			
			tmpUl.remove();
		};
		
		var syncItemValues = function() {
			
			var fv_ul = $( this );
			var fi = fv_ul.closest( 'li' );
			
			var aVals = [];
			
			fv_ul.find( '> li.geko-form-value' ).each( function() {
				var li = $( this );
				var content = li.data( 'content' );
				aVals.push( content.getRawContent( true ) );
			} );
			
			var parCont = fi.data( 'content' );
			parCont.fmitmval = aVals;
			
		};
		
		var itemValValidate = function( dialog ) {

			var errorMsg = [];

			var pfx = oItemValContHlp.prefix;
			
			var label = $.trim( dialog.find( '#' + pfx + 'label' ).val() );
			var slug = $.trim( dialog.find( '#' + pfx + 'slug' ).val() );
			
			var content = dialog.data( 'content' );
			
			var slugs = [];
			var li = editForm.find( '#' + content.li_id );

			li.closest( '.geko-form-values' ).find( 'li.geko-form-value' ).each( function() {
				var compareli = $( this );
				var compareCont = compareli.data( 'content' );
				if (
					( compareli.attr( 'id' ) != content.li_id ) && 
					( compareCont.slug )
				) {
					slugs.push( compareCont.slug.toLowerCase() );
				}
			} );
			
			// do checks
			if ( !label ) {
				errorMsg.push( [ 'Label cannot be blank.', pfx + 'label' ] );
			}
			
			// slug can be left blank, but must otherwise be unique
			if ( slug && ( -1 != $.inArray( slug.toLowerCase(), slugs ) ) ) {
				errorMsg.push( [ 'Code already taken, please use a different one.', pfx + 'slug' ] );
			}
			
			dialog.data( 'error_msg', errorMsg );
			
			// return true if there are errors
			return ( errorMsg.length ) ? true : false ;
			
		};
		
		// edit item value
		itemValDlg.bind( 'save', function() {
			
			var dialog = $( this );
			
			if ( !itemValValidate( dialog ) ) {

				var content = dialog.data( 'content' );
				content.unloadFormValues( dialog );			
				content.unloadMetaValues( dialog, 'choice' );
				
				var fv = secTabs.find( '#' + content.li_id );
				fv.find( 'span.label' ).first().html( content.label );
				
				var fv_ul = fv.closest( 'ul' );
				fv_ul.trigger( 'sync' );
			}
			
		} );
		
		// serialize and submit
		editForm.submit( function() {
				
			var form = $( this );
			var oItemVals = {};
			
			secTabs.find( 'li.geko-form-item' ).each( function() {
				
				var li = $( this );
				
				var content = li.data( 'content' );
				
				var itemValRank = 0;
				$.each( content.fmitmval, function( i, itemval ) {
					
					form.trigger( 'serialize_item_value', [ itemval ] );
					
					var id = itemval.fmitm_id + ':' + itemval.fmitmval_idx;
					var i2 = itemval.fmitm_id + '_' + itemval.fmitmval_idx;
					
					var li2 = li.find( '#itemval-' + i2 );
					
					//// show/hide widgets
					var widgetsFlag = li2.find( '> .geko-form-expand-widgets > span' );
					var showWidgets = widgetsFlag.hasClass( 'geko-form-icon-hide-widgets' ) ? 1 : 0 ;
					
					itemval.show_widgets = showWidgets;
					
					//// show/hide items
					var itemsFlag = li2.find( '> .geko-form-expand-items > span' );
					var hideItems = itemsFlag.hasClass( 'geko-form-icon-show-items' ) ? 1 : 0 ;
					
					itemval.hide_items = hideItems;
					
					//// rank
					itemval.rank = itemValRank;
										
					oItemVals[ id ] = itemval;
					itemValRank++;
				} );
				
			} );
			
			form.find( '#fmitmval' ).val( $.toJSON( oItemVals ) );
			
		} );
		
		
		
		
		
		
		
		
		
		////// meta data stuff
		
		// tabs
		
		metaLangTabs.tabs( {
			tabTemplate: metaLangTabHtml,
			add: tabAdd
		} );
		
		metaLangTabs.bind( 'tab_panel', function( evt, panel, content ) {

			var tp = $( '<div>' + metaPanelHtml + '<\/div>' );
			panel.append( tp.html() );
			
			var panelBody = panel.find( '.ui-tabs-panel-body' );
			
			var mds = $( metaItemsHtml );
			
			$.each( aMetaData, function( i, metadata ) {
				if ( content.lang_id == metadata.lang_id ) {
					mds.append( addMetaData( metadata ) );
				}
			} );
			
			panelBody.append( mds );
			panel.find( '.geko-form-items' ).sortable();
			
		} );
		
		
		
		// edit meta data dialog
		
		var metaDataDlg = $( '#edit_meta_data' );
		
		var oMetaContext = {};
		metaDataDlg.find( '#meta_data_context_id option' ).each( function() {
			var opt = $( this );
			oMetaContext[ opt.val() ] = opt.html();
		} );
		
		metaDataDlg.dialog( {
			autoOpen: false,
			modal: true,
			close: dialogClose,
			buttons: {
				Add: dialogAddButton,
				Save: dialogSaveButton,
				Cancel: dialogCancelButton
			},
			width: iDialogWidth
		} );
		
		var metaDataDlgParent = setDlgParent( metaDataDlg, 'meta_data-' );
		
		
		
		// move meta data dialog
		
		var mvMetaDataDlg = $( '#move_meta_data' );
		
		mvMetaDataDlg.dialog( {
			autoOpen: false,
			modal: true,
			close: dialogClose,
			buttons: {
				Move: dialogMoveButton,
				Cancel: dialogCancelButton
			},
			width: iDialogWidth
		} );
		
		var oMetaDataContHlp = {
			prefix: 'meta_data_',
			fields: [ 'name', 'slug', 'context_id' ],
			serialize_fields: [ 'fmitmtyp_id' ]
		};
		
		var oMetaValContHlp = {
			fields: [ 'label', 'slug', 'is_default' ],
			serialize_fields: [ 'fmmd_id', 'fmmv_idx' ]		
		};
		
		// add meta data
		var metaDataCounter = 1;
		
		// sets context id too
		var setFormMetaDataId = function( content ) {
			
			if ( typeof setFormMetaDataId.counter == 'undefined' ) {
				setFormMetaDataId.counter = 1;
			}
			
			if ( !content.fmmd_id ) {
				// issue new pseudo id
				content.fmmd_id = '_' + setFormMetaDataId.counter;
				content.context_id = aMetaContexts.question.context_id;		// default
				setFormMetaDataId.counter++;
			}
			
			return content;
		};
		
		var addMetaData = function( content ) {
			
			// wrap content
			content = new ContentHelper( oMetaDataContHlp, content );
			
			content = setFormMetaDataId( content );
			content.li_id = 'metadata-' + content.fmmd_id;
			
			
			var md = $( metaItemHtml );
			
			md.attr( 'id', content.li_id );
			
			md.find( '.label' ).html( content.name + ' (' + oMetaContext[ content.context_id ] + ')' );
			md.find( '.geko-form-icon' ).addClass( 'geko-form-icon-' + content.item_type );
			
			md.find( '.geko-form-item-options' ).attr(
				'title', 'Options for "' + content.name + '"'
			).click( function() {
				
				content.loadFormValues( metaDataDlg );
				
				metaDataDlg.find( 'fieldset .manager' ).remove();
				
				var typh = aTypeHandlers[ content.item_type ];
				
				var metavalhlp = $.extend( {
					new_values_cb: function() {
						this.fmmd_id = content.fmmd_id;
						this.fmmv_idx = this._newIndex;
						this.label = '';
						this.slug = '';
					}				
				}, oMetaValContHlp );
				
				var mng = typh.loadValueManager( content.fmmv, metavalhlp );
				
				mng.addClass( 'manager' );
				
				metaDataDlg.find( 'fieldset' ).append( mng );
				
				metaDataDlg.dialog( { title: 'Edit ' + typh.name + ' Meta Data' } );
				
				metaDataDlgParent.find( '#meta_data-save' ).show();
				metaDataDlgParent.find( '#meta_data-add' ).hide();
				
				metaDataDlg.data( 'content', content );
				metaDataDlg.dialog( 'open' );
				
				return false;
				
			} );
			
			md.find( '.geko-form-remove-item' ).click( function() {
				if ( confirm( 'Are you sure you want to remove this meta data?' ) ) {
					md.fadeOut( iAnimDelay, function() {
						$( this ).remove();
					} );
				}
				return false;
			} );
			
			md.find( '.geko-form-move-item' ).click( function() {
				
				var language = $( this ).closest( '.ui-tabs-panel' );
				
				var langSel = mvMetaDataDlg.find( '#meta_data_language' );
				langSel.html( '' );
				
				metaLangTabs.find( 'a.ui-tab' ).each( function() {
					var a = $( this );
					if ( ( '#' + language.attr( 'id' ) ) != a.attr( 'href' ) ) {
						langSel.append( '<option value="' + a.attr( 'href' ).replace( '#', '' ) + '">' + a.html() + '</option>' );
					}
				} );
				
				mvMetaDataDlg.data( 'content', content );
				mvMetaDataDlg.dialog( 'open' );
				
				return false;
			} );
			
			md.data( 'content', content );
			
			return md;
		};
		
		var metaDataValidate = function( dialog ) {
			
			var errorMsg = [];

			var pfx = oMetaDataContHlp.prefix;

			var name = $.trim( dialog.find( '#' + pfx + 'name' ).val() );
			var slug = $.trim( dialog.find( '#' + pfx + 'slug' ).val() );
			var context_id = $.trim( dialog.find( '#' + pfx + 'context_id' ).val() );
			
			var content = dialog.data( 'content' );
			
			var slugs = [];
			var li = editForm.find( '#' + content.li_id );
			
			li.closest( '.geko-form-items' ).find( 'li.geko-form-item' ).each( function() {
				var compareli = $( this );
				var compareCont = compareli.data( 'content' );
				if (
					( compareli.attr( 'id' ) != content.li_id ) && 
					( compareCont.slug ) && 
					( compareCont.context_id == context_id )
				) {
					slugs.push( compareCont.slug.toLowerCase() );
				}
			} );
			
			
			// do checks
			if ( !name ) {
				errorMsg.push( [ 'Name cannot be blank.', pfx + 'name' ] );
			}
			
			if ( !slug ) {
				errorMsg.push( [ 'Code cannot be blank.', pfx + 'slug' ] );
			} else {
				if ( -1 != $.inArray( slug.toLowerCase(), slugs ) ) {
					errorMsg.push( [ 'Code already taken, please use a different one.', pfx + 'slug' ] );
				}
			}
			
			dialog.data( 'error_msg', errorMsg );
			
			// return true if there are errors
			return ( errorMsg.length ) ? true : false ;		
		};

		// add meta data
		metaDataDlg.bind( 'add', function() {
			
			var dialog = $( this );

			if ( !metaDataValidate( dialog ) ) {
				
				var content = dialog.data( 'content' );
				content.unloadFormValues( dialog );
				
				var typh = aTypeHandlers[ content.item_type ];
				content.fmmv = typh.unloadValueManager( dialog.find( 'fieldset .manager' ) );
				
				var panel = dialog.data( 'panel' );
				var md = addMetaData( content );
				
				md.hide();
				panel.find( '.geko-form-items' ).append( md );
				md.fadeIn( iAnimDelay );
				
			}
			
		} );
		
		// edit meta data
		metaDataDlg.bind( 'save', function() {
			
			var dialog = $( this );

			if ( !metaDataValidate( dialog ) ) {
				
				var content = dialog.data( 'content' );
				content.unloadFormValues( dialog );
				
				var typh = aTypeHandlers[ content.item_type ];
				content.fmmv = typh.unloadValueManager( dialog.find( 'fieldset .manager' ) );
				
				metaLangTabs.find( '#' + content.li_id + ' span.label' ).html(
					content.name + ' (' + oMetaContext[ content.context_id ] + ')'
				);
				
			}
			
		} );
		
		
		
		// move item

		mvMetaDataDlg.bind( 'move', function() {
			
			var dialog = $( this );
			
			var content = dialog.data( 'content' );
			var mdLang = dialog.find( '#meta_data_language' ).val();
			var mds = metaLangTabs.find( '#' + mdLang + ' .geko-form-items' );
			mds.append( metaLangTabs.find( '#' + content.li_id ) );
			
		} );
		
		

		editForm.submit( function() {
				
			var form = $( this );
			var oMetaData = {};
			var metaDataRank = 0;
			
			metaLangTabs.find( 'li.geko-form-item' ).each( function() {
				
				var li = $( this );
				var langDiv = li.closest( '.ui-tabs-panel' );
				
				var lang_id = langDiv.attr( 'id' ).replace( 'metalang-', '' );
				
				var content = li.data( 'content' );
				
				var oItem = {
					lang_id: lang_id,
					rank: metaDataRank
				}
				
				oMetaData[ content.fmmd_id ] = content.loadSerialize( oItem );
				metaDataRank++;
				
			} );

			form.find( '#fmmd' ).val( $.toJSON( oMetaData ) );
			
		} );
		
		
		
		
		
		// serialize and submit
		editForm.submit( function() {
			
			var form = $( this );
			var oItemVals = {};
			
			metaLangTabs.find( 'li.geko-form-item' ).each( function() {
				
				var li = $( this );
				
				var content = li.data( 'content' );
				
				var itemValRank = 0;
				$.each( content.fmmv, function( i, itemval ) {
					var id = itemval.fmmd_id + ':' + itemval.fmmv_idx;
					itemval.rank = itemValRank;
					oItemVals[ id ] = itemval;
					itemValRank++;
				} );
				
			} );
			
			form.find( '#fmmv' ).val( $.toJSON( oItemVals ) );
			
		} );

		
		
		
		
		
		////// item meta value stuff
		
		var serializeItemMetaVal = function( evt, item ) {
			
			var form = $( this );
			var field = form.find( '#fmitmmv' );
			
			var content = field.data( 'content' );
			
			if ( item.fmitmmv ) {
				$.each( item.fmitmmv, function( i, v ) {
					
					var meta = {
						context_id: v.context_id,
						fmitm_id: v.fmitm_id,
						fmitmval_idx: v.fmitmval_idx,
						fmsec_id: v.fmsec_id,
						lang_id: v.lang_id,
						slug: v.slug,
						value: v.value
					};
					
					content.push( meta );
				} );
			}
			
			field.data( 'content', content );
			
		};
		
		editForm.bind( 'serialize_section', serializeItemMetaVal );
		editForm.bind( 'serialize_item', serializeItemMetaVal );
		editForm.bind( 'serialize_item_value', serializeItemMetaVal );
		
		// serialize and submit
		editForm.submit( function() {
			
			var form = $( this );
			var field = form.find( '#fmitmmv' );

			var content = field.data( 'content' );
			
			form.find( '#fmitmmv' ).val( $.toJSON( content ) );
			
		} );
		
		
		
		
		
		
		
		
		
		////// item type stuff
		
		var addWidgets = function( headerIcons, appendItemDiv ) {
			
			$.each( aTypes, function( i, itemtype ) {
				var ai = $( addIconHtml );
				
				ai.find( 'span' ).addClass( 'geko-form-icon-' + itemtype.slug );
				ai.attr(
					'title', 'Add "' + itemtype.name + '" question.'
				).click( function() {
					
					
					var typh = aTypeHandlers[ itemtype.slug ];
					
					var content = {
						fmitmtyp_id: itemtype.fmitmtyp_id,
						item_type: itemtype.slug,
						fmitmval: []
					};
					
					var parItmVal = appendItemDiv.closest( '.geko-form-value' );
					
					if ( parItmVal.length ) {
						var itmval = parItmVal.data( 'content' );
						content.parent_itm_id = itmval.fmitm_id;
						content.parent_itmvalidx_id = itmval.fmitmval_idx;
					}
					
					
					
					// wrap content
					content = new ContentHelper( oItemContHlp, content );
					
					// item value content helper
					var itmvalhlp = $.extend( {
						new_values_cb: function() {
							this.fmitm_id = content.fmitm_id;
							this.fmitmval_idx = this._newIndex;
							this.label = '';
							this.slug = '';
							this.help = '';
						}					
					}, oItemValContHlp );
					
					content = setFormItemId( content );
					
					
					//// options
					
					itemDlg.dialog( { title: 'Add ' + itemtype.name + ' Item' } );
					
					// tab button
					itemDlg.find( 'a[href="#item_options_tab"]' ).html( itemtype.name + ' Options' );
									
					// tab panel
					itemDlg.find( '#item_options_tab fieldset' ).html( '' ).append(
						typh.loadValueManager( content.fmitmval, itmvalhlp )
					);
					
					//// validation
	
					// tab panel
					itemDlg.find( '#item_validation_tab fieldset' ).html( '' ).append(
						typh.loadValidationManager( content )
					);
					
					//
					content.loadFormValues( itemDlg );
					content.loadMetaValues( itemDlg, 'question' );
					
					itemDlgParent.find( '#item-add' ).show();
					itemDlgParent.find( '#item-save' ).hide();
					
					itemDlgLangTabs.tabs( { active: 0 } );
					
					itemDlg.data( 'ai', ai );
					itemDlg.data( 'append_item_div', appendItemDiv );
					
					itemDlg.data( 'content', content );
					
					itemDlg.dialog( 'open' );				
					
					return false;
					
				} );
				headerIcons.find( 'span.spacer' ).before( ai );
			} );
		};
		
		// add item buttons to section

		secTabs.bind( 'tab_panel', function( evt, panel, content ) {
			
			// add item buttons
			var headerIcons = panel.find( '.ui-tabs-panel-header div.icons' );
			addWidgets( headerIcons, panel );
			
		} );
		
		
		// add item buttons to meta
		
		metaLangTabs.bind( 'tab_panel', function( evt, panel, content ) {
			
			// add item buttons
			var headerIcons = panel.find( '.ui-tabs-panel-header div.icons' );
			
			$.each( aTypes, function( i, itemtype ) {
				var aim = $( addMetaIconHtml );
				
				aim.find( 'span' ).addClass( 'geko-form-icon-' + itemtype.slug );
				aim.attr(
					'title', 'Add "' + itemtype.name + '" meta data.'
				).click( function() {
					
					// reset
					metaDataDlg.find( 'fieldset .manager' ).remove();
					
					var typh = aTypeHandlers[ itemtype.slug ];
					
					var content = {
						fmitmtyp_id: itemtype.fmitmtyp_id,
						item_type: itemtype.slug,
						fmitmval: []
					};
					
					// wrap content
					content = new ContentHelper( oMetaDataContHlp, content );
					
					var metavalhlp = $.extend( {
						new_values_cb: function() {
							this.fmmd_id = content.fmmd_id;
							this.fmmv_idx = this._newIndex;
							this.label = '';
							this.slug = '';
						}
					}, oMetaValContHlp );
					
					content = setFormMetaDataId( content );
					
					
					//// options
					
					content.loadFormValues( metaDataDlg );
					
					var mng = typh.loadValueManager( content.fmmv, metavalhlp );
					
					mng.addClass( 'manager' );
					
					metaDataDlg.find( 'fieldset' ).append( mng );
					
					metaDataDlg.dialog( { title: 'Add ' + itemtype.name + ' Item' } );
					
					metaDataDlgParent.find( '#meta_data-add' ).show();
					metaDataDlgParent.find( '#meta_data-save' ).hide();
					
					metaDataDlg.data( 'panel', panel );
					metaDataDlg.data( 'content', content );
					
					metaDataDlg.dialog( 'open' );
					
					return false;
					
				} );
				headerIcons.append( aim );
			} );
			
		} );
		
		
		
		
		
		
		////// testing
		
		$( '#form_editor_test' ).click( function() {
			
			/* /
			var test = {
				section: serializeSectionData(),
				items: serializeItemData(),
				metadata: serializeMetaData()
			};
			alert( $.toJSON( test ) );
			/* */
			
			// alert( secTabs.find( 'a[href="#section-6"]' ).html() );
			
			/* /
			alert(
				'load: ' + $.toJSON( aItemVals ) + 
				"\n\n\n" + 
				'unload: ' + $.toJSON( serializeItemValueData() )
			);
			/* */
			
			// alert( $.toJSON( aMetaVals ) );
			// alert( $.toJSON( oMetaValsFmt ) );
			
		} );
		
		
		
				
		
		
		
		////// initialize
		
		$.each( aSections, function( i, content ) {
			addSection( content );
		} );
		
		$.each( aLangs, function( i, content ) {
			
			// section dialog language
			secDlgLangTabs.tabs( 'option', 'tabContent', content );
			secDlgLangTabs.tabs( 'add', '#seclang-' + content.lang_id, content.title );
			
			// item dialog language
			itemDlgLangTabs.tabs( 'option', 'tabContent', content );
			itemDlgLangTabs.tabs( 'add', '#itemlang-' + content.lang_id, content.title );

			// item value dialog language
			itemValDlgLangTabs.tabs( 'option', 'tabContent', content );
			itemValDlgLangTabs.tabs( 'add', '#itmvallang-' + content.lang_id, content.title );
			
			// meta data
			metaLangTabs.tabs( 'option', 'tabContent', content );
			metaLangTabs.tabs( 'add', '#metalang-' + content.lang_id, content.title );
			
		} );
		
		itemDlgLangTabs.tabs( 'option', 'tabContent', {} );
		itemDlgLangTabs.tabs( 'add', '#item_options_tab', 'Options' );
		
		itemDlgLangTabs.tabs( 'option', 'tabContent', {} );
		itemDlgLangTabs.tabs( 'add', '#item_validation_tab', 'Validation' );
		
		$( window ).resize( setSizes );
		setSizes();
		
		$( 'div.loading' ).hide();
		
		secTabs.fadeIn( iAnimDelay );
		metaLangTabs.fadeIn( iAnimDelay );
		
	};
	
	
	
} )( jQuery );
