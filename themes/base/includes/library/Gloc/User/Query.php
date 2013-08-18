<?php

// query
class Gloc_User_Query extends Geko_Wp_User_Query
{
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$sUmPrefix = Gloc_User_Meta::getInstance()->getPrefixForDoc();
		
		$oQuery
			
			->fieldKvp( 'um1.meta_value', 'phone' )
						
			->joinLeftKvp( $wpdb->usermeta, 'um*' )
				->on( 'um*.user_id = u.ID' )
				->on( 'um*.meta_key = ?', $sUmPrefix . '*' )
			
		;
		
		return $oQuery;
	}
	
	
}



