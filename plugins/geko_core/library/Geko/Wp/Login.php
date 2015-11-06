<?php
/*
 * "geko_core/library/Geko/Wp/Login.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Login extends Geko_Singleton_Abstract
{
	const STAT_LOGIN_SUCCESS = 1;
	const STAT_INVALID_EMAIL = 2;
	const STAT_LOGIN_ERROR = 3;
	const STAT_CONFIRM_NEW_PASS = 4;
	const STAT_NEW_PASSWORD = 5;
	const STAT_RESET_PASSWORD_SUCCESS = 6;
	const STAT_CUSTOM_PASS_RESET = 7;
	
	protected $_sInvalidEmailUrl = '/notification/?msg=invalid-email';
	protected $_sLoginErrorUrl = '/notification/?msg=login-error';
	protected $_sConfirmNewPassUrl = '/notification/?msg=confirm-new-pass';
	protected $_sNewPasswordUrl = '/notification/?msg=new-password';
	protected $_sResetPasswordSuccessUrl = '/notification/?msg=reset-password-success';
	protected $_sCustomPassResetUrl = '/custom-password-reset/';
	
	// ties in with Geko_Wp_EmailMessage/Geko_Wp_EmailMessage_Delivery
	protected $_sTrackUserLogin = '';
	protected $_sEmsgRetrievePasswordSlug = '';
	protected $_oEmsgRetrievePassword = NULL;
	protected $_sEmsgPasswordResetSlug = '';
	protected $_oEmsgPasswordReset = NULL;
	
	
	
	//// initialize
	
	//
	public function init() {
		
		add_action( 'init', array( $this, 'loginStart' ) );
		add_action( 'shutdown', array( $this, 'loginEnd' ), 0 );
		add_filter( 'wp_redirect', array( $this, 'overrideRedirectLocation' ), 10, 2 );
		
		if ( $this->_sEmsgRetrievePasswordSlug ) {
			
			$oEmsg = new Geko_Wp_EmailMessage_Delivery( $this->_sEmsgRetrievePasswordSlug );
			if ( $oEmsg->isValid() ) $this->_oEmsgRetrievePassword = $oEmsg;
			
			add_action( 'retreive_password', array( $this, 'trackUserLogin' ) );
			add_filter( 'retrieve_password_title', array( $this, 'modifyRetrievePasswordTitle' ) );
			add_filter( 'retrieve_password_message', array( $this, 'modifyRetrievePasswordMessage' ), 10, 2 );			
		}
		
		if ( $this->_sEmsgPasswordResetSlug ) {
			
			$oEmsg = new Geko_Wp_EmailMessage_Delivery( $this->_sEmsgPasswordResetSlug );
			if ( $oEmsg->isValid() ) $this->_oEmsgPasswordReset = $oEmsg;
			
			add_action( 'password_reset', array( $this, 'trackUserLogin' ), 10, 2 );
			add_filter( 'password_reset_title', array( $this, 'modifyPasswordResetTitle' ) );
			add_filter( 'password_reset_message', array( $this, 'modifyPasswordResetMessage' ), 10, 2 );
		}
		
		if ( $this->_oEmsgRetrievePassword || $this->_oEmsgPasswordReset ) {
			
			add_filter( 'wp_mail_from', array( $this, 'modifyWpAdminEmail' ) );
			add_filter( 'wp_mail_from_name', array( $this, 'modifyWpAdminName' ) );
		}
		
		return $this;
	}
	
	//
	public function loginStart() {
		if ( FALSE !== strpos( $_SERVER[ 'REQUEST_URI' ], '/wp-login.php' ) ) {
			ob_start();
		}
	}
	
	
	//// accessors
	
	//
	public function setInvalidEmailUrl( $sUrl ) {
		$this->_sInvalidEmailUrl = $sUrl;
		return $this;
	}
	
	//
	public function setLoginErrorUrl( $sUrl ) {
		$this->_sLoginErrorUrl = $sUrl;
		return $this;
	}
	
	//
	public function setConfirmNewPassUrl( $sUrl ) {
		$this->_sConfirmNewPassUrl = $sUrl;
		return $this;
	}
	
	//
	public function setNewPasswordUrl( $sUrl ) {
		$this->_sNewPasswordUrl = $sUrl;
		return $this;
	}
	
	//
	public function setResetPasswordSuccessUrl( $sUrl ) {
		$this->_sResetPasswordSuccessUrl = $sUrl;
		return $this;
	}
	
	//
	public function setCustomPassResetUrl( $sUrl ) {
		$this->_sCustomPassResetUrl = $sUrl;
		return $this;
	}
	
	
	
	//
	public function getInvalidEmailUrl() {
		return $this->formatUrl( $this->_sInvalidEmailUrl );
	}
	
	//
	public function getLoginErrorUrl() {
		return $this->formatUrl( $this->_sLoginErrorUrl );
	}
	
	//
	public function getConfirmNewPassUrl() {
		return $this->formatUrl( $this->_sConfirmNewPassUrl );
	}
	
	//
	public function getNewPasswordUrl() {
		return $this->formatUrl( $this->_sNewPasswordUrl );
	}
	
	//
	public function getResetPasswordSuccessUrl() {
		return $this->formatUrl( $this->_sResetPasswordSuccessUrl );
	}
	
	//
	public function getCustomPassResetUrl() {
		return $this->formatUrl( $this->_sCustomPassResetUrl );
	}
	
	
	//
	public function formatUrl( $sUrl ) {
		
		if ( NULL === $sUrl ) return '';
		
		if ( 0 === strpos( $sUrl, 'http' ) ) {
			// don't add bloginfo url to the front since absolute URL is provided
			return $sUrl;
		}
		
		// add bloginfo url to the front
		return Geko_Wp::getUrl() . $sUrl;
	}
	
	
	
	//
	public function setEmsgPasswordResetSlug( $sSlug ) {
		$this->_sEmsgPasswordResetSlug = $sSlug;
		return $this;
	}
	
	//
	public function setEmsgRetrievePasswordSlug( $sSlug ) {
		$this->_sEmsgRetrievePasswordSlug = $sSlug;
		return $this;
	}
	
	
	
	
	//// action methods
	
	//
	public function loginEnd() {
		
		global $error;
		
		if ( FALSE !== strpos( $_SERVER[ 'REQUEST_URI' ], 'wp-login.php' ) ) {
			
			$bAjaxResponse = ( $_REQUEST[ 'ajax_response' ] ) ? TRUE : FALSE;
			$aAjaxResponse = array();
			
			$sOutput = ob_get_contents();
			ob_end_clean();
			
			if ( $sOutput ) {
				
				// means the login page had some output, so redirect accordingly
				// no output when shutdown occurs most likely mean that the page was already redirected somewhere else
				
				if ( 'lostpassword' == $_GET[ 'action' ] ) {
					
					// displays the lost password form
					if ( $error ) {
						$aAjaxResponse[ 'status' ] = self::STAT_INVALID_EMAIL;
						$sRedirect = $this->getInvalidEmailUrl();
					} else {
						$aAjaxResponse[ 'status' ] = self::STAT_CUSTOM_PASS_RESET;
						$sRedirect = $this->getCustomPassResetUrl();
					}
					
				} elseif ( 'resetpass' == $_GET[ 'action' ] ) {

					// displays a message screen saying new password was generated
					$aAjaxResponse[ 'status' ] = self::STAT_RESET_PASSWORD_SUCCESS;
					$sRedirect = $this->getResetPasswordSuccessUrl();
					
				} elseif (
					( isset( $_POST[ 'wp-submit' ] ) ) || 
					( isset( $_POST[ 'wp-submit_x' ] ) ) || 
					( isset( $_POST[ 'wp-submit_y' ] ) ) || 
					( isset( $_GET[ 'action' ] ) )	
				) {
					
					// trying to log-in, but was not redirected
					// there probably some error logging in (eg: bad credentials)
					
					// TO DO: capture actual error details
					$aAjaxResponse[ 'status' ] = self::STAT_LOGIN_ERROR;
					$sRedirect = $this->getLoginErrorUrl();
									
				} elseif ( 'confirm' == $_GET[ 'checkemail' ] ) {
					
					// displays a message screen asking to check email for confirmation
					$aAjaxResponse[ 'status' ] = self::STAT_CONFIRM_NEW_PASS;
					$sRedirect = $this->getConfirmNewPassUrl();
					
				} elseif ( 'newpass' == $_GET[ 'checkemail' ] ) {
					
					// displays a message screen saying new password was generated
					$aAjaxResponse[ 'status' ] = self::STAT_NEW_PASSWORD;
					$sRedirect = $this->getNewPasswordUrl();
					
				}
				
				if ( $bAjaxResponse ) {
					// encode some ajax response object
					$aAjaxResponse = $this->modifyAjaxResponse( $aAjaxResponse );
					echo Zend_Json::encode( $aAjaxResponse );
				} else {
					if ( '' != $sRedirect ) {
						header( 'Location: ' . $sRedirect );
					} else {
						echo $sOutput;
					}
				}
				
				die();
				
			} else {
				
				if ( $bAjaxResponse ) {
					
					global $user;
					
					$aAjaxResponse[ 'status' ] = self::STAT_LOGIN_SUCCESS;
					$aAjaxResponse[ 'user_id' ] = $user->ID;
					$aAjaxResponse[ 'user_login' ] = $user->user_login;
					$aAjaxResponse[ 'user_nicename' ] = $user->user_nicename;
					$aAjaxResponse[ 'user_email' ] = $user->user_email;
					
					// format HTTP headers to send to JSON
					$aHeaders = headers_list();
					
					foreach ( $aHeaders as $sHeader ) {
						$aHeader = explode( ':', $sHeader );
						$sKey = trim( array_shift( $aHeader ) );
						$sValue = trim( implode( ':', $aHeader ) );
						$aAjaxResponse[ $sKey ][] = $sValue;
					}
					
					foreach ( $aAjaxResponse as $sKey => $aEntry ) {
						if (
							( is_array( $aEntry ) ) && 
							( count( $aEntry ) == 1 )
						) {
							$aAjaxResponse[ $sKey ] = $aEntry[ 0 ];
						}
					}
					
					// the overrideRedirectLocation() hook method kills any redirects
					$aAjaxResponse = $this->modifyAjaxResponse( $aAjaxResponse );
					echo Zend_Json::encode( $aAjaxResponse );
					
				}
				
			}
			
		}
		
	}
	
	//
	protected function modifyAjaxResponse( $aAjaxResponse ) {
		return $aAjaxResponse;
	}
	
	
	
	// email notification
	
	//
	public function trackUserLogin( $mUser, $sNewPass = '' ) {
		
		if ( is_string( $mUser ) ) {
			$this->_sTrackUserLogin = $mUser;
		} elseif ( is_object( $mUser ) ) {
			$this->_sTrackUserLogin = $mUser->user_login;
		}
		
	}
	
	//
	public function modifyRetrievePasswordTitle( $sTitle ) {
		return ( $this->_oEmsgRetrievePassword ) ?
			$this->_oEmsgRetrievePassword->getSubjectMerged() : 
			$sTitle
		;
	}
	
	//
	public function modifyRetrievePasswordMessage( $sMessage, $sKey ) {
	
		if ( $this->_oEmsgRetrievePassword ) {
			
			$sUrl = network_site_url( sprintf( 'wp-login.php?action=rp&key=%s&login=%s', $sKey, rawurlencode( $this->_sTrackUserLogin ) ),  'login' );
			$oUser = new Geko_Wp_User( sanitize_title( $this->_sTrackUserLogin ) );
			
			return $this->_oEmsgRetrievePassword
				->setMergeParam( '__recipient_email', $oUser->getEmail() )
				->setMergeParam( '__recipient_name', $oUser->getFullName() )
				->setMergeParam( 'username', $this->_sTrackUserLogin )
				->setMergeParam( 'password_reset_url', $sUrl )
				->setMergeParam( 'password_reset_key', $sKey )
				->getBodyTextMerged()
			;
		}
		
		return $sMessage;
	}
	
	//
	public function modifyPasswordResetTitle( $sTitle ) {
		return ( $this->_oEmsgPasswordReset ) ?
			$this->_oEmsgPasswordReset->getSubjectMerged() : 
			$sTitle
		;
	}
	
	//
	public function modifyPasswordResetMessage( $sMessage, $sNewPass ) {
		
		if ( $this->_oEmsgPasswordReset ) {
			return $this->_oEmsgPasswordReset
				->setMergeParam( 'username', $this->_sTrackUserLogin )
				->setMergeParam( 'new_pass', $sNewPass )
				->getBodyTextMerged()
			;
		}
		
		return $sMessage;
	}
	
	
	//
	public function modifyWpAdminEmail( $sEmail ) {
		
		$aBacktrace = debug_backtrace();
		$sFunc = $aBacktrace[ 4 ][ 'function' ];	// retrieve_password or reset_password
		
		if ( ( 'retrieve_password' == $sFunc ) && ( $this->_oEmsgRetrievePassword ) ) {
			return $this->_oEmsgRetrievePassword->getSenderEmail();
		} elseif ( ( 'reset_password' == $sFunc ) && ( $this->_oEmsgPasswordReset ) ) {
			return $this->_oEmsgPasswordReset->getSenderEmail();		
		}
		
		return $sEmail;
	}

	//
	public function modifyWpAdminName( $sName ) {
		
		$aBacktrace = debug_backtrace();
		$sFunc = $aBacktrace[ 4 ][ 'function' ];	// retrieve_password or reset_password
		
		if ( ( 'retrieve_password' == $sFunc ) && ( $this->_oEmsgRetrievePassword ) ) {
			return $this->_oEmsgRetrievePassword->getSenderName();
		} elseif ( ( 'reset_password' == $sFunc ) && ( $this->_oEmsgPasswordReset ) ) {
			return $this->_oEmsgPasswordReset->getSenderName();		
		}
		
		return $sName;
	}
	
	// prevents status 302 from occuring which messes up ajax requests
	public function overrideRedirectLocation( $location, $status ) {
		
		if (
			( FALSE !== strpos( $_SERVER[ 'REQUEST_URI' ], 'wp-login.php' ) ) && 
			( $_REQUEST[ 'ajax_response' ] )
		) {
			return '';
		}
		
		return $location;
	}
	
}



// ---------------------------------------------------------------------------------------------- //
/*

Sample Headers on Success:

{
	"X-Powered-By": "PHP\/5.2.10",
	"Expires": "Wed, 11 Jan 1984 05:00:00 GMT",
	"Last-Modified": "Sun, 17 Oct 2010 05:22:03 GMT",
	"Cache-Control": "no-cache, must-revalidate, max-age=0",
	"Pragma": "no-cache",
	"Content-Type": "text\/html; charset=UTF-8",
	"Set-Cookie": [
		"wordpress_test_cookie=WP+Cookie+check; path=\/",
		"wordpress_bf61784411b63d321aede8b513f39594=john.doe%40acme.com%7C1287465723%7C8640f44f463fbc4b9184c82d7dfbb016; path=\/wp-content\/plugins; httponly",
		"wordpress_bf61784411b63d321aede8b513f39594=john.doe%40acme.com%7C1287465723%7C8640f44f463fbc4b9184c82d7dfbb016; path=\/wp-admin; httponly",
		"wordpress_logged_in_bf61784411b63d321aede8b513f39594=john.doe%40acme.com%7C1287465723%7Cf979a786a7940498dd4c0a0230544ad1; path=\/; httponly"
	],
	"Location": "http:\/\/goodfoot.geekoracle.com"
}

*/