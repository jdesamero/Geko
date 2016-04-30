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
	public function _setOrigValues() {
		
		if ( NULL === $this->_aOrigValues ) {
			
			$oSubject = $this->_oSubject;

			$oRawEntity = $oSubject->initRawEntity();
			$this->_aOrigValues = get_object_vars( $oRawEntity );			
		}
		
		return $this;
	}
	
	
	//
	public function setEntityPropertyValue() {
		
		$oSubject = $this->_oSubject;
		
		$aArgs = func_get_args();
		
		$this->_setOrigValues();
		
		if ( is_array( $aValues = $aArgs[ 0 ] ) ) {
			
			// associative array method
			
			foreach ( $aValues as $sKey => $mValue ) {
				$this->setEntityPropertyValue( $sKey, $mValue );
			}
			
		} else {

			// key/value pair method
			
			list( $sKey, $mValue ) = $aArgs;
			
			// get where key is mapped to
			$mProp = $oSubject->getEntityMapping( $sKey );
			
			// get the raw entity, remember, the Geko_Entity class knows nothing about mutating
			// the value of the raw entity
			$oRawEntity = $oSubject->getRawEntity();
			
			if ( is_string( $mProp ) ) {
				
				$oRawEntity->$mProp = $mValue;
			
			} elseif ( is_array( $mProp ) ) {
				
				foreach ( $mProp as $i => $sProp ) {
					$oRawEntity->$sProp = Geko_String::coalesce( $mValue[ $sProp ], $mValue[ $i ] );
				}
			}
					
		}
		
		return $oSubject;
	}
	
	
	//// entity record methods: save and destroy
	
	// will figure itself out whether to insert or update database entry
	public function save() {
		
		// $oSubject is a Geko_Entity
		$oSubject = $this->_oSubject;
		
		if ( $oTable = $oSubject->getPrimaryTable() ) {
			
			// format values
			list( $aAllValues, $aValues, $aOtherValues ) = $this->formatValues( $oTable, $oSubject );
			
			
			// are we creating a new record or updating an existing record?
			
			if ( $oPkf = $oTable->getPrimaryKeyField() ) {
				
				$sPkfName = $oPkf->getName();
				
				if (
					!$oSubject->hasEntityProperty( $sPkfName ) || 
					!$oSubject->getEntityPropertyValue( $sPkfName )
				) {
					
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
		
		
		list( $aAllValues, $aValues, $aOtherValues ) = $this->formatInsertValues(
			array( $aAllValues, $aValues, $aOtherValues )
		);
		
		
		
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
			
			throw $this->newException( 'db', 'Insert failed!', $e->getMessage() );
			
		}
		
	}
	
	//
	public function formatInsertValues( $aValues ) {
		return $aValues;
	}
	
	
	
	//
	public function update( $oTable, $aAllValues, $aValues, $aOtherValues, $aWhere ) {
		
		$oDb = Geko::get( 'db' );
		
		$sTableName = $oTable->getTableName();
				
		
		// implement some "auto" functionality
		
		if ( $oTable->hasField( 'date_created' ) && $aValues[ 'date_created' ] ) {
			unset( $aValues[ 'date_created' ] );		// retain original
		}
		
		if ( $oTable->hasField( 'date_modified' ) ) {
			$aValues[ 'date_modified' ] = $oDb->getTimestamp();
		}
		
		
		list( $aAllValues, $aValues, $aOtherValues ) = $this->formatUpdateValues(
			array( $aAllValues, $aValues, $aOtherValues )
		);

		
		// run validation hook
		$this->throwValidate( $aAllValues, 'update' );
		
		
		
		// try-catch any possible database errors
		
		try {
		
			// update
			$oDb->update( $sTableName, $aValues, $this->formatWhere( $aWhere ) );
			
			// hook method
			$this->handleOtherValues( $aOtherValues, 'update' );
			
			
		} catch ( Zend_Db_Statement_Exception $e ) {
			
			throw $this->newException( 'db', 'Update failed!', $e->getMessage() );
			
		}
		
	}
	
	//
	public function formatUpdateValues( $aValues ) {
		return $aValues;
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
			
			throw $this->newException( 'db', 'Delete failed!', $e->getMessage() );
			
		}
		
		
	}
	
	//
	protected function throwValidate( $aValues, $sMode = 'insert' ) {
		
		$this->_setOrigValues();
		
		if (
			( is_array( $aErrors = $this->validate( $aValues, $sMode ) ) ) && 
			( count( $aErrors ) > 0 )
		) {
			
			$oRecordException = $this->newException( 'validation', 'Validation failed!', '', $aErrors );
			
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
	
	
	
	
	
	//// overridable methods for custom behaviour
	
	// get raw entity values of subject and format according to table structure
	// data should now be good for insert/update
	protected function formatValues( $oTable, $oSubject ) {
		
		// get the field keys contained in the entity
		$aEntityKeys = array_keys( get_object_vars( $oSubject->getRawEntity() ) );
		
		// get field values of associated primary database table
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
		
		// return in sequence: $aAllValues, $aValues, $aOtherValues
		return array( array_merge( $aValues, $aOtherValues ), $aValues, $aOtherValues );
	}
		
	
	
	
	
	//// hook methods, to be implemented by sub-class
	
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
	
	
	
	
	
	//// helpers
	
	//
	protected function formatWhere( $aWhere ) {
		
		$aWhereFmt = array();
		
		foreach ( $aWhere as $sKey => $mValue ) {
			$aWhereFmt[ sprintf( '%s = ?', $sKey ) ] = $mValue;
		}
		
		return $aWhereFmt;
	}
	
	// shortcut for creating new exception
	protected function newException( $sType, $sMessage, $sOrigMessage = '', $aDetails = NULL ) {

		$oRecordException = new Geko_Entity_Record_Exception( $sMessage );
		
		$oRecordException->setErrorType( $sType );
		
		if ( $sOrigMessage ) {
			$oRecordException->setOrigMessage( $sOrigMessage );
		}
		
		// $aDetails is a key/value pair of field keys and corresponding error messages
		if ( is_array( $aDetails ) ) {
			$oRecordException->setErrorDetail( $aDetails );
		}
		
		return $oRecordException;
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


