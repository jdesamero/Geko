;( function ( $ ) {

	var ItemView = Backstab.View.extend( {
		
		params: {
			delay: 200,
			fadeDelay: 500,
			msgPause: 3000
		},
		
		events: {
			'mouseover a.toggle-link': 'showBuy',
			'click a.toggle-close': 'hideBuy',
			'click div.Cart66ButtonPrimary': 'addToCart'
		},
		
		$buy: null,
		
		initialize: function() {
			
			this.$buy = this.$( 'div.toggle-content' );
			this.$buy.hide();
			
			this.$add = this.$( 'div.Cart66ButtonPrimary' );
		},
		
		showBuy: function() {
			
			var _this = this;
			var myContNode = this.$buy.get( 0 );
			
			// close other buy panels if shown
			this.options.prods.each( function() {
				
				var otherLi = $( this );
				var otherBuy = otherLi.find( 'div.toggle-content' );
				var otherContNode = otherBuy.get( 0 );
				
				if ( !otherBuy.is( ':hidden' ) && ( myContNode !== otherContNode ) ) {
					otherBuy.slideUp( _this.params.delay );
				}
				
			} );
			
			this.$buy.slideDown( this.params.delay );
			
		},
		
		hideBuy: function() {
			
			this.$buy.slideUp( this.params.delay );
			
			return false;
		},
		
		addToCart: function() {
			
			var _this = this;
			
			var prodUrl = this.$( 'h3 > a' ).attr( 'href' );
			
			var opt = this.$( 'select option:selected' );
			
			var prodName = opt.attr( 'data-prodname' );
			var prodId = opt.val();
			
			var qty = this.$( 'span.Cart66UserQuantity input' ).val();
			
			if ( prodId ) {
				
				this.$add.html( 'Adding...' );
				
				$.post(
					this.options.script.url + '/?cart66AjaxCartRequests=2',
					{
						'cart66ItemId': prodId,
						'itemName': prodName,
						'item_quantity': qty,
						'product_url': prodUrl
					},
					function( data ) {
						
						_this.$add.html( 'Add to Cart' );
						
						var p = $( data.msg );
						p.hide();
						
						_this.$( '.inner' ).append( p );
						
						p.fadeIn( _this.params.fadeDelay, function() {
							setTimeout( function() {
								p.fadeOut( _this.params.fadeDelay, function() {
									p.remove();
								} );
							}, _this.params.msgPause );
						} );
						
					},
					'json'
				);
				
			} else {
				alert( 'Please select a product!' );
			}
		}
		
	} );
	
	
	$.gekoWpExtCart66 = function( oParams ) {
		
		if ( oParams.addToCart ) {
			
			var atcParams = oParams.addToCart;
			
			if ( true == atcParams.product_group ) {
			
				var prods = $( 'li.products' );
				
				prods.each( function() {
					
					var li = $( this );
					
					var itemView = new ItemView( {
						el: li,
						prods: prods,
						script: oParams.script
					} );
					
				} );
			}		
		}
		
	};

} )( jQuery );
