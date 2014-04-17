<?php

//
class Geko_Wp_Bootstrap extends Geko_Bootstrap
{
	
	
	//// properties
	
	protected $_aPrefixes = array( 'Gloc_', 'Geko_Wp_', 'Geko_' );
	
	
	
	
	
	//// methods
	
	//
	public function doInitPre() {
		
		parent::doInitPre();
		
		$this
			
			->mergeDeps( array(
				
				'consts' => NULL,
				'frmtrns' => NULL,				// form transform
				'extfiles' => NULL,
				'hooks' => NULL,
				
				'role.types' => NULL,
				'role.mng' => array( 'role.types' ),
				
				'emsg.mng' => NULL,
				
				'user.mng' => NULL,
				'user.rewrite' => NULL,
				'user.photo' => NULL,
				'user.security' => NULL,
				
				'cat.alias' => NULL,
				'cat.tmpl' => NULL,
				'cat.posttmpl' => NULL,
				
				'post.meta' => NULL,
				'page.meta' => NULL
				
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
				'mng' => 'Manage',
				'posttmpl' => 'PostTemplate',
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
	
	
	//// role
	
	//
	public function compRole_Type( $mArgs ) {
		
		$oRoleTypes = Geko_Wp_Role_Types::getInstance();
		$oRoleTypes->register( 'Geko_Wp_User_RoleType' );
		
		$this->set( 'role.types', $oRoleTypes );
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
