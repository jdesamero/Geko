<?php

//
class Geko_Wp_Ext_Cart66_Hooks extends Geko_Wp_Admin_Hooks_PluginAbstract
{	
	
	protected $_aTabs = array(
		'checkout' => 'cart_checkout_settings',
		'gateways' => 'gateways_settings',
		'notifications' => 'notifications_settings'
	);
	
	
	
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
			
			$aTabs = $this->_aTabs;
			
			foreach ( $aTabs as $sKey => $sTabVal ) {
				if ( $sTabVal == $sTab ) {
					$aRet[] = sprintf( 'cart66_settings_%s', $sKey );
					break;
				}
			}
			
			return $aRet;
		}
		
		return FALSE;
	}
	
	
	//
	public function applyFilters( $sContent, $sState ) {
		
		$aTabs = array_keys( $this->_aTabs );
		
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



