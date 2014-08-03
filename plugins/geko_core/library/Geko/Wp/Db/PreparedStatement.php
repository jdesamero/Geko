<?php

// a prepared-statement-like object for $wpdb

//
class Geko_Wp_Db_PreparedStatement
{
	
	protected $wpdb;
	
	protected $_sOrigQuery = '';
	protected $_sCurQuery = '';
	
	protected $_iCurRow = 0;
	
	
	//
	public function __construct( $sQuery, $wpdb ) {
		
		$this->wpdb = $wpdb;
		$this->_sOrigQuery = $sQuery;
		
	}
	
	
	//
	public function bindParams( $aParams ) {
		
		$sSql = $this->_sOrigQuery;
		
		if ( $aParams ) {
			
			foreach ( $aParams as $mParam ) {
				
				$sPlaceholder = '%s';		// default
				
				if ( is_int( $mParam ) ) {
					$sPlaceholder = '%d';
				} elseif ( is_float( $mParam ) ) {
					$sPlaceholder = '%f';			
				}
				
				$sSql = Geko_String::replaceFirstMatch( '?', $sPlaceholder, $sSql );
			}
			
			array_unshift( $aParams, $sSql );
			
			$sSql = call_user_func_array( array( $this->wpdb, 'prepare' ), $aParams );
		}
		
		$this->_sCurQuery = $sSql;
		
		return $this;
	}
	
	
	//
	public function getOrigQuery() {
		return $this->_sOrigQuery;
	}
	
	//
	public function getCurQuery() {
		return $this->_sCurQuery;
	}
	
	
	// fetch the next result
	public function fetch() {
		
		$aRes = $this->wpdb->last_result;
		
		if ( is_array( $aRes ) ) {
			
			if ( $aRow = $aRes[ $this->_iCurRow ] ) {
				$this->_iCurRow++;
				return $aRow;
			}
			
			$this->resetCurRow();
		}
		
		return NULL;
	}
	
	
	// reset fetch pointer
	public function resetCurRow() {
		
		$this->_iCurRow = 0;
		
		return $this;
	}
	
	
	// do a full reset
	public function reset() {
		
		$this->_sCurQuery = '';
		$this->resetCurRow();
		
		return $this;
	}
	
	
	
	//
	public function execute() {
		
		if ( $this->_sCurQuery ) {
			
			$this->resetCurRow();
			return $this->wpdb->query( $this->_sCurQuery );
		}
		
		return NULL;
	}
	
	
	
}
