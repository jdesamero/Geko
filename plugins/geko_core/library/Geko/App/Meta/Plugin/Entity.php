<?php
/*
 * "geko_core/library/Geko/App/Meta/Plugin/Entity.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Meta_Plugin_Entity extends Geko_Entity_Plugin
{

	// constructEnd action hook
	public function constructEnd( $oEntity, $oRawEntity, $oQuery, $aData, $aQueryParams, $oPrimaryTable ) {
		
		if (
			( $oPrimaryTable ) && 
			( $oQuery ) && 
			( $oMetaQuery = $oQuery->getData( 'meta_query' ) ) && 
			( $sEntityKey = $oQuery->getData( 'meta_entity_key' ) )
		) {
			
			// create meta subsets
			
			$sSubsetMethod = sprintf( 'subset%s', Geko_Inflector::camelize( $sEntityKey ) );
			
			// get all meta values belonging to this particular entity
			$aMeta = $oMetaQuery->$sSubsetMethod( $oEntity->getEntityPropertyValue( 'id' ) );
			
			// the entity type slug is same as the table name, minus the prefix
			$sEntityTypeSlug = $oPrimaryTable->getNoPrefixTableName();
			
			$aKeys = Geko_App_Meta_Key::getKeys( $sEntityTypeSlug, TRUE );
			
			//
			$oEntity
				->setData( 'meta', $aMeta )
				->setData( 'meta_keys', $aKeys )
			;
			
		}
		
	}
	
	
	
	// filter
	public function getRawMeta( $mValue, $sMetaKey, $oRawEntity, $oQuery, $aData, $aQueryParams, $oPrimaryTable, $oEntity ) {
		
		if (
			( $aMeta = $oEntity->getData( 'meta' ) ) && 
			( $aMetaKeys = $oEntity->getData( 'meta_keys' ) )
		) {
			
			if ( $oKey = $aMetaKeys->subsetOneSlug( $sMetaKey ) ) {
				return $oKey->getFormattedValue( $aMeta->subsetMetaKey( $sMetaKey ) );
			}
		}
		
		return $mValue;
	}
	
	
}


