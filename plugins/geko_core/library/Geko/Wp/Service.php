<?php

//
class Geko_Wp_Service extends Geko_Singleton_Abstract
{
	protected $aAjaxResponse = array();
	protected $sEncryptKey = '';
	
	
	
	//// status stuff
	
	//
	public function setStatus( $iStatus ) {
		
		$this->aAjaxResponse[ 'status' ] = $iStatus;
		
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
	public function setResponseValue( $sKey, $mValue ) {
		
		$this->aAjaxResponse[ $sKey ] = $mValue;
		
		return $this;
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
	
	
	
	//// email delivery stuff
	
	//
	public function deliverMail( $sSlug, $aParams ) {
	
		$oDeliverMail = new Geko_Wp_EmailMessage_Delivery( $sSlug );
		
		if ( is_array( $aRecipients = $aParams[ 'recipients' ] ) ) {
			$oDeliverMail->addRecipients( $aRecipients );
		}
		
		if ( is_array( $aMergeParams = $aParams[ 'merge_params' ] ) ) {
			$oDeliverMail->setMergeParams( $aMergeParams );
		}
		
		if ( is_array( $aFiles = $aParams[ 'files' ] ) ) {
			
			foreach ( $aFiles as $sFile ) {
				
				if ( $sFilePath = $_FILES[ $sFile ][ 'tmp_name' ] ) {
					$oDeliverMail->addAttachment( array(
						'path' => $sFilePath,
						'type' => $_FILES[ $sFile ][ 'type' ],
						'name' => $_FILES[ $sFile ][ 'name' ]
					) );
				}
			}
		}
		
		return $oDeliverMail;
	}
	
	
	
	
	//// other stuff
	
	
	//
	public function process() {
		return $this;
	}
	
	//
	public function output() {
		echo Zend_Json::encode( $this->aAjaxResponse );
		return $this;
	}
	
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
	
	// hook
	public function modifyParams( $aParams ) {
		return $aParams;
	}
	
}


