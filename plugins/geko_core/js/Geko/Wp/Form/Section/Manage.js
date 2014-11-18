( function() {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	
	
	// section
	
	Geko.setNamespace( 'Wp.Form.Section.Manage', Backstab.family( {
		
		name: 'section',
		
		unescapeTemplateSrc: true,
		enableLocalDispatcher: true,
		useElementPrefix: true,
		
		model: {
			
			extend: {
				
				fields: Backstab.ModelFields[ 'form.section' ],
				
				getItemDataValues: function( oItemMetaVals, oMetaData ) {
					
					return oItemMetaVals.where( {
						'fmsec_id': this.get( 'fmsec_id' ),
						'context_id': oMetaData.get( 'context_id' ),
						'lang_id': oMetaData.get( 'lang_id' ),
						'slug': oMetaData.get( 'slug' )
					} );
				},
				
				setItemMetaValue: function( oItemMetaVal, oMetaData ) {
					
					oItemMetaVal.set( {
						'fmsec_id': this.get( 'fmsec_id' ),
						'context_id': oMetaData.get( 'context_id' ),
						'lang_id': oMetaData.get( 'lang_id' ),
						'slug': oMetaData.get( 'slug' )
					} );
				}
				
			}
			
		},
		
		// Section
		itemView: {
			params: {
				
				postInit: function() {
					
					var oListView = this.data.listView;
					
					
					var eTabs = oListView.$el;
					var eContent = this.getTmpl( null, 'section-content' );
					
					this.$content = eContent;
					
					eTabs.append( eContent );
					
					
					var oModel = this.model;
					
					//// items (questions)
					
					// part items, by section
					
					var oMainItems = this.family.data.items;				// flat list of items
					var oItemsParted = this.family.data.itemsParted;
					
					
					var iSecId = oModel.get( 'fmsec_id' );
					
					var oItems = oItemsParted.setSectionPart( iSecId );
					
					
					
					var oItemListView = new Geko.Wp.Form.Item.Manage.ListView( {
						collection: oItems,
						el: eContent.find( '> div > ul.geko-form-items' ),
						data: {
							section: oModel
						}
					} );
					
					
					//// instantiate add widget controls
					
					var oSectionControls = new Geko.Wp.Form.Section.Manage.Controls( {
						el: eContent.find( '.ui-tabs-panel-header div.icons' ),
						model: oModel,
						data: {
							sectionDispatcher: this.localDispatcher,
							itemDispatcher: oItemListView.localDispatcher,
							sectionItems: oItems
						}
					} );
					
					
					
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
					
				},
				
				removeElem: function() {
					
					var eTabs = this.data.listView.$el;
					
					this.$el.remove();
					this.$content.remove();
					
					// refresh
					setTimeout( function() {
						eTabs.tabs( 'refresh' );
					}, 10 );						
				}
				
			}
			
		},
		
		// Section
		listView: {
			
			params: {
				
				appendTarget: '.ui-tabs-nav',
				
				postInit: function() {
					
					var eTabs = this.$el;
					
					eTabs.tabs();		// initialize tabs
					
				},
				
				postAppend: function( appendTarget, item, model, e ) {
					
					if ( 'collection:add' === e.type ) {
						
						var eTabs = this.$el;
						
						setTimeout( function() {
							eTabs.tabs( { active: -1 } );
						}, 15 );
					}
					
				}
				
			},
			
			extend: {
				
				events: {
					'click ul li.add_menu a': 'addSection',
					'reveal this': 'reveal'
				},
				
				addSection: function() {
					this.localDispatcher.trigger( 'openAddSection' );
					return false;
				},
				
				reveal: function() {
					
					var eTabs = this.$el;
					
					eTabs.fadeIn( Geko.Wp.Form.Manage.iAnimDelay );
					
					setTimeout( function() {
						eTabs.tabs( { active: 0 } );
					}, 15 );
					
				}
				
			}
			
		},
		
		// Section
		formView: {
			
			params: {
				
				postInit: function() {
					
					var _this = this;
					
					var eDialog = this.$el;
					var eTabs = eDialog.find( '#edit_form_section_lang' );
					
					this.$tabs = eTabs;
					
					var oLanguages = this.family.data.languages;
					var oMetaDataLangParted = this.family.data.metaDataLangParted;
					var oContexts = this.family.data.contexts;
					
					var iContextId = oContexts.getContextId( 'section' );
					
					
					eTabs.tabs();
					
					// create a tab for each language
					oLanguages.each( function( oLang ) {
						
						var iLangId = oLang.get( 'lang_id' );
						
						var eTab = _this.getTmpl( null, 'section-dialog-tab' );
						var eContent = _this.getTmpl( null, 'section-dialog-content' );
						
						// if not the default language, then clear the fields
						if ( !oLang.get( 'is_default' ) ) {
							eContent.find( '.ui-helper-reset' ).html( '' );
						}
						
						var sTabId = 'section_%s'.printf( oLang.get( 'code' ) );
						
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
							Add: _.bind( this.addSection, this ),
							Save: _.bind( this.saveSection, this ),
							Cancel: _.bind( this.cancel, this )
						}
					} );
					
					Geko.Wp.Form.Manage.setDlgParent( eDialog, this.elementPrefix );
				}

			},
			
			extend: {
				
				events: {
					'localDispatcher:openAddSection this': 'openAddSection',
					'localDispatcher:openEditSection this': 'openEditSection'
				},
				
				
				// handling of meta-data values
				// when form is opened (add or edit mode) meta fields must be updated accordingly
				
				
				openAddSection: function( e, oCollection ) {
					
					var eDialog = this.$el;
					
					var eDlgParent = eDialog.parent();
					eDlgParent.showHideElem( '#section_add', '#section_save' );
					
					eDialog.dialog( { title: 'Add Section' } );
					
					// reset
					this.extractModelValues( new Geko.Wp.Form.Section.Manage.Model() );
					
					// update dialog meta-data values
					this.localDispatcher.trigger( 'openAddMetaData' );
					
					
					eDialog.dialog( 'open' );
				},
				
				openEditSection: function( e, oModel ) {
					
					this.current = { model: oModel };
					
					var eDialog = this.$el;
					
					var eDlgParent = eDialog.parent();
					eDlgParent.showHideElem( '#section_save', '#section_add' );
					
					eDialog.dialog( { title: 'Edit Section' } );
					
					this.extractModelValues( oModel );
					
					// update dialog meta-data values
					this.localDispatcher.trigger( 'openEditMetaData', oModel );
					
					
					eDialog.dialog( 'open' );
				},
				
				addSection: function() {
					
					var eDialog = this.$el;
					
					var oSections = this.family.data.sections;
					
					
					var oModel = new Geko.Wp.Form.Section.Manage.Model();
					
					this.setModelValues( oModel, null, {
						'intcid:fmsec_id': true
					} );
					
					
					oSections.add( oModel );
					
					// add meta-data values
					this.localDispatcher.trigger( 'addMetaData', oModel );
					
					
					eDialog.dialog( 'close' );
				},
				
				saveSection: function() {
					
					var eDialog = this.$el;
					
					this.setModelValues( this.current.model );
					
					// edit meta-data values
					this.localDispatcher.trigger( 'editMetaData' );
					
					
					eDialog.dialog( 'close' );
				},
				
				cancel: function() {
					var eDialog = this.$el;
					eDialog.dialog( 'close' );
				}
				
			}
			
		}
		
	} ) );
	
	var _family = Geko.Wp.Form.Section.Manage;
	
	
	
	// section controls
	
	Geko.setNamespace( 'Wp.Form.Section.Manage.Controls', Backstab.View.extend( {

		events: {
			'click a.geko-form-edit-section': 'openDialog',
			'click a.geko-form-remove-section': 'confirmRemove'
		},
		
		initialize: function() {
			
			var _this = this;
			
			var eAddItemTemplate = this.$( '.geko-form-add-item' );
			
			var oItemTypes = _family.data.itemTypes;
			
			oItemTypes.each( function( oItemType ) {
				
				var oAddWidgetControl = new Geko.Wp.Form.ItemType.Manage.AddWidgetControl( {
					el: eAddItemTemplate.clone(),
					model: oItemType,
					data: {
						itemDispatcher: _this.data.itemDispatcher,
						addEvent: 'openAddItem',
						sectionItems: _this.data.sectionItems,
						section: _this.model
					}
				} );
				
				_this.$( 'span.spacer' ).before( oAddWidgetControl.render().$el );
				
			} );
			
			eAddItemTemplate.remove();
			
		},
		
		openDialog: function() {
			this.data.sectionDispatcher.trigger( 'openEditSection', this.model );
			return false;
		},
		
		confirmRemove: function() {
			
			if ( confirm( 'Are you sure you want to remove this section?' ) ) {
				this.model.destroy();
			}
			
			return false;
		}
		
	} ) );
			
	
	
	
} ).call( this );