<?php
/*
 * "geko_core/library/Geko/App/Meta/Plugin/Query.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Meta_Plugin_Query extends Geko_Entity_Query_Plugin
{
	
	//
	public function setupEntityQuery( $oEntityQuery, $aParams ) {
		
		parent::setupEntityQuery( $oEntityQuery, $aParams );
		
		if (
			( is_array( $aParams ) ) && 
			( $sEntityKey = $aParams[ 'entity_key' ] )
		) {
			$oEntityQuery->setData( 'meta_entity_key', $sEntityKey );
		}
	}
	
	
	//
	public function setRawEntities( $aEntities, $aParams, $aData, $oPrimaryTable, $oEntityQuery ) {
		
		if (
			( $sMetaQueryClass = $oEntityQuery->getMetaQueryClass() ) && 
			( $sEntityKey = $oEntityQuery->getData( 'meta_entity_key' ) )
		) {
			
			$oMetaQuery = new $sMetaQueryClass( array(
				$sEntityKey => $oEntityQuery->gatherId(),
				'orderby' => 'sub_key_order',
				'order' => 'ASC'
			), FALSE );
			
			$oEntityQuery->setData( 'meta_query', $oMetaQuery );
			
		}
		
	}
	
	
}


