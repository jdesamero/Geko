<?php

//
class Geko_Wp_User_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_sSubject = 'User';
	
	protected $_sManagementCapability = 'edit_users';
	protected $_sEditFormId = 'your-profile';
	
	protected $_sEntityIdVarName = 'user_id';
	protected $_sSubAction = 'user';
	
	
	//
	protected function __construct() {
		
		global $current_user;
		
		parent::__construct();
		
		// see if the current user has management capabilities and store it
		if (
			is_user_logged_in() && 
			$current_user && 
			$current_user->has_cap( $this->_sManagementCapability ) 
		) {
			$this->_bHasManagementCapability = TRUE;
		}
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		if ( FALSE !== strpos( $sUrlPath, '/wp-admin/user-edit.php' ) ) {
			self::$sCurrentPage = $this->_sInstanceClass;
		}
		
		add_action( 'edit_user_profile_update', array( $this, 'update' ), 9 );
	}
	
	
	//
	public function attachPage() {
		if ( $iEntityId = $this->_iCurrentEntityId ) {
			$sUrl = sprintf( '/wp-admin/user-edit.php?%s=%d', $this->_sEntityIdVarName, $iEntityId );
			Geko_Wp_Admin_Menu::addMenu( $this->_sInstanceClass, 'Details', $sUrl );
			add_filter( 'admin_user_fields_pq', array( $this, 'attachMenu' ) );
			add_filter( 'admin_user_h2_pq', array( $this, 'modifyH2Doc' ) );
		}
	}
	
	//
	public function modifyH2Doc( $oDoc ) {

		$oDoc->find( 'h2' )->after(
			Geko_String::fromOb( array( $this, 'notificationMessages' ) )
		);
		
		return $oDoc;
	}
	
	
	
	//
	public function addAdmin() {
	
		parent::addAdmin();
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		if ( FALSE !== strpos( $sUrlPath, '/wp-admin/profile.php' ) ) {
			$this->_sCurrentDisplayMode = 'edit';
		}
		
	}

	//
	public function isCurrentPage() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		if (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/users.php' ) ) || 
			( FALSE !== strpos( $sUrlPath, '/wp-admin/user-edit.php' ) ) || 
			( FALSE !== strpos( $sUrlPath, '/wp-admin/user-new.php' ) ) || 
			( FALSE !== strpos( $sUrlPath, '/wp-admin/profile.php' ) )
		) {
			return TRUE;
		}
		
		return parent::isCurrentPage();
	}
	
	//
	public function attachMenu( $oDoc ) {
		
		$oDoc->find( 'form' )->before(
			Geko_String::fromOb(
				array( 'Geko_Wp_Admin_Menu', 'showMenu' ),
				array( $this->getDetailsMenuHandle() )
			)
		);
		
		$oDoc->find( 'p.submit' )->before(
			Geko_String::fromOb( array( $this, 'outputBeforeSubmitDefault' ) ) . 
			Geko_String::fromOb( array( $this, 'outputBeforeSubmit' ) )
		)->append(
			'<span class="btn_spacer"></span>' . 
			Geko_String::fromOb( array( $this, 'outputAppendSubmitDefault' ) ) .
			Geko_String::fromOb( array( $this, 'outputAppendSubmit' ) )
		);
		
		return $oDoc;
	}
	
	
	
	//// crud methods
	
	//
	public function update() {
		
		$aParams = array();
		
		$oUrl = new Geko_Uri( $_SERVER[ 'HTTP_REFERER' ] );
		$oUrl->unsetVar( 'updated' );
		$sReferer = strval( $oUrl );
		
		$aParams[ 'referer' ] = $sReferer;
		
		if ( FALSE !== strpos( $sReferer, '/wp-admin/user-new.php' ) ) {
			
			if ( !$mRes = $this->doCustomActions( 'add', $aParams ) ) {
				$aParams = $this->doAddAction( $aParams );
			} else {
				$aParams = $mRes;
			}
			
		} elseif (
			( FALSE !== strpos( $sReferer, '/wp-admin/user-edit.php' ) ) || 
			( FALSE !== strpos( $sReferer, '/wp-admin/profile.php' ) )
		) {
			
			if ( !$mRes = $this->doCustomActions( 'edit', $aParams ) ) {
				$aParams = $this->doEditAction( $aParams );
			} else {
				$aParams = $mRes;
			}
			
		}
		
		// re-route
		if ( $aParams ) {
			header( sprintf( 'Location: %s', $aParams[ 'referer' ] ) );
			die();
		}
	}
	
	//
	public function doAddAction( $aParams ) {
		return FALSE;
	}
	
	//
	public function doEditAction( $aParams ) {
		return FALSE;
	}
	
	
}



