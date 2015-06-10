<?php

//
class Geko_Wp_Db_Statement extends Zend_Db_Statement
{
	
	protected $_keys;
	
	protected $_values;
	protected $_meta = NULL;
	
	
	
	// 
	public function _prepare( $sSql ) {
		
		// use a wrapper for $wpdb that mimic prepared statement functionality
		$this->_stmt = new Geko_Wp_Db_PreparedStatement( $sSql, $this->_adapter->getConnection() );
		
	}
	
	//
	protected function _bindParam( $parameter, &$variable, $type = NULL, $length = NULL, $options = NULL ) {
		return true;
	}
	
	//
	public function close() {
	
		return false;
	}
	
	//
	public function closeCursor() {
		
		return $this->_stmt->reset();
		
		return false;
	}
	
	//
	public function columnCount() {
		return 0;
	}
	
	//
	public function errorCode() {
		return false;
	}
	
	//
	public function errorInfo() {
	
		return array();
	}
	
	
	// this would normally return a result set or something,
	// but in this case, it returns an SQL statement to be executed
	public function _execute( array $aParams = NULL ) {
		
        if ( !$this->_stmt ) {
            return FALSE;
        }
        
		if ( NULL === $aParams ) {
			$aParams = $this->_bindParam;
		}
		
		
		$oStmt = $this->_stmt;
		
		$oStmt->bindParams( $aParams );
		
		// echo $oStmt->getOrigQuery() . '<br />';
		// echo $oStmt->getCurQuery() . '<br />';
		
		return $oStmt->execute();
	}
	
	
	//
	public function fetch( $iMode = NULL, $iCursor = NULL, $iOffset = NULL ) {
		
        if ( !$this->_stmt ) {
            return FALSE;
        }
		
		// fetch the next result
		$oRow = $this->_stmt->fetch();
		
		switch ( $oRow ) {
			case NULL:					// end of data
			case FALSE:					// error occurred
				
				$this->_stmt->reset();
				return FALSE;
			
			default:					// fallthrough
			
		}
		
		// make sure we have a fetch mode
		if ( NULL === $iMode ) {
			$iMode = $this->_fetchMode;
		}
		
		//
		
		$aRow = FALSE;
		
		switch ( $iMode ) {
			
			case Zend_Db::FETCH_NUM:
				
				$aRow = array_values( ( array ) $oRow );
				break;
			
			case Zend_Db::FETCH_ASSOC:
				
				$aRow = ( array ) $oRow;
				break;
			
			case Zend_Db::FETCH_BOTH:
				
				$aRow = array_merge( array_values( ( array ) $oRow ), ( array ) $oRow );
				break;
			
			case Zend_Db::FETCH_OBJ:
				
				$aRow = $oRow;
				break;
			
			// Support this later ???
			// case Zend_Db::FETCH_BOUND:
				
			default:
				
				throw new Geko_Wp_Db_Adapter_Exception( sprintf( 'Invalid fetch mode (%d) specified', $iMode ) );
				break;			
			
		}
		
		return $aRow;
	}
	
	
	//
	public function nextRowset() {
		throw new Geko_Wp_Db_Adapter_Exception( sprintf( '%s is not implemented', __METHOD__ ) );
	}
	
	//
	public function rowCount() {
		
		$wpdb = $this->_adapter->getConnection();
		
		return $wpdb->rows_affected;
	}

}


