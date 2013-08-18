<?php

// decorator for Geko_Entity
class Geko_Entity_Format
{

	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
	
		//
		if ( 0 === strpos( $sMethod, 'echo' ) ) {
			$sCall = substr_replace( $sMethod, 'get', 0, 4 );
			if ( method_exists( $this, $sCall ) ) {
				echo call_user_func_array( array( $this, $sCall ), $aArgs );
				return TRUE;
			}
		}
		
		throw new Exception( 'Invalid method ' . __CLASS__ . '::' . $sMethod . '() called.' );
	}
	
}

