<?php

//
class Gloc_Post_Location_Manage extends Geko_Wp_Post_Location_Manage
{
	// protected $_sUserType = 'user';
	// protected $_sSectionLabel = 'Address';
	
	
	protected $_aFields = array(
		'address_line_1',
		'city',
		'province_id',
		'postal_code',
		'latitude',
		'lat_offset',
		'longitude',
		'long_offset'
	);
	
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		$this->_aFieldLabels[ 'address_line_1' ] = 'Address';
	}	
	
	
	//
	public function attachPage() {
	
		$oPost = Gloc_Post_Meta::getInstance()->getCurPost();
		
		// only activate for this category
		if ( $oPost && $oPost->inCategory( array( 'locations' ) ) ) {
			parent::attachPage();
		}
		
	}
	
	
}

