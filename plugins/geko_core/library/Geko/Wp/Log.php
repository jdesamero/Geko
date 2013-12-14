<?php

//
class Geko_Wp_Log extends Geko_Wp_Entity
{

	protected $_sEntityIdVarName = 'log_id';
	// protected $_sEntitySlugVarName = 'generic_slug';

	protected $_sEditEntityIdVarName = 'log_id';
	
	
	//
	public function init()
	{
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'log_id' )
		;
		
		return $this;
	}
	
	
}


