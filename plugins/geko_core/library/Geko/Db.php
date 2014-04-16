<?php

// wrapper for Zend_Db::factory
class Geko_Db
{
	
	protected $_sPrefix = NULL;
	protected $_sPrefixPlaceholder = '##pfx##';
	
	protected $_oDb;
	protected $_sDbClass;
	protected $_sVendorClass;							// some Geko_Db_* class
	
	protected $_aDbVendorMapping = array(
		'Zend_Db_Adapter_Pdo_Mysql' => 'Mysql',
		'Zend_Db_Adapter_Pdo_Sqlite' => 'Sqlite'
	);
	
	
	
	
	//
	public static function factory() {
		
		$aArgs = func_get_args();
		
		// create native instance of Zend_Db_Adapter
		$oDb = call_user_func_array( array( 'Zend_Db', 'factory' ), $aArgs );
		
		// wrap it
		$oDbWrap = new Geko_Db( $oDb );
		
		if ( $sPrefix = $aArgs[ 1 ][ 'table_prefix' ] ) {
			$oDbWrap->setPrefix( $sPrefix );
		}
		
		return $oDbWrap;
	}
	
	
	
	
	//
	public function __construct( $oDb ) {
		
		$this->_oDb = $oDb;
		
		$this->_sDbClass = get_class( $oDb );
		$this->_sVendorClass = sprintf( 'Geko_Db_%s', $this->_aDbVendorMapping[ $this->_sDbClass ] );
		
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
	public function replacePrefixPlaceholder( $sOutput ) {
		if ( NULL !== $this->_sPrefix ) {
			return str_replace( $this->_sPrefixPlaceholder, $this->_sPrefix, $sOutput );
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
	
	
	
	//// delegate to matching vendor handler
	
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
			
			try {
				$sTableName = $this->replacePrefixPlaceholder( $sTableName );
				$oDb->describeTable( $sTableName );
			} catch ( Exception $s ) {
				$oDb->exec( $sQuery );
			}
		}
		
	}
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( $oDb = $this->_oDb ) {
			
			if ( in_array( $sMethod, array(
				'insert', 'update', 'delete', 'fetchAll', 'fetchAssoc', 'fetchCol', 'fetchPairs', 'fetchRow', 'fetchOne'
			) ) ) {
				$aArgs[ 0 ] = $this->replacePrefixPlaceholder( $aArgs[ 0 ] );
			}
			
			// delegate
			return call_user_func_array( array( $oDb, $sMethod ), $aArgs );
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', get_class( $this ), $sMethod ) );
	}
	
	
}


