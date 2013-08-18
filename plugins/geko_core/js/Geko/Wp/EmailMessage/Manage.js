;( function ( $ ) {
	
	$.gekoWpEmailMessageManage = function( options ) {
		
		var opts = $.extend( {
			// ...
		}, options );
		
		
		
		//// other operations
		
		
		// view bounces
		
		var bounceDlg = $( '#view_bounces' );
		
		bounceDlg.dialog( {
			title: 'View Bounces',
			autoOpen: false,
			modal: true,
			buttons: {
				Close: function() {
					var dialog = $( this );
					dialog.dialog( 'close' );
				}
			},
			width: 800,
			height: 400
		} );
		
		$( '#view_bounces_btn' ).click( function() {
			
			// reset
			bounceDlg.find( '.inner' ).html( '<p class="text">Loading bounced invites... <span class="loading"><\/span><\/p>' );
			
			// open dialog
			bounceDlg.dialog( 'open' );
			
			$.get( opts.bounces_link, function( data ) {
				
				bounceDlg.find( '.inner' ).html( data );
				
				// add functionality
				bounceDlg.find( 'td.exp a' ).click( function() {
					
					var a = $( this );
					var tr = a.closest( 'tr' );
					var tr2 = bounceDlg.find( '#' + tr.attr( 'id' ).replace( 'r1_', 'r2_' ) );
					
					if ( tr2.is( ':visible' ) ) {
						a.html( 'Show' );
						tr2.hide();
					} else {
						a.html( 'Hide' );
						tr2.show();
					}
					
					return false;
				} );
				
			} );
			
			return false;
			
		} );		
		
		
		
	};
	
} )( jQuery );