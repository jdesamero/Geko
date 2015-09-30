<?php

//
class Geko_Wp_Ext_WooCommerce_File_DownloadHistory extends Geko_Wp_Entity
{


	protected $_sEntityIdVarName = 'dlh_id';
	
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->addDelegate( 'Geko_Wp_Ext_WooCommerce_File_DownloadHistory_Record' )
		;
		
		return $this;
	}
	
	
	
}

