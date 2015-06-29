<?php

//
class Geko_Wp_Service extends Geko_Service
{
	
	protected $_aPrefixes = array( 'Gloc_', 'Geko_Wp_', 'Geko_' );
	
	
	
	
	////// convenience methods for saving to database
	
	
	//// email delivery stuff
	
	//
	public function deliverMail( $sSlug, $aParams ) {
	
		$oDeliverMail = new Geko_Wp_EmailMessage_Delivery( $sSlug );
		
		if ( is_array( $aRecipients = $aParams[ 'recipients' ] ) ) {
			$oDeliverMail->addRecipients( $aRecipients );
		}
		
		if ( is_array( $aCcs = $aParams[ 'ccs' ] ) ) {
			$oDeliverMail->addCcs( $aCcs );
		}
		
		if ( is_array( $aMergeParams = $aParams[ 'merge_params' ] ) ) {
			
			foreach ( $aMergeParams as $sKey => $mParam ) {
				if ( is_array( $mParam ) ) {
					$aMergeParams[ $sKey ] = implode( ', ', $mParam );
				}
			}
			
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
	
	
	
	
	//// create wordpress user
	
	
	//
	public function createUser( $sLogin, $sPassword, $sEmail, $sRole = 'subscriber' ) {
		
		// create user
		
		$iUserId = wp_create_user( $sLogin, $sPassword, $sEmail );
		
		// set user role
		
		$oWpUser = new WP_User( $iUserId );
		$oWpUser->set_role( $sRole );
		
		Geko_Wp_User_RoleType::getInstance()->reconcileAssigned();
		
		
		return $iUserId;
		
	}
	
	
	//// enumeration
	
	
	//
	public function getEnumeration( $sSlug ) {
		return Geko_Wp_Enumeration_Query::getSet( $sSlug );
	}
	
	
	//// user meta
	
	//
	public function insertUserMeta( $iUserId, $aValues, $aFiles = NULL, $sManageClass = NULL ) {
		
		$aValues = $this->updateUserNative( $iUserId, $aValues );
		
		if ( count( $aValues ) > 0 ) {
		
			if ( $oMng = $this->resolveClassInstance( 'User_Meta', $sManageClass ) ) {
				
				$oMng->setUserId( $iUserId );
				
				return $oMng->save( $iUserId, 'insert', NULL, $aValues, $aFiles );
			}
			
		} else {
			
			return TRUE;
		}
		
		return NULL;
	}
	
	//
	public function updateUserMeta( $iUserId, $aValues, $aFiles = NULL, $sManageClass = NULL ) {
		
		$aValues = $this->updateUserNative( $iUserId, $aValues );
		
		if ( count( $aValues ) > 0 ) {
			
			if ( $oMng = $this->resolveClassInstance( 'User_Meta', $sManageClass ) ) {
				
				$oMng->setUserId( $iUserId );
				
				return $oMng->save( $iUserId, 'update', NULL, $aValues, $aFiles );
			}
			
		} else {
			
			return TRUE;
		}
		
		return NULL;
	}
	
	//
	public function updateUserNative( $iUserId, $aValues ) {
		
		// raw database fields
		$aRawDbFields = array( 'user_login' );
		
		// keys used in wp_insert_user() or wp_update_user()
		$aUserFields = array(
			'user_pass', 'user_nicename', 'user_url', 'user_email', 'display_name', 'nickname',
			'first_name', 'last_name', 'description', 'rich_editing', 'user_registered', 'role',
			'jabber', 'aim', 'yim', 'show_admin_bar_front'
		);
		
		//// setup buckets
		
		$sRole = NULL;
		$sPassword = NULL;
		
		$aRawDb = array();
		$aUser = array();
		$aUserMeta = array();
		
		$aLeftover = array();
		
		foreach ( $aValues as $sKey => $mValue ) {
			
			if ( '!role' == $sKey ) {
				
				$sRole = $mValue;

			} elseif ( '!password' == $sKey ) {
				
				$sPassword = $mValue;
				
			} elseif ( 0 === strpos( $sKey, '!' ) ) {
				
				$sKey = substr( $sKey, 1 );
				
				if ( in_array( $sKey, $aRawDbFields ) ) {
					
					// raw db query
					$aRawDb[ $sKey ] = $mValue;
					
				} elseif ( in_array( $sKey, $aUserFields ) ) {
					
					// wp_users table
					$aUser[ $sKey ] = $mValue;
					
				} else {
					
					// wp_usermeta table
					$aUserMeta[ $sKey ] = $mValue;			
				}
				
			
			} else {
				$aLeftover[ $sKey ] = $mValue;
			}
		}
		
		
		//// perform operation
		
		// role
		if ( $sRole ) {
			
			$oWpUser = new WP_User( $iUserId );
			$oWpUser->set_role( $sRole );
			
			Geko_Wp_User_RoleType::getInstance()->reconcileAssigned();
		}
		
		// password
		if ( $sPassword ) {
			wp_set_password( $sPassword, $iUserId );
		}
		
		// raw db query
		if ( count( $aRawDb ) > 0 ) {
			
			$oDb = Geko_Wp::get( 'db' );
			
			$oDb->update( '##pfx##users', $aRawDb, array( 'ID = ?' => $iUserId ) );
		}
		
		// update wp_users
		if ( count( $aUser ) > 0 ) {
			
			$aUser[ 'ID' ] = $iUserId;
			
			wp_update_user( $aUser );
		}
		
		// update wp_usermeta
		if ( count( $aUserMeta ) > 0 ) {
			
			foreach ( $aUserMeta as $sKey => $mValue ) {
				update_usermeta( $iUserId, $sKey, $mValue );
			}
		}
		
		
		return $aLeftover;
	}
	
	
	
	//// user location
	
	//
	public function insertUserLocation( $iUserId, $aValues, $sManageClass = NULL ) {
		
		if ( $oMng = $this->resolveClassInstance( 'User_Location_Manage', $sManageClass ) ) {

			return $oMng->save(
				array(
					'object_id' => $iUserId,
					'object_type' => 'user'
				),
				'insert',
				$aValues
			);
		}
		
		return NULL;
	}
	
	//
	public function updateUserLocation( $iUserId, $aValues, $sManageClass = NULL ) {
		
		if ( $oMng = $this->resolveClassInstance( 'User_Location_Manage', $sManageClass ) ) {

			return $oMng->save(
				array(
					'object_id' => $iUserId,
					'object_type' => 'user'
				),
				'update',
				$aValues
			);
		}
		
		return NULL;	
	}
	
	
	
	
	//// helpers
	
	//
	public function resolveClass( $sClassSuffix, $sManageClass = NULL ) {
		
		// inline override
		if ( $sManageClass ) return $sManageClass;
		
		return Geko_Class::getBestMatch( $this->_aPrefixes, $sClassSuffix );
	}
	
	//
	public function resolveClassInstance( $sClassSuffix, $sManageClass = NULL ) {
		
		if ( $sClass = $this->resolveClass( $sClassSuffix, $sManageClass ) ) {
			
			return Geko_Singleton_Abstract::getInstance( $sClass )->init();
		}
		
		return NULL;
	}
	
	
}



