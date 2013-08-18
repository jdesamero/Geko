<?php

class Geko_Wp_Booking_Item extends Geko_Wp_Entity
{
		
	protected $_sEntityIdVarName = 'geko_bkitm_id';
	protected $_sEntitySlugVarName = 'geko_bkitm_slug';
	
	protected $_sEditEntityIdVarName = 'bkitm_id';
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'bkitm_id' )
		;
		
		return $this;
	}
	
	
	
	//
	public function getBookingTypeFmt() {
		return (
			Geko_Wp_Booking_Schedule_Manage::TYPE_PRIVATE == 
			$this->getEntityPropertyValue( 'booking_type' )
		) ?
			'Private' : 'Public'
		;
	}
	
	//
	public function getCostFmt() {
		return floatval( $this->getEntityPropertyValue( 'cost' ) );
	}
	
	//// helper accessors
	
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
	
	//
	public function getDateItem( $sFormat = '' ) {
		return $this->mysql2DateFormat( 'date_item', $sFormat );
	}
	
	//
	public function getFullTime( $sKey, $sFormat = '' ) {

		$sTime = $this->getEntityPropertyValue( $sKey );
		
		$sDate = date( 'Y-m-d', strtotime( $this->getEntityPropertyValue( 'date_item' ) ) ) . ' ' . $sTime;
		
		if ( '12:00 AM' == $sTime ) {
			$sDate = date( 'Y-m-d g:i A', strtotime( $sDate ) + ( 60 * 60 * 24 ) - 1 );
		}
		
		if ( $sFormat ) $sDate = date( $sFormat, strtotime( $sDate ) );
		
		return $sDate;
		
	}
	
	//
	public function getFullStartTime( $sFormat = '' ) {
		return $this->getFullTime( 'time_start', $sFormat );	
	}
	
	//
	public function getFullEndTime( $sFormat = '' ) {
		return $this->getFullTime( 'time_end', $sFormat );	
	}
	
	//
	public function getTheUnit() {
		return floatval( $this->getEntityPropertyValue( 'unit' ) );
	}
	
	//
	public function getSlotsAvailable() {
		return intval( $this->getEntityPropertyValue( 'slots' ) ) - 
			intval( $this->getEntityPropertyValue( 'slots_taken' ) )
		;
	}
	
	//
	public function isFull() {
		return ( 0 == $this->getSlotsAvailable() );
	}
	
	//
	public function getUserBookedAllSlots() {
		return ( $this->isFull() && (
			intval( $this->getEntityPropertyValue( 'slots_taken' ) ) == 
			intval( $this->getEntityPropertyValue( 'slots_taken_by_user' ) )
		) );
	}
	
	//
	public function getUserId() {
		return intval( $this->getEntityPropertyValue( 'user_id' ) );
	}
	
	//
	public function isPast( $iCutoffTs ) {
		$sDate = str_replace(
			'00:00:00',
			 $this->getEntityPropertyValue( 'time_end' ),
			 $this->getEntityPropertyValue( 'date_item' )
		);
		return ( strtotime( $sDate ) < $iCutoffTs );
	}
	
}



