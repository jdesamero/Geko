<?php
/*
 * "geko_core/library/Geko/App/Taxonomy/Query.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Taxonomy_Query extends Geko_App_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function init() {
	
		$this->addPlugin( 'Geko_App_Meta_Plugin_Query', array(
			'entity_key' => 'taxonomy_id'
		) );
		
		parent::init();
		
		return $this;
	}
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'tx.id = ?', $iId );			
		}
		
		return $oQuery;
	}
	
	
}


