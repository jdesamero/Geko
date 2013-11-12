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
			
			if ( 'gateways_settings' == $oUrl->getVar( 'tab' ) ) {
				$aRet[] = 'cart66_settings_gateways';
			}
			
			return $aRet;
		}
		
		return FALSE;
	}
	
	
	//
	public function applyFilters( $sContent, $sState ) {

		if ( 'cart66_settings_gateways' == $sState ) {
			
			$aRegs = array();
			if ( preg_match( '/(<div id="cart66-inner-tabs">.+?)(<script type="text\/javascript">.+?<\/script>)/s', $sContent, $aRegs ) ) {
				
				$sPart1 = $this->replace(
					$aRegs[ 1 ],
					'admin_cart66_settings_gateways_form_pq',
					'/<form.+?<\/form>/s'
				);
				
				$sPart2 = $this->replace(
					$aRegs[ 2 ],
					'admin_cart66_settings_gateways_script_pq',
					'/<script.+?<\/script>/s'
				);
				
				$sContent = str_replace( $aRegs[ 1 ], $sPart1, $sContent );
				$sContent = str_replace( $aRegs[ 2 ], $sPart2, $sContent );
				
			}
			
			
		}
		
		
		return $sContent;
	}
	
}



