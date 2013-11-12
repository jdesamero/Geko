<?php

//
class Geko_Wp_Cart66_ShortcodeManager extends Cart66ShortcodeManager
{
	
	//
	public function beanstreamCheckout( $attrs ) {
		
		if ( Cart66Session::get( 'Cart66Cart' )->countItems() > 0 ) {
			
			$gatewayName = Cart66Common::postVal( 'cart66-gateway-name' );  
			
			if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && $gatewayName != 'Geko_Wp_Cart66_Gateway_Beanstream' ) {
				return ( $gatewayName == 'Cart66ManualGateway' ) ? $this->manualCheckout() : '';
			}
			
			
			
			//// START: CHECKOUT CODE
			
			if (
				Cart66Session::get( 'Cart66Cart' )->getGrandTotal() > 0 || 
				Cart66Session::get( 'Cart66Cart' )->hasSpreedlySubscriptions()
			) {
				
				try {
					
					$oBsGw = Geko_Wp_Cart66_Gateway_Beanstream::getInstance();
					$view = $this->_buildCheckoutView( $oBsGw );
					
				} catch( Cart66Exception $e ) {
					
					$exception = Cart66Exception::exceptionMessages( $e->getCode(), $e->getMessage() );
					$view = Cart66Common::getView( 'views/error-messages.php', $exception );
				}
				
				return $view;
				
			} elseif( Cart66Session::get( 'Cart66Cart' )->countItems() > 0 ) {
				
				Cart66Common::log( sprintf(
					'[%s - line %s] Displaying manual checkout instead of PayLeap Checkout because the cart value is $0.00',
					basename( __FILE__ ), __LINE__
				) );
				
				return $this->manualCheckout();
			}
			
			//// END: CHECKOUT CODE
			
		}
		
	}

}


/* /
			if ( !Cart66Session::get( 'Cart66Cart' )->hasPayPalSubscriptions() ) {
				
				//// RUN: CHECKOUT CODE
				
			} else {
				
				Cart66Common::log( sprintf(
					'[%s - line %s] Not rendering PayLeap checkout form because the cart contains a PayPal subscription',
					basename( __FILE__ ), __LINE__
				) );
				
			}
/* */


