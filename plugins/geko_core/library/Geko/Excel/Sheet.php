<?php

// decorator for Spreadsheet_Excel_Reader sheet
class Geko_Excel_Sheet implements Iterator, ArrayAccess, Countable
{
	
	protected $_aSheet = array();
	protected $_iPos = 0;	
	
	protected $_aFields = array();
	
	
	
	//
	public function __construct( $oExcelReader, $iOffset = 0 ) {
		
		$this->_aSheet = $oExcelReader->sheets[ $iOffset ];
		
		// create a field name hash using the first row
		
		$aFields = array();
		
		foreach ( $this->_aSheet[ 'cells' ][ 1 ] as $i => $sField ) {
			$sKey = Geko_Inflector::underscore( trim( $sField ) );
			$aFields[ $sKey ] = $i;
		}
		
		$this->_aFields = $aFields;
			
	}
	
	
	//
	public function wrapRow( $iPos ) {
		return new Geko_Excel_Row(
			$this->_aSheet[ 'cells' ][ $iPos + 2 ],
			$this->_aFields
		);
	}
	
	
	//// Iterator interface methods
	
	//
	public function rewind() {
		$this->_iPos = 0;
	}
	
	//
	public function current() {
		return $this->wrapRow( $this->_iPos );
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
		return isset( $this->_aSheet[ 'cells' ][ $this->_iPos + 2 ] );
	}
	
	
	//// ArrayAccess interface methods
	
	//
	public function offsetSet( $iOffset, $mValue ) {
		$this->_aSheet[ 'cells' ][ $iOffset ] = $mValue;
	}
	
	//
	public function offsetExists( $iOffset ) {
		return isset( $this->_aSheet[ 'cells' ][ $iOffset ] );
	}
	
	//
	public function offsetUnset( $iOffset ) {
		unset( $this->_aSheet[ 'cells' ][ $iOffset ] );
	}
	
	//
	public function offsetGet( $iOffset ) {
		return isset( $this->_aSheet[ 'cells' ][ $iOffset ] ) ? 
			$this->wrapRow( $iOffset ) :
			NULL;
	}
	
	
	//// Countable interface methods
	
	//
	public function count() {
		return count( $this->_aSheet[ 'cells' ] ) - 1;
	}
	
	
	
}



