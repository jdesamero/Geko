<?php

// "Active Record" implementation of Geko_Entity, set up as a delegate
class Geko_Entity_Record extends Geko_Delegate
{
	
	protected $_aOrigValues = NULL;
	
	
	
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
		
		if ( NULL === $this->_aOrigValues ) {

			$oRawEntity = $oSubject->initRawEntity();
			$this->_aOrigValues = get_object_vars( $oRawEntity );
		}
		
		if ( is_array( $aValues = $aArgs[ 0 ] ) ) {
			
			// associative array method
			
			foreach ( $aValues as $sKey => $mValue ) {
				$this->setEntityPropertyValue( $sKey, $mValue );
			}
			
		} else {
			
			list( $sKey, $mValue ) = $aArgs;
			
			// key/value pair method
			
			$oRawEntity = $oSubject->getRawEntity();
			$oRawEntity->$sKey = $mValue;
						
		}
		
		return $oSubject;
	}
	
	
	//// entity record methods: save and destroy
	
	// will figure itself out whether to insert or update database entry
	public function save() {
		
		$oSubject = $this->_oSubject;
		
		if ( $oTable = $oSubject->getPrimaryTable() ) {
			
			
			$aEntityKeys = array_keys( get_object_vars( $oSubject->getRawEntity() ) );
			
			
			// get field values
			
			$aFields = $oTable->getFields( TRUE );
			
			$aValues = array();
			$aOtherValues = array();			// track values that do not belong to the main table
			
			foreach ( $aEntityKeys as $sFieldName ) {
				
				if ( $oField = $aFields[ $sFieldName ] ) {

					$aValues[ $sFieldName ] = $oField->getAssertedValue(
						$oSubject->getEntityPropertyValue( $sFieldName )
					);
				
				} else {
					
					$aOtherValues[ $sFieldName ] = $oSubject->getEntityPropertyValue( $sFieldName );
				}
				
			}
			
			$aAllValues = array_merge( $aValues, $aOtherValues );
			
			
			// are we creating a new record or updating an existing record?
			
			if ( $oPkf = $oTable->getPrimaryKeyField() ) {
				
				$sPkfName = $oPkf->getName();
				
				if ( !$oSubject->hasEntityProperty( $sPkfName ) ) {
					
					$this->insert( $oTable, $aAllValues, $aValues, $aOtherValues );
					
				} else {

					if ( $mId = $aValues[ $sPkfName ] ) {
						
						$aWhere = array( $sPkfName => $mId );
						
						unset( $aValues[ $sPkfName ] );
						
						$this->update( $oTable, $aAllValues, $aValues, $aOtherValues, $aWhere );
						
					} else {
						throw new Exception( sprintf( 'Entity "%s" has no primary key value.', $this->_sSubjectClass ) );
					}
					
				}
				
			} else {
				
				// TO DO: multi-key support
				// $oTable->getKeyFields
				
				throw new Exception( 'Multi-key support is not yet implemented!' );
				
			}
			
		} else {
			throw new Exception( sprintf( 'Entity "%s" requires a primary table.', $this->_sSubjectClass ) );
		}
		
		return $oSubject;
	}
	
	//
	public function destroy() {
		
		$oDb = Geko::get( 'db' );
		
		$oSubject = $this->_oSubject;
		
		if ( $oTable = $oSubject->getPrimaryTable() ) {
			
			if ( $oPkf = $oTable->getPrimaryKeyField() ) {
				
				$sPkfName = $oPkf->getName();
				
				if ( $mId = $oSubject->getEntityPropertyValue( $sPkfName ) ) {
					
					$aWhere = array( $sPkfName => $mId );
					
					$this->delete( $oTable, $aWhere );
					
				} else {
					
					throw new Exception( 'Entity could not be deleted, no primary key was given!' );
					
				}
				
			} else {

				// TO DO: multi-key support
				// $oTable->getKeyFields
				
				throw new Exception( 'Multi-key support is not yet implemented!' );
				
			}
			
		} else {
			throw new Exception( sprintf( 'Entity "%s" requires a primary table.', $this->_sSubjectClass ) );
		}

		return $oSubject;
	}
	
	
	//// db crud methods: insert, update, and delete
	
	//
	public function insert( $oTable, $aAllValues, $aValues, $aOtherValues ) {
		
		$oDb = Geko::get( 'db' );
		
		$sTableName = $oTable->getTableName();
		
		
		// implement some "auto" functionality
		
		if ( $oTable->hasField( 'date_created' ) && !$aValues[ 'date_created' ] ) {
			$aValues[ 'date_created' ] = $oDb->getTimestamp();
		}

		if ( $oTable->hasField( 'date_modified' ) && !$aValues[ 'date_modified' ] ) {
			$aValues[ 'date_modified' ] = $oDb->getTimestamp();
		}
		

		// run validation hook
		$this->throwValidate( $aAllValues );							
		
		
		// try-catch any possible database errors
		
		try {
			
			// insert
			$oDb->insert( $sTableName, $aValues );
			
			// if primary key field name was provided, then get last insert id
			if ( $oPkf = $oTable->getPrimaryKeyField() ) {
				
				$sPkfName = $oPkf->getName();
				$iLastInsertId = $oDb->lastInsertId();
				
				$this->setEntityPropertyValue( $sPkfName, $oPkf->getAssertedValue( $iLastInsertId ) );
			}
			
			// hook method
			$this->handleOtherValues( $aOtherValues );
			
			
		} catch ( Zend_Db_Statement_Exception $e ) {
			
			$oRecordException = new Geko_Entity_Record_Exception( 'Insert failed!' );
			$oRecordException
				->setErrorType( 'db' )
				->setOrigMessage( $e->getMessage() )
			;
			
			throw $oRecordException;
		}

	}
	
	//
	public function update( $oTable, $aAllValues, $aValues, $aOtherValues, $aWhere ) {
		
		$oDb = Geko::get( 'db' );
		
		$sTableName = $oTable->getTableName();
		
		// run validation hook
		$this->throwValidate( $aAllValues, 'update' );
		
		
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
			
			// hook method
			$this->handleOtherValues( $aOtherValues, 'update' );
			
			
		} catch ( Zend_Db_Statement_Exception $e ) {
			
			$oRecordException = new Geko_Entity_Record_Exception( 'Update failed!' );
			$oRecordException
				->setErrorType( 'db' )
				->setOrigMessage( $e->getMessage() )
			;
			
			throw $oRecordException;
		}
		
	}
	
	//
	public function delete( $oTable, $aWhere ) {
		
		$oDb = Geko::get( 'db' );
		
		$sTableName = $oTable->getTableName();
		
		// Invoke deletion hook
		$this->beforeDelete();
		
		// try-catch any possible database errors
		
		try {
		
			// update
			$oDb->delete( $sTableName, $this->formatWhere( $aWhere ) );
			
			// Invoke deletion hook
			$this->afterDelete();
			
			
		} catch ( Zend_Db_Statement_Exception $e ) {
			
			$oRecordException = new Geko_Entity_Record_Exception( 'Delete failed!' );
			$oRecordException
				->setErrorType( 'db' )
				->setOrigMessage( $e->getMessage() )
			;
			
			throw $oRecordException;
		}
		
		
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
			
			$oRecordException = new Geko_Entity_Record_Exception( 'Validation failed!' );
			$oRecordException
				->setErrorType( 'validation' )
				->setErrorDetail( $aErrors )
			;
			
			$oRecordException = $this->applyPluginFilter(
				'modifyValidateException',
				$oRecordException,
				$aErrors,
				$aValues,
				$sMode,
				$this->_oSubject,
				$this
			);
			
			throw $oRecordException;
						
		}

		$this->doPluginAction( 'throwValidate', $aValues, $sMode, $this->_oSubject, $this );
		
	}
	
	
	// hook methods, to be implemented by sub-class
	
	//
	public function validate( $aValues, $sMode = 'insert' ) { }
	
	
	//
	public function handleOtherValues( $aOtherValues, $sMode = 'insert' ) {
		
		$this->doPluginAction( 'handleOtherValues', $aOtherValues, $sMode, $this->_oSubject, $this );
	}
	
	//
	public function beforeDelete() {
		
		$this->doPluginAction( 'beforeDelete', $this->_oSubject, $this );
	}
	
	//
	public function afterDelete() {
		
		$this->doPluginAction( 'afterDelete', $this->_oSubject, $this );
	}
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		
		if ( 0 === strpos( $sMethod, 'set' ) ) {
			
			$sProp = Geko_Inflector::underscore( substr( $sMethod, 3 ) );
			
			return $this->setEntityPropertyValue( $sProp, $aArgs[ 0 ] );
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', $this->_sDelegateClass, $sMethod ) );
	}

}


