<?php

//
class Gloc_Service_Profile extends Geko_Wp_Service
{

	const STAT_LOGIN = 1;
	const STAT_NOT_ACTIVATED = 2;
	
	const STAT_ACTIVATE_ACCOUNT = 3;
	
	const STAT_REGISTER = 4;
	const STAT_EMAIL_EXISTS = 5;
	
	const STAT_UPDATE_PROFILE = 6;
	const STAT_BAD_PASSWORD = 7;
	const STAT_CHANGE_EMAIL = 8;
	const STAT_CHANGE_PASSWORD = 9;
	
	const STAT_FORGOT_PASSWORD = 10;
	const STAT_SET_PASSWORD = 11;
	
	const STAT_SEND_NOTIFICATION_FAILED = 12;
	
	const STAT_UNSUBSCRIBE = 13;
	
	const STAT_ERROR = 999;
	
	
	
	
	//
	public function processLogin() {
		
		$sEmail = trim( $_POST[ 'email' ] );
		$sPassword = trim( $_POST[ 'password' ] );
		
		// do initial validation
		if ( $sEmail && $sPassword && email_exists( $sEmail ) ) {
			
			$oUser = new Gloc_User( $sEmail );
			
			if ( $oUser->getIsActivated() ) {
				
				$oWpUser = wp_signon( array(
					'user_login' => $oUser->getUserLogin(),
					'user_password' => $sPassword
				) );
				
				if ( !is_wp_error( $oWpUser ) ) {
					$this->setStatus( self::STAT_LOGIN );
				}
				
			} else {
				$this->setStatus( self::STAT_NOT_ACTIVATED );
			}
		}
		
	}
	
	//
	public function processRegister() {
		
		$sEmail = trim( $_POST[ 'email' ] );
		$sFirstName = trim( $_POST[ 'first_name' ] );
		$sLastName = trim( $_POST[ 'last_name' ] );
		$sPassword = trim( $_POST[ 'password' ] );
		$sConfirmPass = trim( $_POST[ 'confirm_pass' ] );
		
		// do initial validation
		if ( $sEmail && $sFirstName && $sLastName && $sPassword && $sConfirmPass && ( $sPassword == $sConfirmPass ) ) {
			
			// do checks
			if ( !email_exists( $sEmail ) ) {
				
				// set-up vars
				
				$sActivationKey = md5( $sEmail . $sPassword . $sFirstName . $sLastName . rand() . time() );
				
				// create user
				
				$iUserId = wp_create_user( $sEmail, $sPassword, $sEmail );
				
				// set user role
				
				$oWpUser = new WP_User( $iUserId );
				$oWpUser->set_role( 'subscriber' );
				
				Geko_Wp_User_RoleType::getInstance()->reconcileAssigned();

				// set meta-data
				
				update_usermeta( $iUserId, 'first_name', $sFirstName );
				update_usermeta( $iUserId, 'last_name', $sLastName );
				update_usermeta( $iUserId, 'show_admin_bar_front', 'false' );
				update_usermeta( $iUserId, 'geko_activation_key', $sActivationKey );
				
				// send notification
				
				try {
					
					$this->deliverMail( 'activate-account-notification', array(
						'recipients' => array(
							array( $sEmail, $sFirstName . ' ' . $sLastName )
						),
						'merge_params' => array(
							'activation_link' => Geko_Wp::getUrl() . '/login/activate-account/?key=' . $sActivationKey
						)
					) )->send();
					
					$this->setStatus( self::STAT_REGISTER );
					
				} catch ( Zend_Mail_Transport_Exception $e ) {
					$this->setStatus( self::STAT_SEND_NOTIFICATION_FAILED );
				}
				
			} else {
				$this->setStatus( self::STAT_EMAIL_EXISTS );
			}
			
		}
		
	}
	
	//
	public function processActivateAccount() {
		
		if ( $sKey = trim( $_REQUEST[ 'key' ] ) ) {
			
			$oUser = Gloc_User::getOne( array( 'geko_activation_key' => $sKey ), FALSE );
			
			if ( $oUser->isValid() ) {

				update_usermeta( $oUser->getId(), 'geko_activation_key', '' );
				
				// send notification
		
				try {

					$this->deliverMail( 'activate-account-confirm-notification', array(
						'recipients' => array(
							array( $oUser->getEmail(), $oUser->getFullName() )
						)
					) )->send();
					
					$this->setStatus( self::STAT_ACTIVATE_ACCOUNT );
					
				} catch ( Zend_Mail_Transport_Exception $e ) {
					$this->setStatus( self::STAT_SEND_NOTIFICATION_FAILED );
				}
				
			}
		}
		
	}
	
	//
	public function processUpdateProfile() {
		
		global $user_ID;
		global $wpdb;
		
		$sEmail = trim( $_POST[ 'email' ] );
		$sFirstName = trim( $_POST[ 'first_name' ] );
		$sLastName = trim( $_POST[ 'last_name' ] );
		$sPassword = trim( $_POST[ 'password' ] );
		$sNewPass = trim( $_POST[ 'new_pass' ] );
		$sConfirmNewPass = trim( $_POST[ 'confirm_new_pass' ] );
		
		$bNewEmail = FALSE;
		$bNewPass = FALSE;
		
		if ( $user_ID ) {
			$oUser = new Gloc_User( $user_ID );
			$iUserId = $oUser->getId();
			$bNewEmail = ( $sEmail != $oUser->getEmail() ) ? TRUE : FALSE;
		}
		
		$bNewPass = ( $sNewPass && $sConfirmNewPass && ( $sNewPass == $sConfirmNewPass ) ) ? TRUE : FALSE;
		
		// do initial checks
		if (
			( $iUserId && $sEmail && $sFirstName && $sLastName ) && 
			(
				( !$sPassword ) || 
				( $sPassword && ( $bNewEmail || $bNewPass ) )
			)
		) {
			
			//// do checks
			
			// make sure email given does not exist in the system
			if ( $bNewEmail && email_exists( $sEmail ) ) {
				$this->setStatus( self::STAT_EMAIL_EXISTS );
			}
			
			// make sure current password given was good, if changing email and/or password
			if (
				( !$this->hasStatus() ) && 
				( ( $bNewEmail || $bNewPass ) && !wp_check_password( $sPassword, $oUser->getUserPass() ) )
			) {
				$this->setStatus( self::STAT_BAD_PASSWORD );
			}
			
			// proceed if checks are okay
			if ( !$this->hasStatus() ) {
			
				update_usermeta( $iUserId, 'first_name', $sFirstName );
				update_usermeta( $iUserId, 'last_name', $sLastName );
				
				// email was changed
				if ( $bNewEmail ) {

					// change email and generate activation key
					
					wp_update_user( array(
						'ID' => $iUserId,
						'user_nicename' => sanitize_title( $sEmail ),
						'user_email' => $sEmail,
						'display_name' => $sEmail
					) );
					
					$wpdb->update(
						$wpdb->users,
						array( 'user_login' => $sEmail ),
						array( 'ID' => $iUserId )
					);

					$sActivationKey = md5( $sEmail . $sPassword . $sFirstName . $sLastName . rand() . time() );
					update_usermeta( $iUserId, 'geko_activation_key', $sActivationKey );
					
					// send notification
					try {
						
						$this->deliverMail( 'activate-account-notification', array(
							'recipients' => array(
								array( $sEmail, $sFirstName . ' ' . $sLastName )
							),
							'merge_params' => array(
								'activation_link' => Geko_Wp::getUrl() . '/login/activate-account/?key=' . $sActivationKey
							)
						) )->send();
						
						$this->setStatusMulti( self::STAT_CHANGE_EMAIL );
						
					} catch ( Zend_Mail_Transport_Exception $e ) {
						$this->setStatus( self::STAT_SEND_NOTIFICATION_FAILED );
					}
					
				}
				
				// password was changed
				if ( $bNewPass ) {
					wp_set_password( $sNewPass, $iUserId );
					$this->setStatusMulti( self::STAT_CHANGE_PASSWORD );
				}
				
				// if email and/or password was changed
				if ( $bNewEmail || $bNewPass ) {
					// log-out user
					wp_logout();
				} else {
					// no re-login required
					$this->setStatus( self::STAT_UPDATE_PROFILE );
				}
				
			}
			
		}
		
	}
	
	
	//
	public function processForgotPassword() {
		
		// do checks
		$sEmail = strtolower( trim( $_REQUEST[ 'email' ] ) );
		
		if ( email_exists( $sEmail ) ) {
			
			$oUser = new Gloc_User( $sEmail );
			$iUserId = $oUser->getId();
			
			$sKey = md5( $oUser->getFullName() . time() . rand() );
			
			update_usermeta( $iUserId, 'geko_password_reset_key', $sKey );
			
			// send notification
			
			try {
				
				$this->deliverMail( 'set-password-notification', array(
					'recipients' => array(
						array( $oUser->getEmail(), $oUser->getFullName() )
					),
					'merge_params' => array(
						'password_reset_link' => Geko_Wp::getUrl() . '/login/set-password/?key=' . $sKey
					)
				) )->send();
				
				$this->setStatus( self::STAT_FORGOT_PASSWORD );
				
			} catch ( Zend_Mail_Transport_Exception $e ) {
				$this->setStatus( self::STAT_SEND_NOTIFICATION_FAILED );
			}
			
		}
		
	}
	
	
	//
	public function processSetPassword() {
		
		// do checks
		$sKey = $_REQUEST[ 'key' ];
		$sPassword = trim( $_REQUEST[ 'password' ] );
		$sConfirmPass = trim( $_REQUEST[ 'confirm_pass' ] );
		
		$oUser = Gloc_User::getOne( array( 'geko_password_reset_key' => $sKey ), FALSE );
		
		if ( $oUser->isValid() && ( $sPassword == $sConfirmPass ) ) {
			
			$iUserId = $oUser->getId();
			
			update_usermeta( $iUserId, 'geko_password_reset_key', '' );
			
			wp_set_password( $sPassword, $iUserId );

			// send notification
			
			try {

				$this->deliverMail( 'set-password-confirm-notification', array(
					'recipients' => array(
						array( $oUser->getEmail(), $oUser->getFullName() )
					)
				) )->send();
				
				$this->setStatus( self::STAT_SET_PASSWORD );
				
			} catch ( Zend_Mail_Transport_Exception $e ) {
				$this->setStatus( self::STAT_SEND_NOTIFICATION_FAILED );
			}
			
		}
		
	}
	
	
	//
	public function processUnsubscribe() {
		
		// do checks
		$sEmail = strtolower( trim( $_REQUEST[ 'email' ] ) );
		
		if ( email_exists( $sEmail ) ) {
			
			$oUser = new Gloc_User( $sEmail );
			$iUserId = $oUser->getId();
			
			wp_delete_user( $iUserId, 1 );
			
			$this->setStatus( self::STAT_UNSUBSCRIBE );
			
		}
		
	}
	
	
	
}



