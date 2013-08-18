;( function ( $ ) {
	
	$.gekoWpEmailMessageRecipientManage = function( options ) {
		
		var opts = $.extend( {
			
		}, options );
		
		$( '.recipients_send_test' ).click(function() {
			
			var tr = $( this ).parent().parent();
			var rcpt_name = tr.find( 'input.recipient_name' ).val();
			var rcpt_email = tr.find( 'input.recipient_email' ).val();
			var rcpt = rcpt_name + ' &lt;' + rcpt_email + '&gt;';
			
			var elem = $( '<div class="updated fade below-h2"><p>Sending test message to: ' + rcpt + '...<\/p><\/div>' );
			$( '#editform' ).before( elem );
			$.getJSON(
				opts.test_link + '&name=' + encodeURIComponent( rcpt_name ) + '&email=' + encodeURIComponent( rcpt_email ),
				function( data, textStatus ) {
					
					var msg = '';
					if ( 1 == data.status ) {
						msg = 'Success!';
					} else {
						msg = 'Failed!';						
					}
					
					var html = elem.find( 'p' ).html();
					elem.find( 'p' ).html( html + ' <strong>' + msg + '<\/strong>' );
					
					elem.stop().css( 'background-color', '#FFFFE0' ).animate( { backgroundColor: '#FFFBCC' }, 1500 );
					setTimeout(
						function () { elem.fadeOut( 400 ); },
						2000
					);					
				}
			);
			
			return false;
			
		} );
		
	};
	
} )( jQuery );