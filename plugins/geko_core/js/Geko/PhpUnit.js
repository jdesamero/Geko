;( function ( $ ) {
	
	$.gekoPhpUnit = function( options ) {
		
		var opts = $.extend( {
			
		}, options );

		if ( 'regular' == opts.mode ) {

			var jlul = $( 'ul.geko_phpunit.jumplinks' );
			
			var resCols = {
				'not_implemented': { title: 'Not Implemented' },
				'skipped': { title: 'Skipped' },
				'failure': { title: 'Failures' },
				'error': { title: 'Errors' }
			};
			
			$( 'div.geko_phpunit.container' ).each( function() { 
				
				var cont = $( this );
				var h2 = cont.find( 'h2' );
				var jlname = h2.find( 'a' ).attr( 'name' );
				var msg = cont.find( 'table.geko_phpunit.messages' );
				var res = cont.find( 'table.geko_phpunit.results' );
				
				var a = $( '<a href="#" class="details">Show Details<\/a>' );
				
				h2.after( '<br clear="all"/>' );
				h2.after( a );
				
				a.on( 'click', function() {
					if ( msg.is( ':hidden' ) ) {
						a.html( 'Hide Details' );
						msg.show();
					} else {
						a.html( 'Show Details' );
						msg.hide();					
					}
					return false;
				} );
				
				$.each( resCols, function( k, v ) {
					var err = res.find( 'td.' + k + '_count.error' );
					if ( err.length > 0 ) {
						var li = jlul.find( 'a[href="#' + jlname + '"]' ).parent();
						li.append( ' <span class="' + k + '">(' + v.title + ': ' + parseInt( err.html() ) + ')<\/span>' );
					}
				} );
				
			} );
			
		} else if ( 'compact' == opts.mode ) {
			
			$( 'table.geko_phpunit.results th.test' ).after( '<th>Details<\/th>' );
			
			$( 'table.geko_phpunit.results tr.results' ).each( function() {
				
				var tr = $( this );
				var a = $( '<a href="#" class="details">Show<\/a>' );
				var showTd = $( '<td><\/td>' );
				var resTr = tr.next();
				var resTd = resTr.find( '> td' );
				
				var colSpan = parseInt( resTd.attr( 'colspan' ) );
				resTd.attr( 'colspan', colSpan + 1 );
				
				showTd.append( a );
				tr.find( 'td.test' ).after( showTd );
				
				a.on( 'click', function() {
					if ( resTr.is( ':hidden' ) ) {
						a.html( 'Hide' );
						resTr.show();
					} else {
						a.html( 'Show' );
						resTr.hide();					
					}
					return false;
				} );
				
			} );
			
		}
		
		return this;
	};
	
} )( jQuery );