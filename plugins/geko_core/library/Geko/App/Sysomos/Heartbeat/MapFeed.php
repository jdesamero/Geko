<?php

//
class Geko_App_Sysomos_Heartbeat_MapFeed extends Geko_App_Entity
{
	
	
	// protected $_sEntityIdVarName = '';
	// protected $_sEntitySlugVarName = '';
	
	protected $_sEditEntityIdVarName = '';
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'seq' )
			->setEntityMapping( 'title', 'location' )
		;
		
		return $this;
	}
	
	

}


