( function() {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	
	
	//// item type
	
	Geko.setNamespace( 'Wp.Form.ItemType.Manage', Backstab.family( {
		
		name: 'item_type',
		
		model: {
			
			extend: {
				
				fields: Backstab.ModelFields[ 'form.item_type' ]
				
			}
			
		},
		
		itemView: {
			
			params: {
				
				updateElem: function() {
					
					// var oModel = this.model;				// this is "item type"
					
					var sKey = this.data.metaKey;
					
					
					if ( this.setMetaDataKey ) {
						
						this.setMetaDataKey( sKey );
						
					} else if ( this.widgetSelector ) {
						
						this.$( this.widgetSelector ).attr( 'id', sKey ).attr( 'name', sKey );
					}
					
				}
				
			},
			
			extend: {
				
				openAddValue: function( e ) {
					
					this.$( this.widgetSelector ).val( '' );
				},
				
				openEditValue: function( e, aValues ) {
					
					var sVal = '';
					
					if ( aValues && aValues[ 0 ] ) {
						sVal = aValues[ 0 ];
					}
					
					this.$( this.widgetSelector ).val( sVal );
				},
				
				getWidgetValue: function() {
					
					return [ this.$( this.widgetSelector ).val() ];
				}
				
			}
			
		}
		
	} ) );
	
	var _family = Geko.Wp.Form.ItemType.Manage;
	
	
	
	//// widget, a ui control such as text field, select, textarea, etc.
	
	Geko.setNamespace( 'Wp.Form.ItemType.Manage.Widget', Backstab.Hash.extend( {
		
		initialize: function( oOptions ) {
			
			// oOptions = this.params;
			
			var oItemType = oOptions.itemType;
			var oParams = oOptions.params;
			var ePanelWidget = oOptions.panelWidget;
			
			//// view constructor
			
			var oVcParams = {};
			
			if ( oParams && oParams.widgetSelector ) oVcParams.widgetSelector = oParams.widgetSelector;
			
			var fViewConstructor = Geko.Wp.Form.ItemType.Manage.ItemView.extend( oVcParams );
			
			
			//// panel view
			
			var oOptionsPanelView = new Geko.Wp.Form.ItemValue.Manage.OptionsPanel( {
				el: ePanelWidget,
				model: oItemType
			} );
			
			
			// assign to the factory hash
			this.viewConstructor = fViewConstructor;
			this.valueHandler = oOptionsPanelView;
			
		}
		
	} ) );
	
	
	//// widget factory, a singleton responsible for handling widgets (i.e.: form controls)
	
	
	// TEMPORARY!!! Fix this later...
	var oWidgetParams = {
		'text': { widgetSelector: '> input' },
		'textarea': { widgetSelector: '> textarea' },
		'checkbox': { widgetSelector: '> input' },
		'select': { widgetSelector: '> select' },
		'select_multi': { widgetSelector: '> select' }
	};
	
	
	//
	Geko.setNamespace( 'Wp.Form.ItemType.Manage.WidgetFactory', Backstab.Hash.extend( {
		
		member: Geko.Wp.Form.ItemType.Manage.Widget,
		
		initialize: function( oMembers, oOptions ) {
			
			var _this = this;
			
			// DOM container of widget templates
			var eTemplates = oOptions.templates;
			var oItemTypes = oOptions.itemTypes;
			
			
			this.eFields = eTemplates.find( '> div.fields' );
			this.eValues = eTemplates.find( '> div.values' );
			
			
			// initialize stuff
			
			oItemTypes.each( function( oItemType ) {
				_this.registerWidget( oItemType );
			} );
			
		},
		
		registerWidget: function( oItemType ) {
			
			var sItemType = oItemType.get( 'slug' );
			
			var sTypeSelector = '> div.%s'.printf( sItemType );
			
			this.set( sItemType, {
				itemType: oItemType,
				panelWidget: this.eValues.find( sTypeSelector ),
				params: oWidgetParams[ sItemType ]
			} );
		},
		
		make: function( oModel, oContextDispatcher, sMetaKey ) {
			
			// oModel is a meta-data item
			
			var sItemType = oModel.getItemTypeSlug();
			
			var sTypeSelector = '> div.%s'.printf( sItemType );
			var eWidget = this.eFields.find( sTypeSelector ).clone();
			eWidget.removeClass( sItemType );
			
			var oWidget = this.get( sItemType );
			
			//
			return new oWidget.viewConstructor( {
				el: eWidget,
				model: oModel,
				data: {
					contextDispatcher: oContextDispatcher,
					metaKey: sMetaKey
				}
			} );
		},
		
		
		loadOptionsPanel: function( ePanel, oItemType, oItem ) {
		
			var sItemType = oItemType.get( 'slug' );
			
			var eValues = this.eValues;
			
			// take what's in the panel, if any, and put into values
			var eCurVal = ePanel.find( '> *' );
			if ( eCurVal.length ) {
				eValues.append( eCurVal );
			}
			
			var sTypeSelector = '> div.%s'.printf( sItemType );
			
			eCurVal = eValues.find( sTypeSelector );
			ePanel.append( eCurVal );
			
			// trigger loadPanel on value handler, if it exists
			var oValueHandler = this.get( sItemType ).valueHandler;
			oValueHandler.trigger( 'loadPanel', oItemType, oItem );
			
		},
		
		commitValues: function( oItem ) {
			
			var sItemType = oItem.getItemTypeSlug();
			
			// trigger loadPanel on value handler, if it exists
			var oValueHandler = this.get( sItemType ).valueHandler;
			oValueHandler.trigger( 'commitValues', oItem );
			
		}
		
		
	} ) );
	
	
	
	
	//// widget controls view (add new items/meta-data)
	
	Geko.setNamespace( 'Wp.Form.ItemType.Manage.AddWidgetControl', Backstab.View.extend( {
		
		events: {
			'click :first': 'openAddDialog'
		},
		
		initialize: function() {
			
			var oModel = this.model;
			
			this.$( 'span' ).addClass( 'geko-form-icon-%s'.printf( oModel.get( 'slug' ) ) );
			this.$el.attr(
				'title', 'Add "%s" question.'.printf( oModel.get( 'name' ) )
			);
			
		},
		
		openAddDialog: function( e ) {
			
			// remember, this.model is an ItemType
			
			this.data.itemDispatcher.trigger( this.data.addEvent, this.model, this.data );
			
			return false;
		}
		
	} ) );
	
	
	
	//// view to manage item options
	
	Geko.setNamespace( 'Wp.Form.ItemType.Manage.ItemOptionsPanel', Backstab.View.extend( {
		
		localDispatcher: new Backstab.Dispatcher(),
		
		events: {
			'localDispatcher:loadPanel this': 'loadPanel',
			'localDispatcher:commitValues this': 'commitValues'
		},
		
		loadPanel: function( e, oItemType, oItem ) {
			
			var oWidgetFactory = _family.data.widgetFactory;
			oWidgetFactory.loadOptionsPanel( this.$( 'div.value_options' ), oItemType, oItem );
			
		},
		
		commitValues: function( e, oItem ) {
			
			var oWidgetFactory = _family.data.widgetFactory;
			oWidgetFactory.commitValues( oItem );
			
		}
		
		
	} ) );
	
	
	
} ).call( this );