<?php

// "Active Record" implementation of Geko_Entity, set up as a delegate
class Geko_Entity_Record extends Geko_Delegate
{


	//
	public function canHandleMethod( $sMethod ) {
		
		if ( 0 === strpos( $sMethod, 'set' ) ) {
			return TRUE;
		}
		
		return FALSE;
	}
	
	
	//
	public function setEntityPropertyValue() {
		
		$oSubject = $this->_oSubject;
		
		$aArgs = func_get_args();
		
		if ( is_array( $aValues = $aArgs[ 0 ] ) ) {
			
			// associative array method
			
			foreach ( $aValues as $sKey => $mValue ) {
				$this->setEntityPropertyValue( $sKey, $mValue );
			}
			
		} else {
			
			list( $sKey, $mValue ) = $aArgs;
			
			// key/value pair method
			
			$oRawEntity = $oSubject->initRawEntity();
			$oRawEntity->$sKey = $mValue;
						
		}
		
		return $oSubject;
	}
	
	
	//
	public function save() {
		
		$oDb = Geko::get( 'db' );
		
		$oSubject = $this->_oSubject;
		
		if ( $oTable = $oSubject->getPrimaryTable() ) {
			
			$sTableName = $oTable->getTableName();
			
			
			// get field values
			
			$aFields = $oTable->getFields( TRUE );
			
			$aValues = array();
			
			foreach ( $aFields as $sFieldName => $oField ) {
				
				if ( $oSubject->hasEntityProperty( $sFieldName ) ) {
					
					$aValues[ $sFieldName ] = $oField->getAssertedValue(
						$oSubject->getEntityPropertyValue( $sFieldName )
					);
				}
				
			}
			
			
			// are we creating a new record or updating an existing record?
			
			if ( $oPkf = $oTable->getPrimaryKeyField() ) {
				
				$sPkfName = $oPkf->getName();
				
				if ( !$oSubject->hasEntityProperty( $sPkfName ) ) {
					
					// implement some "auto" functionality
					
					if ( $oTable->hasField( 'date_created' ) && !$aValues[ 'date_created' ] ) {
						$aValues[ 'date_created' ] = $oDb->getTimestamp();
					}

					if ( $oTable->hasField( 'date_modified' ) && !$aValues[ 'date_modified' ] ) {
						$aValues[ 'date_modified' ] = $oDb->getTimestamp();
					}
					
										
					
					// try-catch any possible database errors
					
					try {
						
						// run validation hook
						$this->throwValidate( $aValues );
					
						// insert
						$oDb->insert( $sTableName, $aValues );
						
						$iLastInsertId = $oDb->lastInsertId();
						
						$this->setEntityPropertyValue( $sPkfName, $oField->getAssertedValue( $iLastInsertId ) );
					
					} catch ( Zend_Db_Statement_Exception $e ) {
						
						$eFmt = new Geko_Entity_Record_Exception( 'Insert failed!' );
						$eFmt
							->setErrorType( 'db' )
							->setOrigMessage( $e->getMessage() )
						;
						
						throw $eFmt;
					}
					
				} else {
					
					if ( $mId = $aValues[ $sPkfName ] ) {

						// run validation hook
						$this->throwValidate( $aValues, 'update' );
						
						
						$aWhere[ $sPkfName ] = $mId;
						
						unset( $aValues[ $sPkfName ] );

						// implement some "auto" functionality
						
						if ( $oTable->hasField( 'date_created' ) && $aValues[ 'date_created' ] ) {
							unset( $aValues[ 'date_created' ] );		// retain original
						}
						
						if ( $oTable->hasField( 'date_modified' ) ) {
							$aValues[ 'date_modified' ] = $oDb->getTimestamp();
						}
						
						
						
						// try-catch any possible database errors
						
						try {
						
							// update
							$oDb->update( $sTableName, $aValues, $this->formatWhere( $aWhere ) );

						} catch ( Zend_Db_Statement_Exception $e ) {
							
							$eFmt = new Geko_Entity_Record_Exception( 'Update failed!' );
							$eFmt
								->setErrorType( 'db' )
								->setOrigMessage( $e->getMessage() )
							;
							
							throw $eFmt;
						}
						
					} else {
						throw new Exception( sprintf( 'Entity "%s" has no primary key value.', $this->_sSubjectClass ) );
					}
					
				}
				
			} else {
				
				// TO DO: multi-key support
				// getKeyFields
				
				throw new Exception( 'Multi-key support is not yet implemented!' );
				
			}
			
		} else {
			throw new Exception( sprintf( 'Entity "%s" requires a primary table.', $this->_sSubjectClass ) );
		}
		
		return $oSubject;
	}
	
	
	
	//// helpers
	
	//
	protected function formatWhere( $aWhere ) {
		
		$aWhereFmt = array();
		
		foreach ( $aWhere as $sKey => $mValue ) {
			$aWhereFmt[ sprintf( '%s = ?', $sKey ) ] = $mValue;
		}
		
		return $aWhereFmt;
	}
	
	//
	protected function throwValidate( $aValues, $sMode = 'insert' ) {
		
		if (
			( is_array( $aErrors = $this->validate( $aValues, $sMode ) ) ) && 
			( count( $aErrors ) > 0 )
		) {
			
			$eFmt = new Geko_Entity_Record_Exception( 'Validation failed!' );
			$eFmt
				->setErrorType( 'validation' )
				->setErrorDetail( $aErrors )
			;
			
			throw $eFmt;
						
		}
		
	}
	
	
	// hook methods, to be implemented by sub-class
	
	//
	public function validate( $aValues, $sMode = 'insert' ) { }
	
	
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		
		if ( 0 === strpos( $sMethod, 'set' ) ) {
			
			$sProp = Geko_Inflector::underscore( substr( $sMethod, 3 ) );
			
			return $this->setEntityPropertyValue( $sProp, $aArgs[ 0 ] );
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', $this->_sDelegateClass, $sMethod ) );
	}

}


