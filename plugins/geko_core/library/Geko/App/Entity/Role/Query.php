<?php
/*
 * "geko_core/library/Geko/App/Entity/Role/Query.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Entity_Role_Query extends Geko_App_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'r.id = ?', $iId );			
		}
		
		//
		if ( $iRelTypeId = intval( $aParams[ 'rel_type_id' ] ) ) {
			$oQuery->where( 'r.rel_type_id = ?', $iRelTypeId );
		}
		
		
		return $oQuery;
	}

}


