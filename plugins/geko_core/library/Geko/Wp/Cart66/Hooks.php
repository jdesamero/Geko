<?php

//
class Geko_Wp_Cart66_Hooks extends Geko_Wp_Admin_Hooks_PluginAbstract
{	
	
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();

		if (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/admin.php' ) ) && 
			( 'cart66-settings' == $oUrl->getVar( 'page' ) )
		) {
			
			$aRet[] = 'cart66_settings';
			
			$sTab = $oUrl->getVar( 'tab' );
			
			if ( 'gateways_settings' == $sTab ) {
				$aRet[] = 'cart66_settings_gateways';
			} elseif ( 'cart_checkout_settings' == $sTab ) {
				$aRet[] = 'cart66_settings_checkout';			
			}
			
			return $aRet;
		}
		
		return FALSE;
	}
	
	
	//
	public function applyFilters( $sContent, $sState ) {
		
		$aTabs = array( 'checkout', 'gateways' );
		
		foreach ( $aTabs as $sTab ) {
		
			if ( sprintf( 'cart66_settings_%s', $sTab ) == $sState ) {
				
				$aRegs = array();
				if ( preg_match( '/(<div id="cart66-inner-tabs">.+?)(<script type="text\/javascript">.+?<\/script>)/s', $sContent, $aRegs ) ) {
					
					$sPart1 = $this->replace(
						$aRegs[ 1 ],
						sprintf( 'admin_cart66_settings_%s_form_pq', $sTab ),
						'/<form.+?<\/form>/s'
					);
					
					$sPart2 = $this->replace(
						$aRegs[ 2 ],
						sprintf( 'admin_cart66_settings_%s_script_pq', $sTab ),
						'/<script.+?<\/script>/s'
					);
					
					$sContent = str_replace( $aRegs[ 1 ], $sPart1, $sContent );
					$sContent = str_replace( $aRegs[ 2 ], $sPart2, $sContent );
					
				}	
			}
		
		}
		
		return $sContent;
	}
	
}



