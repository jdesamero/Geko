<?php

//
class Geko_Wp_Bootstrap extends Geko_Bootstrap
{
	
	
	//// properties
	
	protected $_aPrefixes = array( 'Gloc_', 'Geko_Wp_', 'Geko_' );
	
	
	
	
	
	//// methods
	
	//
	public function __construct() {
		
		parent::__construct();
		
		$this
			
			->mergeDeps( array(
				
				'db' => NULL,
				'sess' => NULL,
				'consts' => NULL,
				'setup' => NULL,
				'hooks' => NULL,
				
				'role.types' => NULL,
				'role.mng' => array( 'role.types' ),
				
				'form.mng' => array( 'lang.rslv' ),
				
				'emsg.mng' => NULL,

				'cont.mng' => NULL,
				
				'loc.mng' => NULL,
				
				'lang.mng' => NULL,
				'lang.rslv' => array( 'lang.mng' ),
				
				'navmng.lang' => array( 'lang.rslv' ),
				
				'user.mng' => NULL,
				'user.rewrite' => NULL,
				'user.photo' => NULL,
				'user.security' => NULL,
				'user.op' => NULL,
				
				'cat.meta' => NULL,
				'cat.alias' => NULL,
				'cat.tmpl' => NULL,
				'cat.posttmpl' => NULL,
				
				'post.meta' => NULL,
				'post.defcat' => NULL,
				'post.expdate' => NULL,
				
				'page.meta' => NULL,
				
				'pin.mng' => NULL,
				'pin.log.mng' => array( 'pin.mng' ),
				
				'custhks' => NULL
				
			) )
			
			->mergeConfig( array(
				
				'db' => TRUE,
				'consts' => TRUE,
				'setup' => TRUE,
				'hooks' => TRUE
				
			) )
			
			->mergeAbbrMap( array(
				
				'cat' => 'Category',
				'cont' => 'Contact',
				'custhks' => 'CustomHooks',
				'defcat' => 'DefaultCategory',
				'expdate' => 'ExpirationDate',
				'emsg' => 'EmailMessage',
				'lang' => 'Language',
				'loc' => 'Location',
				'mng' => 'Manage',
				'navmng' => 'NavigationManagement',
				'op' => 'Operation',
				'pnt' => 'Point',
				'posttmpl' => 'PostTemplate',
				'rslv' => 'Resolver',
				'tmpl' => 'Template'
				
			) )
			
		;
		
	}
	
	
	
	//// start
	
	//
	public function start() {
		
		parent::start();
		
		add_filter( 'template_include', array( $this, 'templateInclude' ), 9999 );
		
	}
	
	
	//// components
	
	
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


	// form transform
	public function compSetup( $aArgs ) {
		
		if ( $aArgs[ 'force_https' ] ) {
			
			// force https
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
		
		
		// external files
		Geko_Wp::registerExternalFiles( sprintf( '%s/etc/register.xml', TEMPLATEPATH ) );
		
		
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
	
	
	
	
	
	//// components
	
	
	// role type
	public function compRole_Types( $aArgs ) {
		
		$oRoleTypes = Geko_Wp_Role_Types::getInstance();
		$oRoleTypes->register( 'Geko_Wp_User_RoleType' );
		
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
			
			$aPlugins = array( 'Uri', 'Page', 'Category', 'Post', 'Role' );
			
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
	
	
	
	
	//// helpers
	
	//
	public function templateInclude( $sTemplate ) {
		
		
		$sCurTmpSuffix = Geko_Inflector::camelize( pathinfo( $sTemplate, PATHINFO_FILENAME ) );
		
		
		//
		Geko_Debug::out( $sTemplate, __METHOD__ );
		
		$aClassFileMapping = array(
			'Main' => sprintf( '%s/layout_main.php', TEMPLATEPATH ),
			'Widgets' => sprintf( '%s/layout_widgets.php', TEMPLATEPATH ),
			$sCurTmpSuffix => $sTemplate
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


