( function() {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	
	
	//// main family
	
	Geko.setNamespace( 'Wp.Form.ItemValue.Manage', Backstab.family( {
		
		name: 'item_value',
		
		unescapeTemplateSrc: true,
		enableLocalDispatcher: true,
		useElementPrefix: true,
		
		// ItemValue
		model: {
			
			extend: {
			
				fields: Backstab.ModelFields[ 'form.item_value' ],
				
				
				// item meta values
				
				getItemDataValues: function( oItemMetaVals, oMetaData ) {
					
					return oItemMetaVals.where( {
						'fmitm_id': this.get( 'fmitm_id' ),
						'fmitmval_idx': this.get( 'fmitmval_idx' ),
						'context_id': oMetaData.get( 'context_id' ),
						'lang_id': oMetaData.get( 'lang_id' ),
						'slug': oMetaData.get( 'slug' )
					} );
				},
				
				setItemMetaValue: function( oItemMetaVal, oMetaData ) {
					
					oItemMetaVal.set( {
						'fmitm_id': this.get( 'fmitm_id' ),
						'fmitmval_idx': this.get( 'fmitmval_idx' ),
						'context_id': oMetaData.get( 'context_id' ),
						'lang_id': oMetaData.get( 'lang_id' ),
						'slug': oMetaData.get( 'slug' )
					} );
				}
				
			}
			
		},
					
		// ItemValue
		itemView: {
			
			params: {
				
				postInit: function() {
					
					var _this = this;
					
					var oListView = this.data.listView;
					
					var oModel = this.model;								// this is an ItemValue
					var eLi = this.$el;
					
					
					var oSection = oListView.data.section;
					var oItemType = oListView.data.itemType;
					
					var oMainItems = this.family.data.items;				// flat list of items
					var oItemsParted = this.family.data.itemsParted;
					var oItemTypes = this.family.data.itemTypes;
					
					
					
					if ( oItemType.get( 'has_choice_subs' ) ) {
						
						//// show sub-items
						
						var iItemId = oModel.get( 'fmitm_id' );
						var iChoiceIdx = oModel.get( 'fmitmval_idx' );
						
						var oItems = oItemsParted.setChoicePart( iItemId, iChoiceIdx );
						
						var oItemListView = new Geko.Wp.Form.Item.Manage.ListView( {
							collection: oItems,
							data: {
								itemValue: oModel,
								section: oSection
							}
						} );
						
						this.$( '> .geko-form-sub-items' ).append( oItemListView.render().$el );
						
						
						
						//// add controls
						
						var eAddItemTemplate = this.$( '.geko-form-add-item' );
						
						oItemTypes.each( function( oItemType ) {
							
							var oAddWidgetControl = new Geko.Wp.Form.ItemType.Manage.AddWidgetControl( {
								el: eAddItemTemplate.clone(),
								model: oItemType,
								data: {
									itemDispatcher: oListView.data.itemDispatcher,
									addEvent: 'openAddItem',
									sectionItems: oItems,
									section: oSection,
									itemValue: oModel
								}
							} );
							
							_this.$( 'span.spacer' ).before( oAddWidgetControl.render().$el );
							
						} );
						
						
						eAddItemTemplate.remove();
						
					}
					
						
				},
				
				updateElem: function( e ) {
					
					var oModel = this.model;
					var eLi = this.$el;
					
					var eExpandWidgets = this.$( '> .geko-form-expand-widgets > span' );
					var eExpandItems = this.$( '> .geko-form-expand-items > span' );
					var eWidgets = this.$( '> .icons_container > .icons' );
					
					
					eLi.find( '> span.label' ).html( oModel.get( 'label' ) );
					
					
					//// widgets toggle
					
					eExpandWidgets.ternaryClass( oModel.get( 'show_widgets' ), 'geko-form-icon-hide-widgets', 'geko-form-icon-show-widgets' );
					
					if ( oModel.get( 'show_widgets' ) ) {
						
						if ( e ) {
							eWidgets.effect( 'blind', { direction: 'right', mode: 'show' }, Geko.Wp.Form.Manage.iAnimDelay );
						} else {
							eWidgets.show();
						}
						
					} else {

						if ( e ) {
							eWidgets.effect( 'blind', { direction: 'right', mode: 'hide' }, Geko.Wp.Form.Manage.iAnimDelay );
						} else {
							eWidgets.hide();								
						}
						
					}
					
					
					//// items toggle
					eExpandItems.ternaryClass( oModel.get( 'hide_items' ), 'geko-form-icon-show-items', 'geko-form-icon-hide-items' );
					
				}
				
			},
			
			extend: {
				
				events: {
					'click > .geko-form-item-options': 'openEditItemValue',
					'click > .geko-form-expand-widgets': 'toggleExpandWidgets',
					'click > .geko-form-expand-items': 'toggleExpandItems'
				},
				
				openEditItemValue: function() {
					
					this.localDispatcher.trigger( 'openEditItemValue', this.model );
					
					return false;
				},
				
				toggleExpandWidgets: function() {
					
					this.model.toggleValue( 'show_widgets' );
					
					return false;
				},
				
				toggleExpandItems: function() {
					
					this.model.toggleValue( 'hide_items' );
					
					return false;
				}
				
			}
			
		},
		
		// ItemValue
		listView: {
			
			params: {
				
				appendTarget: 'ul.geko-form-values',
				itemTmplSelector: '.geko-form-value'
				
			},
			
			extend: {
				
				events: {
					'data.item:change:hide_subs this': 'toggleVisible'
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
		
		// ItemValue
		formView: {
			
			params: {
				
				postInit: function() {
					
					var _this = this;
					
					var eDialog = this.$el;
					var eTabs = eDialog.find( '#edit_form_item_value_lang' );
					
					this.$tabs = eTabs;
					
					var oLanguages = this.family.data.languages;
					var oMetaDataLangParted = this.family.data.metaDataLangParted;
					var oContexts = this.family.data.contexts;
					
					var iContextId = oContexts.getContextId( 'choice' );
					
					
					eTabs.tabs();
					
					// create a tab for each language
					oLanguages.each( function( oLang ) {
						
						var iLangId = oLang.get( 'lang_id' );
						
						var eTab = _this.getTmpl( null, 'item_value-dialog-tab' );
						var eContent = _this.getTmpl( null, 'item_value-dialog-content' );
						
						// if not the default language, then clear the fields
						if ( !oLang.get( 'is_default' ) ) {
							eContent.find( '.ui-helper-reset' ).html( '' );
						}
						
						var sTabId = 'choice_%s'.printf( oLang.get( 'code' ) );
						
						
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
								contextDispatcher: _this.localDispatcher			// section dispatcher
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
							Add: _.bind( this.addItemValue, this ),
							Save: _.bind( this.saveItemValue, this ),
							Cancel: _.bind( this.cancel, this )
						}
					} );
					
					Geko.Wp.Form.Manage.setDlgParent( eDialog, this.elementPrefix );
				}
				
			},
			
			extend: {
				
				events: {
					'localDispatcher:openAddItemValue this': 'openAddItemValue',
					'localDispatcher:openEditItemValue this': 'openEditItemValue'
				},
									
				openAddItemValue: function( e, oCollection ) {
					
					this.current = { collection: oCollection };
					
					var eDialog = this.$el;
					
					var eDlgParent = eDialog.parent();
					eDlgParent.showHideElem( '#item_value_add', '#item_value_save' );
					
					eDialog.dialog( { title: 'Add Item Value' } );
					
					// reset
					this.extractModelValues( new Geko.Wp.Form.ItemValue.Manage.Model() );
					
					// update dialog meta-data values
					this.localDispatcher.trigger( 'openAddMetaData' );
					
					
					eDialog.dialog( 'open' );
				},
				
				openEditItemValue: function( e, oModel ) {
					
					this.current = { model: oModel };
					
					var eDialog = this.$el;
					
					var eDlgParent = eDialog.parent();
					eDlgParent.showHideElem( '#item_value_save', '#item_value_add' );
					
					eDialog.dialog( { title: 'Edit Item Value' } );
					
					this.extractModelValues( oModel );
					
					// update dialog meta-data values
					this.localDispatcher.trigger( 'openEditMetaData', oModel );
					
					
					eDialog.dialog( 'open' );
					
					// this.$tabs.tabs( 'refresh' );
				},
				
				addItemValue: function() {
					
					var eDialog = this.$el;
					
					var oModel = new Geko.Wp.Form.ItemValue.Manage.Model();
					this.setModelValues( oModel );
					
					this.current.collection.add( oModel );
					
					eDialog.dialog( 'close' );
				},
				
				saveItemValue: function() {
					
					var eDialog = this.$el;
					
					this.setModelValues( this.current.model );
					
					eDialog.dialog( 'close' );
				},
				
				cancel: function() {
					var eDialog = this.$el;
					eDialog.dialog( 'close' );
				}
				
			}
			
		}
		
	} ) );
	
	
	
} ).call( this );