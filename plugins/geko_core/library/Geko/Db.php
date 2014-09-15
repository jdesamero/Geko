<?php

// wrapper for Zend_Db::factory
class Geko_Db
{
	
	protected static $_bReplacePrefixPlaceholder = TRUE;
	protected static $_bResolveNamedParams = FALSE;
	
	
	protected $_sPrefix = NULL;
	protected $_sPrefixPlaceholder = '##pfx##';
	
	protected $_oDb;
	protected $_sDbClass;
	protected $_sVendorClass;							// some Geko_Db_* class
	
	protected $_aDbVendorMapping = array(
		'Zend_Db_Adapter_Pdo_Mysql' => 'Mysql',
		'Zend_Db_Adapter_Pdo_Sqlite' => 'Sqlite',
		'Geko_Wp_Db_Adapter' => 'Mysql'					// HACKISH!!! Temporary solution
	);
	
	protected $_aTables = array();
	protected $_aRoutines = array();
	
	protected $_bHasRegisterTableMethod = FALSE;
	protected $_bHasRegisterRoutineMethod = FALSE;
	protected $_sCreateTableMethod = 'query';
	
	protected $_sDbName = '';
	
	
	
	//
	public static function factory() {
		
		$aArgs = func_get_args();
		
		//// determine the adapter class name
		
		$aArgParams = $aArgs[ 1 ];
		
		//
		$sDbAdptClass = $aArgs[ 0 ];
		
		if ( !$sAdptNamespace = $aArgParams[ 'adapterNamespace' ] ) {
			$sAdptNamespace = 'Zend_Db_Adapter';
		}
		
		$sDbAdptClass = sprintf( '%s_%s', $sAdptNamespace, $sDbAdptClass );
		
		
		//// look for argFormat method
		
		$fnArgFormat = $aArgParams[ 'argFormatCallback' ];
		
		// second check, use default if it exists
		if ( !$fnArgFormat && method_exists( $sDbAdptClass, 'argFormat' ) ) {
			$fnArgFormat = 'argFormat';
		}
		
		//
		if ( $fnArgFormat ) {
			$aArgs = call_user_func( array( $sDbAdptClass, $fnArgFormat ), $aArgs[ 0 ], $aArgParams );
		}
		
		
		
		//// create native instance of Zend_Db_Adapter
		
		$oDb = call_user_func_array( array( 'Zend_Db', 'factory' ), $aArgs );
		
		// wrap it
		$oDbWrap = new Geko_Db( $oDb );
		
		if ( $sPrefix = $aArgs[ 1 ][ 'table_prefix' ] ) {
			$oDbWrap->setPrefix( $sPrefix );
		}
		
		$oDbWrap->postConstruct();
		
		
		return $oDbWrap;
	}
	
	
	//
	public static function setReplacePrefixPlaceholder( $bReplacePrefixPlaceholder ) {
		self::$_bReplacePrefixPlaceholder = $bReplacePrefixPlaceholder;
	}
	
	//
	public static function setResolveNamedParams( $bResolveNamedParams ) {
		self::$_bResolveNamedParams = $bResolveNamedParams;
	}
	
	
	
	
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
	
	
	//
	public function __construct( $oDb ) {
		
		$this->_oDb = $oDb;
		
		$this->_sDbClass = get_class( $oDb );
		$this->_sVendorClass = sprintf( 'Geko_Db_%s', $this->_aDbVendorMapping[ $this->_sDbClass ] );
		
		$aConfig = $oDb->getConfig();
		
		
		//// initial setup stuff
		
		$this->_sDbName = $aConfig[ 'dbname' ];
		
		// method overrides
		
		if ( method_exists( $oDb, 'registerTableName' ) ) {
			$this->_bHasRegisterTableMethod = TRUE;
		}		
		
		if ( method_exists( $oDb, 'registerRoutineName' ) ) {
			$this->_bHasRegisterRoutineMethod = TRUE;
		}		
		
		if ( method_exists( $oDb, 'createTable' ) ) {
			$this->_sCreateTableMethod = 'createTable';
		}
		
		
	}
	
	
	// HACKISH!!!
	public function postConstruct() {
		
		$oDb = $this->_oDb;
		
		// get all currently existing tables and routines
		
		$aTables = $oDb->listTables();
		
		foreach ( $aTables as $sTable ) {
			$this->registerTable( $sTable );
		}
		
		
		$aRoutines = $this->getUserRoutines();
		
		foreach ( $aRoutines as $sRoutine ) {
			$this->registerRoutine( $sRoutine );
		}
		
		
		return $this;
	}
	
	
	
	//
	public function setPrefix( $sPrefix ) {
		$this->_sPrefix = $sPrefix;
		return $this;
	}
	
	//
	public function getPrefix() {
		return $this->_sPrefix;
	}
	
	//
	public function setPrefixPlaceholder( $sPrefixPlaceholder ) {
		$this->_sPrefixPlaceholder = $sPrefixPlaceholder;
		return $this;
	}
	
	//
	public function getPrefixPlaceholder() {
		return $this->_sPrefixPlaceholder;
	}
	
	//
	public function replacePrefixPlaceholder( $sOutput, $bRemove = FALSE ) {
		
		if ( NULL !== $this->_sPrefix ) {
			
			$sReplace = ( $bRemove ) ? '' : $this->_sPrefix ;
			
			return str_replace( $this->_sPrefixPlaceholder, $sReplace, $sOutput );
		}
		
		return $sOutput;
	}
	
	//
	public function getDb() {
		return $this->_oDb;
	}
	
	//
	public function getDbClass() {
		return $this->_sDbClass;
	}
	
	//
	public function getDbName() {
		return $this->_sDbName;
	}
	
	//
	public function getHasRegisterTableMethod() {
		return $this->_bHasRegisterTableMethod;
	}
	
	
	// get the prefixed table name, if it exists
	public function getPrefixedTableName( $sTableName ) {
		
		$sPrefixedTableName = sprintf( '%s%s', $this->_sPrefix, $sTableName );
		
		if ( $this->_aTables[ $sPrefixedTableName ] ) {
			return $sPrefixedTableName;
		}
		
		return '';
	}
	
	// shortcut to getPrefixedTableName()
	public function _p( $sTableName ) {
		return $this->getPrefixedTableName( $sTableName );
	}
	
	
	//
	public function getUnprefixedTableName( $sPrefixedTableName ) {
		
		if ( 0 === strpos( $sPrefixedTableName, $this->_sPrefix ) ) {
			return substr( $sPrefixedTableName, strlen( $this->_sPrefix ) );
		}
		
		return $sPrefixedTableName;
	}
	
	//
	public function getUnprefixedRoutineName( $sPrefixedRoutineName ) {
		return $this->getUnprefixedTableName( $sPrefixedRoutineName );
	}
	
	//
	public function registerTable( $sTableName ) {
		
		$this->_aTables[ $sTableName ] = TRUE;
		
		if ( $this->_bHasRegisterTableMethod ) {
			
			$sUnprefixedTableName = $this->getUnprefixedTableName( $sTableName );
			
			if ( $sUnprefixedTableName != $sTableName ) {
				
				$oDb = $this->_oDb;
				$oDb->registerTableName( $sTableName, $sUnprefixedTableName, $this );
			}
			
		}
		
	}
	
	//
	public function registerRoutine( $sRoutineName ) {
	
		$this->_aRoutines[ $sRoutineName ] = TRUE;
		
		if ( $this->_bHasRegisterRoutineMethod ) {
			
			$sUnprefixedRoutineName = $this->getUnprefixedRoutineName( $sRoutineName );
			
			if ( $sUnprefixedRoutineName != $sRoutineName ) {
				
				$oDb = $this->_oDb;
				$oDb->registerRoutineName( $sRoutineName, $sUnprefixedRoutineName, $this );
			}
			
		}

	}
	
	
	
	////
	
	//
	public function resolveNamedParams( $sQuery, $mParams ) {
		
		if ( $mParams ) {
			
			if ( !is_array( $mParams ) ) {
				$aParams = array( $mParams );
			} else {
				$aParams = $mParams;
			}
			
			$aRegs = array();
			
			if ( preg_match_all( '/:[A-Za-z0-1_]+|\?/sm', $sQuery, $aRegs ) )  {
				
				$aNamedParams = array();
				$aIntParams = array();
				
				foreach ( $aParams as $mKey => $sValue ) {
					
					if ( is_int( $mKey ) ) {
						$aIntParams[] = $sValue;
					} else {
						$aNamedParams[ $mKey ] = $sValue;
					}
					
				}
				
				$aParams = array();			// reset
				
				$aMatches = $aRegs[ 0 ];
				
				foreach ( $aMatches as $sPlaceholder ) {
					
					if ( '?' == $sPlaceholder ) {
						
						$sParam = '';
						if ( count( $aIntParams ) > 0 ) {
							$sParam = array_shift( $aIntParams );
						}
						
						$aParams[] = $sParam;
						
					} else {
						
						$aParams[] = $aNamedParams[ $sPlaceholder ];
						$sQuery = Geko_String::replaceFirstMatch( $sPlaceholder, '?', $sQuery );
					}
				}
				
			}
		
		}
		
		return array( $sQuery, $aParams );
	}
	
	
	
	
	//// delegate to matching vendor handler
	
	//
	public function getUserRoutines() {
		return call_user_func( array( $this->_sVendorClass, 'getUserRoutines' ), $this );
	}

	//
	public function getTimestamp() {
		return call_user_func( array( $this->_sVendorClass, 'getTimestamp' ) );
	}
	
	//
	public function gekoQueryInit( $oQuery, $aParams ) {
		return call_user_func( array( $this->_sVendorClass, 'gekoQueryInit' ), $oQuery, $aParams );
	}
	
	//
	public function gekoQueryOrderRandom( $oQuery ) {
		return call_user_func( array( $this->_sVendorClass, 'gekoQueryOrderRandom' ), $oQuery, $aParams );
	}
	
	//
	public function gekoQueryFoundRows( $oEntityQuery ) {
		return call_user_func( array( $this->_sVendorClass, 'gekoQueryFoundRows' ), $oEntityQuery );	
	}
	
	
	
	
	//
	public function tableCreateIfNotExists() {
		
		$aArgs = func_get_args();
		
		if ( $aArgs[ 0 ] instanceof Geko_Sql_Table ) {
			
			$oTable = $aArgs[ 0 ];
			
			$sTableName = $oTable->getTableName();
			$sQuery = strval( $oTable );
			
		} elseif ( is_string( $aArgs[ 0 ] ) && is_string( $aArgs[ 1 ] ) ) {

			$sTableName = $aArgs[ 0 ];
			$sQuery = $aArgs[ 1 ];
		}
		
		
		//
		if ( $sTableName && $sQuery ) {
			
			$oDb = $this->_oDb;
			
			$sPrefixedTableName = $this->replacePrefixPlaceholder( $sTableName );
			
			// attempt to create table if it does not exist
			if ( !$this->tableExists( $sPrefixedTableName ) ) {
				
				$sQuery = $this->replacePrefixPlaceholder( $sQuery );
				
				// this creates the table
				$sCreateTable = $this->_sCreateTableMethod;
				$oDb->$sCreateTable( $sQuery );
				
				
				$this->registerTable( $sPrefixedTableName );
				
				// TO DO: check for failure to create table
				
				return TRUE;
			}
						
		}
		
		return FALSE;
	}
	
	
	//
	public function tableExists( $sTable ) {
		return ( $this->_aTables[ $sTable ] ) ? TRUE : FALSE ;
	}
	
	
	//
	public function routineCreateIfNotExists( $sRoutineName, $sQuery ) {
		
		if ( $sRoutineName && $sQuery ) {
			
			$oDb = $this->_oDb;
			
			$sPrefixedRoutineName = $this->replacePrefixPlaceholder( $sRoutineName );
			
			// attempt to create routine if it does not exist
			if ( !$this->routineExists( $sPrefixedRoutineName ) ) {
				
				$sQuery = $this->replacePrefixPlaceholder( $sQuery );
				
				// this creates the routine
				$bRes = $oDb->query( $sQuery );
				
				$this->registerRoutine( $sPrefixedRoutineName );
				
				// TO DO: check for failure to create function
				
				return $bRes;
			}
		}
		
		return FALSE;
	}
	
	
	//
	public function routineExists( $sRoutine ) {
		return ( $this->_aRoutines[ $sRoutine ] ) ? TRUE : FALSE ;
	}
	
	
	
	//
	public function getTableNumRows( $sTable ) {
		
		$sTable = $this->replacePrefixPlaceholder( $sTable );
		
		if ( !$this->tableExists( $sTable ) ) {
			
			$sTable = $this->_p( $sTable );
			
			if ( !$this->tableExists( $sTable ) ) {
				return FALSE;
			}
		}
		
		if ( $sTable ) {
			
			$sQuery = sprintf( 'SELECT COUNT(*) AS num_rows FROM %s', $sTable );
			
			$oDb = $this->_oDb;
			
			return intval( $oDb->fetchOne( $sQuery ) );
		}
		
		return FALSE;
	}
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( $oDb = $this->_oDb ) {
			
			if ( in_array( $sMethod, array(
				'insert', 'update', 'delete', 'fetchAll', 'fetchAssoc', 'fetchCol', 'fetchPairs', 'fetchRow', 'fetchOne', 'describeTable', 'query'
			) ) ) {
				
				if ( self::$_bReplacePrefixPlaceholder ) {
					$aArgs[ 0 ] = $this->replacePrefixPlaceholder( $aArgs[ 0 ] );
				}
				
				$bResolveNamedParams = NULL;
				
				$aConfig = $oDb->getConfig();
				
				if ( isset( $aConfig[ 'resolveNamedParams' ] ) ) {
					$bResolveNamedParams = $aConfig[ 'resolveNamedParams' ];
				} else {
					$bResolveNamedParams = self::$_bResolveNamedParams;
				}
				
				if ( $bResolveNamedParams ) {
					$aArgs = $this->resolveNamedParams( $aArgs[ 0 ], $aArgs[ 1 ] );
				}
				
			}
			
			// delegate
			return call_user_func_array( array( $oDb, $sMethod ), $aArgs );
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', get_class( $this ), $sMethod ) );
	}
	
	
	//
	public function debug() {
		print_r( $this->_aTables );
	}
	
	
}


