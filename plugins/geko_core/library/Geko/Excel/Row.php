<?php

// decorator for Spreadsheet_Excel_Reader row
class Geko_Excel_Row implements Iterator, ArrayAccess, Countable
{
	
	protected $_aRow = array();
	protected $_iPos = 0;	
	
	protected $_aFields = array();
	
	
	
	//
	public function __construct( $aRow, $aFields ) {
		
		$this->_aRow = $aRow;
		$this->_aFields = $aFields;
		
	}
	
	
	//
	public function resolveOffset( $mOffset ) {
		if ( !preg_match( '/^[0-9]+$/', $mOffset ) ) {
			return intval( $this->_aFields[ $mOffset ] );
		}
		return $mOffset;
	}
	
	
	//// Iterator interface methods
	
	//
	public function rewind() {
		$this->_iPos = 0;
	}
	
	//
	public function current() {
		return $this->_aRow[ $this->_iPos + 1 ];
	}

	//
	public function key() {
		return $this->_iPos;
	}
	
	//
	public function next() {
		++$this->_iPos;
	}

	//
	public function valid() {
		return isset( $this->_aRow[ $this->_iPos + 1 ] );
	}
	
	
	//// ArrayAccess interface methods
	
	//
	public function offsetSet( $mOffset, $mValue ) {
		$iOffset = $this->resolveOffset( $mOffset );
		$this->_aRow[ $iOffset ] = $mValue;
	}
	
	//
	public function offsetExists( $mOffset ) {
		$iOffset = $this->resolveOffset( $mOffset );
		return isset( $this->_aRow[ $iOffset ] );
	}
	
	//
	public function offsetUnset( $mOffset ) {
		$iOffset = $this->resolveOffset( $mOffset );
		unset( $this->_aRow[ $iOffset ] );
	}
	
	//
	public function offsetGet( $mOffset ) {
		$iOffset = $this->resolveOffset( $mOffset );
		return isset( $this->_aRow[ $iOffset ] ) ? 
			$this->_aRow[ $iOffset ] :
			NULL;
	}
	
	
	//// Countable interface methods
	
	//
	public function count() {
		return count( $this->_aRow ) - 1;
	}
	
	
	
}



