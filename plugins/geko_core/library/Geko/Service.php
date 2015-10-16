<?php

//
class Geko_Service extends Geko_Singleton_Abstract
{
	
	protected $_sOutputMethod = 'json';
	
	protected $_aPrefixes = array( 'Geko_' );
	
	protected $_aLeftovers = array();
	protected $_aAjaxResponse = array(
		'context' => 'service'
	);
	
	protected $_aFileOutput = array(
		'_headers' => array(
			'Content-Description' => 'File Transfer',
			'Content-Type' => 'application/octet-stream',
			'Expires' => '0',
			'Cache-Control' => 'must-revalidate',
			'Pragma' => 'public'
		)
	);
	
	protected $_sEncryptKey = '';
	
	protected $_aMapMethods = array();
	
	
	
	
	
	//// accessors
	
	//
	public static function get( $sKey ) {
		return Geko::getBoot()->get( $sKey );
	}
	
	//
	public static function getVal( $sKey ) {
		return Geko::getBoot()->getVal( $sKey );
	}
	
	
	
	
	//// call hooks
	
	//
	public function preStart() {
		
		
		//// init best match static root class
		
		foreach ( $this->_aPrefixes as $sPrefix ) {
			
			$sClass = rtrim( $sPrefix, '_' );
			
			if ( class_exists( $sClass ) ) {
				
				$this->_aMapMethods = array_merge( $this->_aMapMethods, array(
					'regGet' => array( $sClass, 'get' ),
					'regVal' => array( $sClass, 'getVal' )
				) );
				
				break;
			}
		}
		
		
	}
	
	
	// implement hook method
	public function start() {
		
		parent::start();
		
		Geko_Http_Var::formatHttpRawPostData();
		
	}
	
	
	
	// action: do_some_stuff
	// matching method: processDoSomeStuff()
	
	//
	public function process() {
		
		// checks can be done during start() which can set an error status on failure
		// if none, we're good to go
		
		if ( !$this->hasStatus() ) {
		
			if ( $sAction = $this->getAction() ) {
				
				// see if matching method is defined
				
				$sActionMethod = sprintf( 'process%s', Geko_Inflector::camelize( $sAction ) );
				
				if ( method_exists( $this, $sActionMethod ) ) {
					$this->$sActionMethod();		// perform the action
				}
				
			}
		
		}
		
		// no status from above? perform default action
		if ( !$this->hasStatus() ) {
			$this->processDefault();
		}
		
		// still no status?
		$this->setIfNoStatus( $this->getDefaultStatus() );
		
		return $this;
	}
	
	
	// hook for default actions
	public function processDefault() { }
	
	
	//
	public function output() {
		
		if ( 'file' == $this->_sOutputMethod ) {
			$this->outputFile();
		} else {
			$this->outputJson();
		}
		
		return $this;
	}
	
	// hook
	public function modifyParams( $aParams ) {
		return $aParams;
	}
	
	//
	public function setLeftovers( $aLeftovers ) {
		 $this->_aLeftovers = $aLeftovers;
		 return $this;
	}
	
	
	
	
	
	//// json action/status/response stuff
	
	// service/action paradigm
	// eg: service: profile; action: register, update password, etc.
	
	
	//
	public function getAction() {
		
		if ( isset( $_REQUEST[ '_action' ] ) ) {
			// new way of doing things
			$sAction = $_REQUEST[ '_action' ];
		} else {
			// old way
			$sAction = $_REQUEST[ 'subaction' ];
		}
		
		return $sAction;
	}
	
	// TO DO: HACKISH!!!!!!!!
	public function isAction( $sAction ) {
		return ( $sAction == $this->getAction() ) ? TRUE : FALSE ;
	}
	
	
	//
	public function setStatus( $iStatus, $bArray = FALSE ) {
		
		if ( $bArray ) {
			
			if ( !is_array( $this->_aAjaxResponse[ 'status' ] ) ) {
				$this->_aAjaxResponse[ 'status' ] = array();
			}
			
			$this->_aAjaxResponse[ 'status' ][] = $iStatus;
			
		} else {
			$this->_aAjaxResponse[ 'status' ] = $iStatus;		
		}
		
		return $this;
	}
	
	//
	public function getStatus() {
		return $this->_aAjaxResponse[ 'status' ];
	}
	
	//
	public function setStatusMulti( $iStatus ) {
		$this->setStatus( $iStatus, TRUE );
		return $this;
	}
	
	//
	public function setIfNoStatus( $iStatus ) {
		
		if ( !$this->_aAjaxResponse[ 'status' ] ) {
			$this->_aAjaxResponse[ 'status' ] = $iStatus;
		}
		
		return $this;
	}
	
	//
	public function hasStatus() {
		return ( $this->_aAjaxResponse[ 'status' ] ) ? TRUE : FALSE ;
	}
	
	//
	public function getDefaultStatus() {
		
		// Note: self::STAT_ERROR will not work for sub-classes
		//    Use static::STAT_ERROR instead
		
		if ( defined( 'static::STAT_ERROR' ) ) {
			return constant( 'static::STAT_ERROR' );
		} else {
			// PHP 5.2.x backwards compatibility
			$sConst = sprintf( '%s::STAT_ERROR', get_class( $this ) );
			if ( defined( $sConst ) ) {
				return constant( $sConst );
			}
		}
		
		return NULL;
	}
	
	
	//
	public function setResponseValue( $sKey, $mValue ) {
		
		if ( $mValue instanceof Geko_Json_Encodable ) {
			$mValue = $mValue->toJsonEncodable();
		}
		
		$this->_aAjaxResponse[ $sKey ] = $mValue;
		
		return $this;
	}
	
	//
	public function setResponseValues( $aValues ) {
		
		$this->_aAjaxResponse = array_merge( $this->_aAjaxResponse, $aValues );
		
		return $this;
	}
	
	
	//
	public function getStatusValues() {
		
		$aConsts = Geko_Class::getConstants( $this->_sInstanceClass );
		$aValues = array();
		
		foreach ( $aConsts as $sKey => $mValue ) {
			
			if ( 0 === strpos( $sKey, 'STAT_' ) ) {
				$aValues[ strtolower( substr( $sKey, 5 ) ) ] = $mValue;
			}
		}
		
		return $aValues;
	}
	
	
	//
	public function outputJson() {

		// json, default
		echo Zend_Json::encode( $this->_aAjaxResponse );
		
		return $this;
	}
	
	
	
	
	//// file output
	
	
	//
	public function setFileOutputErrorMessage( $sMessage ) {
		
		$this->setFileOutputValue( '_error_msg', $sMessage );
		
		return $this;
	}
	
	// full path to output file
	public function setFileOutputSource( $sSource ) {
		
		$this->setFileOutputValue( '_source', $sSource );
		
		return $this;
	}
	
	//
	public function setFileOutputName( $sName ) {
		
		$this->setFileOutputValue( '_name', $sName );
		
		return $this;	
	}
	
	//
	public function setFileOutputHeader( $sHeader, $sValue ) {
		
		if ( !is_array( $aHeaders = $this->_aFileOutput[ '_headers' ] ) ) {
			$aHeaders = array();
		}
		
		$aHeaders[ $sHeader ] = $sValue;
		
		$this->_aFileOutput[ '_headers' ] = $aHeaders;
		
		return $this;
	}
	
	
	//
	public function setFileOutputValue( $sKey, $mValue ) {
	
		$this->_aFileOutput[ $sKey ] = $mValue;
		
		return $this;
	}
	
	
	//
	public function outputFile() {

		$aFileOutput = $this->_aFileOutput;
		
		if ( $sErrorMsg = $aFileOutput[ '_error_msg' ] ) {
			
			echo $sErrorMsg;
		
		} else {
			
			// get all the headers
			$aHeaders = $aFileOutput[ '_headers' ];
			
			// get the file to read
			$sSource = $aFileOutput[ '_source' ];
			
			if ( is_file( $sSource ) ) {

				// determine the name
				if ( !$sName = $aFileOutput[ '_name' ] ) {
					$sName = basename( $sSource );
				}
				
				$aHeaders[ 'Content-Disposition' ] = sprintf( 'attachment; filename="%s"', $sName );
				$aHeaders[ 'Content-Length' ] = filesize( $sSource );
				
				foreach ( $aHeaders as $sHeader => $sValue ) {
					$sHeaderFull = sprintf( '%s: %s', $sHeader, $sValue );
					header( $sHeaderFull );
				}
				
				readfile( $sSource );
				
			} else {
				echo 'File does not exist!';
			}
		}
		
		return $this;
	}
	
	
	
	
	
	//// encrypt/decrypt helpers
	
	//
	public function encrypt( $sData, $sKey = '' ) {
		
		if ( !$sKey ) $sKey = $this->_sEncryptKey;
		
		return base64_encode(
			mcrypt_encrypt(
				MCRYPT_RIJNDAEL_256,
				md5( $sKey ),
				$sData,
				MCRYPT_MODE_CBC,
				md5( md5( $sKey ) )
			)
		);
	}
	
	//
	public function decrypt( $sData, $sKey = '' ) {
		
		if ( !$sKey ) $sKey = $this->_sEncryptKey;
		
		return rtrim( mcrypt_decrypt(
			MCRYPT_RIJNDAEL_256,
			md5( $sKey ),
			base64_decode( $sData ),
			MCRYPT_MODE_CBC,
			md5( md5( $sKey ) )
		), "\0" );
	}

	
	
	
	
	//// captcha stuff
	
	//
	public function captchaIsValid() {
		
		$sPrivateKey = $this->getVal( 'recaptcha_private_key' );
		
		if ( !$sPrivateKey && defined( 'RECAPTCHA_PRIVATE_KEY' ) ) {
			$sPrivateKey = RECAPTCHA_PRIVATE_KEY;
		}
		
		if ( $sPrivateKey ) {
		
			$oCaptcha = recaptcha_check_answer(
				$sPrivateKey,
				$_SERVER[ 'REMOTE_ADDR' ],
				$_REQUEST[ 'recaptcha_challenge_field' ],
				$_REQUEST[ 'recaptcha_response_field' ]
			);
			
			return $oCaptcha->is_valid;
		}
		
		return FALSE;
	}
	
		
	
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( array_key_exists( $sMethod, $this->_aMapMethods ) ) {
			
			return call_user_func_array(
				$this->_aMapMethods[ $sMethod ],
				$aArgs
			);
			
		} elseif ( $sCreateType = Geko_Class::callCreateType( $sMethod ) ) {
			
			return Geko_Class::callCreateInstance( $sCreateType, $sMethod, $aArgs, $this->_aPrefixes );
			
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', __CLASS__, $sMethod ) );
	}
	
	
}


