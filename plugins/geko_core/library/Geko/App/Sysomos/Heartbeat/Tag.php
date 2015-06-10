<?php

//
class Geko_App_Sysomos_Heartbeat_Tag extends Geko_App_Entity
{
	
	
	// protected $_sEntityIdVarName = '';
	protected $_sEntitySlugVarName = 'title';
	
	protected $_sEditEntityIdVarName = '';
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this->setEntityMapping( 'slug', 'title' );
		
		return $this;
	}
	
	

}


