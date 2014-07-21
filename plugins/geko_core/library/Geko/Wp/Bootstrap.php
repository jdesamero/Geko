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
				
				'sess' => NULL,
				'consts' => NULL,
				'setup' => NULL,
				'extfiles' => NULL,
				'hooks' => NULL,
				
				'role.types' => NULL,
				'role.mng' => array( 'role.types' ),
				
				'form.mng' => NULL,
				
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

				'consts' => TRUE,
				'setup' => TRUE,
				'extfiles' => TRUE,
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

	
	
	
	//// components
	
	
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
		
		// form transformation hooks
		
		Geko_PhpQuery_FormTransform_Plugin_File::setDefaultFileDocRoot( substr( ABSPATH, 0, strlen( ABSPATH ) - 1 ) );
		Geko_PhpQuery_FormTransform_Plugin_File::setDefaultFileUrlRoot( Geko_Wp::getUrl() );
		
		Geko_PhpQuery_FormTransform::registerPlugin( 'Geko_PhpQuery_FormTransform_Plugin_File' );
		Geko_PhpQuery_FormTransform::registerPlugin( 'Geko_PhpQuery_FormTransform_Plugin_RowTemplate' );
		
	}

	
	// external files
	public function compExtfiles( $aArgs ) {
		
		Geko_Wp::registerExternalFiles( sprintf( '%s/etc/register.xml', TEMPLATEPATH ) );
		
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
	
	
	// location manage
	public function compLoc_Mng( $aArgs ) {
		
		$oLocMng = Geko_Wp_Location_Manage::getInstance();
		
		$oLocMng->init()
			/* /
			->install()
			->populateContinentTable( array( 'NA' ) )	
			->populateCountryTable( array( 'CA' ) )
			->populateProvinceTable()
			/* */
		;
		
		$this->set( 'loc.mng', $oLocMng );
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
		
		if ( class_exists( 'Geko_Wp_NavigationManagement_Language' ) ) {
			$aPlugins[] = 'Geko_Wp_NavigationManagement_Language';
		}
		
		$oLangMng = Geko_Wp_Language_Manage::getInstance();
		
		$oLangMng->init();
		
		call_user_func_array( array( $oLangMng, 'registerPlugins' ), $aPlugins );
		
		$this->set( 'lang.mng', $oLangMng );
	}
	
	
	// point manage
	public function compPnt_Mng( $aArgs ) {
		
		$oPntMng = Geko_Wp_Point_Manage::getInstance();
		
		if ( $sPaEmsg = $aArgs[ 'points_approval_emsg' ] ) {
			$oPntMng->setPointsApprovalEmsg( $sPaEmsg );
		}
		
		$oPntMng->init();
		
		$this->set( 'pnt.mng', $oPntMng );
	}
	
	
	
	
	
	
	//// helpers
	
	//
	public function renderTemplate() {
		
		// determine the wordpress template file that was called
		
		$aBt = debug_backtrace();
		$sTemplate = $aBt[ 1 ][ 'file' ];
		
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


