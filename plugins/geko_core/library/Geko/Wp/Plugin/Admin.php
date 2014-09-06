<?php

// abstract
class Geko_Wp_Plugin_Admin extends Geko_Wp_Options_Admin
{
	const OPTIONS = 0;
	const MANAGEMENT = 1;
	
	protected static $aPluginDirs = array();
	
	protected $_sAdminType = 'Plugin';
	
	protected $_sVersion;
	protected $_sPluginName;
	protected $_iMenuPlacement;
	
	protected $_sMenuTitleSuffix = '';
	
	protected $_sPluginClass;
	
	
	// constructor
	protected function __construct() {
		
		parent::__construct();
		
		$this->_sPluginClass = Geko_Class::resolveRelatedClass(
			$this, '_PluginAdmin', '', $this->_sPluginClass
		);
		
		$this->_sPluginClass = Geko_Class::isSubclassOf( $this->_sPluginClass, 'Geko_Wp_Plugin' ) ?
			$this->_sPluginClass : ''
		;
		
		$this->_iMenuPlacement = self::OPTIONS;
				
	}
	
	
	//
	public function retrieveInfo() {
		
		if ( FALSE == function_exists( 'get_plugins' ) ) {
			require_once( sprintf( '%swp-admin/includes/plugin.php', ABSPATH ) );
		}
		
		$aPluginInfo = get_plugins( $this->getPluginPath() );
		$aPluginInfo = current( $aPluginInfo );
		
		$this->_sVersion = $aPluginInfo[ 'Version' ];
		$this->_sName = $aPluginInfo[ 'Name' ];
		
		return $this->_sName;
	}
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		$this->_sPrefix = $this->getPluginMethodResult( 'getPrefix' );
		
		return $this;
	}
	
	
	// add_action( 'admin_menu', array( $this, 'attachPage' ) );
	public function attachPage() {
		
		if ( self::MANAGEMENT == $this->_iMenuPlacement ) {
			$sFunction = 'add_management_page';
		} else {
			$this->_sIconId = 'icon-options-general';
			$sFunction = 'add_options_page';
		}
		
		$sFunction(
			$this->getPageTitle(), $this->getMenuTitle(), 8, $this->_sInstanceClass, array( $this, 'outputForm' )
		);
		
	}
	
	
	
	
	//// accessors
	
	//
	public function setMenuPlacement( $_iMenuPlacement = self::OPTIONS ) {
		$this->_iMenuPlacement = $_iMenuPlacement;
		return $this;
	}
	

	//
	public function getPluginMethodResult() {
		
		$aArgs = func_get_args();
		$sMethod = array_shift( $aArgs );
		
		return $this->getPluginMethodResultArray( $sMethod, $aArgs );
	}
	
	//
	public function getPluginMethodResultArray( $sMethod, $aArgs = array() ) {
		
		if ( $this->_sPluginClass ) {
			return call_user_func_array(
				array(
					Geko_Singleton_Abstract::getInstance( $this->_sPluginClass ),
					$sMethod
				),
				$aArgs
			);
		} else {
			return NULL;
		}
	}
	
	//
	public function getPluginUrl() {
		return $this->getPluginMethodResult( 'getPluginUrl' );
	}
	
	// get the plugin url
	public function getUrl() {
		
		if ( self::MANAGEMENT == $this->_iMenuPlacement ) {
			$sScript = 'tools.php';
		} else {
			$sScript = 'options-general.php';
		}
		
		return sprintf( '%s/wp-admin/%s?page=%s', Geko_Wp::getUrl(), $sScript, $this->_sInstanceClass );
	}
	
	//
	public function getPluginPath() {
		
		if ( !self::$aPluginDirs[ $this->_sInstanceClass ] ) {
			
			$oReflect = new ReflectionClass( $this->_sInstanceClass );
			
			$sPluginDir = str_replace( sprintf( '%s%s', ABSPATH, PLUGINDIR ), '', $oReflect->getFileName() );
			
			if ( FALSE !== strpos( $sPluginDir, DIRECTORY_SEPARATOR, 1 ) ) {
				$sPluginDir = substr( $sPluginDir, 0, strpos( $sPluginDir, DIRECTORY_SEPARATOR, 1 ) );
			}
			
			self::$aPluginDirs[ $this->_sInstanceClass ] = $sPluginDir;
		}
		
		return self::$aPluginDirs[ $this->_sInstanceClass ];
	}
		
		
	
}



