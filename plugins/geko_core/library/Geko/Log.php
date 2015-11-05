<?php
/*
 * "geko_core/library/Geko/Log.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * integrated Zend_Log and Zend_Writer_*
 */

//
class Geko_Log
{
	
	const WRITER_DISABLED = 1;
	const WRITER_STREAM = 2;
	const WRITER_FILE = 3;
	const WRITER_DB = 4;
	const WRITER_FIREBUG = 5;
	
	
	//
	protected $_oLogger = NULL;
	protected $_oWriter = NULL;
	
	protected $_oChannel = NULL;
	protected $_oResponse = NULL;
	
	
	
	//
	public function __construct( $iWriter = NULL, $aParams = array() ) {
		
		// instantiate type
		if ( self::WRITER_STREAM == $iWriter ) {
		
			$sStream = $aParams[ 'stream' ];
			
			if ( !$sStream ) $sStream = 'php://output';
			
			$oWriter = new Zend_Log_Writer_Stream( $sStream );
			
		} elseif ( self::WRITER_FILE == $iWriter ) {
			
			$sFile = $aParams[ 'file' ];
			
			if ( is_file( $sFile ) ) {
				$oWriter = new Zend_Log_Writer_Stream( $sFile );
			}
			
		} elseif ( self::WRITER_DB == $iWriter ) {
			
			$oDb = $aParams[ 'db' ];						// Zend_Db
			$sTableName = $aParams[ 'table' ];				// table name
			$aMapping = $aParams[ 'mapping' ];				// column mapping
			
			// array key corresponds to field name
			// $aMapping = array( 'lvl' => 'priority', 'msg' => 'message' );
			
			if ( $oDb && $sTableName && $aMapping ) {
				$oWriter = new Zend_Log_Writer_Db( $oDb, $sTableName, $aMapping );
			}
			
		} elseif ( self::WRITER_FIREBUG == $iWriter ) {
			
			$oWriter = new Zend_Log_Writer_Firebug();

			$oRequest = new Zend_Controller_Request_Http();
			$oResponse = new Zend_Controller_Response_Http();
			
			$oChannel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
			$oChannel->setRequest( $oRequest );
			$oChannel->setResponse( $oResponse );
			
			$this->_oResponse = $oResponse;
			$this->_oChannel = $oChannel;
			
			ob_start();										// start output buffering
			
		} else {
			
			// logging is disabled
			// $iWriter == self::WRITER_DISABLED
			
		}
		
		// has writer
		if ( $oWriter ) {

			// actual logger
			$oLogger = new Zend_Log( $oWriter );
			
			$this->_oLogger = $oLogger;
			$this->_oWriter = $oWriter;
			
		}
		
	}
	
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( $oLogger = $this->_oLogger ) {
		
			if ( method_exists( $oLogger, $sMethod ) ) {
				
				return call_user_func_array( array( $oLogger, $sMethod ), $aArgs );
				
			} else {
				
				// test for priority
				$sPriority = strtoupper( $sMethod );
				$oLogRef = new Geko_Reflection( $oLogger );
				$aPriorities = $oLogRef->getPropertyValue( '_priorities' );
				
				if ( in_array( $sPriority, $aPriorities ) ) {
					return call_user_func_array( array( $oLogger, $sPriority ), $aArgs );
				}
				
			}
			
			throw new Exception( sprintf( 'Invalid method %s::%s() called.', get_class( $this ), $sMethod ) );
		}
		
	}
	
	
	//
	public function __destruct() {
		
		$oResponse = $this->_oResponse;
		$oChannel = $this->_oChannel;
		
		if ( $oResponse && $oChannel ) {
			$oChannel->flush();								// flush output buffer
			$oResponse->sendHeaders();
		}
		
	}
	
	
}

