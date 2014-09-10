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
	
	
	
	// create table
	public function install() {
		
		parent::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	
	
	
	
	//// crud methods
	
	//
	public function doAddAction( $aParams ) {
		
		global $wpdb;
		
		$oItem = $aParams[ 'item_entity' ];
		$oUser = $aParams[ 'user_entity' ];
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		
		$aInsertValues = array(
			'bkitm_id' => $oItem->getId(),
			'user_id' => $oUser->getId(),
			'date_created' => $sDateTime
		);
		
		$aInsertFormat = array( '%d', '%d', '%s' );
		
		// update the database first
		$wpdb->insert(
			$wpdb->geko_bkng_request,
			$aInsertValues,
			$aInsertFormat
		);
		
		$aParams[ 'entity_id' ] = $wpdb->get_var( 'SELECT LAST_INSERT_ID()' );
		
		return $aParams;
		
	}
	
	public function doEditAction( $aParams ) {
	
	}
	
	public function doDelAction( $aParams ) {

		global $wpdb;
		
		$oItem = $aParams[ 'item_entity' ];
		$oUser = $aParams[ 'user_entity' ];
		
		if ( $oUser ) {
			$sQuery = $wpdb->prepare(
				"
					DELETE FROM					$wpdb->geko_bkng_request
					WHERE						( bkitm_id = %d ) AND 
												( user_id = %d )
				",
				$oItem->getId(),
				$oUser->getId()
			);
		} else {
			$sQuery = $wpdb->prepare(
				"
					DELETE FROM					$wpdb->geko_bkng_request
					WHERE						( bkitm_id = %d )
				",
				$oItem->getId()
			);		
		}
		
		$wpdb->query( $sQuery );
		
	}
	
	
}



