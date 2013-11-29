<?php

//
class Geko_Service extends Geko_Singleton_Abstract
{
	
	protected $aLeftovers = array();
	protected $aAjaxResponse = array(
		'context' => 'service'
	);
	protected $sEncryptKey = '';
	
	
	
	
	//// other stuff
	
	//
	public function init() {
		
		if ( $sData = $GLOBALS[ 'HTTP_RAW_POST_DATA' ] ) {
			try {
				$_POST = $_REQUEST = Zend_Json::decode( $sData );
			} catch ( Exception $e ) { }
		}
		
		return $this;
	}
	
	//
	public function process() {
		return $this;
	}
	
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
	
	// TO DO: HACKISH!!!!!!!!
	public function isAction( $sAction ) {
		return ( $sAction == $_REQUEST[ 'subaction' ] ) ? TRUE : FALSE ;
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
	public function setResponseValue( $sKey, $mValue ) {
		
		$this->aAjaxResponse[ $sKey ] = $mValue;
		
		return $this;
	}
	
	//
	public function setResponseValues( $aValues ) {
		
		$this->aAjaxResponse = array_merge( $this->aAjaxResponse, $aValues );
		
		return $this;
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
		
		$oCaptcha = recaptcha_check_answer(
			RECAPTCHA_PRIVATE_KEY,
			$_SERVER[ 'REMOTE_ADDR' ],
			$_REQUEST[ 'recaptcha_challenge_field' ],
			$_REQUEST[ 'recaptcha_response_field' ]
		);
		
		return $oCaptcha->is_valid;
	}
	
		
	
	
	
}


