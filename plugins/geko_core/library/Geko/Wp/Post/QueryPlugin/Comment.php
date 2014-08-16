<?php

//
class Geko_Wp_Post_QueryPlugin_Comment extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// $aParams[ 'some_var' ]
		
		
		return $oQuery;
	
	}
	
	
}



