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
				self::$aIdSlugHash[ $oType->getId() ] = $oType->getSlug();
				self::$aSlugIdHash[ $oType->getSlug() ] = $oType->getId();
			}
		}
		
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
	
	
}


