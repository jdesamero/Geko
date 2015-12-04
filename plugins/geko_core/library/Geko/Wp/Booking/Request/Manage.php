<?php

//
class Geko_Wp_Booking_Request_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'bkreq_id';
	
	protected $_sSubject = 'Booking Request';
	protected $_sDescription = 'Checks for users requesting the availability of an event';
	protected $_sType = 'bkreq';
	
	protected $_bHasDisplayMode = FALSE;
	
	//// init
	
	
	//
	public function add() {
		
		parent::add();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_bkng_request', 'brq' )
			->fieldBigInt( 'bkreq_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'bkitm_id', array( 'unsgnd', 'key' ) )
			->fieldBigInt( 'user_id', array( 'unsgnd', 'key' ) )
			->fieldDateTime( 'date_created' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
	}
	
	
	
	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	
	
	
	
	//// crud methods
	
	//
	public function doAddAction( $aParams ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$oItem = $aParams[ 'item_entity' ];
		$oUser = $aParams[ 'user_entity' ];
		
		$sDateTime = $oDb->getTimestamp();
		
		$aInsertValues = array(
			'bkitm_id' => $oItem->getId(),
			'user_id' => $oUser->getId(),
			'date_created' => $sDateTime
		);
		
		// update the database first
		$oDb->insert( '##pfx##geko_bkng_request', $aInsertValues );
		
		$aParams[ 'entity_id' ] = $oDb->lastInsertId();
		
		return $aParams;
		
	}
	
	//
	public function doEditAction( $aParams ) {
	
	}
	
	//
	public function doDelAction( $aParams ) {

		$oDb = Geko_Wp::get( 'db' );
		
		$oItem = $aParams[ 'item_entity' ];
		
		$aWhere = array( 'bkitm_id = ?', $oItem->getId() );
		
		if ( $oUser = $aParams[ 'user_entity' ] ) {
			$aWhere[ 'user_id = ?' ] = $oUser->getId();
		}
		
		$oDb->delete( '##pfx##geko_bkng_request', $aWhere );
	}
	
	
}



