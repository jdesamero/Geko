<?php

//
class Geko_Wp_Post_QueryPlugin_Parent extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		// $aParams[ 'some_var' ]
		
		
		return $oQuery;
	
	}
	
	
}



