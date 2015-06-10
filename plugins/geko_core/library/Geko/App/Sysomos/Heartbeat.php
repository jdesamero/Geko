<?php

//
class Geko_App_Sysomos_Heartbeat extends Geko_App_Entity
{
	
	
	protected $_sEntityIdVarName = 'id';
	protected $_sEntitySlugVarName = 'title';
	
	protected $_sEditEntityIdVarName = '';
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'hid' )
			->setEntityMapping( 'title', 'name' )
		;
		
		return $this;
	}
	
	

}


