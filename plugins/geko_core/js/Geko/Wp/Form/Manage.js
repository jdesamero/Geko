( function() {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	Geko.setNamespace( 'Wp.Form.Manage' );
	
	
	//// apply convenience methods collection instances
	
	var fSetConveniencMethods = function( oSharedData, oContexts, oItemsParted, oMetaDataParted, oMetaDataLangParted ) {
		
		
		//// context enumeration
		
		$.extend( oContexts, {
			
			getContextId: function( sContext ) {
				return this.findAndGet( 'value', { 'slug': 'geko-form-context-%s'.printf( sContext ) } );
			}
			
		} );
		
		
		//// items (questions), parted by section and by choice (for conditional questions)
		
		$.extend( oItemsParted, {
			
			assertColumns: [ 'parent_itmvalidx_id' ],
			
			// by section
			
			setSectionPart: function( iSecId ) {
				
				oMainCollection = oSharedData.items;
				
				return oMainCollection.createPart( 'items', this, {
					'fmsec_id': iSecId,
					'parent_itm_id': 0
				} );
			},
			
			getSectionPart: function( iSecId ) {
			
				return this.findAndGet( 'items', {
					'fmsec_id': iSecId
				} );
			},
			
			
			// by choice
			
			setChoicePart: function( iItemId, iChoiceIdx ) {
				
				oMainCollection = oSharedData.items;
				
				return oMainCollection.createPart( 'items', this, {
					'parent_itm_id': iItemId,
					'parent_itmvalidx_id': iChoiceIdx
				} );
			},
			
			getChoicePart: function( iItemId, iChoiceIdx ) {
			
				return this.findAndGet( 'items', {
					'parent_itm_id': iItemId,
					'parent_itmvalidx_id': iChoiceIdx
				} );
			}
			
		} );
		
		
		//// meta-data, parted by language
		
		$.extend( oMetaDataParted, {
			
			setPart: function( iLangId ) {
				
				oMainCollection = oSharedData.metaData;
				
				return oMainCollection.createPart( 'meta_data', this, {
					'lang_id': iLangId						
				} );
			},
			
			getPart: function( iLangId ) {
				
				return this.findAndGet( 'meta_data', {
					'lang_id': iLangId
				} );
			}
			
		} );
		
		
		//// meta-data, parted by language and context
		
		$.extend( oMetaDataLangParted, {
			
			setPart: function( iLangId, iContextId ) {
				
				oMainCollection = oSharedData.metaData;
				
				return oMainCollection.createPart( 'meta_data', this, {
					'lang_id': iLangId,
					'context_id': iContextId						
				} );
			},
				
			getPart: function( iLangId, iContextId ) {
				
				return this.findAndGet( 'meta_data', {
					'lang_id': iLangId,
					'context_id': iContextId
				} );
			}
			
		} );
		
		
	};
	
	
	
	//// implement Geko.Wp.Form.Manage.run()
	
	$.extend( Geko.Wp.Form.Manage, {
		
		iAnimDelay: 300,
		iDialogWidth: 500,
		
		//// helper functions, clean up later
		
		setDlgParent: function( dialog, pfx ) {
			
			var dlgParent = dialog.parent();
			
			dlgParent.find( '.ui-dialog-buttonpane .ui-button' ).each( function() {
				var name = $( this ).find( 'span' ).html().convertToSlug();
				$( this ).attr( 'id', '%s%s'.printf( pfx, name ) );
			} );
			
			return dlgParent;
		},
		
		
		run: function( oParams ) {
			
			
			var opts = $.extend( {
				// ...
			}, oParams );
			
			
			if ( opts.unsupported_browser ) {
				alert( 'Sorry, "%s" is currently not a supported browser. Please use a different browser.'.printf( opts.unsupported_browser ) );
			}
			
			
			////// load database values
			
			var aLangs = opts.values.langs;
			var aContexts = opts.values.contexts;
			var aTypes = opts.values.fmitmtyp;
			var aSections = opts.values.fmsec;
			var aItems = ( opts.values.fmitm ) ? opts.values.fmitm : [] ;
			var aItemVals = ( opts.values.fmitmval ) ? opts.values.fmitmval : [] ;
			var aMetaData = ( opts.values.fmmd ) ? opts.values.fmmd : [] ;
			var aMetaVals = ( opts.values.fmmv ) ? opts.values.fmmv : [] ;
			var aItemMetaVals = ( opts.values.fmitmmv ) ? opts.values.fmitmmv : [] ;
			
			
			
			
			
			////// populate
			
			
			//// model/collections
			
			// context (section, question, or choice) enumeration
			var oContexts = new Geko.Wp.Enumeration.Manage.Collection( aContexts );
			
			// item types (widgets)
			var oItemTypes = new Geko.Wp.Form.ItemType.Manage.Collection( aTypes );
			
			// languages
			var oLanguages = new Geko.Wp.Language.Manage.Collection( aLangs );
			
			// item values
			var oItemValues = new Geko.Wp.Form.ItemValue.Manage.Collection( aItemVals );
					
			// items
			var oItems = new Geko.Wp.Form.Item.Manage.Collection( aItems );
			
			// sections
			var oSections = new Geko.Wp.Form.Section.Manage.Collection( aSections );
			
			// meta-data
			var oMetaData = new Geko.Wp.Form.MetaData.Manage.Collection( aMetaData );
			
			// item meta values
			var oItemMetaVals = new Geko.Wp.Form.ItemMetaValue.Manage.Collection( aItemMetaVals );
			
			
			// track "parted" collections
			var oItemsParted = new Backstab.Collection();
			var oMetaDataParted = new Backstab.Collection();
			var oMetaDataLangParted = new Backstab.Collection();
			
						
			
			
			// item types
			
			// this is a pseudo-view that generates widgets
			var oWidgetFactory = new Geko.Wp.Form.ItemType.Manage.WidgetFactory( {
				el: $( '#dialog_field_templates' )
			} );
			
			
			
			
			//// set-up shared data as a hash
			
			var oSharedData = {
				contexts: oContexts,
				itemTypes: oItemTypes,
				languages: oLanguages,
				itemValues: oItemValues,
				items: oItems,
				sections: oSections,
				metaData: oMetaData,
				itemMetaVals: oItemMetaVals,
				itemsParted: oItemsParted,
				metaDataParted: oMetaDataParted,
				metaDataLangParted: oMetaDataLangParted,
				widgetFactory: oWidgetFactory
			};
			
			// set convenience methods
			
			fSetConveniencMethods( oSharedData, oContexts, oItemsParted, oMetaDataParted, oMetaDataLangParted );

			
			// primitives
			
			Geko.Wp.Form.ItemValue.Manage.setData( oSharedData );
			Geko.Wp.Form.ItemType.Manage.setData( oSharedData );
			Geko.Wp.Form.Item.Manage.setData( oSharedData );
			Geko.Wp.Form.MetaData.Manage.setData( oSharedData );
			Geko.Wp.Form.MetaData.Manage.Tabs.setData( oSharedData );
			Geko.Wp.Form.MetaData.Manage.LangField.setData( oSharedData );
			Geko.Wp.Form.Section.Manage.setData( oSharedData );
			
			
			
			
			//// views
			
			
			// meta data
			
			var oMetaDataDialog = new Geko.Wp.Form.MetaData.Manage.FormView( {
				el: $( '#edit_meta_data' )
			} );
			
			var oMetaDataMoveDialog = new Geko.Wp.Form.MetaData.Manage.MoveDialog( {
				el: $( '#move_meta_data' ),
				data: {
					metaDataDispatcher: oMetaDataDialog.localDispatcher,
				}
			} );
			
			var oMetaTabsView = new Geko.Wp.Form.MetaData.Manage.Tabs.ListView( {
				collection: oLanguages,
				el: $( '#meta_data_editor' )
			} );
			
			
			
			
			// item values
			
			var oItemValueDialog = new Geko.Wp.Form.ItemValue.Manage.FormView( {
				el: $( '#edit_form_value' )
			} );
			
			
			
			// items
			
			var oItemDialog = new Geko.Wp.Form.Item.Manage.FormView( {
				el: $( '#edit_form_item' )
			} );
			
			var oItemMoveDialog = new Geko.Wp.Form.Item.Manage.MoveDialog( {
				el: $( '#move_form_item' ),
				data: {
					itemDispatcher: oItemDialog.localDispatcher
				}
			} );
			
						
			
			
			// section
			
			var oSectionDialog = new Geko.Wp.Form.Section.Manage.FormView( {
				el: $( '#edit_form_section' )
			} );
			
			var oSectionsView = new Geko.Wp.Form.Section.Manage.ListView( {
				collection: oSections,
				el: $( '#form_editor' )
			} );
			
			
			
			
			//// do the reveal
			
			$( 'div.loading' ).hide();
			
			oSectionsView.render().trigger( 'reveal' );
			oMetaTabsView.render().trigger( 'reveal' );
			
			
			
			//// enable inspector
			
			var oInspector = new Backstab.Inspector( {
				data: {
					collectionHash: {
						'context': { title: 'Contexts', collection: oContexts },
						'language': { title: 'Languages', collection: oLanguages },
						'itemtype': { title: 'Item Types', collection: oItemTypes },
						'section': { title: 'Sections', collection: oSections },
						'itemval': { title: 'Item Values', collection: oItemValues },
						'item': { title: 'Items', collection: oItems },
						'metadata': { title: 'Meta Data Items', collection: oMetaData },
						'itemmetaval': { title: 'Item Meta Values', collection: oItemMetaVals },
						'itemsparted': { title: 'Items Parted', collection: oItemsParted },
						'metadataparted': { title: 'Meta Data Parted', collection: oMetaDataParted },
						'metadatalangparted': { title: 'Meta Data Language Parted', collection: oMetaDataLangParted }
					}
				}
			} );
			
			$( '#editform' ).after( oInspector.render().$el );
			
			
		}
		
	} );
	
	
	
} ).call( this );