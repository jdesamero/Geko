<?php

class Geko_Wp_Booking_Schedule extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'geko_bksch_id';
	protected $_sEntitySlugVarName = 'geko_bksch_slug';
	
	protected $_sEditEntityIdVarName = 'bksch_id';
	
	
	//// object oriented functions
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'bksch_id' )
			->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'slug', 'slug' )
			->setEntityMapping( 'content', 'description' )
		;
		
		return $this;
	}
	
	//
	public function getDateStart( $sFormat = '' ) {
		return $this->mysql2DateFormat( 'date_start', $sFormat );
	}
	
	//
	public function getDateEnd( $sFormat = '' ) {
		return $this->mysql2DateFormat( 'date_end', $sFormat );
	}
	
	
	//
	public function getCostFmt() {
		return floatval( $this->getEntityPropertyValue( 'cost' ) );
	}
	
	//
	public function getTheUnit() {
		return floatval( $this->getEntityPropertyValue( 'unit' ) );
	}
	
	
	//
	public function isPrivate() {
		return (
			Geko_Wp_Booking_Schedule_Manage::TYPE_PRIVATE == 
			$this->getEntityPropertyValue( 'booking_type' )
		) ?
			TRUE : FALSE
		;	
	}
	
	//
	public function isOpen() {
		return (
			Geko_Wp_Booking_Schedule_Manage::TYPE_OPEN == 
			$this->getEntityPropertyValue( 'booking_type' )
		) ?
			TRUE : FALSE
		;	
	}
	
	
}



