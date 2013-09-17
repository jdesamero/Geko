<?php

// wrapper for Zend_Db::factory
class Geko_Db
{
	
	protected $_sPrefix = NULL;
	protected $_sPrefixPlaceholder = '##pfx##';
	
	protected $_oDb;
	
	
	
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
	
	
	
	
	// TO DO: make this vendor aware
	public function getTimestamp() {
		return Geko_Db_Mysql::getTimestamp();
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
				$oDb->getConnection()->exec( $sQuery );
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
		
		throw new Exception( 'Invalid method ' . get_class( $this ) . '::' . $sMethod . '() called.' );
	}
	
	
}


