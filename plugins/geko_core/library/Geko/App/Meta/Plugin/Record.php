<?php
/*
 * "geko_core/library/Geko/App/Meta/Plugin/Record.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * this is a Geko_Delegate
 */

//
class Geko_App_Meta_Plugin_Record extends Geko_Entity_Record_Plugin
{
	
	//
	public function handleOtherValues( $aValues, $sMode, $oSubject, $oRecord ) {
		
		if (
			( $oQuery = $oSubject->getParentQuery() ) && 
			( $sEntityKey = $oQuery->getData( 'meta_entity_key' ) )
		) {
			
			$sSetEntityIdMethod = sprintf( 'set%s', Geko_Inflector::camelize( $sEntityKey ) );
			$sMetaClass = $oSubject->_getMetaClass();
			$mId = $oSubject->getId();
			
			$aMeta = $oSubject->getData( 'meta' );
			$aMetaKeys = $oSubject->getData( 'meta_keys' );
			
			// the entity type slug is same as the table name, minus the prefix
			$oPrimaryTable = $oSubject->getPrimaryTable();
			$sEntityTypeSlug = $oPrimaryTable->getNoPrefixTableName();
			
			//
			foreach ( $aValues as $sKey => $mValue ) {
				
				if ( !$oKey = $aMetaKeys->subsetOneSlug( $sKey ) ) {
					$oKey = Geko_App_Meta_Key::add( $sKey, $sEntityTypeSlug, '', $mValue );
				}
				
				if ( $oKey ) {
					
					$aMetaVal = $aMeta->subsetMetaKey( $sKey );			// may return empty set
					
					if ( $oKey->getIsMultiple() ) {
						
						// multi-value
						if ( $mValue ) {
							$aValues = Geko_Array::wrap( $mValue );
						} else {
							$aValues = array();				// empty array
						}
						
						// "squash" values so that it is forced into a one-dimensional array
						$aFlat = Geko_Array::squash( $aValues );
						
						$i = 0;
						foreach ( $aFlat as $sSubKey => $mSubValue ) {
							
							if ( !$oMeta = $aMetaVal->subsetOneSubKey( $sSubKey ) ) {
								
								// create new meta data instance
								$oMeta = new $sMetaClass();
								$oMeta
									->$sSetEntityIdMethod( $mId )
									->setMetaKeyId( $oKey->getId() )
									->setSubKey( $sSubKey )
								;
								
								$aMeta->addRawEntities( $oMeta );
							}
							
							// save and mark as "keep"
							$oMeta
								->setValue( $mSubValue )
								->setSubKeyOrder( $i )
								->setData( 'meta_keep', TRUE )
								->save()
							;
							
							$i++;
						}
						
						// destroy any leftovers
						foreach ( $aMetaVal as $oMeta ) {
							if ( $oMeta->getData( 'meta_keep' ) ) {
								$oMeta->unsetData( 'meta_keep' );		// unset "keep" flag
							} else {
								$oMeta->destroy();
							}
						}
						
					} else {
						
						// single value
						
						$oMeta = $aMetaVal->getOne();
						
						if ( !$oMeta->isValid() ) {
							$oMeta
								->$sSetEntityIdMethod( $mId )
								->setMetaKeyId( $oKey->getId() )
							;
						}
						
						$oMeta->setValue( $mValue )->save();
						
					}
					
				} else {
				
					// non-existent meta-key was encountered
					// this would be an exception, because a key would always be generated
					
				}
				
			}
			
			
		}
		
		
	}
	
	
	//
	public function beforeDelete( $oSubject, $oRecord ) {
	
		if ( $aMeta = $oSubject->getData( 'meta' ) ) {
			
			foreach ( $aMeta as $oMeta ) {
				$oMeta->destroy();
			}
			
		}
		
	}
	
}


