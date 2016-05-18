<?php
/*
 * "geko_core/library/Geko/App/Finance/Entry/Query.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Entry_Query extends Geko_App_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'e.id = ?', $iId );			
		}
		
		//
		if ( $iOwnerId = intval( $aParams[ 'owner_id' ] ) ) {
			$oQuery->where( 'e.owner_id = ?', $iOwnerId );
		}
		
		
		// date filters
		
		if ( $iMonth = intval( $aParams[ 'month' ] ) ) {
			$oQuery->where( "DATE_FORMAT( e.date_entered, '%c' ) = ?", $iMonth );
		}
		
		if ( $iYear = intval( $aParams[ 'year' ] ) ) {
			$oQuery->where( "DATE_FORMAT( e.date_entered, '%Y' ) = ?", $iYear );		
		}
		
		
		return $oQuery;
	}
	
	
}


