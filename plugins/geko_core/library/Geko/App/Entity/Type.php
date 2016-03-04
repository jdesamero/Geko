<?php
/*
 * "geko_core/library/Geko/App/Entity/Type.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Entity_Type extends Geko_App_Entity
{
	
	protected static $aSlugIdHash = NULL;
	protected static $aIdSlugHash = NULL;
	
	
	//
	protected static function populateHashes() {
		
		if ( !self::$aSlugIdHash && !self::$aIdSlugHash ) {
			
			$aTypes = new Geko_App_Entity_Type_Query( array(), FALSE );
			
			foreach ( $aTypes as $oType ) {
				
				self::_createHashEntries( $oType->getId(), $oType->getSlug() );
			}
			
		}
		
	}
	
	
	
	//
	public static function _createHashEntries( $iTypeId, $sTypeSlug ) {

		self::$aIdSlugHash[ $iTypeId ] = $sTypeSlug;
		self::$aSlugIdHash[ $sTypeSlug ] = $iTypeId;	
	}
	
	
	//
	public static function _getId( $sSlug ) {
		
		self::populateHashes();
		
		return self::$aSlugIdHash[ $sSlug ];
	}
	
	//
	public static function _getSlug( $iId ) {
		
		self::populateHashes();
		
		return self::$aIdSlugHash[ $iId ];
	}
	
	// id or slug may be provided, but always return the id
	public static function _assertId( $mId ) {
		
		if ( !preg_match( '/^[0-9]+$/', $mId ) ) {
			
			// $mId is a slug
			return self::_getId( $mId );
		}
		
		return $mId;
	}
	
	
	//
	public static function _createEntityType( $sEntityTypeSlug ) {
		
		// From: Geko_App_Meta_Plugin_Record::constructEnd
		// the entity type slug is same as the table name, minus the prefix
		
		$sName = ucwords( str_replace( '_', ' ', $sEntityTypeSlug ) );
		
		$oEntityType = new Geko_App_Entity_Type();
		
		$oEntityType
			
			->setName( $sName )
			->setSlug( $sEntityTypeSlug )
			
			->save()
		;
		
		$iEntityTypeId = $oEntityType->getId();
		
		// update the hash
		self::_createHashEntries( $iEntityTypeId, $sEntityTypeSlug );
		
		return $iEntityTypeId;
	}
	
	
}



