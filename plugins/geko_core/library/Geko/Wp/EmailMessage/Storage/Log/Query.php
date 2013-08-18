<?php

//
class Geko_Wp_EmailMessage_Storage_Log_Query extends Geko_Wp_Log_Query
{
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		//
		$oQuery
			
			->field( 'sc.meta_value', 'status_code' )
			->joinLeft( $wpdb->geko_logs_email_storage_meta, 'sc' )
				->on( 'sc.log_id = l.log_id' )
				->on( 'sc.mkey_id = ?', Geko_Wp_Options_MetaKey::getId( 'delivery_status_code' ) )
			
			->field( 'COALESCE( rc.meta_value, ec.meta_value )', 'failed_recipient' )

			->joinLeft( $wpdb->geko_logs_email_storage_meta, 'rc' )
				->on( 'rc.log_id = l.log_id' )
				->on( 'rc.mkey_id = ?', Geko_Wp_Options_MetaKey::getId( 'x-failed-recipients' ) )
			
			->joinLeft( $wpdb->geko_logs_email_storage_meta, 'ec' )
				->on( 'ec.log_id = l.log_id' )
				->on( 'ec.mkey_id = ?', Geko_Wp_Options_MetaKey::getId( 'geko_emsg_recipient' ) )
				
		;
		
		return $oQuery;
	}
	
}


