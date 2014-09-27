<?php

//
class Geko_Wp_Activity_Query extends Geko_Wp_Log_Query
{
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oGetQuery = new Geko_Sql_Select();
		$oGetQuery
			->field( 'm.log_id' )
			->field( 'GROUP_CONCAT( CONCAT( mk.meta_key, "=", m.meta_value ) SEPARATOR " | " )', 'get_data' )
			->from( '##pfx##geko_logs_activity_meta', 'm' )
			->joinLeft( '##pfx##geko_meta_key', 'mt' )
				->on( 'mt.mkey_id = m.type_id' )
			->joinLeft( '##pfx##geko_meta_key', 'mk' )
				->on( 'mk.mkey_id = m.mkey_id' )
			->where( 'mt.meta_key = ?', 'get' )
			->group( 'm.log_id' )
		;
		
		$oPostQuery = new Geko_Sql_Select();
		$oPostQuery
			->field( 'm.log_id' )
			->field( 'GROUP_CONCAT( CONCAT( mk.meta_key, "=", m.meta_value ) SEPARATOR " | " )', 'post_data' )
			->from( '##pfx##geko_logs_activity_meta', 'm' )
			->joinLeft( '##pfx##geko_meta_key', 'mt' )
				->on( 'mt.mkey_id = m.type_id' )
			->joinLeft( '##pfx##geko_meta_key', 'mk' )
				->on( 'mk.mkey_id = m.mkey_id' )
			->where( 'mt.meta_key = ?', 'post' )
			->group( 'm.log_id' )
		;
		
		$oQuery
			
			->field( 'g.get_data' )
			->joinLeft( $oGetQuery, 'g' )
				->on( 'g.log_id = l.log_id' )
			
			->field( 'p.post_data' )
			->joinLeft( $oPostQuery, 'p' )
				->on( 'p.log_id = l.log_id' )
				
		;
		
		return $oQuery;
		
	}
	
	
}

