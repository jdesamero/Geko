<?php

//
class Geko_App_Sysomos_Geo_Coords extends Geko_App_Entity
{
	
	
	// protected $_sEntityIdVarName = '';
	protected $_sEntitySlugVarName = 'hash';
	
	protected $_sEditEntityIdVarName = '';
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'slug', 'hash' )
			->setEntityMapping( 'title', 'address' )
			->setEntityMapping( 'latitude', 'lat' )
			->setEntityMapping( 'longitude', 'lng' )
		;
		
		return $this;
	}
	
	

}


