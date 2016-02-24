<?php
/*
 * "geko_core/library/Geko/Wp/Bootstrap.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Bootstrap extends Geko_Bootstrap
{
	
	
	//// properties
	
	protected $_aPrefixes = array( 'Gloc_', 'Geko_Wp_', 'Geko_' );
	
	protected $_sTemplateOverride = NULL;
	
	
	
	
	
	//// methods
	
	//
	public function __construct() {
		
		parent::__construct();
		
		$this
			
			->mergeDeps( array(
				
				'role.mng' => array( 'role.types' ),
				'form.mng' => array( 'lang.rslv' ),
				'lang.rslv' => array( 'lang.mng' ),
				'navmng.lang' => array( 'lang.rslv' ),
				'pin.log.mng' => array( 'pin.mng' )
				
			) )
			
			->mergeConfig( array(
				
				'path' => TRUE,
				'geo' => TRUE,
				'currency' => TRUE,
				'load_ext' => TRUE,
				'db' => TRUE,
				'consts' => TRUE,
				'setup' => TRUE,
				'hooks' => TRUE,
				'gf' => TRUE,
				'opts.reg' => TRUE,
				'navmng' => TRUE
				
			) )
			
			->mergeAbbrMap( array(
				
				'cat' => 'Category',
				'cont' => 'Contact',
				'custhks' => 'CustomHooks',
				'defcat' => 'DefaultCategory',
				'dlhis' => 'DownloadHistory',
				'expdate' => 'ExpirationDate',
				'emsg' => 'EmailMessage',
				'enum' => 'Enumeration',
				'lang' => 'Language',
				'loc' => 'Location',
				'mng' => 'Manage',
				'navmng' => 'NavigationManagement',
				'op' => 'Operation',
				'opts' => 'Options',
				'pnt' => 'Point',
				'posttmpl' => 'PostTemplate',
				'reg' => 'Registry',
				'rslv' => 'Resolver',
				'tmpl' => 'Template',
				'wc' => 'WooCommerce'
				
			) )
			
		;
		
	}
	
	
	//// accessors
	
	
	//
	public function setTemplateOverride( $sTemplateOverride ) {
		$this->_sTemplateOverride = $sTemplateOverride;
		return $this;
	}
	
	
	
	//// start
	
	//
	public function start() {
		
		parent::start();
		
		add_filter( 'template_include', array( $this, 'templateInclude' ), 9999 );
		
	}
	
	
	
	//// hard-coded components
	
	
	// file path/url configuration
	// independent
	public function compPath( $aArgs ) {
	
		// set-up path (url-to-path/path-to-url) conversion
		Geko_String_Path::setRoots( Geko_Wp::getUrl(), ABSPATH );
		
		
		// register global urls to services
		
		Geko_Uri::setUrl( array(
			'wp_admin' => sprintf( '%s/wp-admin/admin.php', Geko_Wp::getUrl() ),
			'wp_login' => sprintf( '%s/wp-login.php', Geko_Wp::getUrl() ),
			'wp_user_edit' => sprintf( '%s/wp-admin/user-edit.php', Geko_Wp::getUrl() )
		) );
		
		
		parent::compPath( $aArgs ); 
		
		
		// image thumbnailer
		if ( defined( 'GEKO_IMAGE_THUMB_CACHE_DIR' ) ) {
			Geko_Image_Thumb::setCacheDir( GEKO_IMAGE_THUMB_CACHE_DIR );
		}
		
		// scss pre-processor
		if ( defined( 'GEKO_SCSS_CACHE_DIR' ) ) {
			Geko_Scss::setCacheDir( GEKO_SCSS_CACHE_DIR );
		}
		
	}
	
	
	// the external files loader
	// independent
	public function compLoadExt( $aArgs ) {
		
		$oThis = $this;
		
		
		//// backstab model fields functionality
		
		$aModelFields = array();
		$aDeferFields = array();
		
		// if "data-model" was provided, then track it
		Geko_Hooks::addFilter( 'Geko_Loader_ExternalFiles::registerFromXmlConfigFile::params', function( $aParams, $sId, $sType, $oItem ) use ( &$aModelFields, &$aDeferFields ) {
				
			if ( $sDataModel = strval( $oItem[ 'data-model' ] ) ) {
				
				// start with NULL values, we'll populate this later
				$aModelFields[ $sDataModel ] = NULL;
			}
			
			if ( $oItem[ 'defer' ] ) {
				$aDeferFields[] = $sId;
			}
			
			return $aParams;
		} );
		
		
		add_action( 'wp_print_scripts', function( $oLoadExt ) use ( &$aModelFields, $oThis ) {
			
			$aBackstabFields = array();
			
			foreach ( $aModelFields as $sKey => $null ) {
				
				$sCompKey = sprintf( '%s.mng', $sKey );
				
				// only get primary table info if component was init'ed
				if (
					( $oMng = Geko_App::get( $sCompKey ) ) && 
					( $oMng->getCalledInit() )
				) {
					$aBackstabFields[ $sKey ] = $oMng->getPrimaryTable();
				}
				
			}
			
			if ( count( $aBackstabFields ) > 0 ): ?>
				<script type="text/javascript">
					( function() {
					
						if ( !this.Backstab ) this.Backstab = {};
						
						var Backstab = this.Backstab;
						
						Backstab.ModelFields = <?php echo Geko_Json::encode( $aBackstabFields ); ?>;
						
					} ).call( this );
				</script>
			<?php endif;
			
		} );
		
		
		add_filter( 'script_loader_tag', function( $sTag, $sHandle, $sSrc ) use ( &$aDeferFields ) {
			
			if ( in_array( $sHandle, $aDeferFields ) ) {
				$sTag = str_replace( '<script', '<script defer="defer"', $sTag );
			}
			
			return $sTag;
			
		}, 10, 3 );
		
		
		
		// register external files (js/css)
		$aPlh = array();
		
		if ( defined( 'GEKO_CORE_ROOT' ) ) {
			$aPlh[ 'geko_core_root' ] = GEKO_CORE_ROOT;
		}
		
		if ( defined( 'GEKO_CORE_URI' ) ) {
			$aPlh[ 'geko_core_uri' ] = GEKO_CORE_URI;
		}
		
		
		// set placeholders, if any
		if ( count( $aPlh ) > 0 ) {
			Geko_Wp::setStandardPlaceholders( $aPlh );
		}
		
		
		if ( defined( 'GEKO_REGISTER_XML' ) ) {
			Geko_Wp::registerExternalFiles( GEKO_REGISTER_XML );
		}
		
		
		// external files
		$sExternalFiles = sprintf( '%s/etc/register.xml', TEMPLATEPATH );
		
		if ( is_file( $sExternalFiles ) ) {
			Geko_Wp::registerExternalFiles( $sExternalFiles );
		}
		
	}
	
	
	
	
	// database connection
	// independent
	public function compDb( $aArgs ) {
		
		if ( is_string( $aArgs[ 0 ] ) ) {
			
			$oDb = Geko_Db::factory( $aArgs[ 0 ], $aArgs[ 1 ] );
			
		} else {
			
			$oDb = Geko_Db::factory( 'Wp_Db_Adapter', array(
				'adapterNamespace' => 'Geko'
			) );
			
		}
		
		$this->set( 'db', $oDb );
	}

	
	
	// session handler
	public function compSess( $aArgs ) {
		
		@session_start();
		
	}
	
	
	// constants
	public function compConsts( $aArgs ) {
		
		define( 'ABS_WP_URL_ROOT', str_replace( sprintf( 'http://%s', $_SERVER[ 'SERVER_NAME' ] ), '', Geko_Wp::getUrl() ) );
		
	}


	// initial setup stuff
	public function compSetup( $aArgs ) {
		
		//// init meta key system
		
		Geko_Wp_Options_MetaKey::init();
		
		
		
		//// force https
		
		if ( $aArgs[ 'force_https' ] ) {
			Geko_Uri::forceHttps();	
		}
		
		
		
		//// for Geko_Scss
		
		Geko_Hooks::addFilter( 'Geko_Scss::__construct', function( $aParams ) {
			
			if (
				( $iDirId = $aParams[ 'dirid' ] ) && 
				( !$aParams[ 'd' ] )
			) {
				unset( $aParams[ 'dirid' ] );
				$aParams[ 'd' ] = Geko_Wp_Options_MetaKey::getKey( $iDirId );
			}
			
			return $aParams;
		} );
		
		Geko_Hooks::addFilter( 'Geko_Scss::buildCssUrl', function( $oUrl ) {
			
			if ( $sDir = $oUrl->getVar( 'd' ) ) {
				
				$iDirId = Geko_Wp_Options_MetaKey::getId( $sDir );
				
				$oUrl
					->unsetVar( 'd' )
					->setVar( 'dirid', $iDirId )
				;
			}
			
			return $oUrl;
		} );		
		

		//// for Geko_Image_Thumb
		
		Geko_Hooks::addFilter( 'Geko_Image_Thumb::__construct', function( $aParams ) {
			
			if (
				( $iDirId = $aParams[ 'dirid' ] ) && 
				( $sFileName = $aParams[ 'fn' ] ) && 
				( !$aParams[ 'src' ] )
			) {
				
				unset( $aParams[ 'dirid' ] );
				unset( $aParams[ 'fn' ] );
				
				$aParams[ 'src' ] = sprintf( '%s/%s', Geko_Wp_Options_MetaKey::getKey( $iDirId ), $sFileName );
			}
			
			return $aParams;
		} );
		
		Geko_Hooks::addFilter( 'Geko_Image_Thumb::buildThumbUrl', function( $oUrl ) {
			
			if ( $sFull = $oUrl->getVar( 'src' ) ) {
				
				$sFileName = basename( $sFull );
				$sDirName = dirname( $sFull );
				
				$iDirId = Geko_Wp_Options_MetaKey::getId( $sDirName );
				
				$oUrl
					->unsetVar( 'src' )
					->setVar( 'dirid', $iDirId )
					->setVar( 'fn', $sFileName )
				;
			}
			
			return $oUrl;
		} );		

		
		
		// form transformation hooks
		
		Geko_PhpQuery_FormTransform::registerPlugin( 'Geko_PhpQuery_FormTransform_Plugin_File' );
		Geko_PhpQuery_FormTransform::registerPlugin( 'Geko_PhpQuery_FormTransform_Plugin_RowTemplate' );
		
		
		
		
		// misc
		if ( isset( $aArgs[ 'use_is_home' ] ) ) {
			Geko_Wp::setUseIsHome( $aArgs[ 'use_is_home' ] );
		}
		
	}

	
	
	
	// hooks
	public function compHooks( $aArgs ) {
		
		// plugins for Geko_Wp_Admin_Hooks
		
		$aPlugins = $aArgs[ 'plugins' ];
		if ( !is_array( $aPlugins ) ) {
			$aPlugins = array();
		}
		
		// adds the hooks: admin_head, admin_body_header, admin_body_footer
		// adds the filters: admin_page_source
		Geko_Wp_Admin_Hooks::init( $aPlugins );
		
		if ( $aArgs[ 'fix_https' ] ) {
			Geko_Wp_Hooks::setFixHttps();
		}
		
		Geko_Wp_Hooks::init();
		
		Geko_Wp_Hooks::attachGekoHookActions(
			'theme_head_late',
			'theme_body_header',
			'theme_body_footer',
			'admin_head',
			'admin_body_header',
			'admin_body_footer'
		);
		
		Geko_Wp_Hooks::attachGekoHookFilters(
			'theme_page_source',
			'admin_page_source'
		);
		
				
	}
	
	
	// for ACF Pro
	public function compGf( $aArgs ) {
		
		if (
			( isset( $_GET[ 'page' ] ) ) && 
			( 'gf_activation' == $_GET[ 'page' ] )
		) {
			
			global $wp_filter;
			
			// remove existing template redirect
			if ( is_array( $wp_filter ) ) {

				foreach ( $wp_filter[ 'wp' ] as $i => $aFilters ) {
					foreach ( $aFilters as $sKey => $cb ) {
						if ( 'GFUser::maybe_activate_user' == $sKey ) {
							
							// name of template is hard-coded for now (activate.php)
							$this->setTemplateOverride( sprintf( '%s/activate.php', TEMPLATEPATH ) );
							
							// unset
							unset( $wp_filter[ 'wp' ][ $i ][ $sKey ] );
							break 2;
						}						
					}
				}
			}
					
		}
		
	}
	
	
	
	
	//// class-based components
	
	
	// role type
	public function compRole_Types( $aArgs ) {
		
		$oRoleTypes = Geko_Wp_Role_Types::getInstance();
		
		$oRoleTypeUser = Geko_Wp_User_RoleType::getInstance();
		
		if ( $aArgs[ 'user' ][ 'disable_additional_caps' ] ) {
			$oRoleTypeUser->setDisableUserAdditionalCaps( TRUE );
		}
		
		$oRoleTypes->register( $oRoleTypeUser );
		
		$this->set( 'role.types', $oRoleTypes );
	}
	
	
	//
	public function compCat_Meta( $aArgs ) {
		
		if ( isset( $aArgs[ 'use_term_tx' ] ) ) {
			Geko_Wp_Category_Meta::setUseTermTaxonomy( $aArgs[ 'use_term_tx' ] );
		}
		
		$this->handleComponent( 'cat.meta', NULL, $aArgs );
	}
	
	
	// navigation management language
	public function compNavmng_Lang( $aArgs ) {
	
		if ( class_exists( 'Geko_Wp_NavigationManagement_Language' ) ) {
			
			$oNavmngLang = Geko_Wp_NavigationManagement_Language::getInstance();
			
			$aPlugins = array( 'Uri', 'Page', 'Category', 'CustomType', 'Post', 'Role' );
			
			call_user_func_array( array( $oNavmngLang, 'registerPlugins' ), $aPlugins );
			
			$this->set( 'navmng.lang', $oNavmngLang );
		}
		
	}
	
	
	// language manage
	public function compLang_Mng( $aArgs ) {
		
		// hard-code for now
		$aPlugins = array( 'Post', 'Category' );
		
		if (
			( $this->_aConfig[ 'navmng.lang' ] ) && 
			( class_exists( 'Geko_Wp_NavigationManagement_Language' ) )
		) {
			$aPlugins[] = 'Geko_Wp_NavigationManagement_Language';
		}
		
		$oLangMng = Geko_Wp_Language_Manage::getInstance();
		
		$oLangMng->init();
		
		call_user_func_array( array( $oLangMng, 'registerPlugins' ), $aPlugins );
		
		$this->set( 'lang.mng', $oLangMng );
	}
	
	
	// logged-in user
	public function compUser() {
		
		global $user_ID;
		
		if ( $user_ID ) {
			
			$oUser = $this->newUser( $user_ID );
			
			// ensure user has been activated, ie: geko_activation_key is not set
			
			if ( $oUser->getIsActivated() ) {
				
				$this->set( 'user', $oUser );
				
			} else {
				
				wp_logout();
				unset( $user_ID );
			}
			
		}
		
	}
	
	
	
	//// helpers
	
	//
	public function resolveTemplateSuffix( $sTemplate ) {

		// handle templates in sub-directories
		
		$sClassPath = str_replace( TEMPLATEPATH, '', $sTemplate );
		$aParts = pathinfo( $sClassPath );
		
		$aSecs = array();
		
		if ( $sDir = trim( $aParts[ 'dirname' ], '/' ) ) {
			$aSecs = explode( '/', $sDir );
		}
		
		$aSecs[] = $aParts[ 'filename' ];
		
		$sCurTmplSuffix = '';
		
		foreach ( $aSecs as $sSec ) {
			
			if ( $sCurTmplSuffix ) {
				$sCurTmplSuffix .= '_';
			}
			
			$sCurTmplSuffix .= Geko_Inflector::camelize( str_replace( '-', '_', $sSec ) );
		}
		
		
		/* /
		// HACK HACK HACK, change dashes to underscore
		$sCurTmplSuffix = pathinfo( $sTemplate, PATHINFO_FILENAME );
		$sCurTmplSuffix = Geko_Inflector::camelize( str_replace( '-', '_', $sCurTmplSuffix ) );
		// echo $sCurTmplSuffix;
		/* */
		
		return $sCurTmplSuffix;
	}
	
	
	//
	public function templateInclude( $sTemplate ) {
		
		
		// override template, if there's one
		if ( $this->_sTemplateOverride ) {
			$sTemplate = $this->_sTemplateOverride;
		}
		
		
		//
		$sCurTmplSuffix = $this->resolveTemplateSuffix( $sTemplate );
		
		//
		Geko_Debug::out( $sTemplate, __METHOD__ );
		
		$aClassFileMapping = array(
			'Main' => sprintf( '%s/layout_main.php', TEMPLATEPATH ),
			'Widgets' => sprintf( '%s/layout_widgets.php', TEMPLATEPATH ),
			$sCurTmplSuffix => $sTemplate
		);
		
		$oResolve = new Geko_Wp_Resolver();
		
		// init
		$oResolve = $this->modifyResolverInit( $oResolve );		
		$oResolve->setClassFileMapping( $aClassFileMapping );
		
		// before add path
		$oResolve = $this->modifyResolverBeforeAddPath( $oResolve );
		$oResolve->addPath( 'default', new Geko_Wp_Resolver_Path_Default() );
		
		// before run
		$oResolve = $this->modifyResolverBeforeRun( $oResolve );		
		$oResolve->run();
		
		
		// initialize the various layout classes
		// layout classes are an instance of Geko_Layout
		
		$aSuffixes = $oResolve->getClassSuffixes();
		foreach ( $aSuffixes as $sSuffix ) {
			Geko_Singleton_Abstract::getInstance( $oResolve->getClass( $sSuffix ) )->init();
		}
		
		
		
		// render the final layout
		// the layout renderer class is an instance of Geko_Layout_Renderer
		Geko_Singleton_Abstract::getInstance( $oResolve->getClass( 'Renderer' ) )->render();
		
		
		
		// let's have wp-includes/template-loader.php load an empty file instead
		$sEmptyTemplate = sprintf( '%s/includes/empty.inc.php', TEMPLATEPATH );
		
		return $sEmptyTemplate;
	}
	
	
	
	// hook methods
	
	//
	public function modifyClassFileMapping( $aClassFileMapping ) {
		return $aClassFileMapping;
	}

	//
	public function modifyResolverInit( $oResolve ) {
		return $oResolve;
	}
	
	//
	public function modifyResolverBeforeAddPath( $oResolve ) {
		return $oResolve;
	}
	
	//
	public function modifyResolverBeforeRun( $oResolve ) {
		return $oResolve;
	}
	
	
}


