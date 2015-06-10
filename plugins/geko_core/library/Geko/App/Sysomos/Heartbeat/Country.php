<?php

//
class Geko_App_Sysomos_Heartbeat_Country extends Geko_App_Entity
{
	
	
	// protected $_sEntityIdVarName = '';
	protected $_sEntitySlugVarName = 'abbr';
	
	protected $_sEditEntityIdVarName = '';
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'slug', 'abbr' )
			->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'mentions', 'mention_total' )
		;
		
		return $this;
	}
	
	

}


