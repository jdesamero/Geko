<?php
/*
 * "geko_core/library/Geko/App/Taxonomy/Item/Query.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Taxonomy_Item_Query extends Geko_App_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'ti.id = ?', $iId );			
		}
		
		return $oQuery;
	}

}


