<?php

// this is a Geko_Delegate
class Geko_Wp_User_Record extends Geko_Wp_Entity_Record
{
	
	// field values accepted by wp_insert_user() or wp_update_user()
	protected $_aKeys = array(
		
		// these are actual fields in the wp_users table
		
		'ID',
		'user_login',					// only works with wp_insert_user()
		'user_pass',					// retrieved value is a hash value
		'user_nicename',
		'user_email',
		'user_url',						// automatically appends http:// if not present
		'user_registered',
		'display_name',
		
		// these fields are in the table, but not affected: user_activation_key, user_status
		
		// these are wp_usermeta keys
		
		'nickname',
		'first_name',
		'last_name',
		'description',
		'rich_editing',
		'role',
		'jabber',
		'aim',
		'yim',
		'show_admin_bar_front'
		
	);
	
	protected $_aIgnoreKeys = array(
		'user_activation_key',
		'user_status',
		'role_id',
		'role_title',
		'role_slug'
	);
	
	// Fields from Geko_Wp_User_Query
	// "synthetic" fields: role_id, role_title, role_slug
	// joined via usermeta: geko_activation_key, geko_password_reset_key, geko_has_logged_in
	// phone (from Gloc_User_Query)
	
	
	
	//
	public function init() {
		
		$this->addPlugin( 'Geko_Wp_User_Record_Meta' );
		
		parent::init();
		
		return $this;
	}
	
	
	
	
	//// db crud methods: insert, update, and delete
	
	//
	public function insert( $oTable, $aAllValues, $aValues, $aOtherValues ) {
		
		
		// run validation hook
		$this->throwValidate( $aAllValues );
		
		$aValues = $this->sanitizeValues( $aValues );
		
		
		
		// catch any possible wordpress errors
		
		//
		$mRes = wp_insert_user( $aValues );
		
		if ( !is_wp_error( $mRes ) ) {
			
			$this->afterCommit( $aValues );
			
			// hook method
			$this->handleOtherValues( $aOtherValues );
			
			$this->setEntityPropertyValue( 'ID', $mRes );
			
		} else {
			
			throw $this->newException( 'db', 'Insert user failed!', $this->formatWpErrorMessages( $mRes ) );
		}
		
		
	}
	
	//
	public function update( $oTable, $aAllValues, $aValues, $aOtherValues, $aWhere ) {
		
		
		// run validation hook
		$this->throwValidate( $aAllValues, 'update' );
		
		$aValues = $this->sanitizeValues( $aValues, 'update' );
		
		
		// catch any possible wordpress errors
		
		// merge $aValues and $aWhere because the ID field is needed in wp_update_user()
		$mRes = wp_update_user( array_merge( $aValues, $aWhere ) );
		
		if ( !is_wp_error( $mRes ) ) {
			
			$this->afterCommit( $aValues, 'update' );

			// hook method
			$this->handleOtherValues( $aOtherValues, 'update' );
						
		} else {
			
			throw $this->newException( 'db', 'Update user failed!', $this->formatWpErrorMessages( $mRes ) );
		}
		
		
	}
	
	//
	public function delete( $oTable, $aWhere ) {
		
		// Invoke deletion hook
		$this->beforeDelete();
		
		require_once( sprintf( '%swp-admin/includes/user.php', ABSPATH ) );
		
		wp_delete_user( $aWhere[ 'ID' ], 1 );
		
	}
	
	
	
	//// sanitize values before insert or update
	
	//
	protected function sanitizeValues( $aValues, $sMode = 'insert' ) {
		
		$aValues = $this->formatTrueFalse( $aValues, array( 'show_admin_bar_front' ) );
		
		if ( 'update' == $sMode ) {
			
			// ensure password does not get messed when updating
			if (
				( $aValues[ 'user_pass' ] ) && 
				( $this->_aOrigValues[ 'user_pass' ] ) &&
				( $aValues[ 'user_pass' ] === $this->_aOrigValues[ 'user_pass' ] )
			) {
				unset( $aValues[ 'user_pass' ] );		// don't commit this value
			}
			
		}
				
		return $aValues;
	}
	
	//
	protected function afterCommit( $aValues, $sMode = 'insert' ) {
		
		if ( $aValues[ 'role' ] ) {
			Geko_Wp_User_RoleType::getInstance()->reconcileAssigned();
		}
		
	}
	
	
	
	
	//// override super-class behavior
	
	//
	protected function formatValues( $oTable, $oSubject ) {
		
		$aAllValues = ( array ) $oSubject->getRawEntity();
		
		
		$aValues = array();
		$aOtherValues = array();
		
		foreach ( $aAllValues as $sKey => $mValue ) {
			
			if ( in_array( $sKey, $this->_aKeys ) ) {
				$aValues[ $sKey ] = $mValue;
			} else {
				if ( !in_array( $sKey, $this->_aIgnoreKeys ) ) {
					$aOtherValues[ $sKey ] = $mValue;
				}
			}
			
		}
		
		return array( $aAllValues, $aValues, $aOtherValues );
	}
	
	
	
	// input validation
	public function validate( $aValues, $sMode ) {
		
		$aErrors = array();
		
		$sUserEmail = trim( $aValues[ 'user_email' ] );
		$sUserLogin = trim( $aValues[ 'user_login' ] );
		$sRole = strtolower( trim( $aValues[ 'role' ] ) );
		
		
		if ( !$sUserEmail ) {
			$aErrors[ 'user_email' ] = 'An email address must be specified!';
		} else {
			if ( !is_email( $sUserEmail ) ) {
				$aErrors[ 'user_email' ] = 'An valid email address must be specified!';		
			} else {
				
				if (
					( $sUserEmail != $this->_aOrigValues[ 'user_email' ] ) && 
					( email_exists( $sUserEmail ) )
				) {
					$aErrors[ 'user_email' ] = 'Sorry, that email address is already used!';
				}
				
			}
		}
		
		
		if ( !$sUserLogin ) {
			$aErrors[ 'user_login' ] = 'A user login must be specified!';
		} else {
			
			if (
				( $sUserLogin != $this->_aOrigValues[ 'user_login' ] ) && 
				( username_exists( $sUserLogin ) )
			) {
				$aErrors[ 'user_login' ] = 'Sorry, that user login is already used!';
			}
		}
		
		
		
		// change role
		if ( !$sRole ) {
			$aErrors[ 'role' ] = 'A role must be specified!';		
		} else {
			if ( !Geko_Wp_User_RoleType::getInstance()->hasRoleSlug( $sRole ) ) {
				$aErrors[ 'role' ] = 'Invalid role was specified!';		
			}
		}
		
		return $aErrors;
	}
	
	
	
	
	
}


