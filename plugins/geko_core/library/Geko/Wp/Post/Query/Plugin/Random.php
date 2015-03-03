<?php

//
class Geko_Wp_Post_Query_Plugin_Random extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams, $oEntityQuery ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams, $oEntityQuery );
		
		
		// $aParams[ 'some_var' ]
		
		
		return $oQuery;
	
	}
	
	
}



