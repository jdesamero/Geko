<?php

//
class Geko_Service extends Geko_Singleton_Abstract
{
	
	protected $_aPrefixes = array( 'Geko_' );
	
	protected $aLeftovers = array();
	protected $aAjaxResponse = array(
		'context' => 'service'
	);
	protected $sEncryptKey = '';
	
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
		
		if ( $sData = $GLOBALS[ 'HTTP_RAW_POST_DATA' ] ) {
			
			try {
				
				$_POST = Zend_Json::decode( $sData );
				$_GET = Geko_Uri::getGlobal()->getVars();
				
				$_REQUEST = array_merge( $_POST, $_GET );
				
			} catch ( Exception $e ) { }
		}
		
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
		echo Zend_Json::encode( $this->aAjaxResponse );
		return $this;
	}
	
	// hook
	public function modifyParams( $aParams ) {
		return $aParams;
	}
	
	//
	public function setLeftovers( $aLeftovers ) {
		 $this->aLeftovers = $aLeftovers;
		 return $this;
	}
	
	
	
	
	
	//// action/status/response stuff
	
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
			
			if ( !is_array( $this->aAjaxResponse[ 'status' ] ) ) {
				$this->aAjaxResponse[ 'status' ] = array();
			}
			
			$this->aAjaxResponse[ 'status' ][] = $iStatus;
			
		} else {
			$this->aAjaxResponse[ 'status' ] = $iStatus;		
		}
		
		return $this;
	}
	
	//
	public function getStatus() {
		return $this->aAjaxResponse[ 'status' ];
	}
	
	//
	public function setStatusMulti( $iStatus ) {
		$this->setStatus( $iStatus, TRUE );
		return $this;
	}
	
	//
	public function setIfNoStatus( $iStatus ) {
		
		if ( !$this->aAjaxResponse[ 'status' ] ) {
			$this->aAjaxResponse[ 'status' ] = $iStatus;
		}
		
		return $this;
	}
	
	//
	public function hasStatus() {
		return ( $this->aAjaxResponse[ 'status' ] ) ? TRUE : FALSE ;
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
		
		$this->aAjaxResponse[ $sKey ] = $mValue;
		
		return $this;
	}
	
	//
	public function setResponseValues( $aValues ) {
		
		$this->aAjaxResponse = array_merge( $this->aAjaxResponse, $aValues );
		
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
	
	
	
	
	//// encrypt/decrypt helpers
	
	//
	public function encrypt( $sData, $sKey = '' ) {
		
		if ( !$sKey ) $sKey = $this->sEncryptKey;
		
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
		
		if ( !$sKey ) $sKey = $this->sEncryptKey;
		
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


