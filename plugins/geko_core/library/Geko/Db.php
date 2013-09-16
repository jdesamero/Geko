<?php

// wrapper for Zend_Db::factory
class Geko_Db
{
	
	protected $_oDb;
	
	
	//
	public static function factory() {
		
		$aArgs = func_get_args();
		
		// create native instance of Zend_Db_Adapter
		$oDb = call_user_func_array( array( 'Zend_Db', 'factory' ), $aArgs );
		
		// wrap it
		return new Geko_Db( $oDb );
	}
	
	
	//
	public function __construct( $oDb ) {
		
		$this->_oDb = $oDb;
		
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
				$oDb->describeTable( $sTableName );
			} catch ( Exception $s ) {
				$oDb->getConnection()->exec( $sQuery );
			}
		}
		
	}
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( $oDb = $this->_oDb ) {
			// delegate
			return call_user_func_array( array( $oDb, $sMethod ), $aArgs );
		}
		
		throw new Exception( 'Invalid method ' . get_class( $this ) . '::' . $sMethod . '() called.' );
	}
	
	
}


