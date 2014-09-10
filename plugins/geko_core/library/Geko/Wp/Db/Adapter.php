<?php

//
class Geko_Wp_Db_Adapter extends Zend_Db_Adapter_Abstract
{
	
	//
	protected $_numericDataTypes = array(
		Zend_Db::INT_TYPE => Zend_Db::INT_TYPE,
		Zend_Db::BIGINT_TYPE => Zend_Db::BIGINT_TYPE,
		Zend_Db::FLOAT_TYPE => Zend_Db::FLOAT_TYPE,
		'INT' => Zend_Db::INT_TYPE,
		'INTEGER' => Zend_Db::INT_TYPE,
		'MEDIUMINT' => Zend_Db::INT_TYPE,
		'SMALLINT' => Zend_Db::INT_TYPE,
		'TINYINT' => Zend_Db::INT_TYPE,
		'BIGINT' => Zend_Db::BIGINT_TYPE,
		'SERIAL' => Zend_Db::BIGINT_TYPE,
		'DEC' => Zend_Db::FLOAT_TYPE,
		'DECIMAL' => Zend_Db::FLOAT_TYPE,
		'DOUBLE' => Zend_Db::FLOAT_TYPE,
		'DOUBLE PRECISION' => Zend_Db::FLOAT_TYPE,
		'FIXED' => Zend_Db::FLOAT_TYPE,
		'FLOAT' => Zend_Db::FLOAT_TYPE
	);
	
	//
	protected $_stmt = null;
	
	
	//
	protected $_defaultStmtClass = 'Geko_Wp_Db_Statement';
	
	
	
	
	
	//
	protected function _quote( $mValue ) {
		
		if ( is_int( $mValue ) || is_float( $mValue ) ) {
			return $mValue;
		}
		
		$this->_connect();
		
		return $this->_connection->prepare( '%s', $mValue );
	}
	
	//
	public function getQuoteIdentifierSymbol() {
		return '`';
	}
	
	//
	public function listTables() {
		
		$this->_connect();
		
		$wpdb = $this->_connection;
		
		$sErrBefore = $wpdb->last_error;
		
		$aRes = $wpdb->get_col( 'SHOW TABLES' );

		$sErrAfter = $wpdb->last_error;
		
		if ( ( $sErrBefore != $sErrAfter ) && $sErrAfter ) {
			//
			throw new Geko_Wp_Db_Adapter_Exception( sprintf( '%s error: %s', __METHOD__, $sErrAfter ) );
		}
		
		return $aRes;
	}
	
	//
	public function describeTable( $sTableName, $sSchemaName = NULL ) {
		
		$this->_connect();
		
		$aDesc = array();
		
		$sFullName = $sTableName;
		
		if ( $sSchemaName ) {
			$sFullName = sprintf( '%s.%s', $sSchemaName, $sTableName );
		}
		
		$wpdb = $this->_connection;
		
		$sQuery = sprintf( 'DESCRIBE %s', $this->quoteIdentifier( $sFullName ) );
		
		$sErrBefore = $wpdb->last_error;
		
		$aRes = $wpdb->get_results( $sQuery, ARRAY_A );

		$sErrAfter = $wpdb->last_error;
		
		if ( ( $sErrBefore != $sErrAfter ) && $sErrAfter ) {
			//
			throw new Geko_Wp_Db_Adapter_Exception( sprintf( '%s error: %s', __METHOD__, $sErrAfter ) );
		}
		
		
		//// from Zend_Db_Adapter_Mysqli
		
        $aDesc = array();
		
		$aRowDefaults = array(
			'Length' => NULL,
			'Scale' => NULL,
			'Precision' => NULL,
			'Unsigned' => NULL,
			'Primary' => FALSE,
			'PrimaryPosition' => NULL,
			'Identity' => FALSE
		);
		
		$i = 1;
		$p = 1;

		foreach ( $aRes as $sKey => $aRow ) {
			
			$aRegs = array();
			
			$aRow = array_merge( $aRowDefaults, $aRow );
			
			if ( preg_match( '/unsigned/', $aRow[ 'Type' ] ) ) {
				$aRow[ 'Unsigned' ] = TRUE;
			}
			
			if ( preg_match( '/^((?:var)?char)\((\d+)\)/', $aRow[ 'Type' ], $aRegs ) ) {
				
				$aRow[ 'Type' ] = $aRegs[ 1 ];
				$aRow[ 'Length' ] = $aRegs[ 2 ];
			
			} elseif ( preg_match( '/^decimal\((\d+),(\d+)\)/', $aRow[ 'Type' ], $aRegs ) ) {
				
				$aRow[ 'Type' ] = 'decimal';
				$aRow[ 'Precision' ] = $aRegs[ 1 ];
				$aRow[ 'Scale' ] = $aRegs[ 2 ];
			
			} else if ( preg_match( '/^float\((\d+),(\d+)\)/', $aRow[ 'Type' ], $aRegs ) ) {
				
				$aRow[ 'Type' ] = 'float';
				$aRow[ 'Precision' ] = $aRegs[ 1 ];
				$aRow[ 'Scale' ] = $aRegs[ 2 ];
			
			} else if ( preg_match( '/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $aRow[ 'Type' ], $aRegs ) ) {
				
				$aRow[ 'Type' ] = $aRegs[ 1 ];
			}
			
			if ( 'PRI' == strtoupper( $aRow[ 'Key' ] ) ) {
				
				$aRow[ 'Primary' ] = TRUE;
				$aRow[ 'PrimaryPosition' ] = $p;
				
				if ( 'auto_increment' == $aRow[ 'Extra' ] ) {
					$aRow[ 'Identity' ] = TRUE;
				} else {
					$aRow[ 'Identity' ] = FALSE;
				}
				
				++$p;
			}
			
			$aDesc[ $this->foldCase( $aRow[ 'Field' ] ) ] = array(
				'SCHEMA_NAME' => NULL, // @todo
				'TABLE_NAME' => $this->foldCase( $tableName ),
				'COLUMN_NAME' => $this->foldCase( $aRow[ 'Field' ] ),
				'COLUMN_POSITION' => $i,
				'DATA_TYPE' => $aRow[ 'Type' ],
				'DEFAULT' => $aRow[ 'Default' ],
				'NULLABLE' => ( bool ) ( 'YES' == $aRow[ 'Null' ] ),
				'LENGTH' => $aRow[ 'Length' ],
				'SCALE' => $aRow[ 'Scale' ],
				'PRECISION' => $aRow[ 'Precision' ],
				'UNSIGNED' => $aRow[ 'Unsigned' ],
				'PRIMARY' => $aRow[ 'Primary' ],
				'PRIMARY_POSITION' => $aRow[ 'PrimaryPosition' ],
				'IDENTITY' => $aRow[ 'Identity' ]
			);
			
			++$i;
		}
		
        return $aDesc;
	}
	
	
	
	//
	protected function _connect() {
		
		if ( $this->_connection ) {
			return;
		}
		
		if ( $this->_config[ 'useNewConnection' ] ) {
			
			// use new connection
			
			$this->_connection = new wpdb(
				$this->_config[ 'username' ],
				$this->_config[ 'password' ],
				$this->_config[ 'dbname' ],
				$this->_config[ 'host' ]
			);
			
		} else {

			global $wpdb;
			
			$this->_connection = $wpdb;
			
		}
		
	}
	
	
	
	//
	public function isConnected() {		
		return ( $this->_connection ) ? TRUE : FALSE ;
	}
	
	
	//
	public function closeConnection() {
		unset( $this->_connection );
	}
	
	//
	public function prepare( $sql ) {
		
		$stmtClass = $this->_defaultStmtClass;
		
		$stmt = new $stmtClass( $this, $sql );
		if ( $stmt === false ) {
			return false;
		}
			
		$stmt->setFetchMode( $this->_fetchMode );
		$this->_stmt = $stmt;
		
		return $stmt;
	}
	
	//
	public function lastInsertId( $tableName = null, $primaryKey = null ) {
		
		return $this->_connection->insert_id;
	}
	
	//
	protected function _beginTransaction() {
		//
	}
	
	//
	protected function _commit() {
		//
	}
	
	//
	protected function _rollBack() {
		//
	}
	
	//
	public function setFetchMode( $iMode ) {

		switch ( $iMode ) {
			
			case Zend_Db::FETCH_LAZY:
			case Zend_Db::FETCH_ASSOC:
			case Zend_Db::FETCH_NUM:
			case Zend_Db::FETCH_OBJ:
			case Zend_Db::FETCH_NAMED:
			case Zend_Db::FETCH_BOTH:
			
				$this->_fetchMode = $iMode;
				break;
								
			case Zend_Db::FETCH_BOUND: // bound to PHP variable
				
				//
				throw new Geko_Wp_Db_Adapter_Exception( 'FETCH_BOUND is not supported yet' );
				break;
				
			default:
				
				//
				throw new Geko_Wp_Db_Adapter_Exception( sprintf( 'Invalid fetch mode (%d) specified', $iMode ) );
		}
		
		return $this;
	}
	
	
	//
	public function limit( $sql, $count, $offset = 0 ) {
		
		return $sql;
	}
	
	//
	public function supportsParameters( $type ) {
		//
	}
	
	//
	public function getServerVersion() {
		return $this->_connection->db_version();
	}
	
	
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
	
	//// custom Geko methods
	
	//
	public function registerTableName( $sPrefixedTableName, $sTableName ) {
		
		$this->_connect();
		
		$wpdb = $this->_connection;
		$oDb = Geko_Wp::get( 'db' );
		
		if ( $wpdb && $oDb ) {
			
			$sTableName = str_replace( $oDb->getPrefixPlaceholder(), '', $sTableName );
			
			if ( $sPrefixedTableName != $sTableName ) {
				$wpdb->$sTableName = $sPrefixedTableName;
			}
		}
		
	}
	
	//
	public function createTable( $sSql ) {
		
		require_once( sprintf( '%swp-admin/includes/upgrade.php', ABSPATH ) );
		
		return dbDelta( $sSql );		
	}
	
	
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
	
	//
	public static function argFormat( $sDbClass, $aParams ) {
		
		global $wpdb;
		
		$bUseNewConn = FALSE;
		
		if ( !$aParams[ 'dbname' ] ) {
			$aParams[ 'dbname' ] = DB_NAME;
		} else {
			$bUseNewConn = TRUE;
		}

		if ( !$aParams[ 'username' ] ) {
			$aParams[ 'username' ] = DB_USER;		
		} else {
			$bUseNewConn = TRUE;
		}
		
		if ( !$aParams[ 'password' ] ) {
			$aParams[ 'password' ] = DB_PASSWORD;		
		} else {
			$bUseNewConn = TRUE;
		}
		
		if ( !$aParams[ 'host' ] ) {
			$aParams[ 'host' ] = DB_HOST;
		} else {
			$bUseNewConn = TRUE;		
		}
		
		if ( !$aParams[ 'table_prefix' ] ) {
			if ( $wpdb->prefix ) {
				$aParams[ 'table_prefix' ] = $wpdb->prefix;
			}
		} else {
			$bUseNewConn = TRUE;
		}
		
		$aParams[ 'useNewConnection' ] = $bUseNewConn;
		
		return array( $sDbClass, $aParams );
	}
	

}


