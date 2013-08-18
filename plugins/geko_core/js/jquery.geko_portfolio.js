;(function ($) {
	
	//
	$.gekoPortfolio = function() {
		
		//
		var MainState = function() {
			
			// properties
			this.isExpanded = false;
			// this.isWithRightLink = true;
			// this.isWithLeftLink = true;
			
			
			this.exitUrl = null;
			
			this.item = null;
			this.prevItem = null;
			
			this.subItem = null;
			this.prevSubItem = null;
			
			this.subItems = [];
			
			
			
			// track all updateable elements
			this.updateables = jQuery('.updateable, .updateable_children a');
			
			
			// methods
				
			//
			this.setExpanded = function( value ) {
				this.isExpanded = value;
				return this;
			}
			
			//
			this.getExpanded = function() {
				return this.isExpanded;
			}
			
			//
			this.toggleExpanded = function() {
				this.isExpanded = ( false == this.isExpanded ) ? true : false;
				return this;
			}
			
			
			
			
			//
			this.setItem = function( value ) {
				this.prevItem = this.item;
				this.item = value;
				return this;
			}
			
			//
			this.getItem = function() {
				return this.item;
			}
		
			//
			this.getPrevItem = function() {
				return this.prevItem;
			}
			
			//
			this.unsetItem = function() {
				
				this.item = null;
				this.prevItem = null;
				
				this.subItem = null;
				this.prevSubItem = null;
				
				this.subItems = [];
				
				return this;
			}
			
			
			
			//
			this.getSubItemCurIdx = function() {
				return jQuery.inArray( this.subItem, this.subItems );
			}
			
			
			//
			this.setSubItem = function( value ) {
				this.prevSubItem = this.subItem;
				this.subItem = value;
				return this;
			}
			
			//
			this.getSubItem = function() {
				return this.subItem;
			}
			
			//
			this.getPrevSubItem = function() {
				return this.prevSubItem;
			}
			
			//
			this.setNextSubItem = function() {
				var idx = this.getSubItemCurIdx() + 1;
				this.setSubItem( this.subItems[idx] );
				return this;
			}
			
			//
			this.setPrevSubItem = function() {
				var idx = this.getSubItemCurIdx() - 1;
				this.setSubItem( this.subItems[idx] );
				return this;
			}	
			
			
			
			//
			this.setExitUrl = function( value ) {
				this.exitUrl = value;
				return this;
			}
		
			//
			this.getExitUrl = function( value ) {
				return this.exitUrl;
			}
			
			
			
			//
			this.getWithRightLink = function() {
				return ( this.getSubItemCurIdx() < ( jQuery( this.subItems ).size() - 1 ) );
			}
			
			//
			this.getWithLeftLink = function() {
				return ( this.getSubItemCurIdx() > 0 );
			}
			
			
			//
			this.setSubItems = function( value ) {
				this.subItems = value;
				this.setSubItem( this.subItems[0] );
				return this;
			}
			
			
			//
			this.update = function() {
				this.updateables.trigger( 'update' );
			}
			
			
			
			/* /
			this.update = function() {
				alert('foo');
			}
			/* */
			
			// alert('foo');
			
		}
		
		//
		var LinkTransition = function() {
			
			// pseudo constants
			this.CONTRACTED = 0;
			this.EXPANDING = 1;
			this.FADING_IN = 2;
			this.EXPANDED = 3;
			this.FADING_OUT = 4;
			this.CONTRACTING = 5;
			
			// properties
			this.currentState = this.CONTRACTED;
			
			this.getCurrentState = function() {
				return this.currentState;
			}
			
			//
			this.isContracted = function() {
				return ( this.currentState == this.CONTRACTED );
			}
			
			//
			this.setContracted = function() {
				this.currentState = this.CONTRACTED;
				return this;
			}
		
			//
			this.isExpanding = function() {
				return ( this.currentState == this.EXPANDING );
			}
			
			//
			this.setExpanding = function() {
				this.currentState = this.EXPANDING;
				return this;
			}
		
			//
			this.isFadingIn = function() {
				return ( this.currentState == this.FADING_IN );
			}
		
			//
			this.setFadingIn = function() {
				this.currentState = this.FADING_IN;
				return this;
			}
		
			//
			this.isExpanded = function() {
				return ( this.currentState == this.EXPANDED );
			}
		
			//
			this.setExpanded = function() {
				this.currentState = this.EXPANDED;
				return this;
			}
		
			//
			this.isFadingOut = function() {
				return ( this.currentState == this.FADING_OUT );
			}
		
			//
			this.setFadingOut = function() {
				this.currentState = this.FADING_OUT;
				return this;
			}
		
			//
			this.isContracting = function() {
				return ( this.currentState == this.CONTRACTING );
			}
			
			//
			this.setContracting = function() {
				this.currentState = this.CONTRACTING;
				return this;
			}
			
			
			//
			this.isStopped = function() {
				return ( ( this.currentState == this.EXPANDED ) || ( this.currentState == this.CONTRACTED ) );
			}
			
			//
			this.setNextLinkState = function() {
				this.currentState = ( this.isContracted() ) ? this.EXPANDING : this.FADING_IN;
				return this;
			}
			
		}
		
		
		//// setup vars
		
		var oState = new MainState;
		var oLinkTrans = new LinkTransition;

		var iCitemCount = $('#main .bottom .inner .c_item').length;

		var parseItemId = function( elem ) {
			return $(elem).attr( 'id' ).replace(
				'b_', ''
			).replace(
				'c_', ''			
			).replace(
				'si_', ''			
			);
		}
				
		var getSubItems = function( item ) {
			var subItems = [];
			$( '#main .content .s_item.s_' + item ).each( function () {
				subItems.push( $(this).attr( 'id' ).replace( 'si_', '' ) );
			} );
			return subItems;
		}
		
		
		
		
		
		
				
		
		//// set actions and behaviors
		
		// main navigation links
		$('#main .logo a, #main .navigation a').click( function () {
			if ( oLinkTrans.isExpanded() ) {
				oLinkTrans.setFadingOut();
			}
			oState.setExitUrl( $(this).attr('href') ).update();
			return false;
		} );
		
		//
		$('body').bind( 'update', function() {
			if ( oLinkTrans.isContracted() ) {
				var eurl = oState.getExitUrl();
				if ( ( null != eurl ) && ( '#' != eurl ) && ( '' != eurl ) ) {
					window.location.href = oState.getExitUrl();
				}
			}
		} );
		
		
		// item links
		$('#main .bottom .inner .nav_l2 a').click( function() {
			if ( oLinkTrans.isStopped() ) {
				var itemName = parseItemId( this );
				if ( ( oState.getItem() == itemName ) && oLinkTrans.isExpanded() ) {
					oLinkTrans.setFadingOut();
					oState.update();			
				} else {
					oLinkTrans.setNextLinkState();
					oState.setItem( itemName ).setSubItems( getSubItems( itemName ) ).update();
				}
			}
		} ).bind( 'update', function() {
			if ( oLinkTrans.isExpanded() ) {
				if ( oState.getItem() == parseItemId( this ) ) {
					$(this).addClass( 'current' );
				} else if ( oState.getPrevItem() == parseItemId( this ) ) {
					$(this).removeClass( 'current' );
				}
			} else if ( oLinkTrans.isContracted() ) {
				if ( oState.getItem() == parseItemId( this ) ) {
					$(this).removeClass( 'current' );
					oState.unsetItem();
				}
			}
		} );
		
		
		// sub-item links
		$('#main .bottom .inner .nav_l3 a').click( function() {
			if ( oLinkTrans.isExpanded() ) {
				oLinkTrans.setFadingIn();
				oState.setSubItem( parseItemId( this ) ).update();
			}
		} ).bind( 'update', function() {
			if ( oLinkTrans.isFadingIn() ) {
				if ( oState.getSubItem() == parseItemId( this ) ) {
					$(this).addClass( 'current' );
				} else {
					$(this).removeClass( 'current' );
				}
			} else if ( oLinkTrans.isFadingOut() ) {
				if ( oState.getSubItem() == parseItemId( this ) ) {
					$(this).removeClass( 'current' );
				}
			}
		} );
		
		
		
		// content pop-over
		$('#main .content').bind( 'update', function() {
			if ( oLinkTrans.isExpanding() ) {
				$(this).slideDown( 1500, function() {
					oLinkTrans.setFadingIn();
					oState.update();
				} );
			} else if ( oLinkTrans.isContracting() ) {
				$(this).slideUp( 1500, function() {
					oLinkTrans.setContracted();
					oState.update();
				} );
			}
		} );
		
		// content items
		$('#main .content .s_item').bind( 'update', function() {
			if ( oLinkTrans.isFadingIn() ) {
				if ( oState.getSubItem() == parseItemId( this ) ) {
					$(this).fadeIn( 1500, function() {
						oLinkTrans.setExpanded();
						oState.update();
					} );
				} else {
					$(this).fadeOut( 1500 );
				}				
			} else if ( oLinkTrans.isFadingOut() ) {
				if ( oState.getSubItem() == parseItemId( this ) ) {
					$(this).fadeOut( 1500, function() {
						oLinkTrans.setContracting();
						oState.update();
					} );
				}				
			}
		} );

				
		// bottom inner items
		$('#main .bottom .inner .c_item').bind( 'update', function() {
			if ( iCitemCount > 1 ) {
				if ( oLinkTrans.isFadingIn() ) {
					if ( oState.getItem() == parseItemId( this ) ) {
						$(this).fadeIn( 1500 );
					} else {
						$(this).fadeOut( 1500 );
					}				
				} else if ( oLinkTrans.isFadingOut() ) {
					if ( oState.getItem() == parseItemId( this ) ) {
						$(this).fadeOut( 1500 );
					}			
				}
			}
		} );
		
		
		//// left/right arrows
		
		$('#nav_right').click( function() {
			if ( oLinkTrans.isExpanded() ) {
				oLinkTrans.setFadingIn();
				oState.setNextSubItem().update();
			}
		} ).bind( 'update', function() {
			if ( oLinkTrans.isFadingIn() ) {
				if ( true == oState.getWithRightLink() ) {
					$(this).fadeIn( 1500 );
				} else {
					$(this).fadeOut( 1500 );					
				}
			} else if ( oLinkTrans.isFadingOut() ) {
				$(this).fadeOut( 1500 );					
			}
		} );
		
		$('#nav_left').click( function() {
			if ( oLinkTrans.isExpanded() ) {
				oLinkTrans.setFadingIn();
				oState.setPrevSubItem().update();
			}
		} ).bind( 'update', function() {
			if ( oLinkTrans.isFadingIn() ) {
				if ( true == oState.getWithLeftLink() ) {
					$(this).fadeIn( 1500 );
				} else {
					$(this).fadeOut( 1500 );
				}
			} else if ( oLinkTrans.isFadingOut() ) {
				$(this).fadeOut( 1500 );
			}
		} );
		
		
		// set initial state
		$('#main .content, #main .content .s_item, #main .content .s_nav, #main .bottom .inner .c_item').hide();
		$('#main .bottom .inner #c_default').show();
		
	};
	
})(jQuery);


