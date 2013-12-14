<?php

//
class Geko_Wp_Generic extends Geko_Wp_Entity
{

	protected $_sEntityIdVarName = 'generic_id';
	// protected $_sEntitySlugVarName = 'generic_slug';

	protected $_sEditEntityIdVarName = 'generic_id';
	
	
	//
	public function init()
	{
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'generic_id' )
		;
		
		return $this;
	}
	
	
}


