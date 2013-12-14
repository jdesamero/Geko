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
	public function affix() {
		Geko_Wp_Db::addPrefix( 'geko_bkng_request' );
		return $this;
	}
	
	
	
	// create table
	public function install() {
		
		$sSql = '
			CREATE TABLE %s
			(
				bkreq_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				bkitm_id BIGINT UNSIGNED,
				user_id BIGINT UNSIGNED,
				date_created DATETIME,
				PRIMARY KEY(bkreq_id)
			)
		';
		
		Geko_Wp_Db::createTable( 'geko_bkng_request', $sSql );
				
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



