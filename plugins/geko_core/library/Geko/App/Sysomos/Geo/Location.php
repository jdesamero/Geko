<?php

//
class Geko_App_Sysomos_Geo_Location extends Geko_App_Entity
{
	
	//
	const STAT_REQUEST_DENIED = 101;					// request denied, secret location???
	const STAT_BAD_ADDRESS = 102;						// bad address
	const STAT_MORE_THAN_ONE = 103;						// more than one, OBSOLETE, there is match_count
	const STAT_NO_MATCHES_FOUND = 104;					// no matches found
	const STAT_ALREADY_COORDS = 105;					// is geo-coords already
	
	
	
	// protected $_sEntityIdVarName = '';
	protected $_sEntitySlugVarName = 'hash';
	
	protected $_sEditEntityIdVarName = '';
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'slug', 'hash' )
			->setEntityMapping( 'title', 'location' )
			->setEntityMapping( 'latitude', 'lat' )
			->setEntityMapping( 'longitude', 'lng' )
		;
		
		return $this;
	}
	
	

}


