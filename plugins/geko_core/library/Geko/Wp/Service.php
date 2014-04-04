<?php

//
class Geko_Wp_Service extends Geko_Service
{
	
	protected $_sManageClassPrefix = 'Geko_Wp_';
	
	protected $_aManageClassOverrides = array();
	
	
	/* /
	protected $_aManageClassOverrides = array(
		'User_Meta' => 'Geko_Wp_User_Meta',
		'User_Location_Manage' => 'Geko_Wp_User_Location_Manage'
	);
	/* */
	
	
	
	
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
		
		if ( $oMng = $this->resolveClassInstance( 'User_Meta', $sManageClass ) ) {
			
			$oMng->setUserId( $iUserId );
			
			return $oMng->save( $iUserId, 'insert', NULL, $aValues, $aFiles );
		}
		
		return NULL;
	}
	
	//
	public function updateUserMeta( $iUserId, $aValues, $aFiles = NULL, $sManageClass = NULL ) {
		
		if ( $oMng = $this->resolveClassInstance( 'User_Meta', $sManageClass ) ) {
			
			$oMng->setUserId( $iUserId );
			
			return $oMng->save( $iUserId, 'update', NULL, $aValues, $aFiles );
		}
		
		return NULL;
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
		
		// class override
		$sClass = $this->_aManageClassOverrides[ $sClassSuffix ];
		if ( $sClass ) return $sClass;
		
		// default
		$sClass = sprintf( '%s%s', $this->_sManageClassPrefix, $sClassSuffix );
		if ( class_exists( $sClass ) ) return $sClass;
		
		return NULL;
	}
	
	//
	public function resolveClassInstance( $sClassSuffix, $sManageClass = NULL ) {
		
		if ( $sClass = $this->resolveClass( $sClassSuffix, $sManageClass ) ) {
			
			return Geko_Singleton_Abstract::getInstance( $sClass )->init();
		}
		
		return NULL;
	}
	
	
}



