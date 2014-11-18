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
	
	
	
	//// actual widget views
	
	var oWidgets = {};
	var GwfItv = Geko.Wp.Form.ItemType.Manage.ItemView;
	
	// <input type="text" />
	oWidgets[ 'text' ] = GwfItv.extend( {
		
		widgetSelector: '> input'
		
	} );
	
	// <textarea></textarea>
	oWidgets[ 'textarea' ] = GwfItv.extend( {
		
		widgetSelector: '> textarea'
		
	} );
	
	// <input type="radio" /> ...
	oWidgets[ 'radio' ] = GwfItv.extend( {
	
	} );
	
	// <input type="checkbox" />
	oWidgets[ 'checkbox' ] = GwfItv.extend( {
	
	} );
	
	// <input type="checkbox" /> ...
	oWidgets[ 'checkbox_multi' ] = GwfItv.extend( {
	
	} );
	
	// <select></select>
	oWidgets[ 'select' ] = GwfItv.extend( {
		
		widgetSelector: '> select'
		
	} );
	
	// <select multiple="multiple"></select>
	oWidgets[ 'select_multi' ] = GwfItv.extend( {
		
		widgetSelector: '> select'
		
	} );
	
	// default widget
	oWidgets[ 'default' ] = oWidgets[ 'text' ];
	
	
	// namespace for widget views
	Geko.Wp.Form.ItemType.Manage.Widgets = oWidgets;
	
	
	
	
	
	//// widget factory, a pseudo-view that generates widgets
	
	Geko.setNamespace( 'Wp.Form.ItemType.Manage.WidgetFactory', Backstab.View.extend( {
		
		make: function( oModel, oContextDispatcher, sMetaKey ) {
			
			// oModel is a meta-data item
			
			var sItemType = oModel.getItemTypeSlug();
			
			var sTypeSelector = '> .fields > div.%s'.printf( sItemType );
			var eWidget = this.$( sTypeSelector ).clone();
			eWidget.removeClass( sItemType );
			
			var WidgetView = oWidgets[ sItemType ];
			if ( !WidgetView ) {
				var WidgetView = oWidgets[ 'default' ];		
			}
			
			return new WidgetView( {
				el: eWidget,
				model: oModel,
				data: {
					contextDispatcher: oContextDispatcher,
					metaKey: sMetaKey
				}
			} );
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
	
	
	
} ).call( this );