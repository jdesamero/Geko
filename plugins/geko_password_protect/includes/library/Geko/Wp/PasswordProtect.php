<?php

class Geko_Wp_PasswordProtect extends Geko_Wp_Plugin
{
	
	protected $_sPrefix = 'geko_ppt';
	
	protected $_bAddTemplatePages = TRUE;
	
	protected $sCustomFormPath = '';
	protected $bLoginFailed = FALSE;
	protected $bIsAuth = FALSE;
	
	
	//// accessors
	
	//
	public function setCustomFormPath( $sCustomFormPath ) {
		$this->sCustomFormPath = $sCustomFormPath;
		return $this;
	}
	
	//
	public function getLoginFailed() {
		return $this->bLoginFailed;
	}
	
	//
	public function getIsAuth() {
		return $this->bIsAuth;
	}
	
	
	//// hook to theme filters and actions
	
	//
	public function templateRedirect() {
		
		$aSessParams = session_get_cookie_params();
		
		@session_set_cookie_params(
			$aSessParams[ 'lifetime' ],
			Geko_Wp::getSessionPath(),
			$aSessParams[ 'domain' ],
			$aSessParams[ 'secure' ],
			$aSessParams[ 'httponly' ]
		);
		
		@session_start();
		
		$sAuthKey = sprintf( '%s-auth', $this->getPrefix() );
		
		// perform authentication
		if ( isset( $_POST[ 'user' ] ) && isset( $_POST[ 'pass' ] ) ) {
			
			if (
				( $_POST[ 'user' ] ) && ( $this->getOption( 'user' ) == $_POST[ 'user' ] ) && 
				( $_POST[ 'pass' ] ) && ( $this->getOption( 'pass' ) == $_POST[ 'pass' ] )
			) {
				
				$_SESSION[ $sAuthKey ] = TRUE;
				header( sprintf( 'Location: %s', Geko_Uri::getFullCurrent() ) );
				
				die();
				
			} else {
				$this->bLoginFailed = $bLoginFailed = TRUE;
			}
		}
		
		if ( $_SESSION[ $sAuthKey ] ) $this->bIsAuth = TRUE;
		
		//
		if ( !$_SESSION[ $sAuthKey ] ) {
			
			$sPath = NULL;
			
			if ( $this->getOption( 'use_custom_form' ) ) {
				
				if ( $this->sCustomFormPath ) {
					$sPath = $this->sCustomFormPath;
				} else {
					$sPath = sprintf( '%s/page_login.php', TEMPLATEPATH );
				}
				
			} else {
				
				$sPath = sprintf( '%s/login_form.php', $this->getPluginDir() );
			}
			
			//
			if ( $sPath ) {
				include( $sPath );
			}
			
			die();
		}
	}
	
	
}


