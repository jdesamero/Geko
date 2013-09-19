<?php

//
class Geko_Sql_Table
{
	
	//// constants
	
	// operations
	const CREATE = 1;
	const ALTER = 2;
	const RENAME = 3;
	const DROP = 4;
	
	
	
	// properties
	protected $_iClause;
	protected $_aTable = array();
	protected $_aFields = array();
	protected $_aIndexKey = array();
	protected $_aIndexUnq = array();
	protected $_aOptions = array();
	
	protected $_aTypes = array(
		'bool', 'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'bit',
		'real', 'double', 'float', 'decimal', 'numeric',
		'char', 'varchar',
		'binary', 'varbinary',
		'date', 'time', 'datetime', 'timestamp', 'year',
		'tinyblob', 'blob', 'mediumblob', 'longblob',
		'tinytext', 'text', 'mediumtext', 'longtext',
		'enum', 'set'
	);
	
	protected $_aNumericTypes = array(
		'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'bit',
		'real', 'double', 'float', 'decimal', 'numeric'
	);
	
	protected $_aEngines = array(
		'myisam' => 'MyISAM',
		'innodb' => 'InnoDB',
		'ibmdb2i' => 'IBMDB2I',
		'merge' => 'MERGE',
		'memory' => 'MEMORY',
		'example' => 'EXAMPLE',
		'federated' => 'FEDERATED',
		'archive' => 'ARCHIVE',
		'csv' => 'CSV',
		'blackhole' => 'BLACKHOLE'
	);
	
	protected $_aQueryOptions = array(
		'auto_increment', 'avg_row_length', 'checksum', 'comment', 'connection',
		'data directory', 'delay_key_write', 'index directory', 'insert_method', 'key_block_size',
		'max_rows', 'min_rows', 'pack_keys', 'password', 'row_format', 'tablespace', 'union'
	);
	
	protected $_aQueryOptionsWithDefault = array(
		'character set', 'collate'
	);
	
	protected $_oDb = NULL;
	
	
	
	//// constructor
	public function __construct( $oDb = NULL ) {
		$this->_oDb = $oDb;
	}
	
	//
	public function create( $sKey, $sPrefix = '' ) {
		$this->_iClause = self::CREATE;
		$this->table( $sKey, $sPrefix );
		return $this;
	}
	
	//
	public function drop( $sKey, $sPrefix = '' ) {
		$this->_iClause = self::DROP;
		$this->table( $sKey, $sPrefix );
		return $this;	
	}
	
	//
	public function table( $sKey, $sPrefix = '' ) {
		$this->_aTable = array( $sKey, $sPrefix );
		return $this;
	}
	
	//
	public function field( $sType, $sKey, $aParams = array() ) {
		array_unshift( $aParams, $sType );
		$this->_aFields[ $sKey ] = $aParams;
		return $this;
	}
	
	//
	public function indexKey( $sKey, $aParams ) {
		$this->_aIndexKey[ $sKey ] = $aParams;
		return $this;
	}
	
	//
	public function indexUnq( $sKey, $aParams ) {
		$this->_aIndexUnq[ $sKey ] = $aParams;
		return $this;
	}
	
	//
	public function option( $sKey, $mParams ) {
		$this->_aOptions[ $sKey ] = $mParams;
		return $this;	
	}
	
	
	
	//// accessors
	
	//
	public function getRawTableName() {
		return $this->_aTable[ 0 ];
	}
	
	//
	public function getTableName() {
		
		$sTable = $this->_aTable[ 0 ];
		
		// auto-prefix replacement
		if ( $oDb = $this->_oDb ) {
			$sTable = $oDb->replacePrefixPlaceholder( $sTable );
		}
		
		return $sTable;
	}
	
	//
	public function getFields( $bUseKey = FALSE ) {
		
		$aFieldsFmt = array();
		
		foreach ( $this->_aFields as $sFieldName => $aParams ) {
			$oField = new Geko_Sql_Table_Field( $sFieldName, $aParams );
			if ( $bUseKey ) {
				$aFieldsFmt[ $sFieldName ] = $oField;
			} else {
				$aFieldsFmt[] = $oField;
			}
		}
		
		return $aFieldsFmt;
	}
	
	//
	public function getPrimaryKeyField() {

		foreach ( $this->_aFields as $sFieldName => $aParams ) {
			if ( $this->hasFlag( 'prky', $aParams ) ) {
				return new Geko_Sql_Table_Field( $sFieldName, $aParams );
			}
		}
		
		return NULL;
	}
	
	// multi-key support
	// return an array of <sql table field objects> which is used as the
	// unique identifier for a given row
	public function getKeyFields( $bUseKey = FALSE ) {
		
		$aRet = array();
		
		if ( $oPkf = $this->getPrimaryKeyField() ) {
			$aRet[] = $oPkf;
		} elseif ( count( $this->_aIndexUnq ) > 0 ) {
			
			// get first registered unique index
			foreach ( $this->_aIndexUnq as $aFields ) break;

			foreach ( $aFields as $sFieldName ) {
				$aRet[] = new Geko_Sql_Table_Field(
					$sFieldName, $this->_aFields[ $sFieldName ]
				);
			}
		}
		
		if ( $bUseKey ) {
			$aRetFmt = array();
			foreach ( $aRet as $oField ) {
				$aRetFmt[ $oField->getFieldName() ] = $oField;
			}
			return $aRetFmt;
		}
		
		return $aRet;
	}
	
	//
	public function getSelect() {
		
		$oSqlSelect = new Geko_Sql_Select();
		
		list( $sTableName, $sTablePrefix ) = $this->_aTable;
		
		$sFieldPrefix = '';
		if ( $sTablePrefix ) $sFieldPrefix = $sTablePrefix . '.';
		
		foreach ( $this->_aFields as $sFieldName => $aParams ) {
			$oSqlSelect->field( $sFieldPrefix . $sFieldName );
		}
		
		if ( !$sTablePrefix ) $sTablePrefix = NULL;
		
		$oSqlSelect->from( $sTableName, $sTablePrefix );
		
		return $oSqlSelect;
		
	}
	
	
	
	//// helpers
	
	//
	public function hasFlag( $mFlag, $aParams ) {
		return in_array( $mFlag, $aParams );
	}
	
	//
	public function hasField( $sFieldNameCheck ) {
		foreach ( $this->_aFields as $sFieldName => $aParams ) {
			if ( $sFieldNameCheck == $sFieldName ) return TRUE;
		}
		return FALSE;
	}
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		//
		if ( 0 === strpos( $sMethod, 'field' ) ) {
			$sFieldType = strtolower( str_replace( 'field', '', $sMethod ) );
			if ( in_array( $sFieldType, $this->_aTypes ) ) {
				array_unshift( $aArgs, $sFieldType );
				return call_user_func_array( array( $this, 'field' ), $aArgs );
			}
		}
		
		throw new Exception( 'Invalid method ' . $this->_sEntityClass . '::' . $sMethod . '() called.' );
		
	}
	
	// output the completed query
	public function __toString() {
		
		$sOutput = '';
		
		// main clause
		
		if ( self::CREATE == $this->_iClause ) {
			$sOutput = 'CREATE TABLE ' . $this->_aTable[ 0 ] . ' ( ';
		}
		
		// fields
		
		$bFirst = TRUE;
		foreach ( $this->_aFields as $sFieldName => $aParams ) {
			
			if ( $bFirst ) $bFirst = FALSE;
			else $sOutput .= ' , ';
			
			// field name and type
			$sFieldType = $aParams[ 0 ];
			$sOutput .= $sFieldName . ' ' . strtoupper( $sFieldType );
			
			// size, if any
			if ( $mSize = $aParams[ 'size' ] ) {
				$sOutput .= '(' . $mSize . ')';
			}
			
			// flags: unsgnd, notnull, autoinc
			// TO DO: recognize multiple forms, eg: autoinc, autoincrement, ai
			//        will work for AUTO_INCREMENT
			
			// only allow numeric types for signed/unsigned
			if ( in_array( $sFieldType, $this->_aNumericTypes ) ) {
				if ( $this->hasFlag( 'unsgnd', $aParams ) ) $sOutput .= ' UNSIGNED';
				if ( $this->hasFlag( 'sgnd', $aParams ) ) $sOutput .= ' SIGNED';
			}
			
			if ( $this->hasFlag( 'notnull', $aParams ) ) $sOutput .= ' NOT NULL';
			
			// only allow numeric types for auto_increment
			if ( in_array( $sFieldType, $this->_aNumericTypes ) ) {
				if ( $this->hasFlag( 'autoinc', $aParams ) ) $sOutput .= ' AUTO_INCREMENT';
			}
			
			// default value, if any
			if ( $mDefVal = $aParams[ 'default' ] ) {
				$sOutput .= " DEFAULT '" . $mDefVal . "' ";
			}
			
		}
		
		// indexes: prky, unq, key
		foreach ( $this->_aFields as $sFieldName => $aParams ) {
			
			// prky
			
			if ( $this->hasFlag( 'prky', $aParams ) ) {
				$sOutput .= ' , PRIMARY KEY(' . $sFieldName . ')';
			}
			
			// unq
			
			if ( $this->hasFlag( 'unq', $aParams ) ) $aParams[ 'unq' ] = $sFieldName;
			
			if ( $sIndexName = $aParams[ 'unq' ] ) {
				$sOutput .= ' , UNIQUE KEY ' . $sIndexName . '(' . $sFieldName . ')';			
			}
			
			// key

			if ( $this->hasFlag( 'key', $aParams ) ) $aParams[ 'key' ] = $sFieldName;
			
			if ( $sIndexName = $aParams[ 'key' ] ) {
				$sOutput .= ' , KEY ' . $sIndexName . '(' . $sFieldName . ')';
			}
			
		}
		
		//
		foreach ( $this->_aIndexKey as $sIndexName => $aParams ) {
			$sOutput .= ' , KEY ' . $sIndexName . '(' . implode( ', ', $aParams ) . ')';
		}
		
		//
		foreach ( $this->_aIndexUnq as $sIndexName => $aParams ) {
			$sOutput .= ' , UNIQUE KEY ' . $sIndexName . '(' . implode( ', ', $aParams ) . ')';
		}
		
		$sOutput .= ' ) ';
		
		// options
		foreach ( $this->_aOptions as $sOption => $mParams ) {
			if ( 'engine' == $sOption ) {
				$sEngine = strtolower( ( is_array( $mParams ) ) ? $mParams[ 0 ] : $mParams );
				if ( $sEngine = $this->_aEngines[ $sEngine ] ) {
					$sOutput .= ' ENGINE=' . $sEngine . ' ';
				}
			}
		}
		
		// auto-prefix replacement
		if ( $oDb = $this->_oDb ) {
			$sOutput = $oDb->replacePrefixPlaceholder( $sOutput );
		}
		
		return trim( $sOutput );
	}
	
}

