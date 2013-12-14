<?php

//
class Geko_Wp_EmailMessage_Log_Query extends Geko_Wp_Log_Query
{
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			->field( 'k1.meta_key', 'delivery_status' )
			->joinLeft( $wpdb->geko_meta_key, 'k1' )
				->on( 'k1.mkey_id = l.dlvstat_id' )
		;
		
		return $oQuery;
	}
	
}


