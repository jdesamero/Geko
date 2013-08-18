<?php

class Geko_Wp_Point_Event extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'pntevt_id';
	protected $_sEntitySlugVarName = 'pntevt_slug';
	
	protected $_sEditEntityIdVarName = 'pntevt_id';
	
	
	
	//// static methods
	
	//
	public static function getPointValue( $sPointEventSlug ) {
		$oMng = Geko_Wp_Point_Event_Manage::getInstance();
		$aPointEvents = $oMng->getPointEvents();
		if ( $oPointEvent = $aPointEvents[ $sPointEventSlug ] ) {
			return $oPointEvent->getValue();
		}
		return 0;
	}
	
	//
	public static function getMaxTimesValue( $sPointEventSlug ) {
		$oMng = Geko_Wp_Point_Event_Manage::getInstance();
		$aPointEvents = $oMng->getPointEvents();
		if ( $oPointEvent = $aPointEvents[ $sPointEventSlug ] ) {
			return $oPointEvent->getMaxTimes();
		}
		return 0;
	}	
	
	
	
	//// object oriented functions
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'pntevt_id' )
			->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'content', 'description' )
		;
		
		return $this;
	}

	
	//
	public function getTheValue() {
		if ( intval( $this->getEntityPropertyValue( 'arbitrary_points' ) ) ) {
			return 'Arbitrary';
		}
		return $this->getEntityPropertyValue( 'value' );
	}
	
	
}


