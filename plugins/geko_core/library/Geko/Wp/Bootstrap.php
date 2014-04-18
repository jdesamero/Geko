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
				
				'consts' => NULL,
				'frmtrns' => NULL,				// form transform
				'extfiles' => NULL,
				'hooks' => NULL,
				
				'role.types' => NULL,
				'role.mng' => array( 'role.types' ),
				
				'emsg.mng' => NULL,
				
				'loc.mng' => NULL,
				
				'lang.mng' => NULL,
				'lang.rslv' => array( 'lang.mng' ),
				
				'navmng.lang' => array( 'lang.rslv' ),
				
				'user.mng' => NULL,
				'user.rewrite' => NULL,
				'user.photo' => NULL,
				'user.security' => NULL,
				'user.op' => NULL,
				
				'cat.alias' => NULL,
				'cat.tmpl' => NULL,
				'cat.posttmpl' => NULL,
				
				'post.meta' => NULL,
				'page.meta' => NULL,
				
				'pin.mng' => NULL,
				'pin.log.mng' => array( 'pin.mng' ),
				
				'custhks' => NULL
				
			) )
			
			->mergeConfig( array(

				'consts' => TRUE,
				'frmtrns' => TRUE,
				'extfiles' => TRUE,
				'hooks' => TRUE
				
			) )
			
			->mergeAbbrMap( array(
				
				'emsg' => 'EmailMessage',
				'cat' => 'Category',
				'custhks' => 'CustomHooks',
				'lang' => 'Language',
				'loc' => 'Location',
				'mng' => 'Manage',
				'navmng' => 'NavigationManagement',
				'op' => 'Operation',
				'posttmpl' => 'PostTemplate',
				'rslv' => 'Resolver',
				'tmpl' => 'Template'
				
			) )
			
		;
		
	}

	
	
	
	//// components
	
	// constants
	public function compConsts( $mArgs ) {
		
		define( 'ABS_WP_URL_ROOT', str_replace( sprintf( 'http://%s', $_SERVER[ 'SERVER_NAME' ] ), '', Geko_Wp::getUrl() ) );
		
	}


	// form transform
	public function compFrmtrns( $mArgs ) {
		
		// form transformation hooks
		
		Geko_PhpQuery_FormTransform_Plugin_File::setDefaultFileDocRoot( substr( ABSPATH, 0, strlen( ABSPATH ) - 1 ) );
		Geko_PhpQuery_FormTransform_Plugin_File::setDefaultFileUrlRoot( Geko_Wp::getUrl() );
		
		Geko_PhpQuery_FormTransform::registerPlugin( 'Geko_PhpQuery_FormTransform_Plugin_File' );
		Geko_PhpQuery_FormTransform::registerPlugin( 'Geko_PhpQuery_FormTransform_Plugin_RowTemplate' );
		
	}

	
	// external files
	public function compExtfiles( $mArgs ) {
		
		Geko_Wp::registerExternalFiles( sprintf( '%s/etc/register.xml', TEMPLATEPATH ) );
		
	}
	
	
	// hooks
	public function compHooks( $mArgs ) {
		
		// adds the hooks: admin_head, admin_body_header, admin_body_footer
		// adds the filters: admin_page_source
		Geko_Wp_Admin_Hooks::init();
		
		Geko_Wp_Hooks::setFixHttps();		// !!!!!!!!!!!!!  ****
		
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
	public function compRole_Type( $mArgs ) {
		
		$oRoleTypes = Geko_Wp_Role_Types::getInstance();
		$oRoleTypes->register( 'Geko_Wp_User_RoleType' );
		
		$this->set( 'role.types', $oRoleTypes );
	}
	
	
	// location manage
	public function compLoc_Mng( $mArgs ) {
		
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
	
	
	// navigation management language
	public function compNavmng_Lang( $mArgs ) {
	
		if ( class_exists( 'Geko_Wp_NavigationManagement_Language' ) ) {
			
			$oNavmngLang = Geko_Wp_NavigationManagement_Language::getInstance();
			
			$aPlugins = array( 'Uri', 'Page', 'Category', 'Post', 'Role' );
			
			call_user_func_array( array( $oNavmngLang, 'registerPlugins' ), $aPlugins );
			
			$this->set( 'navmng.lang', $oNavmngLang );
		}
		
	}
	
	
	// language manage
	public function compLang_Mng( $mArgs ) {
		
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
	
	
	
	
	
	
	
	
	
	//// helpers
	
	//
	public function renderTemplate() {
		
		
		$aBt = debug_backtrace();
		$sTemplate = $aBt[ 1 ][ 'file' ];
		
		//
		Geko_Debug::out( $sTemplate, __METHOD__ );
		
		
		$oResolve = new Geko_Wp_Resolver();
		$oResolve
			->setClassFileMapping( array(
				'Main' => sprintf( '%s/layout_main.php', TEMPLATEPATH ),
				'Widgets' => sprintf( '%s/layout_widgets.php', TEMPLATEPATH ),
				'Template' => $sTemplate
			) )
			->addPath( 'default', new Geko_Wp_Resolver_Path_Default() )
			->run()
		;
		
		
		
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
	
	
	
}