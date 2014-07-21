<?php

//
class Geko_Wp_User extends Geko_Wp_Entity
{	
	
	//// factory methods
	
	//
	public static function getLoggedIn( $aData = array(), $aQueryParams = NULL ) {
		
		global $current_user;
		get_currentuserinfo();
		
		$sClass = get_called_class();
		
		if ( $current_user ) {
			if ( is_array( $aQueryParams ) ) {
				return new $sClass( $current_user->ID, NULL, $aData, $aQueryParams );
			} else {
				return new $sClass( $current_user );
			}
		} else {
			return NULL;
		}
	}
	
	
	
	
	//// object oriented functions

	protected $_sEntityIdVarName = 'geko_user_id';
	// protected $_sEntitySlugVarName = 'geko_user_slug';
	protected $_sEntitySlugVarName = 'login_email_nicename';
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
			->setEntityMapping( 'title', 'display_name' )
			->setEntityMapping( 'slug', 'user_nicename' )
			->setEntityMapping( 'date_created', 'user_registered' )
		;
		
		return $this;
	}
	
	
	
	
	
	// !!! function getTitle() may not be intuitive since people
	// have titles such as vice-president

	//
	public function getContent() {
		return $this->getEntityPropertyValue( 'description' );
	}
	
	
	
	//// specific to this type
	
	//
	public function getLogin() {
		return $this->getEntityPropertyValue( 'user_login' );
	}
	
	//
	public function getEmail() {
		return $this->getEntityPropertyValue( 'user_email' );
	}
	
	//
	public function getDisplayName() {
		return $this->getTitle();
	}
	
	//
	public function getIsActivated() {
		return ( trim( $this->getEntityPropertyValue( 'geko_activation_key' ) ) ) ? FALSE : TRUE;
	}
	
	//
	public function getActivationKey() {
		return $this->getEntityPropertyValue( 'geko_activation_key' );
	}
	
	//
	public function getPasswordResetKey() {
		return $this->getEntityPropertyValue( 'geko_password_reset_key' );
	}
	
	//
	public function getIsFirstLogin() {
		return ( trim( $this->getEntityPropertyValue( 'geko_has_logged_in' ) ) ) ? FALSE : TRUE;
	}
	
	//
	public function getEditUrl() {
		return sprintf( '%s?user_id=%d', Geko_Uri::getUrl( 'wp_user_edit' ), $this->getId() );
	}
	
	// !!! show_latest_post_date = TRUE must be defined for query
	public function getLatestPostDate( $sDateFormat = '' ) {
		return $this->dateFormat(
			$this->getEntityPropertyValue( 'latest_post_date' ),
			$sDateFormat
		);
	}
	
	
	
	//
	public function getRawMeta( $sMetaKey ) {
		return get_usermeta( $this->getId(), $sMetaKey );
	}
	
	//
	public function getEmailLink( $sInnerHtml = '' ) {
		$sInnerHtml = ( $sInnerHtml ) ? $sInnerHtml : $this->getEmail();
		return sprintf( '<a href="mailto:%s">%s</a>', $this->getEmail(), $sInnerHtml );
	}
	
	//
	public function getFullName() {
		return trim( sprintf( '%s %s', $this->getFirstName(), $this->getLastName() ) );
	}
	
	
	//
	public function getTheTitle() {
		return Geko_String::coalesce( $this->getFullName(), $this->getTitle() );
	}
	
	
	
	//
	public function getDefaultEntityValue() {
		
		if ( $oEntity = parent::getDefaultEntityValue() ) {
			return $oEntity;
		}
		
		global $current_user;
		
		get_currentuserinfo();
		
		if ( $current_user && $current_user->ID ) {
			if ( is_array( $this->_aQueryParams ) ) {
				return $current_user->ID;
			} else {
				return $current_user;			
			}
		}
		
		return NULL;
		
	}
	
	
}


