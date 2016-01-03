<?php
/*
 * "geko_navigation_management/includes/library/Geko/Wp/NavigationManagement/Language.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * also implements pluginHookActivate() and pluginHookDeactivate()
 */

//
class Geko_Wp_NavigationManagement_Language extends Geko_Wp_Language_Manage
{
	protected $sLangQueryVar = 'nav_lang_id';
	
	//
	protected $_oNavMgmt = NULL;
	protected $_oLangMgm = NULL;
	protected $_oLangRslv = NULL;
	
	protected $_aPlugins = array();
	
	protected $_aSubOptions = array();
	
	
	
	//// methods
	
	
	//
	public function add() {
		
		parent::add();
		
		add_filter( 'admin_geko_wp_nav_new_group', array( $this, 'modNewNavGroup' ) );
		add_filter( 'admin_geko_wp_nav_load_group', array( $this, 'modLoadNavGroup' ), 10, 2 );
		add_filter( 'admin_geko_wp_nav_save_group', array( $this, 'modSaveNavGroup' ), 10, 2 );
		
		add_filter( 'admin_geko_wp_nav_get_params', array( $this, 'modGetParams' ) );
		add_filter( 'admin_geko_wp_nav_configure_page_manager', array( $this, 'modPageManager' ) );
		add_filter( 'admin_geko_wp_nav_redirect', array( $this, 'modRedirect' ) );
		
		$this->_oNavMgmt = Geko_Wp_NavigationManagement::getInstance();
		$this->_oLangMgm = Geko_Wp_Language_Manage::getInstance();
		$this->_oLangRslv = Geko_Wp_Language_Resolver::getInstance();
		
		// activate plugins
		$this->_oNavMgmt->activatePlugin( __CLASS__ );
		foreach ( $this->_aPlugins as $oPlugin ) $oPlugin->init();
		
		return $this;
	}
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( 'admin_geko_wp_nav_left', array( $this, 'editNavigationSelector' ) );
		add_action( 'admin_geko_wp_nav_hidden_fields', array( $this, 'navHiddenFields' ) );
				
		return $this;
	}
	
	
	//
	public function attachPage() { }

	
	//
	public function editNavigationSelector() {
		
		$iLangId = $_REQUEST[ $this->sLangQueryVar ];
		
		$oUrl = new Geko_Uri;
		
		$aLangs = $this->getLanguages();
		$aLinks = array();
		
		foreach ( $aLangs as $oLang ) {
			
			if (
				( $iLangId == $oLang->getId() ) || 
				( !$iLangId && $oLang->getIsDefault() )
			) {
				$aLinks[] = $oLang->getTitle();
			} else {
				$oUrl->setVar( $this->sLangQueryVar, $oLang->getId() );
				$aLinks[] = sprintf(
					'<a href="%s">%s</a>',
					strval( $oUrl ),
					$oLang->getTitle()
				);
			}
		}
		
		?>
		<h3>Language</h3>
		
		<div>
			<?php echo implode( ' | ', $aLinks ); ?>
		</div>
		
		<br />
		<?php
	}
	
	
	
	//// plugin management
	
	//
	public function registerPlugin( $mPlugin ) {
		
		$oPlugin = NULL;
		
		if ( is_string( $mPlugin ) ) {
			
			$sPluginClass = 'Geko_Wp_NavigationManagement_Language_Plugin';
			$sSubClass = sprintf( '%s_%s', $sPluginClass, $mPlugin );
			
			if ( @is_subclass_of( $sSubClass, $sPluginClass ) ) {
				$oPlugin = Geko_Singleton_Abstract::getInstance( $sSubClass )->init();
			} elseif ( @is_subclass_of( $mPlugin, $sPluginClass ) ) {
				$oPlugin = Geko_Singleton_Abstract::getInstance( $mPlugin )->init();
			}
			
		} elseif ( is_object( $oPlugin ) ) {
			$oPlugin = $mPlugin;
		}
		
		if ( $oPlugin ) $this->_aPlugins[] = $oPlugin;
		
		return $this;
	}
	
	//
	public function registerPlugins() {
		
		$aArgs = func_get_args();
		
		foreach ( $aArgs as $mPlugin ) {
			$this->registerPlugin( $mPlugin );
		}
		
		return $this;
	}
	
	
	
	//// helpers (for language codes)
	
	public function getLangCode( $iLangId = NULL ) {
		if ( NULL === $iLangId ) $iLangId = $this->getLangId();
		return parent::getLangCode( $iLangId );
	}
	
	// will return $this->sLangQueryVar request var, otherwise the default language id
	public function getLangId() {
		if ( $_REQUEST[ $this->sLangQueryVar ] ) return $_REQUEST[ $this->sLangQueryVar ];
		return parent::getLangId();
	}

	
	
	//// hooks to main navigation plugin
	
	//
	public function modNewNavGroup( $aNavGroup ) {
		
		$this->getLanguages();		// initialize lang array
		
		$aNewNavGroup = array();
		
		foreach ( self::$aLanguages as $oLang ) {
			$aNewNavGroup[ $oLang->getSlug() ] = $aNavGroup;	
		}
		
		return $aNewNavGroup;
	}
	
	//
	public function modLoadNavGroup( $aNavGroup, $sInvokerClass ) {
		
		$this->getLanguages();		// initialize lang array
		
		if (
			( 'Geko_Wp_NavigationManagement_PluginAdmin' == $sInvokerClass ) && 
			( isset( $aNavGroup[ $this->getLangCode() ] ) )
		) {
			return $aNavGroup[ $this->getLangCode() ];
		} elseif ( 'Geko_Wp_NavigationManagement' == $sInvokerClass ) {
			return $aNavGroup[ $this->_oLangRslv->getCurLang( FALSE ) ];
		}
		
		return $aNavGroup;
	}
	
	//
	public function modSaveNavGroup( $aNewNavGroup, $aOldNavGroup ) {
		
		$this->getLanguages();		// initialize lang array
		
		if ( $this->isDefLang() ) {
			
			$aNewNavGroup = $this->getSiblings( $aNewNavGroup );
			$aReconcile = array();
			
			foreach ( $aNewNavGroup as $sLangCode => $aParams ) {
				
				if ( $this->getDefLangCode() == $sLangCode ) {
					$aReconcile[ $sLangCode ] = $aParams;
				} else {
					$aReconcile[ $sLangCode ] = $this->reconcileSave(
						$aParams,
						$this->flattenParams( $aOldNavGroup[ $sLangCode ] )
					);
				}
			}
			
			$aOldNavGroup = $aReconcile;
						
		} else {
			$aOldNavGroup[ $this->getLangCode() ] = $aNewNavGroup;
		}
		
		return $aOldNavGroup;
	}
	
	//
	public function modGetParams( $aGetParams ) {
		
		if ( $iLangId = $_REQUEST[ $this->sLangQueryVar ] ) {
			$aGetParams[ $this->sLangQueryVar ] = $iLangId;
		}
		
		return $aGetParams;
	}
	
	//
	public function modPageManager( $oPageManager ) {
		
		if ( !$this->isDefLang() ) {
			
			$oPageManager->setJsOption( array(
				'remove_drag_template' => TRUE,
				'remove_outdent' => TRUE,
				'remove_indent' => TRUE,
				'remove_remove' => TRUE,
				'remove_toggle_visibility' => TRUE,
				'remove_trash' => TRUE,
				'remove_sortable' => TRUE,
				'disable_default_params' => TRUE
			) );
			
			foreach ( $this->_aPlugins as $oPlugin ) {
				$oPageManager = $oPlugin->modPageManager( $oPageManager );
			}
			
		}
		
		return $oPageManager;
	}
	
	
	
	
	//
	public function modRedirect( $oUrl ) {
		
		if ( $iLangId = $_REQUEST[ $this->sLangQueryVar ] ) {
			$oUrl->setVar( $this->sLangQueryVar, $iLangId );
		}
		
		return $oUrl;
	}
	
	//
	public function modQueryParams( $aParams ) {
		if ( $this->isDefLang() ) {
			$aParams[ 'lang' ] = $this->getLangCode();
		}
		return $aParams;
	}
	
	//
	public function navHiddenFields() {
		?><input type="hidden" name="<?php echo $this->sLangQueryVar; ?>" value="<?php echo $_REQUEST[ $this->sLangQueryVar ]; ?>" /><?php
	}
	
	//
	public function pluginHookActivate() {
		
		// echo 'Activating... ';
		
		// $aLangs = $this->getLanguages();
		$sDefLangCode = $this->getLangCode( 0 );
		// var_dump( $aLangs );
		
		$aCodeHash = $this->_oNavMgmt->getCodeHash();
		foreach ( $aCodeHash as $sKey ) {
			$aParams = Geko_Json::decode( $this->_oNavMgmt->getOption( $sKey ) );
			$aParamsFmt = $this->getSiblings( $aParams );
			$this->_oNavMgmt->updateOption( $sKey, Geko_Json::encode( $aParamsFmt ) );
		}
	}
	
	//
	public function pluginHookDeactivate() {
		
		// echo 'De-Activating... ';
		$this->init();
		$sDefLangCode = $this->getLangCode( 0 );
		
		$aCodeHash = $this->_oNavMgmt->getCodeHash();
		foreach ( $aCodeHash as $sKey ) {
			$aParams = Geko_Json::decode( $this->_oNavMgmt->getOption( $sKey ) );
			if ( isset( $aParams[ $sDefLangCode ] ) ) {
				$aParamsFmt = $aParams[ $sDefLangCode ];
				$this->_oNavMgmt->updateOption( $sKey, Geko_Json::encode( $aParamsFmt ) );
			}
		}
	}
	
	
	
	//
	public function getSiblings( $aParams ) {
		
		$sDefLangCode = $this->getLangCode( 0 );
		
		
		$aFlat = $this->flattenParams( $aParams );
		$aSibParams = array( 'add_siblings_field' => TRUE );
		
		// loop through plugins
		foreach ( $this->_aPlugins as $oPlugin ) {
			$aSibParams = $oPlugin->getSiblingQueryCond( $aSibParams, $aFlat );
		}
		
		
		if ( is_array( $aSibParams[ 'filter' ] ) ) {
			
			$aItems = new Geko_Wp_Language_Member_Query( $aSibParams, FALSE );
			$aSibsFmt = array();
			
			// group by type/item id/lang code => sibling id
			foreach ( $aItems as $oItem ) {
				if ( $aSibs = $oItem->getTheSiblings() ) {
					foreach ( $aSibs as $oSib ) {
						$aSibsFmt[ $oItem->getType() ][ $oItem->getObjId() ][ $oSib->getLangCode() ] = $oSib->getObjId();
					}
				}
			}
			
			//
			$aParamsFmt = array();
			$aLangs = $this->getLanguages();
			foreach ( $aLangs as $oLang ) {
				if ( $sDefLangCode == $oLang->getSlug() ) {
					$aParamsFmt[ $oLang->getSlug() ] = $aParams;				
				} else {
					$aParamsFmt[ $oLang->getSlug() ] = $this->rebuildParams( $aParams, $aSibsFmt, $oLang->getSlug() );
				}
			}
			
			// print_r( $aSibsFmt );
			// print_r( $aParamsFmt );
			// die();
			
			return $aParamsFmt;
			
		} else {
			return $aParams;
		}
		
	}
	
	
	//// helpers
	
	//
	public function rebuildParams( $aParams, $aSibsFmt, $sLang ) {
		
		if ( is_array( $aParams ) ) {
		
			foreach ( $aParams as $i => $aParam ) {
				
				// loop through plugins
				foreach ( $this->_aPlugins as $oPlugin ) {
					$aParams[ $i ] = $oPlugin->rebuildParams( $aParams[ $i ], $aSibsFmt, $sLang );
				}
				
				if ( $aParam[ 'pages' ] ) {
					$aParams[ $i ][ 'pages' ] = $this->rebuildParams( $aParam[ 'pages' ], $aSibsFmt, $sLang );
				}
			}
		}
		
		return $aParams;
	}
	
	//
	public function flattenParams( $aParams ) {
		
		$aFlat = array();
		
		if ( is_array( $aParams ) ) {
			
			foreach ( $aParams as $i => $aParam ) {
				unset( $aParam[ 'pages' ] );
				$aFlat[] = $aParam;	
				if ( $aParams[ $i ][ 'pages' ] ) {
					$aFlat = array_merge( $aFlat, $this->flattenParams( $aParams[ $i ][ 'pages' ] ) );
				}
			}		
		}
		
		return $aFlat;
	}
	
	//
	public function reconcileSave( $aParams, $aFlat ) {
		
		if ( is_array( $aParams ) ) {
		
			foreach ( $aParams as $i => $aParam ) {
				
				if (
					( isset( $aParam[ 'item_idx' ] ) ) && 
					( $aOld = $aFlat[ $aParam[ 'item_idx' ] ] )
				) {
					if ( $aOld[ 'title' ] ) $aParams[ $i ][ 'title' ] = $aOld[ 'title' ];
					if ( $aOld[ 'label' ] ) $aParams[ $i ][ 'label' ] = $aOld[ 'label' ];

					// loop through plugins
					foreach ( $this->_aPlugins as $oPlugin ) {
						$aParams[ $i ] = $oPlugin->reconcileSave( $aParams[ $i ], $aOld );
					}
				}
				
				if ( $aParam[ 'pages' ] ) {
					$aParams[ $i ][ 'pages' ] = $this->reconcileSave( $aParam[ 'pages' ], $aFlat );
				}
			}
		}
		
		return $aParams;
	}
	
	
	//// output
	
	//
	public function render() {
		
		$aNavSpecific = array();
		
		// loop through plugins
		foreach ( $this->_aPlugins as $oPlugin ) {
			if ( count( $aNavSpecific = $oPlugin->getNavItems() ) > 0 ) {
				break;
			}
		}
		
		// loop through languages
		
		$aNav = array();
		$aLangs = $this->getLanguages();
				
		foreach ( $aLangs as $oLang ) {
			
			$iLangId = $oLang->getId();
			
			if ( $aNavItem = $aNavSpecific[ $iLangId ] ) {
				
				// nav item of a specific type
				$aNavItem[ 'label' ] = $oLang->getTitle();
				$aNav[] = $aNavItem;
			
			} else {
				
				$oUrl = new Geko_Uri();
				
				$oUrl = $this->_oLangRslv->resolveUrl( $oUrl, $iLangId );
				
				if (
					( $oLang->getIsDefault() ) || 
					( 1 === $this->_oLangMgm->getLangDomainCount( $oUrl->getHost() ) )
				) {
					$oUrl->unsetVar( 'lang' );
				} else {
					$oUrl->setVar( 'lang', $oLang->getSlug() );
				}
				
				// generic nav item (Geko_Navigation_Page_Uri)
				$aNav[] = array(
					'type' => 'Geko_Navigation_Page_Uri',
					'uri' => strval( $oUrl ),
					'label' => $oLang->getTitle(),
					'strict_match' => TRUE
				);
			}
		}
		
		//////
		
		echo Geko_Navigation_Renderer::menu( new Zend_Navigation( $aNav ), $aParams );
			
	}
	
	
}


