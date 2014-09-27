<?php

//
class Geko_Wp_Post_Query_Plugin_ByLetter extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		if ( $sLetter = $aParams[ 'filter_by_letter' ] ) {

			if ( '#' == $sLetter ) {
				$oQuery->where( "( UPPER( SUBSTRING( p.post_title, 1, 1 ) ) REGEXP '[0-9]' )" );
			} else {
				$oQuery->where( '( UPPER( SUBSTRING( p.post_title, 1, 1 ) ) = ? )', strtoupper( $sLetter ) );			
			}
		}
		
		return $oQuery;
	
	}
	
	
}



