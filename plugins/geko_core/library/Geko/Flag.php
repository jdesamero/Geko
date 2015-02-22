<?php

// class for handling flag parameters
// typically, we might do something like: in_array( 'flag', $aFlags )
// more elegant to do: $oFlags->has( 'some_flag' );

class Geko_Flag
{
	
	protected $_aFlags = array();
	
	
	//
	public function __construct() {
		
		$aArgs = func_get_args();
		
		if ( is_array( $aFlags = $aArgs[ 0 ] ) ) {
			
			// array of flags was provided
			$this->_aFlags = $aFlags;
				
		} elseif ( is_string( $sFlags = $aArgs[ 0 ] ) ) {
			
			if ( !$sDelim = $aArgs[ 1 ] ) {
				$sDelim = '|';					// pipe is default delimiter
			}
			
			$this->_aFlags = Geko_Array::explodeTrimEmpty( $sDelim, strtolower( $sFlags ) );
		}
		
	}
	
	
	//
	public function has( $mCheck ) {
		return in_array( strtolower( $mCheck ), $this->_aFlags );
	}
	
	
}


