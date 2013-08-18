;( function ( $ ) {
	
	$.fn.gekoRowTemplate = function( options ) {

		var opts = $.extend( {
			group_name: 'some_group',
			row_container_sel: '> .row_container',			// immediate child of rowContainerElem
			row_sel: '> .row',								// immediate child(ren) of <row_container_sel>
			row_template_sel: '> ._row_template',			// immediate child of <row_container_sel>
			add_row_sel: '> .add_row',						// immediate child of rowContainerElem
			del_row_sel: '> .del_row',						// immediate child of <row_container_sel> (to include existing items),
			remove_func: function( elem ) { return elem.parent().parent(); },
			remove_event: null,								// if defined, bind a custom event to be triggered
			load_data: [],									// if defined, use data to populate
			submit_func: function( tmpl ) { tmpl.remove() }
		}, options );
		
		//// assignments
		
		var elemNameRgx = /\[([a-zA-Z0-9_-]+)\]$/;
		
		// iterate
		return this.each( function() {
			
			var elem = $( this );		// main container element
			var rowContainerElem = elem.find( opts.row_container_sel );
			
			var formElem = rowContainerElem.closest( 'form' );
			var addRowElem = $( this ).find( opts.add_row_sel );
			var rowTemplateElem = rowContainerElem.find( opts.row_template_sel );
			
			
			//// do stuff
			
			var updateRowTemplate = function() {
				if ( rowContainerElem.find( opts.row_sel ).length > 1 ) {
					// if rows other than the row template is present, then show the row container
					rowContainerElem.show();	
				} else {
					// if only the row template is left, then hide the row container
					rowContainerElem.hide();
				}				
			}
			
			var cloneRow = function( evt, data ) {
				
				var counter = rowContainerElem.data( 'row_template_counter' );
				
				// _row_template class assigned server side by Geko_PhpQuery_FormTransform_Plugin_RowTemplate
				rowTemplateElem.before(
					rowTemplateElem.clone( true ).css( 'display', '' ).removeClass( '_row_template' )
				);
				
				var cloned = rowTemplateElem.prev();
				cloned.find( 'input, select, textarea' ).each( function() {
					
					if ( $( this ).attr( 'id' ) ) {
						var id = $( this ).attr( 'id' );
						$( this ).attr( 'id', id.replace( opts.group_name + '[]', opts.group_name + '[_' + counter + ']' ) );
					}
					
					if ( $( this ).attr( 'name' ) ) {
						
						var name = $( this ).attr( 'name' );
						$( this ).attr( 'name', name.replace( opts.group_name + '[]', opts.group_name + '[_' + counter + ']' ) );
						
						if ( data ) {
							var match = elemNameRgx.exec( name );
							if ( match && data[ match[ 1 ] ] ) {
								$( this ).val( data[ match[ 1 ] ] );
							}
						}
					}
					
				} );
				
				counter++;
				
				rowContainerElem.data( 'row_template_counter', counter );
				updateRowTemplate();
				
			};
			
			updateRowTemplate();
			
			////
			
			rowContainerElem.data( 'row_template_counter', 0 );
			
			rowContainerElem.find( opts.del_row_sel ).each( function() {
				
				$( this ).click( function() {
					opts.remove_func.call( elem, $( this ) ).remove();
					updateRowTemplate();
					return false;
				} );
				
			} );
			
			rowTemplateElem.hide();
			
			addRowElem.click( cloneRow );
			

			// remove the row template when submitting so it's not processed
			if ( opts.remove_event ) {
				
				formElem.bind( opts.remove_event, function() {
					opts.submit_func.call( elem, rowTemplateElem );
				} );
				
			} else {
				formElem.submit( function() {
					opts.submit_func.call( elem, rowTemplateElem );
				} );
			}
			
			//// load data
			$.each( opts.load_data, function( i, v ) {
				addRowElem.trigger( 'click', [ v ] );
			} );
						
		} );
		
		
	};
	
	
} )( jQuery );

