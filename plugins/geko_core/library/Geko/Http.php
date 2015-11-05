<?php
/*
 * "geko_core/library/Geko/Http.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Http
{
	
	const ERROR_REQUEST_EXCEPTION_THROWN = 101;
	const ERROR_BAD_RESPONSE = 102;
	const ERROR_BAD_XML = 103;
	const ERROR_BAD_JSON = 104;
	const ERROR_UNKNOWN = 199;
	
	
	
	protected $_sRequestUrl = '';
	
	protected $_oClient = NULL;
	
	
	
	
	//// functional stuff
	
	
	//
	protected function _setClientUrl( $sUrl = NULL, $aParams = NULL ) {
		
		$oClient = $this->_getClient();
		
		$oClient->resetParameters();
		
		
		if ( NULL === $sUrl ) {
			$sUrl = $this->_sRequestUrl;
		}
		
		
		$oUrl = new Geko_Uri( $sUrl );
		
		
		if ( is_array( $aParams ) ) {
			$oUrl->setVars( $aParams );
		}
		
		
		$oClient->setUri( strval( $oUrl ) );
		
		
		return $this;
	}
	
	
	//
	protected function _getClient() {
		
		if ( !$this->_oClient ) {
			
			$oClient = new Zend_Http_Client();
			$oClient->setConfig( array(
				'timeout' => 240,
				'keepalive' => TRUE
			) );
			
			$this->_oClient = $oClient;
		}
		
		return $this->_oClient;
	}
	
	
	//
	protected function _getResponseBody() {
		
		$sBody = NULL;
		
		$aError = $this->_formatError(
			self::ERROR_UNKNOWN,
			sprintf( 'Error with: %s', __METHOD__ )
		);
		
		
		try {
			
			$oClient = $this->_getClient();
			
			$oResponse = $oClient->request();
			
			$iRespStatus = intval( $oResponse->getStatus() );
			
			if ( 200 == $iRespStatus ) {
				$sBody = $oResponse->getBody();
			} else {
				
				$aError = $this->_formatError(
					self::ERROR_BAD_RESPONSE,
					sprintf( 'Bad response status: %d', $iRespStatus )
				);
				
			}
			
		} catch( Exception $e ) {
			
			$aError = $this->_formatError(
				self::ERROR_REQUEST_EXCEPTION_THROWN,
				sprintf( 'Request exception message: %d', $e->getMessage() )
			);
			
		}
		
		return ( $sBody ) ? $sBody : $aError ;
	}
	
	
	//
	protected function _getParsedResponseBody( $sType = 'json', $aParams = array() ) {
	
		$mResBody = $this->_getResponseBody();
		
		$aRes = array();
		$aError = NULL;
		
		if ( is_string( $mResBody ) ) {
			
			if ( 'json' == $sType ) {
				
				try {
					
					$mJson = Zend_Json::decode( $mResBody );
					
					if ( !$aError = $this->_getJsonError( $mJson ) ) {
						
						if ( is_array( $mJson ) ) {
							$aRes = array_merge( $aRes, $mJson );
						} else {
							$aRes[ 'parsed_json' ] = $mJson;
						}
					}
					
				} catch ( Exception $e ) {
					
					$aError = $this->_formatError(
						self::ERROR_BAD_JSON,
						sprintf( 'JSON parse exception message: %d', $e->getMessage() )
					);
					
				}
								
			} elseif ( 'xml' == $sType ) {
				
				try {
					
					$oXml = new SimpleXMLElement( $mResBody );

					if ( !$aError = $this->_getXmlError( $oXml ) ) {
					
						if ( $aParams[ 'force_xml_to_array' ] ) {
							
							$aRes = array_merge(
								$aRes,
								json_decode( json_encode( $oXml ), TRUE )
							);
							
						} else {
							$aRes[ 'parsed_xml' ] = $oXml;
						}
					
					}
					
				} catch ( Exception $e ) {
					
					$aError = $this->_formatError(
						self::ERROR_BAD_XML,
						sprintf( 'XML parse exception message: %d', $e->getMessage() )
					);
					
				}
				
			} else {
				
				$aRes[ 'body' ] = $mResBody;
				
			}
			
		} elseif ( is_array( $mResBody ) ) {
			
			$aError = $mResBody;
			
		} else {
			
			// this should not happen
			$aError = $this->_formatError(
				self::ERROR_UNKNOWN,
				sprintf( 'Error with: %s', __METHOD__ )
			);
			
		}
		
		
		// set error
		if ( is_array( $aError ) ) {
			$aRes = array_merge( $aRes, $aError );
		}
		
		
		return $aRes;
	}
	
	
	//// hook methods
	
	//
	public function _getJsonError( $mJson ) {
		return NULL;
	}
	
	//
	public function _getXmlError( $oXml ) {
		return NULL;	
	}
	
	
	
	
	//// helpers
	
	public function _formatError( $iErrorCode, $sMsg ) {
		return array(
			'error_code' => $iErrorCode,
			'error_msg' => $sMsg
		);
	}
	
	
	
}


