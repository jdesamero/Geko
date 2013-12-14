<?php

//
class Geko_Google_Map_Query
{

	protected $_sRequestUrl = '';
	protected $_oHttp = NULL;
	
	
	
	//
	public function __construct( $aParams = array() ) {
		
		if ( $this->_sRequestUrl ) {
			$this->_oHttp = new Zend_Http_Client( $this->_sRequestUrl );
		}
	}
	
	
	// hook method
	public function formatGetParams( $sQuery ) {
		return array( 'query' => $sQuery );
	}
	
	
	//
	public function performRequest( $sQuery ) {
		
		if ( $oHttp = $this->_oHttp ) {
			$oHttp->setParameterGet( $this->formatGetParams( $sQuery ) );
			return $oHttp->request();
		}
		
		return NULL;
	}
	
	//
	public function getResult( $sQuery ) {
		
		$aRes = array();
		
		$oResponse = $this->performRequest( $sQuery );
		
		if ( $oResponse && ( 200 == $oResponse->getStatus() ) ) {
			$sRes = $oResponse->getBody();
			$aRes = Zend_Json::decode( $sRes );
		}
		
		return $this->normalizeResult( $aRes );
	}
	
	
	// hook method
	public function normalizeResult( $aRes ) {
		return $aRes;
	}
	
	
	
}



