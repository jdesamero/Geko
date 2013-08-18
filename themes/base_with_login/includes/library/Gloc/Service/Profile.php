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
	public function process() {
		
		global $wpdb;
		
		if ( 'login' == $_REQUEST[ 'subaction' ] ) {
			
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
						$aAjaxResponse[ 'status' ] = self::STAT_LOGIN;
					}
					
				} else {
					$aAjaxResponse[ 'status' ] = self::STAT_NOT_ACTIVATED;
				}
					
				
			}
			
		} elseif ( 'register' == $_REQUEST[ 'subaction' ] ) {
			
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
						
						$oDeliverMail = new Geko_Wp_EmailMessage_Delivery( 'activate-account-notification' );
						$oDeliverMail
							->addRecipient( $sEmail, $sFirstName . ' ' . $sLastName )
							->setMergeParams( array(
								'activation_link' => get_bloginfo( 'url' ) . '/login/activate-account/?key=' . $sActivationKey
							) )
							->send()
						;
						
						$aAjaxResponse[ 'status' ] = self::STAT_REGISTER;
						
					} catch ( Zend_Mail_Transport_Exception $e ) {
						$aAjaxResponse[ 'status' ] = self::STAT_SEND_NOTIFICATION_FAILED;
					}
					
				} else {
					$aAjaxResponse[ 'status' ] = self::STAT_EMAIL_EXISTS;
				}
				
			}
			
		} elseif ( 'activate_account' == $_REQUEST[ 'subaction' ] ) {
			
			if ( $sKey = trim( $_REQUEST[ 'key' ] ) ) {
				
				$oUser = Gloc_User::getOne( array( 'geko_activation_key' => $sKey ), FALSE );
				
				if ( $oUser->isValid() ) {

					update_usermeta( $oUser->getId(), 'geko_activation_key', '' );
					
					// send notification
			
					try {
						
						$oDeliverMail = new Geko_Wp_EmailMessage_Delivery( 'activate-account-confirm-notification' );
						$oDeliverMail
							->addRecipient( $oUser->getEmail(), $oUser->getFullName() )
							->send()
						;
						
						$aAjaxResponse[ 'status' ] = self::STAT_ACTIVATE_ACCOUNT;

					} catch ( Zend_Mail_Transport_Exception $e ) {
						$aAjaxResponse[ 'status' ] = self::STAT_SEND_NOTIFICATION_FAILED;
					}
					
				}
			}
			
		} elseif ( 'update_profile' == $_REQUEST[ 'subaction' ] ) {
			
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
					$aAjaxResponse[ 'status' ] = self::STAT_EMAIL_EXISTS;
				}
				
				// make sure current password given was good, if changing email and/or password
				if (
					( !$aAjaxResponse[ 'status' ] ) && 
					( ( $bNewEmail || $bNewPass ) && !wp_check_password( $sPassword, $oUser->getUserPass() ) )
				) {
					$aAjaxResponse[ 'status' ] = self::STAT_BAD_PASSWORD;				
				}
				
				// proceed if checks are okay
				if ( !$aAjaxResponse[ 'status' ] ) {
				
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
							
							$oDeliverMail = new Geko_Wp_EmailMessage_Delivery( 'activate-account-notification' );
							$oDeliverMail
								->addRecipient( $sEmail, $sFirstName . ' ' . $sLastName )
								->setMergeParams( array(
									'activation_link' => get_bloginfo( 'url' ) . '/login/activate-account/?key=' . $sActivationKey
								) )
								->send()
							;
							
							$aAjaxResponse[ 'status' ][] = self::STAT_CHANGE_EMAIL;
							
						} catch ( Zend_Mail_Transport_Exception $e ) {
							$aAjaxResponse[ 'status' ] = self::STAT_SEND_NOTIFICATION_FAILED;
						}
						
					}
					
					// password was changed
					if ( $bNewPass ) {
						wp_set_password( $sNewPass, $iUserId );
						$aAjaxResponse[ 'status' ][] = self::STAT_CHANGE_PASSWORD;
					}
					
					// if email and/or password was changed
					if ( $bNewEmail || $bNewPass ) {
						// log-out user
						wp_logout();
					} else {
						// no re-login required
						$aAjaxResponse[ 'status' ] = self::STAT_UPDATE_PROFILE;
					}
					
				}
				
			}
			
		} elseif ( 'forgot_password' == $_REQUEST[ 'subaction' ] ) {
	
			// do checks
			$sEmail = strtolower( trim( $_REQUEST[ 'email' ] ) );
			
			if ( email_exists( $sEmail ) ) {
				
				$oUser = new Gloc_User( $sEmail );
				$iUserId = $oUser->getId();
				
				$sKey = md5( $oUser->getFullName() . time() . rand() );
				
				update_usermeta( $iUserId, 'geko_password_reset_key', $sKey );
				
				// send notification
				
				try {
					
					$oDeliverMail = new Geko_Wp_EmailMessage_Delivery( 'set-password-notification' );
					$oDeliverMail
						->addRecipient( $oUser->getEmail(), $oUser->getFullName() )
						->setMergeParams( array(
							'password_reset_link' => get_bloginfo( 'url' ) . '/login/set-password/?key=' . $sKey
						) )
						->send()
					;
					
					$aAjaxResponse[ 'status' ] = self::STAT_FORGOT_PASSWORD;
					
				} catch ( Zend_Mail_Transport_Exception $e ) {
					$aAjaxResponse[ 'status' ] = self::STAT_SEND_NOTIFICATION_FAILED;
				}
				
			}
			
		} elseif ( 'set_password' == $_REQUEST[ 'subaction' ] ) {
			
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
					
					$oDeliverMail = new Geko_Wp_EmailMessage_Delivery( 'set-password-confirm-notification' );
					$oDeliverMail
						->addRecipient( $oUser->getEmail(), $oUser->getFullName() )
						->send()
					;
					
					$aAjaxResponse[ 'status' ] = self::STAT_SET_PASSWORD;
					
				} catch ( Zend_Mail_Transport_Exception $e ) {
					$aAjaxResponse[ 'status' ] = self::STAT_SEND_NOTIFICATION_FAILED;
				}
				
			}
			
		} elseif ( 'unsubscribe' == $_REQUEST[ 'subaction' ] ) {
			
			// do checks
			$sEmail = strtolower( trim( $_REQUEST[ 'email' ] ) );
			
			if ( email_exists( $sEmail ) ) {
				
				$oUser = new Gloc_User( $sEmail );
				$iUserId = $oUser->getId();
				
				wp_delete_user( $iUserId, 1 );
				
				$aAjaxResponse[ 'status' ] = self::STAT_UNSUBSCRIBE;
				
			}
			
		}
		
		if ( !$aAjaxResponse[ 'status' ] ) {
			$aAjaxResponse[ 'status' ] = self::STAT_ERROR;
		}
		
		$this->aAjaxResponse = $aAjaxResponse;
		
		return $this;
	}
	
	
}



