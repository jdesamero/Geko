<?php

//
class Geko_Wp_Ext_WooCommerce_File extends Geko_Wp_Entity
{


	protected $_sEntityIdVarName = 'file_id';
	protected $_sEntitySlugVarName = 'file_key';
	
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->addDelegate( 'Geko_Wp_Ext_WooCommerce_File_Record' )
			->setEntityMapping( 'id', 'file_id' )
			->setEntityMapping( 'slug', 'file_key' )
		;
		
		return $this;
	}
	
	
	
}

