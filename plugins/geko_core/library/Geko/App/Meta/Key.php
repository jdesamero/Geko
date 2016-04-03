<?php
/*
 * "geko_core/library/Geko/App/Meta/Key.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Meta_Key extends Geko_App_Entity
{
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public static $aKeys = NULL;
	
	
	//// static methods
	
	// $bForceTypeId if set to TRUE, it will force the creation of $iRelTypeId if it does not exist
	public static function getKeys( $mEntityType, $bForceTypeId = FALSE ) {
		
		if ( NULL === self::$aKeys ) {
			self::$aKeys = new Geko_App_Meta_Key_Query( array(), FALSE );
		}
		
		$iRelTypeId = Geko_App_Entity_Type::_assertId( $mEntityType );
		
		if ( !$iRelTypeId && $bForceTypeId ) {
			
			// is is assumed that $mEntityType is a string, or more importantly
			// it should be the value of $oSomeDbTable->getNoPrefixTableName()
			// which is an instance of Geko_Sql_Table
			
			$iRelTypeId = Geko_App_Entity_Type::_createEntityType( $mEntityType );
		}
		
		if ( $iRelTypeId ) {
			return self::$aKeys->subsetRelTypeId( $iRelTypeId );
		}
		
		return NULL;
	}
	
	//
	public static function add( $sMetaKey, $mEntityType, $sLabel = '', $mValue = NULL ) {
		
		if ( $iRelTypeId = Geko_App_Entity_Type::_assertId( $mEntityType ) ) {
			
			// check if $sMetaKey already exists
			$aEntityKeys = self::getKeys( $mEntityType );			// get the keys for the particular entity
			
			if ( !$oMetaKey = $aEntityKeys->subsetoneSlug( $sMetaKey ) ) {
			
				if ( !$sLabel ) $sLabel = Geko_Inflector::humanize( $sMetaKey );
				
				$oMetaKey = new Geko_App_Meta_Key();
				$oMetaKey
					->setLabel( $sLabel )
					->setSlug( $sMetaKey )
					->setRelTypeId( $iRelTypeId )
					->setIsMultiple( ( is_array( $mValue ) ) ? 1 : 0 )
					->save()
				;
				
				$aEntityKeys->addRawEntities( $oMetaKey );
			}
			
			return $oMetaKey;
		}
		
		return NULL;
	}
	
	
	//// instance methods
	
	//
	public function getFormattedValue( $aMetaVal ) {
		
		if ( $aMetaVal instanceof Geko_Entity_Query ) {
		
			// get multiple values
			if ( $this->getEntityPropertyValue( 'is_multiple' ) ) {
				
				$aRet = array();
				
				foreach ( $aMetaVal as $oMeta ) {
					
					// the sub-key may be in "squashed" notation to allow for storage of more complex structures
					$mSubKey = $oMeta->getSubKey();
					
					$aRet[ $mSubKey ] = $oMeta->getValue();
				}
				
				// Geko_Array::release() is complementary to Geko_Array::squash()
				return Geko_Array::release( $aRet );
			}
			
			
			// get single value
			if ( $aMetaVal->count() > 0 ) {
				return $aMetaVal->getOne()->getValue();
			}
		
		}
		
		return NULL;
	}
	
	
}


