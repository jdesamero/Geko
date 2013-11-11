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
			$sContent = $this->replace(
				$sContent,
				'admin_cart66_settings_gateways_form_pq',
				'/<form id="gatewaySettingsForm" action="" method="post" class="ajaxSettingForm".+?<\/form>/s'
			);
		}
		
		return $sContent;
	}
	
}



