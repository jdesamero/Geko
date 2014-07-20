<?php

//
class Geko_Google_Map_Query extends Geko_Http
{
	

	// hook method
	public function formatGetParams( $sQuery ) {
		return array( 'query' => $sQuery );
	}
	
	
	
	
	//// functional stuff
	
	//
	public function getResult( $sQuery ) {
		
		return $this->normalizeResult(
			$this
				->_setClientUrl( NULL, $this->formatGetParams( $sQuery ) )
				->_getParsedResponseBody()
		);
	}
	
	
	
	
	// hook method
	public function normalizeResult( $aRes ) {
		return $aRes;
	}
	
	
	
}


