;( function ( $ ) {
	
	$.fn.imagePicker = function( options ) {

		var opts = $.extend( {
			sel_class: 'ip_selected',
			not_sel_class: 'ip_not_selected',
			multiple: false,
			multi_class: 'multi',							// make it a multiple select if main element has this class
			field_id: '',									// corresponds to hidden field
			field_class: 'imgpck_field',					// class of hidden field
			use_id: false,
			id_prefix: '',
			modal_func: function( link, wrap ) { }
		}, options );
		
		// var bwdt = opts.width;
		// var bhgt = opts.height;
		
		return this.each( function() {
			
			// main element
			var picker = $( this );
			var bPickMode = false;
			
			// add wrapper div
			picker.wrap( '<div class="imgpck_wrap"><\/div>' );
			var wrap = picker.parent();
			
			// add toggle
			picker.after( '<a href="#" class="imgpck_toggle imgpck_tgl_off">Pick Image<\/a><span class="imgpck_sel_btns"> | Select: <a href="#" class="imgpck_sel_all">All<\/a>, <a href="#" class="imgpck_sel_none">None<\/a>, <a href="#" class="imgpck_sel_inv">Inverse<\/a><\/span>' );
			wrap.find( '.imgpck_sel_btns' ).hide();
			
			// multiple flag
			var multiple = opts.multiple;
			if ( picker.hasClass( opts.multi_class ) ) {
				multiple = true;
			}
			
			// hidden field where info is saved
			var field;
			if ( opts.field_id ) {
				field = picker.find( '#' + opts.field_id );
			} else {
				field = picker.find( '.' + opts.field_class );
			}
			
			var statusData = [];
			if ( field.val() ) statusData = $.parseJSON( field.val() );
			
			var getValue = function( link ) {
				
				var att = '';
				if ( opts.use_id ) {
					// use the id for matching
					att = link.attr( 'id' );
					if ( opts.id_prefix ) {
						// remove prefix
						att = att.replace( opts.id_prefix, '' );
					}
				} else {
					// use the href for matching
					att = link.attr( 'href' );
				}
				
				return att;
			}
			
			//// setup
			
			picker.find( 'a' ).each( function() {
				
				var link = $( this );
				
				if ( -1 != $.inArray( getValue( link ), statusData ) ) {
					link.addClass( 'imgpck_on' );
				} else {
					link.addClass( 'imgpck_off' );
					link.hide();
				}
				
			} );
			
			//// add functionality
			
			// click on an image
			picker.find( 'a' ).click( function() {
				
				var link = $( this );
				var img = link.find( 'img' );
				
				if ( !bPickMode ) {
					
					opts.modal_func( link, wrap );
					
				} else {
					
					if ( multiple ) {
						
						// multiple item picker mode
						
						if ( img.hasClass( opts.sel_class ) ) {
							img.removeClass( opts.sel_class ).addClass( opts.not_sel_class );
							var data = [];
							$.each( statusData, function( i, v ) {
								if ( v != getValue( link ) ) data.push( v );
							} );
							statusData = data;
						} else {
							img.removeClass( opts.not_sel_class ).addClass( opts.sel_class );					
							statusData.push( getValue( link ) );
						}
						
						field.val( $.toJSON( statusData ) );
						
					} else {
						
						// single item picker mode
						
						var selected = img.hasClass( opts.sel_class );
						picker.find( 'img.' + opts.sel_class ).removeClass( opts.sel_class ).addClass( opts.not_sel_class );
						
						// reset
						var statusData2 = [];
						
						if ( !selected ) {
							img.addClass( opts.sel_class );
							statusData2.push( getValue( link ) );
						}
						
						field.val( $.toJSON( statusData2 ) );
						
					}
				
				}
				
				return false;
				
			} ).dblclick( function() {
				
				var link = $( this );
				window.open( link.attr( 'href' ) );
				
				return false;
				
			} );
			
			// pick toggle
			wrap.find( '.imgpck_toggle' ).click( function() {
				if ( !bPickMode ) {
					
					$( this ).removeClass( 'imgpck_tgl_off' ).addClass( 'imgpck_tgl_on' ).html( 'Done' );
					
					// go into picker mode
					picker.find( 'a' ).each( function() {
						var link = $( this );
						var img = link.find( 'img' );
						if ( link.hasClass( 'imgpck_on' ) ) {
							img.addClass( opts.sel_class );
							link.removeClass( 'imgpck_on' );
						} else {
							img.addClass( opts.not_sel_class );
							link.removeClass( 'imgpck_off' );
							link.show();
						}
					} );
					
					wrap.find( '.imgpck_sel_btns' ).show();
					
					bPickMode = true;
					
				} else {
					
					$( this ).removeClass( 'imgpck_tgl_on' ).addClass( 'imgpck_tgl_off' ).html( 'Pick Image' );
					
					// go into display mode
					picker.find( 'a' ).each( function() {
						var link = $( this );
						var img = link.find( 'img' );
						if ( img.hasClass( opts.sel_class ) ) {
							link.addClass( 'imgpck_on' );
							img.removeClass( opts.sel_class );
						} else {
							link.addClass( 'imgpck_off' );
							img.removeClass( opts.not_sel_class );
							link.hide();
						}
					} );

					wrap.find( '.imgpck_sel_btns' ).hide();
					
					bPickMode = false;
					
				}
				
				return false;
			} );
			
			// select all
			wrap.find( '.imgpck_sel_all' ).click( function() {
				
				picker.find( 'a' ).each( function() {
					var link = $( this );
					var img = link.find( 'img' );
					if ( img.hasClass( opts.not_sel_class ) ) {
						link.click();
					}
				} );
				
				return false;
				
			} );
			
			// select none
			wrap.find( '.imgpck_sel_none' ).click( function() {
				
				picker.find( 'a' ).each( function() {
					var link = $( this );
					var img = link.find( 'img' );
					if ( img.hasClass( opts.sel_class ) ) {
						link.click();
					}
				} );
				
				return false;
				
			} );
			
			// select inverse
			wrap.find( '.imgpck_sel_inv' ).click( function() {
				
				picker.find( 'a' ).each( function() {
					$( this ).click();
				} );
				
				return false;
				
			} );
			
		} );
	};
	
} )( jQuery );


