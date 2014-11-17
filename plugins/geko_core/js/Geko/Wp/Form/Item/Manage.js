;( function ( $ ) {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	Geko.setNamespace( 'Wp.Form.Item.Manage' );
	
	
	
	//// main family
	
	Geko.Wp.Form.Item.Manage = Backstab.family( {
		
		name: 'item',
		
		unescapeTemplateSrc: true,
		enableLocalDispatcher: true,
		useElementPrefix: true,
		
		// Item
		model: {
			
			extend: {
				
				fields: Backstab.ModelFields[ 'form.item' ],
				
				// item type
				
				getItemType: function() {
					
					var oItemTypes = this.family.data.itemTypes;
					
					return oItemTypes.findWhere( {
						'fmitmtyp_id': this.get( 'fmitmtyp_id' )
					} );
				},
				
				getItemTypeSlug: function() {
					return this.getItemType().get( 'slug' );
				},

				getItemTypeName: function() {
					return this.getItemType().get( 'name' );
				},
				
				
				// item meta values
				
				getItemDataValues: function( oItemMetaVals, oMetaData ) {
					
					return oItemMetaVals.where( {
						'fmitm_id': this.get( 'fmitm_id' ),
						'context_id': oMetaData.get( 'context_id' ),
						'lang_id': oMetaData.get( 'lang_id' ),
						'slug': oMetaData.get( 'slug' )
					} );
				},
				
				setItemMetaValue: function( oItemMetaVal, oMetaData ) {
					
					oItemMetaVal.set( {
						'fmitm_id': this.get( 'fmitm_id' ),
						'context_id': oMetaData.get( 'context_id' ),
						'lang_id': oMetaData.get( 'lang_id' ),
						'slug': oMetaData.get( 'slug' )
					} );
				}
				
			}
			
		},
		
		// Item
		itemView: {
			
			params: {
				
				postInit: function() {
					
					var oListView = this.data.listView;
					
					var oModel = this.model;
					var eLi = this.$el;
					
					
					var oItemValues = this.family.data.itemValues;
					
					
					//// item values
					
					eItemValues = eLi.find( '.geko-form-values-main' );
					
					// retrieve corresponding item type (widget)
					var oItemType = oModel.getItemType();
					
					
					if ( oItemType.get( 'has_multiple_values' ) ) {
						
						var oItemValues = new Geko.Wp.Form.ItemValue.Manage.Collection( oItemValues.where( {
							'fmitm_id': oModel.get( 'fmitm_id' )
						} ) );
						
						var oItemValueListView = new Geko.Wp.Form.ItemValue.Manage.ListView( {
							collection: oItemValues,
							el: eItemValues,
							data: {
								item: oModel,
								itemValues: oItemValues,
								itemType: oItemType,
								itemDispatcher: oListView.localDispatcher,
								section: oListView.data.section
							}
						} );
						
					} else {
						eItemValues.remove();
						eLi.find( '> .geko-form-expand-choices > span' ).remove();
					}
					
				},
				
				updateElem: function() {
					
					var oModel = this.model;
					
					var eLi = this.$el;
					var eExpand = eLi.find( '> .geko-form-expand-choices > span' );
					
					
					eLi.find( '> span.label' ).html( oModel.get( 'title' ) );
					eLi.find( '> .geko-form-item-options > span' ).addClass( 'geko-form-icon-%s'.printf( oModel.getItemTypeSlug() ) );
					
					eExpand.ternaryClass( oModel.get( 'hide_subs' ), 'geko-form-icon-show-items', 'geko-form-icon-hide-items' );
					
				}
				
			},
			
			extend: {
				
				events: {
					'click > .geko-form-expand-choices': 'toggleSubs',
					'click > .geko-form-item-options': 'openEditItem',
					'click > .geko-form-remove-item':  'confirmRemove',
					'click > .geko-form-move-item': 'openMoveItem'
				},
				
				toggleSubs: function() {
					
					this.model.toggleValue( 'hide_subs' );
					
					return false;
				},
				
				openEditItem: function() {
					
					this.localDispatcher.trigger( 'openEditItem', this.model );
					
					return false;
				},
				
				confirmRemove: function( e ) {
			
					if ( confirm( 'Are you sure you want to remove this item?' ) ) {
						this.model.destroy();
					}
					
					return false;
				},
				
				openMoveItem: function() {
					
					this.localDispatcher.trigger( 'openMoveItem', this.model );
					
					return false;
				}
				
			}
			
		},
		
		// Item
		listView: {
			
			params: {
				
				itemTmplSelector: '.geko-form-item'
				
			},
			
			extend: {
				
				events: {
					'data.itemValue:change:hide_items this': 'toggleVisible'
				},
				
				createElement: function() {
					return $( '<ul class="geko-form-items"><\/ul>' );
				},
				
				toggleVisible: function( e, oModel, bValue ) {
					
					if ( bValue ) {
						this.$el.slideUp( Geko.Wp.Form.Manage.iAnimDelay );						
					} else {
						this.$el.slideDown( Geko.Wp.Form.Manage.iAnimDelay );
					}
				}
				
			}
			
		},
		
		// Item
		formView: {
			
			params: {
				
				postInit: function() {
					
					var _this = this;
					
					var eDialog = this.$el;
					var eTabs = eDialog.find( '#edit_form_item_lang' );
						
					this.$tabs = eTabs;
					
					var oLanguages = this.family.data.languages;
					var oMetaDataLangParted = this.family.data.metaDataLangParted;
					var oContexts = this.family.data.contexts;
					
					var iContextId = oContexts.getContextId( 'question' );
					
					
					
					eTabs.tabs();
					
					// create a tab for each language
					oLanguages.each( function( oLang ) {
						
						var iLangId = oLang.get( 'lang_id' );
						
						var eTab = _this.getTmpl( null, 'item-dialog-tab' );
						var eContent = _this.getTmpl( null, 'item-dialog-content' );
						
						// if not the default language, then clear the fields
						if ( !oLang.get( 'is_default' ) ) {
							eContent.find( '.ui-helper-reset' ).html( '' );
						}
						
						var sTabId = 'question_%s'.printf( oLang.get( 'code' ) );
						
						
						// update nav
						var eA = eTab.find( 'a' );
						eA.attr( 'href', '#%s'.printf( sTabId ) );
						eA.html( oLang.get( 'title' ) );
						
						eContent.attr( 'id', sTabId );
						
						eTabs.find( '.ui-tabs-nav' ).append( eTab );
						eTabs.append( eContent );
						
						
						// handle the meta-data fields via a view
						var oMetaDataLangField = new Geko.Wp.Form.MetaData.Manage.LangField.ListView( {
							el: eContent,
							collection: oMetaDataLangParted.getPart( iLangId, iContextId ),
							data: {
								contextDispatcher: _this.localDispatcher			// item dispatcher
							}
						} );
						
						
					} );
					
					setTimeout( function() {
						eTabs.tabs( 'refresh' );
						eTabs.tabs( { active: 0 } );
					}, 10 );
					
					
					eDialog.dialog( {
						autoOpen: false,
						modal: true,
						width: Geko.Wp.Form.Manage.iDialogWidth,
						buttons: {
							Add: _.bind( this.addItem, this ),
							Save: _.bind( this.saveItem, this ),
							Cancel: _.bind( this.cancel, this )
						}
					} );
					
					Geko.Wp.Form.Manage.setDlgParent( eDialog, this.elementPrefix );
					
				}

			},
			
			extend: {
				
				events: {
					'localDispatcher:openAddItem this': 'openAddItem',
					'localDispatcher:openEditItem this': 'openEditItem'
				},
									
				openAddItem: function( e, oItemType, oData ) {
					
					this.current = {
						itemType: oItemType,
						data: oData
					};
					
					
					var eDialog = this.$el;
					
					var eDlgParent = eDialog.parent();
					eDlgParent.showHideElem( '#item_add', '#item_save' );
					
					var sTitle = 'Add %s Item'.printf( oItemType.get( 'name' ) );
					eDialog.dialog( { title: sTitle } );
					
					// reset
					this.extractModelValues( new Geko.Wp.Form.Item.Manage.Model() );
					
					// update dialog meta-data values
					this.localDispatcher.trigger( 'openAddMetaData' );
					
					
					eDialog.dialog( 'open' );
				},
				
				openEditItem: function( e, oModel ) {
					
					this.current = { model: oModel };
					
					var eDialog = this.$el;
					
					var eDlgParent = eDialog.parent();
					eDlgParent.showHideElem( '#item_save', '#item_add' );
					
					
					var sTitle = 'Edit %s Item'.printf( oModel.getItemTypeName() );
					
					eDialog.dialog( { title: sTitle } );
					
					this.extractModelValues( oModel );
					
					// update dialog meta-data values
					this.localDispatcher.trigger( 'openEditMetaData', oModel );
					
					
					eDialog.dialog( 'open' );
				},
				
				addItem: function() {
					
					var oItemType = this.current.itemType;
					var oSection = this.current.data.section;
					var oItemValue = this.current.data.itemValue;
					
					var oItems = this.family.data.items;
					var oSectionItems = this.current.data.sectionItems;
					
					
					var eDialog = this.$el;
					
					var oModel = new Geko.Wp.Form.Item.Manage.Model();
					
					var oModelParams = {
						'val:fmitmtyp_id': oItemType.get( 'fmitmtyp_id' ),
						'val:fmsec_id': oSection.get( 'fmsec_id' )
					};
					
					if ( oItemValue ) {
						$.extend( oModelParams, {
							'val:parent_itm_id': oItemValue.get( 'fmitm_id' ),
							'val:parent_itmvalidx_id': oItemValue.get( 'fmitmval_idx' )
						} );
					}
					
					this.setModelValues( oModel, null, oModelParams );
					
					
					// add to collections
					
					oSectionItems.add( oModel );
					oItems.add( oModel );
					
					
					eDialog.dialog( 'close' );
				},
				
				saveItem: function() {
					
					var eDialog = this.$el;
					
					this.setModelValues( this.current.model, null, null, new Geko.Wp.Form.Item.Manage.Model() );
					
					eDialog.dialog( 'close' );
				},
				
				cancel: function() {
					var eDialog = this.$el;
					eDialog.dialog( 'close' );
				}
				
			}
			
		}
	
	} );
	
	var _family = Geko.Wp.Form.Item.Manage;
	
	
	
	// move item to another section, dialog box view
	
	Geko.Wp.Form.Item.Manage.MoveDialog = Backstab.View.extend( {
		
		events: {
			'data.itemDispatcher:openMoveItem this': 'openMoveItem',
		},
		
		initialize: function() {
			
			var _this = this;
			
			var eDialog = this.$el;
			
			eDialog.dialog( {
				autoOpen: false,
				modal: true,
				width: Geko.Wp.Form.Manage.iDialogWidth,
				buttons: {
					Move: _.bind( this.moveItem, this ),
					Cancel: _.bind( this.cancel, this )
				}
			} );
			
			Geko.Wp.Form.Manage.setDlgParent( eDialog, 'item_' );
		},
		
		openMoveItem: function( e, oModel ) {
			
			this.current = { model: oModel };
			
			var eDialog = this.$el;
			
			var eSelect = eDialog.find( '#item_section' );
			
			var iCurSecId = oModel.get( 'fmsec_id' );
			
			eSelect.find( '> option' ).remove();
			
			
			var oSections = _family.data.sections;
			
			oSections.each( function( oSection ) {
				
				var iSecId = oSection.get( 'fmsec_id' );
				
				if ( iCurSecId !== iSecId ) {
					
					var eOption = $( '<option><\/option>' );
					eOption.html( oSection.get( 'title' ) );
					eOption.attr( 'value', iSecId );
					
					eSelect.append( eOption );
				}
				
			} );
			
			eDialog.dialog( 'open' );
			
		},
		
		moveItem: function() {
			
			var eDialog = this.$el;
			var eSelect = eDialog.find( '#item_section' );
			
			var oModel = this.current.model;
			var oItemsParted = _family.data.itemsParted;
			
			
			// get source collection
			
			var iCurSecId = oModel.get( 'fmsec_id' );
			var oSourceItems = oItemsParted.getSectionPart( iCurSecId );
			
			
			// get destination collection
			
			var iNewSecId = eSelect.intVal();
			var oDestItems = oItemsParted.getSectionPart( iNewSecId );
			
			
			// set model's new attributes
			oModel.set( 'fmsec_id', iNewSecId );
			
			
			// transfer model to destination collection
			oSourceItems.transfer( oModel, oDestItems );
			
			
			eDialog.dialog( 'close' );			
		},
		
		cancel: function() {
			var eDialog = this.$el;
			eDialog.dialog( 'close' );
		}
		
	} );
	
	
} )( jQuery );