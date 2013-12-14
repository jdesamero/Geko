<?php

// abstract
class Geko_Wp_Theme_Admin extends Geko_Wp_Options_Admin
{
	protected $_sMenuTitle = 'Theme';
	protected $_sAdminType = 'Theme';
	protected $_sIconId = 'icon-themes';
	protected $_sSubAction = 'themes';
	
	
	
	//
	public function retrieveInfo() {
		
		$aThemeInfo = Geko_Wp_Theme::get_current_data();
		
		$this->_sVersion = $aThemeInfo['Version'];
		$this->_sName = $aThemeInfo['Name'];		
		$this->_sPrefix = $aThemeInfo['Prefix'];
		
		return $this->_sName;
	}
	
	
	//// init
	
	// add_action('admin_menu', array($this, 'attachPage'));
	public function attachPage() {
		
		add_theme_page(
			$this->getPageTitle(), $this->getMenuTitle(), 8, $this->_sInstanceClass, array( $this, 'outputForm' )
		);
	}

	
	
	//// accessors
		
	// get the plugin url
	public function getUrl() {
		return get_settings('siteurl') . '/wp-admin/themes.php?page=' . $this->_sInstanceClass;
	}
	
	
}


