;( function ( $ ) {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	Geko.setNamespace( 'Wp.Form.MetaData.Manage' );
	
	
	
	//// main family
	
	Geko.Wp.Form.MetaData.Manage = Backstab.family( {
		
		name: 'meta_data',
		
		unescapeTemplateSrc: true,
		enableLocalDispatcher: true,
		useElementPrefix: true,
		
		model: {
			
			extend: {
				
				fields: Backstab.ModelFields[ 'form.meta_data' ],
				
				
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
				
				
				
				// context
				
				getContext: function() {
					
					var oContexts = this.family.data.contexts;
					
					return oContexts.findWhere( {
						'value': this.get( 'context_id' )
					} );
				},
				
				getContextTitle: function() {
					return this.getContext().get( 'title' );
				},
				
				
				// language
				
				getLanguage: function() {
					
					var oLanguages = this.family.data.languages;
					
					return oLanguages.findWhere( {
						'lang_id': this.get( 'lang_id' )
					} );
				},
				
				getLanguageTitle: function() {
					return this.getLanguage().get( 'title' );
				}
				
			}
			
		},
		
		// MetaData
		itemView: {
			
			params: {
				
				updateElem: function() {
					
					var oModel = this.model;
					var eLi = this.$el;
					
					
					var sLabel = '%s (%s)'.printf( oModel.get( 'name' ), oModel.getContextTitle() );
					eLi.find( '> span.label' ).html( sLabel );
					eLi.find( '> .geko-form-item-options > span' ).addClass( 'geko-form-icon-%s'.printf( oModel.getItemTypeSlug() ) );
					
				}
				
			},
			
			extend: {
				
				events: {
					'click > .geko-form-item-options': 'openEditMetaData',
					'click > .geko-form-remove-item':  'confirmRemove',
					'click > .geko-form-move-item': 'openMoveMetaData'
				},
				
				openEditMetaData: function() {
					
					this.localDispatcher.trigger( 'openEditMetaData', this.model );
					
					return false;
				},
				
				confirmRemove: function( e ) {
			
					if ( confirm( 'Are you sure you want to remove this meta-data item?' ) ) {
						this.model.destroy();
					}
					
					return false;
				},
				
				openMoveMetaData: function() {
					
					this.localDispatcher.trigger( 'openMoveMetaData', this.model );
					
					return false;
				}
				
			}
			
		},
		
		// MetaData
		listView: {
			
			params: {
				
				itemTmplSelector: '.geko-form-item'
				
			}
		
		},
		
		// MetaData
		formView: {
			
			params: {
				
				postInit: function() {
					
					var _this = this;
					
					var eDialog = this.$el;
					
					eDialog.dialog( {
						autoOpen: false,
						modal: true,
						width: Geko.Wp.Form.Manage.iDialogWidth,
						buttons: {
							Add: _.bind( this.addMetaData, this ),
							Save: _.bind( this.saveMetaData, this ),
							Cancel: _.bind( this.cancel, this )
						}
					} );
					
					Geko.Wp.Form.Manage.setDlgParent( eDialog, this.elementPrefix );
				}
				
			},
			
			extend: {
				
				events: {
					'localDispatcher:openAddMetaData this': 'openAddMetaData',
					'localDispatcher:openEditMetaData this': 'openEditMetaData'
				},
									
				openAddMetaData: function( e, oItemType, oData ) {
					
					this.current = {
						itemType: oItemType,
						data: oData
					};
					
					var eDialog = this.$el;
					
					var eDlgParent = eDialog.parent();
					eDlgParent.showHideElem( '#meta_data_add', '#meta_data_save' );
					
					var oLanguage = oData.language;
					var sTitle = 'Add %s Item - %s'.printf( oItemType.get( 'name' ), oLanguage.get( 'title' ) );
					
					eDialog.dialog( { title: sTitle } );
					
					
					// reset
					this.extractModelValues( new Geko.Wp.Form.MetaData.Manage.Model() );
					
					eDialog.dialog( 'open' );
				},
				
				openEditMetaData: function( e, oModel ) {
					
					this.current = { model: oModel };
					
					var eDialog = this.$el;
					
					var eDlgParent = eDialog.parent();
					eDlgParent.showHideElem( '#meta_data_save', '#meta_data_add' );
					
					
					var sTitle = 'Edit %s Item - %s'.printf( oModel.getItemTypeName(), oModel.getLanguageTitle() );
					
					eDialog.dialog( { title: sTitle } );
					
					
					this.extractModelValues( oModel );
					
					eDialog.dialog( 'open' );
				},
				
				addMetaData: function() {
					
					var oItemType = this.current.itemType;
					var oLanguage = this.current.data.language;
					
					var oLangTabMetaData = this.current.data.langTabMetaData;
					var oMetaData = this.family.data.metaData;
					
					var eDialog = this.$el;
					
					var oModel = new Geko.Wp.Form.MetaData.Manage.Model();
					
					
					
					var oContexts = this.family.data.contexts;				
					var oMetaDataLangParted = this.family.data.metaDataLangParted;
					
					var iContextId = this.$( '#meta_data_context_id' ).intVal();
					
					
					//
					var iLangId = oLanguage.get( 'lang_id' );
					
					this.setModelValues( oModel, null, {
						'val:fmitmtyp_id': oItemType.get( 'fmitmtyp_id' ),
						'val:lang_id': iLangId
					} );
					
					
					// assign to both collections
					
					oLangTabMetaData.add( oModel );
					oMetaData.add( oModel );
					
					
					// assign to lang/context
					var oMetaDataLang = oMetaDataLangParted.getPart( iLangId, iContextId );
					
					
					oMetaDataLang.add( oModel );
					
					eDialog.dialog( 'close' );
				},
				
				saveMetaData: function() {
					
					var oModel = this.current.model;
					var iOldContextId = oModel.get( 'context_id' );
					
					var eDialog = this.$el;
					
					this.setModelValues( oModel, null, null, new Geko.Wp.Form.MetaData.Manage.Model() );

					var iNewContextId = oModel.get( 'context_id' );
					
					// handle context change
					if ( iOldContextId !== iNewContextId ) {
						
						var iLangId = oModel.get( 'lang_id' );
						
						var oMetaDataLangParted = this.family.data.metaDataLangParted;
						
						var oSourceMetaDataLang = oMetaDataLangParted.getPart( iLangId, iOldContextId );
						var oDestMetaDataLang = oMetaDataLangParted.getPart( iLangId, iNewContextId );
						
						oSourceMetaDataLang.transfer( oModel, oDestMetaDataLang );
					}
					
					eDialog.dialog( 'close' );
				},
				
				cancel: function() {
					var eDialog = this.$el;
					eDialog.dialog( 'close' );
				}
				
				
			}
			
		}
		
	} );
	
	var _family = Geko.Wp.Form.MetaData.Manage;
	
	
	
	//// move meta-data between languages dialog
	
	Geko.Wp.Form.MetaData.Manage.MoveDialog = Backstab.View.extend( {
		
		events: {
			'data.metaDataDispatcher:openMoveMetaData this': 'openMoveMetaData',
		},
		
		initialize: function() {
			
			var _this = this;
			
			var eDialog = this.$el;
			
			eDialog.dialog( {
				autoOpen: false,
				modal: true,
				width: Geko.Wp.Form.Manage.iDialogWidth,
				buttons: {
					Move: _.bind( this.moveMetaData, this ),
					Cancel: _.bind( this.cancel, this )
				}
			} );
			
			Geko.Wp.Form.Manage.setDlgParent( eDialog, 'item_' );
		},
		
		openMoveMetaData: function( e, oModel ) {
			
			this.current = { model: oModel };
			
			var eDialog = this.$el;
			
			var eSelect = eDialog.find( '#meta_data_language' );
			
			var iCurLangId = oModel.get( 'lang_id' );
			
			
			eSelect.find( '> option' ).remove();
			
			var oLanguages = _family.data.languages;
			
			oLanguages.each( function( oLanguage ) {
				
				var iLangId = oLanguage.get( 'lang_id' );
				
				if ( iCurLangId !== iLangId ) {
					
					var eOption = $( '<option><\/option>' );
					eOption.html( oLanguage.get( 'title' ) );
					eOption.attr( 'value', iLangId );
					
					eSelect.append( eOption );
				}
				
			} );
			
			
			eDialog.dialog( 'open' );
			
		},
		
		moveMetaData: function() {
			
			var eDialog = this.$el;
			var eSelect = eDialog.find( '#meta_data_language' );
			
			var oModel = this.current.model;
			
			var oMetaDataParted = _family.data.metaDataParted;
			var oMetaDataLangParted = _family.data.metaDataLangParted;
			
			
			// get source collection
			
			var iContextId = oModel.get( 'context_id' );
			var iCurLangId = oModel.get( 'lang_id' );
			
			var oSourceMetaData = oMetaDataParted.getPart( iCurLangId );
			var oSourceMetaDataLang = oMetaDataLangParted.getPart( iCurLangId, iContextId );			
			
			
			// get destination collection
			
			var iNewLangId = parseInt( eSelect.val() );
			
			var oDestMetaData = oMetaDataParted.getPart( iNewLangId );
			var oDestMetaDataLang = oMetaDataLangParted.getPart( iNewLangId, iContextId );
			
			
			
			// set model's new attributes
			oModel.set( 'lang_id', iNewLangId );
			
			
			// transfer model to destination collection
			oSourceMetaData.transfer( oModel, oDestMetaData );
			oSourceMetaDataLang.transfer( oModel, oDestMetaDataLang );
			
			
			eDialog.dialog( 'close' );			
		},
		
		cancel: function() {
			var eDialog = this.$el;
			eDialog.dialog( 'close' );
		}
		
	} );
	
	
	
	// family applied to meta-data language tabs
	
	Geko.Wp.Form.MetaData.Manage.Tabs = Backstab.family( {
		
		name: 'meta_tabs',
		
		unescapeTemplateSrc: true,
		enableLocalDispatcher: true,
		useElementPrefix: true,
		
		itemView: {
			
			params: {
				
				postInit: function() {
					
					var _this = this;
					
					var oListView = this.data.listView;
					
					var eTabs = oListView.$el;
					var eContent = this.getTmpl( null, 'meta_tabs-content' );
					
					this.$content = eContent;
					
					eTabs.append( eContent );
					
					
					var oContexts = this.family.data.contexts;
					var oMainMetaData = this.family.data.metaData;						// flat list of meta-data items
					var oMetaDataParted = this.family.data.metaDataParted;
					var oMetaDataLangParted = this.family.data.metaDataLangParted;
					var oItemTypes = this.family.data.itemTypes;
					
					
					//// meta data (items/questions)
					
					var iLangId = this.model.get( 'lang_id' );
					
					// part by language
					
					var oMetaData = oMetaDataParted.setPart( iLangId );
					
					
					// part by language/context
					oContexts.each( function( oContext ) {
						
						var iContextId = oContext.get( 'value' );
						var oMetaDataLang = oMetaDataLangParted.setPart( iLangId, iContextId );
						
					} );
					
					
					var oMetaDataListView = new Geko.Wp.Form.MetaData.Manage.ListView( {
						collection: oMetaData,
						el: eContent.find( 'ul.geko-form-items' )
					} );
					
					
					//// controls
					
					var eAddItemTemplate = eContent.find( '> .ui-tabs-panel-header > .icons > .geko-form-add-item' );
					
					oItemTypes.each( function( oItemType ) {
						
						// note that this is meta-data context, not item, but otherwise works in the same way
						var oAddWidgetControl = new Geko.Wp.Form.ItemType.Manage.AddWidgetControl( {
							el: eAddItemTemplate.clone(),
							model: oItemType,
							data: {
								itemDispatcher: oMetaDataListView.localDispatcher,
								addEvent: 'openAddMetaData',
								langTabMetaData: oMetaData,				// collection of meta-data items for language tab
								language: _this.model
							}
						} );
						
						eContent.find( '> .ui-tabs-panel-header > .icons' ).append( oAddWidgetControl.render().$el );
						
					} );
					
					eAddItemTemplate.remove();

					
				},
				
				updateElem: function() {
					
					var eTabs = this.data.listView.$el;
					var eTab = this.$el;
					var oModel = this.model;
					
					// update nav
					var eA = eTab.find( 'a' );
					eA.attr( 'href', '#%s'.printf( this.cid ) );
					eA.html( oModel.get( 'title' ) );
					
					// update content
					var eCont = this.$content;
					eCont.attr( 'id', this.cid );
					eCont.find( '.ui-tabs-panel-header div.description' ).html( oModel.get( 'description' ) );
					
					// refresh
					setTimeout( function() {
						eTabs.tabs( 'refresh' );
					}, 10 );
					
				}
				
			}
		},
		
		listView: {
			
			params: {
				
				appendTarget: '.ui-tabs-nav',
				
				postInit: function() {
					
					var eTabs = this.$el;
					
					eTabs.tabs();		// initialize tabs
					
				}
				
			},
			
			extend: {
				
				events: {
					'reveal this': 'reveal'
				},
				
				reveal: function() {
					
					var eTabs = this.$el;
					
					eTabs.fadeIn( Geko.Wp.Form.Manage.iAnimDelay );
					
					setTimeout( function() {
						eTabs.tabs( { active: 0 } );
					}, 15 );
					
				}
				
			}
		
		}
		
		
	} );
	
	
	// this handles the meta data fields shown on the various tabs
	// (section, item, and choice)
	
	Geko.Wp.Form.MetaData.Manage.LangField = Backstab.family( {
		
		itemView: {
			
			params: {
			
				updateElem: function() {
					
					var oModel = this.model;
					
					this.$( '> .main' ).html( oModel.get( 'name' ) );
					
				}
			
			},
			
			extend: {
				
				events: {
					'data.contextDispatcher:openAddMetaData this': 'openAddMetaData',
					'data.contextDispatcher:openEditMetaData this': 'openEditMetaData',
					'data.contextDispatcher:addMetaData this': 'addMetaData',
					'data.contextDispatcher:editMetaData this': 'editMetaData'
				},
				
				createElement: function() {
					
					var oModel = this.model;					// meta-data item
					
					var oListView = this.data.listView;
					
					var oWidgetFactory = this.family.data.widgetFactory;
					var oLanguages = this.family.data.languages;
					var oContextDispatcher = oListView.data.contextDispatcher;
					
					
					
					this.data.contextDispatcher = oContextDispatcher;
					
					
					var iLangId = oModel.get( 'lang_id' );
					
					var sMetaKey = 'meta_data_%s_%s'.printf(
						oLanguages.getCode( iLangId ),
						oModel.getItemTypeSlug()
					);
					
					
					var oWidget = oWidgetFactory.make( oModel, oContextDispatcher, sMetaKey );
					this.widget = oWidget;						// track the widget view
					
					return oWidget.render().$el;
				},
				
				openAddMetaData: function( e ) {
					
					this.widget.openAddValue();
				},
				
				openEditMetaData: function( e, oContextModel ) {
					
					// find all meta-data for 1) context, 2) language, and 3) belonging to model
					
					var oModel = this.model;					// meta-data item
					var oItemMetaVals = this.family.data.itemMetaVals;
					
					var aMetaData = oContextModel.getItemDataValues( oItemMetaVals, oModel );
					
					this.current = {
						metaData: aMetaData,
						contextModel: oContextModel
					};
					
					var aVals = [];
					
					$.each( aMetaData, function( i, v ) {
						aVals.push( v.get( 'value' ) );
					} );
					
					this.widget.openEditValue( null, aVals );
				},
				
				addMetaData: function( e, oContextModel ) {
					
					var aVals = this.widget.getWidgetValue();
					
					this.newItemMetaValue( oContextModel, aVals );
					
				},
				
				editMetaData: function() {
					
					var aVals = this.widget.getWidgetValue();
					var aMetaData = this.current.metaData;
					
					if ( aMetaData && aMetaData[ 0 ] ) {
						
						aMetaData[ 0 ].set( 'value', aVals[ 0 ] );
					
					} else {
						
						var oContextModel = this.current.contextModel;
						this.newItemMetaValue( oContextModel, aVals );
					}
					
				},
				
				newItemMetaValue: function( oContextModel, aVals ) {

					var oModel = this.model;					// meta-data item
					var oItemMetaVals = this.family.data.itemMetaVals;
					
					var oItemMetaVal = new Geko.Wp.Form.ItemMetaValue.Manage.Model();
					
					oContextModel.setItemMetaValue( oItemMetaVal, oModel );
					oItemMetaVal.set( 'value', aVals[ 0 ] );
					
					oItemMetaVals.add( oItemMetaVal );				
				}
				
			}
			
		},
		
		listView: {
			
			params: {
				
				appendTarget: 'fieldset',
				
				postInit: function() {
					
					// console.log( this.collection );
					
				}
				
			}
			
		}
		
	} );
	
	
	
} )( jQuery );