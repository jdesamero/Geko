<?php

//
class Geko_Wp_Pin_Log_Query extends Geko_Wp_Log_Query
{
	
		
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			
			->field( 'm1.meta_value', 'msg_id' )
			->joinLeft( $wpdb->geko_pin_log_meta, 'm1' )
				->on( 'm1.log_id = l.log_id' )
				->on( 'm1.mkey_id = ?', Geko_Wp_Options_MetaKey::getId( 'msg' ) )
				
			->field( 'm2.meta_value', 'op_id' )
			->joinLeft( $wpdb->geko_pin_log_meta, 'm2' )
				->on( 'm2.log_id = l.log_id' )
				->on( 'm2.mkey_id = ?', Geko_Wp_Options_MetaKey::getId( 'op' ) )
				
		;
		
		//// filters
		
		//
		if ( $mMsgId = $aParams[ 'msg_id' ] ) {
			$oQuery->having( 'm1.meta_value * ($)', $mMsgId );
		}
		
		if ( $mOpId = $aParams[ 'op_id' ] ) {
			$oQuery->having( 'm2.meta_value * ($)', $mOpId );
		}
		
		return $oQuery;
		
	}
	
	
	
	
}

