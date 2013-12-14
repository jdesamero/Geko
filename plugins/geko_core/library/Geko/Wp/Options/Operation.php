<?php

// base class for WP Admin pages
class Geko_Wp_Options_Operation extends Geko_Wp_Options
{
	
	protected $_sName = '';
	protected $_sTitle = '';
	protected $_sMenuTitle = '';
	protected $_sPageTitle = '';
	
	protected $_sSubMenuPage = '';
	protected $_sManagementCapability = 'manage_options';
	
	protected $_sIconId = 'icon-tools';
	protected $_sSubAction = 'admin';
	
	protected $_aOperations = array();
	
	
	// constructor
	protected function __construct() {
		
		parent::__construct();
		
		$this->_sName = $this->retrieveInfo();
		$this->_sMenuTitle = Geko_String::coalesce( $this->_sMenuTitle, $this->_sTitle );
		$this->_sPageTitle = Geko_String::coalesce( $this->_sPageTitle, $this->_sTitle );
		
	}

	
	//// init
	
	// implement by subclass
	public function retrieveInfo() {
		return '';
	}
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( 'admin_init', array( $this, 'doActions' ) );
		
		return $this;
	}
	
	//
	public function attachPage() {
		if ( $this->_sSubMenuPage ) {
			add_submenu_page( $this->_sSubMenuPage, $this->_sPageTitle, $this->_sMenuTitle, $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'outputForm' ) );
		} else {
			add_menu_page( $this->_sPageTitle, $this->_sMenuTitle, $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'outputForm' ) );
		}
	}
	
	
	
	//// accessors
	
	
	//
	public function setName( $sName ) {
		$this->_sName = $sName;
		return $this;
	}
	
	//
	public function setTitle( $sTitle ) {
		$this->_sTitle = $sTitle;
		return $this;
	}
	
	//
	public function setMenuTitle( $sMenuTitle ) {
		$this->_sMenuTitle = $sMenuTitle;
		return $this;
	}
	
	//
	public function setPageTitle( $sPageTitle ) {
		$this->_sPageTitle = $sPageTitle;
		return $this;
	}
	
	
	
	
	//
	public function getMenuTitle() {
		return $this->_sMenuTitle;
	}
	
	//
	public function getPageTitle() {
		return $this->_sPageTitle;
	}
	
	//
	public function getSanitizedTitle() {
		return $this->sanitize( $this->_sTitle );
	}
	
	//
	public function getTitle() {
		return $this->_sTitle;
	}
	
	//
	public function getUrl() {
		return '';
	}
	
	
	
	
	
	
	
	
	//// front-end display methods
	
	// called to output form
	public function outputForm() {
		
		// display
		
		$this->preWrapDiv();
				
		?>
			<div class="wrap">
				
				<div class="icon32" id="<?php echo $this->_sIconId; ?>"><br/></div>
				
				<?php if ( Geko_Wp_Admin_Menu::inTabGroup( $this->_sInstanceClass ) ):
					Geko_Wp_Admin_Menu::showNavTabs( $this->_sInstanceClass );
				else: ?>
					<h2><?php echo $this->_sTitle; ?></h2>
				<?php endif; ?>
				
				<?php
				
				$this->notificationMessages();
				
				$this->preOptionsFormDiv();
				
				foreach ( $this->_aOperations as $sKey => $aOperation ) {
					$aOperation[ 'operation' ] = $sKey;
					$this->outputInnerForm( $aOperation );
				}
				
				$this->postOptionsFormDiv();
				
				?>
				
			</div>		
		<?php
		
		$this->postWrapDiv();
		
		return $this;
	}
	
	//
	protected function outputInnerForm( $aOperation ) {
		
		$oUrl = Geko_Uri::getGlobal();
		
		$sOperation = $aOperation[ 'operation' ];
		$sHumanTitle = Geko_Inflector::humanize( $sOperation );
		
		$sSectionTitle = ( $aOperation[ 'section_title' ] ) ? $aOperation[ 'section_title' ] : $sHumanTitle ;
		$sButtonTitle = ( $aOperation[ 'button_title' ] ) ? $aOperation[ 'button_title' ] : $sHumanTitle ;
		$aExtraButtons = $aOperation[ 'extra_buttons' ];
		
		$sFormFieldMethod = 'formFields' . Geko_Inflector::camelize( $sOperation );
		
		$sNonceField = $this->_sInstanceClass . $sOperation;
		
		?>
		<h3><?php echo $sSectionTitle; ?></h3>
		
		<form action="<?php echo strval( $oUrl ); ?>" method="post" enctype="multipart/form-data">
			
			<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( $sNonceField ); ?>
			
			<input type="hidden" name="__operation" value="<?php echo $sOperation; ?>" />
			
			<?php if ( method_exists( $this, $sFormFieldMethod ) ) $this->$sFormFieldMethod(); ?>
			
			<p class="submit">
				<input type="submit" value="<?php echo $sButtonTitle; ?>" class="button-primary" />
				<?php foreach ( $aExtraButtons as $sKey => $mExtraBtn ):
					
					if ( is_string( $mExtraBtn ) ) {
						$sKey = $mExtraBtn;
						$aExtraBtn = array();
					} else {
						$aExtraBtn = $mExtraBtn;
					}
					
					$sExtraBtnId = ( $aExtraBtn[ 'id' ] ) ? $aExtraBtn[ 'id' ] : $sKey ;
					$sExtraBtnTitle = ( $aExtraBtn[ 'title' ] ) ? $aExtraBtn[ 'title' ] : Geko_Inflector::humanize( $sExtraBtnId ) ;
					$sExtraBtnClass = 'button-primary';
					if ( $aExtraBtn[ 'class' ] ) $sExtraBtnClass = $sExtraBtnClass . ' ' . trim( $aExtraBtn[ 'class' ] );
					
					?>
					<input type="button" id="<?php echo $sExtraBtnId; ?>" value="<?php echo $sExtraBtnTitle; ?>" class="<?php echo $sExtraBtnClass; ?>" />
				<?php endforeach; ?>
			</p>
			
		</form>
		<?php
	}
	
	
	// hooks to the form
	protected function preWrapDiv() { }
	protected function preOptionsFormDiv() { }
	protected function postOptionsFormDiv() { }
	protected function postWrapDiv() { }
	
	
	
	//// do actions
	
	//
	public function doActions() {
		
		@session_start();
		
		$sOperation = $_REQUEST[ '__operation' ];
		$sActionMethod = 'doAction' . Geko_Inflector::camelize( $sOperation );
		
		$sNonceField = $this->_sInstanceClass . $sOperation;
		
		if (
			( $sOperation ) && 
			( $this->_sInstanceClass == $_REQUEST[ 'page' ] ) && 
			( method_exists( $this, $sActionMethod ) ) &&
			( check_admin_referer( $sNonceField ) )
		) {
			
			$this->$sActionMethod( $this->_aOperations[ $sOperation ] );

			header( 'Location: ' . $_REQUEST[ '_wp_http_referer' ] );
			die();
		}
		
	}
	
	
	
}


