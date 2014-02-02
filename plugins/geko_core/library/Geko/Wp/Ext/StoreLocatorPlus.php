<?php

//
class Geko_Wp_Ext_StoreLocatorPlus extends Geko_Singleton_Abstract
{
	
	const MSG_PLUGIN_NOT_ACTIVATED = '<strong>Warning!</strong> Please activate the Store Locator Plus Plugin!';
	
	
	protected $_bSlpPluginActivated = FALSE;
	
	
	
	//
	public function start() {
		
		parent::start();
		
		if ( defined( 'SLPLUS_VERSION' ) ) {
			
			global $slplus_plugin;
			
			$oAjaxHandler = new Geko_Wp_Ext_StoreLocatorPlus_AjaxHandler( array(
				'parent' => $slplus_plugin
			) );
			
			add_action( 'wp_ajax_csl_ajax_onload', array( $oAjaxHandler, 'csl_ajax_onload' ), 0 );
			add_action( 'wp_ajax_nopriv_csl_ajax_onload', array( $oAjaxHandler, 'csl_ajax_onload' ), 0 );

			add_action( 'wp_ajax_csl_ajax_search', array( $oAjaxHandler, 'csl_ajax_search' ), 0 );
			add_action( 'wp_ajax_nopriv_csl_ajax_search', array( $oAjaxHandler, 'csl_ajax_search' ), 0 );
			
			$this->_bSlpPluginActivated = TRUE;
		}
		
	}
	
	

}

