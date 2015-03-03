<?php

// class for handling flag parameters
// typically, we might do something like: in_array( 'flag', $aFlags )
// more elegant to do: $oFlags->has( 'some_flag' );

class Geko_Util_Flag
{
	
	protected $_aFlags = array();
	
	protected $_sOutputType = 'string';
	protected $_sDelim = '|';					// pipe is default delimiter
	
	
	
	//
	public function __construct() {
		
		$aArgs = func_get_args();
		
		if ( is_array( $aFlags = $aArgs[ 0 ] ) ) {
			
			// array of flags was provided
			$this->_aFlags = $aFlags;
				
		} elseif ( is_string( $sFlags = $aArgs[ 0 ] ) ) {
			
			if ( $sDelim = $aArgs[ 1 ] ) {
				$this->_sDelim = $sDelim;					
			}
			
			$this->_aFlags = Geko_Array::explodeTrimEmpty( $this->_sDelim, strtolower( $sFlags ) );
		}
		
	}
	
	// $sOutputType possible values: string, array
	public function setOutputType( $sOutputType ) {
		
		$this->_sOutputType = $sOutputType;
		
		return $this;
	}
	
	//
	public function getFlags() {
		return $this->_aFlags;
	}
	
	//
	public function has( $mCheck ) {
		return in_array( strtolower( $mCheck ), $this->_aFlags );
	}
	
	// works same way as constructor
	public function merge() {
		
		$aArgs = func_get_args();
		
		$oMergeFlags = Geko_Class::createInstance( __CLASS__, $aArgs );
		
		return $this->formatFlags( array_unique( array_merge(
			$oMergeFlags->getFlags(),
			$this->getFlags()
		) ) );
	}
	
	//// helpers
	
	//
	public function formatFlags( $aFlags ) {
		
		if ( 'string' == $this->_sOutputType ) {
			return implode( $this->_sDelim, $aFlags );
		}
		
		return $aFlags;
	}
	
	
}


