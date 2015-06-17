<?php

//
class Geko_Sql_Table implements Geko_Json_Encodable
{
	
	//// constants
	
	// operations
	const CREATE = 1;
	const ALTER = 2;
	const RENAME = 3;
	const DROP = 4;
	
	
	
	// properties
	
	protected $_sPrefixedTableName = NULL;
	protected $_sNoPrefixTableName = NULL;
	
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
		
		if ( NULL === $oDb ) {
			$oDb = Geko::get( 'db' );
		}
		
		$this->_oDb = $oDb;
		
	}
	
	//
	public function create( $sKey, $sPrefix = '' ) {
		
		$this->_iClause = self::CREATE;
		
		$this->table( $sKey, $sPrefix );
		
		
		// auto table registry
		if (
			( $oDb = $this->_oDb ) && 
			( $oDb->getHasRegisterTableMethod() )
		) {
			
			$sUnprefixedTable = $oDb->replacePrefixPlaceholder( $sKey, TRUE );
			$sTable = $oDb->replacePrefixPlaceholder( $sKey );
			
			if (
				( $sUnprefixedTable != $sTable ) &&
				( !$oDb->_p( $sUnprefixedTable ) )
			) {
				$oDb->registerTableName( $sTable, $sUnprefixedTable );
			}
			
		}
		
		
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
	public function initTableName() {
		
		if ( !$this->_sPrefixedTableName && !$this->_sNoPrefixTableName ) {

			$sTable = $this->getRawTableName();
			
			// auto-prefix replacement
			if ( $oDb = $this->_oDb ) {
				
				$this->_sPrefixedTableName = $oDb->replacePrefixPlaceholder( $sTable );			
				$this->_sNoPrefixTableName = $oDb->replacePrefixPlaceholder( $sTable, TRUE );			
				
			} else {
				
				$this->_sPrefixedTableName = $sTable;
				$this->_sNoPrefixTableName = $sTable;				
			}
			
		}
		
	}
	
	//
	public function getRawTableName() {
		return $this->_aTable[ 0 ];
	}
	
	// Can be called a number of times!!!
	public function getTableName() {
		
		$this->initTableName();
		
		return $this->_sPrefixedTableName;
	}
	
	// Can be called a number of times!!!
	public function getNoPrefixTableName() {
		
		$this->initTableName();
		
		return $this->_sNoPrefixTableName;
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
		
		$oSqlSelect = new Geko_Sql_Select( $this->_oDb );
		
		list( $sTableName, $sTablePrefix ) = $this->_aTable;
		
		$sFieldPrefix = '';
		if ( $sTablePrefix ) $sFieldPrefix = sprintf( '%s.', $sTablePrefix );
		
		foreach ( $this->_aFields as $sFieldName => $aParams ) {
			$oSqlSelect->field( sprintf( '%s%s', $sFieldPrefix, $sFieldName ) );
		}
		
		if ( !$sTablePrefix ) $sTablePrefix = NULL;
		
		$oSqlSelect->from( $sTableName, $sTablePrefix );
		
		return $oSqlSelect;
		
	}
	
	
	
	//// helpers
	
	//
	public function hasFlag( $sFlag, $aParams ) {
		return in_array( $sFlag, $aParams ) ? TRUE : FALSE ;
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
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', $this->_sEntityClass, $sMethod ) );
		
	}
	
	// output the completed query
	public function __toString() {
		
		$sOutput = '';
		
		// main clause
		
		if ( self::CREATE == $this->_iClause ) {
			$sOutput = sprintf( 'CREATE TABLE %s ( ', $this->_aTable[ 0 ] );
		}
		
		// fields
		
		$bFirst = TRUE;
		foreach ( $this->_aFields as $sFieldName => $aParams ) {
			
			if ( $bFirst ) $bFirst = FALSE;
			else $sOutput .= ' , ';
			
			// field name and type
			$sFieldType = $aParams[ 0 ];
			$sOutput .= sprintf( '%s %s', $sFieldName, strtoupper( $sFieldType ) );
			
			// size, if any
			if ( $mSize = $aParams[ 'size' ] ) {
				$sOutput .= sprintf( '(%s)', $mSize );
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
				$sOutput .= sprintf( " DEFAULT '%s' ", $mDefVal );
			}
			
		}
		
		// indexes: prky, unq, key
		foreach ( $this->_aFields as $sFieldName => $aParams ) {
			
			// prky
			
			if ( $this->hasFlag( 'prky', $aParams ) ) {
				$sOutput .= sprintf( ' , PRIMARY KEY(%s)', $sFieldName );
			}
			
			// unq
			
			if ( $this->hasFlag( 'unq', $aParams ) ) $aParams[ 'unq' ] = $sFieldName;
			
			if ( $sIndexName = $aParams[ 'unq' ] ) {
				$sOutput .= sprintf( ' , UNIQUE KEY %s(%s)', $sIndexName, $sFieldName );			
			}
			
			// key

			if ( $this->hasFlag( 'key', $aParams ) ) $aParams[ 'key' ] = $sFieldName;
			
			if ( $sIndexName = $aParams[ 'key' ] ) {
				$sOutput .= sprintf( ' , KEY %s(%s)', $sIndexName, $sFieldName );
			}
			
		}
		
		//
		foreach ( $this->_aIndexKey as $sIndexName => $aParams ) {
			$sOutput .= sprintf( ' , KEY %s(%s)', $sIndexName, implode( ', ', $aParams ) );
		}
		
		//
		foreach ( $this->_aIndexUnq as $sIndexName => $aParams ) {
			$sOutput .= sprintf( ' , UNIQUE KEY %s(%s)', $sIndexName, implode( ', ', $aParams ) );
		}
		
		$sOutput .= ' ) ';
		
		// options
		foreach ( $this->_aOptions as $sOption => $mParams ) {
			if ( 'engine' == $sOption ) {
				$sEngine = strtolower( ( is_array( $mParams ) ) ? $mParams[ 0 ] : $mParams );
				if ( $sEngine = $this->_aEngines[ $sEngine ] ) {
					$sOutput .= sprintf( ' ENGINE=%s ', $sEngine );
				}
			}
		}
		
		// auto-prefix replacement
		if ( $oDb = $this->_oDb ) {
			$sOutput = $oDb->replacePrefixPlaceholder( $sOutput );
		}
		
		return trim( $sOutput );
	}
	
	
	
	
	
	//// Geko_Json_Encodable interface methods
	
	//
	public function toJsonEncodable() {
		
		$aRet = array();
		
		$aFields = $this->getFields( TRUE );
		
		foreach ( $aFields as $sKey => $oField ) {
			
			$mDefValue = $oField->getDefaultValue();
			
			$aRet[ $sKey ] = array(
				'value' => $oField->getAssertedValue( $mDefValue ),
				'format' => $oField->getAssertedType()
			);
			
			if ( $oField->hasFlag( 'uniquecheck' ) ) {
				$aRet[ $sKey ][ 'uniqueCheck' ] = TRUE;
			}
		}
		
		return $aRet;
	}
	
	
	
	
	
}

