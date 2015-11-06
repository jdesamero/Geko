<?php
/*
 * "geko_core/library/Geko/Wp/Point.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Point extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'point_id';
	// protected $_sEntitySlugVarName = '';
	
	protected $_sListingTitle = 'Point Id';
	protected $_sEditEntityIdVarName = 'point_id';
	
	
	
	//// static methods
	
	//
	public static function getErrorCode( $sCode ) {
		$aErrors = Geko_Wp_Enumeration_Query::getSet( 'geko-point-award-error' );
		return intval( $aErrors->getValueFromSlug( 'geko-point-awerr-' . $sCode ) );
	}
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'point_id' )
			->setEntityMapping( 'title', 'point_id' )
			->setEntityMapping( 'point_event_id', 'pntevt_id' )
		;
		
		return $this;
	}
	
	
	
	//
	public function getApprovalStatus() {
		if ( $this->getEntityPropertyValue( 'requires_approval' ) ) {
			return $this->getEntityPropertyValue( 'approve_status' );
		}
		return 'Not Applicable';
	}
	
	
	//
	public function getMetaValues() {
		
		$iPointId = $this->getId();
		
		$oMetaMng = Geko_Wp_Point_Meta::getInstance();
		$aPointMeta = $oMetaMng->getMetaData( array(
			'parent_ids' => $iPointId
		) );
		
		return $aPointMeta[ $iPointId ];
	}
	
	
}


