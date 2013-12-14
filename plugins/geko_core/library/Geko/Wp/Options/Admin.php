<?php

// base class for WP Admin pages
class Geko_Wp_Options_Admin extends Geko_Wp_Options
{
	protected $_bShowUpdateMsg = TRUE;
	
	protected $_sName = '';
	protected $_sTitle = '';
	protected $_sMenuTitle = '';
	protected $_sPageTitle = '';
	protected $_sVersion = '';
	
	protected $_sMenuTitleSuffix = 'Options';
	protected $_sPageTitleSuffix = 'Options';
	
	protected $_sAdminType = '';
	protected $_sIconId = 'icon-tools';
	protected $_sSubAction = 'tools';
	
	
	
	
	// constructor
	protected function __construct() {
		
		parent::__construct();
		
		$this->_sName = $this->retrieveInfo();
		$this->_sTitle = $this->addTitleSuffix( $this->_sName );
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
		
		add_action( 'admin_menu', array( $this, 'attachPage' ) );
		
		return $this;
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
	public function getOption( $sKey ) {
		return get_option( $this->getPrefixWithSep() . $sKey );
	}
	
	//
	public function updateOption( $sKey, $mValue ) {
		return update_option( $this->getPrefixWithSep() . $sKey, $mValue );
	}
	
	//
	public function deleteOption( $sKey ) {
		return delete_option( $this->getPrefixWithSep() . $sKey );
	}
	
	
	//
	public function setShowUpdateMsg( $bShowUpdateMsg ) {
		
		$this->_bShowUpdateMsg = $bShowUpdateMsg;
		return $this;
	}
	
	
	
	//
	public function getMenuTitle() {
		return $this->_sMenuTitle . ( $this->_sMenuTitleSuffix ? ' ' . $this->_sMenuTitleSuffix : '' );
	}
	
	//
	public function getPageTitle() {
		return $this->_sPageTitle . ( $this->_sPageTitleSuffix ? ' ' . $this->_sPageTitleSuffix : '' );
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
	public function getVersion() {
		return $this->_sVersion;
	}
	
	//
	public function getUrl() {
		return '';
	}
	
	// get the type, Theme, Plugin, Widget, etc...
	public function getAdminType() {
		return $this->_sAdminType;
	}
	
	// append <type> to title if not already there
	public function addTitleSuffix( $sTitle ) {
		if ( FALSE === strpos( strtolower( $sTitle ), strtolower( $this->_sAdminType ) ) ) {
			$sTitle .= ' ' . $this->_sAdminType;
		}
		return $sTitle;
	}
	
	
	
	
	//
	public function getStoredOptions() {
		
		// get all options and reduce to the ones matching the theme prefix
		$aOptionsCache = wp_cache_get( 'alloptions', 'options' );
		$aOptions = array();
		
		foreach ( $aOptionsCache as $sOptionKey => $mOptionVal ) {
			if ( 0 === strpos( $sOptionKey, $this->getPrefixWithSep() ) ) {
				$aOptions[$sOptionKey] = get_option( $sOptionKey );
			}
		}
		
		return $aOptions;
	}

	
	
	
	//// front-end display methods
	
	// called to output form
	public function outputForm() {
		
		// display
		
		$this->preWrapDiv();
		
		$sVersion = $this->getVersion();
		
		?>
			<div class="wrap">
				
				<?php if ( $this->_bShowUpdateMsg ): if ( $_REQUEST[ 'updated' ] ): ?>
					<div id="message" class="updated fade"><p><strong><?php echo $this->getTitle(); ?> settings saved.</strong></p></div>
				<?php endif; endif; ?>
				
				<div class="icon32" id="<?php echo $this->_sIconId; ?>"><br/></div>
				
				<h2><?php echo $this->getTitle(); ?> Options</h2>
				
				<?php if ( $sVersion ): ?>
					<p><?php echo $this->getAdminType(); ?> Version: <strong><?php echo $sVersion; ?></strong></p>
				<?php endif; ?>
				
				<?php
				
				$this->preOptionsFormDiv();
				$this->outputInnerForm();
				$this->postOptionsFormDiv();
				
				?>
				
			</div>		
		<?php
		
		$this->postWrapDiv();
		
		return $this;
	}
	
	//
	protected function outputInnerForm() {
		
		?>
		<form id="<?php echo $this->getPrefix(); ?>-options-form" name="<?php echo $this->getPrefix(); ?>-options-form" method="post" action="options.php">
			
			<?php wp_nonce_field( 'update-options' );Ê?>
			
			<?php echo $this->inject(); ?>
			
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="<?php echo implode( ',', $this->getOptionList() ); ?>" />
			
			<p class="submit">
				<input type="submit" name="Submit" value="Update Options" class="button-primary" />
			</p>
			
		</form>		
		<?php
	}
	
	
	// hooks to the form
	protected function preWrapDiv() { }
	protected function preOptionsFormDiv() { }
	protected function postOptionsFormDiv() { }
	protected function postWrapDiv() { }
	
	
	
}


