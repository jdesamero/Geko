;( function ( $ ) {
	
	$.fbDoAction = function ( options ) {
		
		// options
		var opts = $.extend( {
			perms: 'publish_stream',
			start: function() { },
			success: function( res ) { },
			loginfail: function( res ) { },
			end: function( res ) { }
		}, options );
		
		// initialize
		FB.init( {
			appId: opts.app_id,
			status: true,
			cookie: true,
			xfbml: true,
			oauth: true
		} );
		
		opts.start();
		
		FB.getLoginStatus( function( res ) {
			
			if ( res.authResponse ) {
				
				opts.success( res );
				opts.end( res );
				
			} else {

				FB.login(
					function( res ) {
						if ( res.authResponse ) {
							opts.success( res );
						} else {
							opts.loginfail( res );
						}
						opts.end( res );
					},
					{ perms: opts.perms }
				);
				
			}
			
		} );
		
	};
	
} )( jQuery );
