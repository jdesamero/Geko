<?php
/*
 * "geko_core/library/Geko/App/Entity/Role/Meta/Query.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Entity_Role_Meta_Query extends Geko_App_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			
			->field( 'mk.slug', 'meta_key' )
			
			->joinLeft( '##pfx##meta_key', 'mk' )
				->on( 'mk.id = erm.meta_key_id' )
			
		;
		
		
		//// filters
		
		//
		if ( $mEnityRoleId = $aParams[ 'entity_role_id' ] ) {
			$oQuery->where( 'erm.entity_role_id * ($)', $mEnityRoleId );
		}
		
		
		return $oQuery;
	}

}


