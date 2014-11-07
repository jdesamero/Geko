<?php

//
class Geko_App_Bootstrap extends Geko_Bootstrap
{
	
	
	//// properties
	
	protected $_aPrefixes = array( 'Gloc_', 'Geko_App_', 'Geko_' );
	
	
	
	
	//// methods
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		$this
			
			->mergeDeps( array(
				
				'db' => NULL,
				'match' => NULL,
				
				'router' => NULL,
				'router.file' => array( 'router' ),
				'router.service' => array( 'router' ),
				'router.layout' => array( 'router' ),
				
				'sess' => array( 'db' ),
				
				'auth' => array( 'sess' ),
				'auth.adapter' => array( 'auth' ),
				'auth.storage' => array( 'auth' ),
				
				'router.auth' => array( 'router', 'auth' )
				
			) )
			
			->mergeConfig(	array(
				
				'match' => TRUE,
				
				'router' => TRUE,
				'router.layout' => TRUE,
				'router.service' => TRUE
			
			) )
			
			->mergeAbbrMap( array(
				
				'addr' => 'Address',
				'cont' => 'Contact',
				'itm' => 'Item',
				'lang' => 'Language',
				'loc' => 'Location',
				'mng' => 'Manage',
				'prov' => 'Province',
				'tx' => 'Taxonomy'
				
			) )
			
		;
		
	}
	
	
	
	//// default components
	
	
	
	// database connection
	// independent
	public function compDb( $aArgs ) {
		
		if ( is_string( $aArgs[ 0 ] ) ) {
			
			$oDb = Geko_Db::factory( $aArgs[ 0 ], $aArgs[ 1 ] );
		
		} else {
			
			$oDb = Geko_Db::factory( 'Pdo_Mysql', array(
				'host' => GEKO_DB_HOST,
				'username' => GEKO_DB_USER,
				'password' => GEKO_DB_PWD,
				'dbname' => GEKO_DB_NAME,
				'table_prefix' => GEKO_DB_TABLE_PREFIX
			) );
		}
		
		$this->set( 'db', $oDb );
	}
	
	
	// session handler
	// depends on: "db"
	public function compSess( $aArgs ) {
		
		$oDb = $this->get( 'db' );
		
		$oSess = Geko_App_Session::getInstance()->setDb( $oDb )->init();
		
		if ( $this->doRegenerateSessionKey() ) {
			$oSess->regenerateSessionKey();
		}
		
		$this->set( 'sess', $oSess );
	}
	
	//
	public function doRegenerateSessionKey() {
		return ( $_REQUEST[ 'ajax_content' ] ) ? FALSE : TRUE ;
	}
	
	
	
	// matcher
	// independent
	public function compMatch( $aArgs ) {
		
		$sClass = $this->getBestMatch( 'Match' );
		
		$oMatch = new $sClass();
		
		call_user_func( array( $sClass, 'set' ), $oMatch );
		
		$this->set( 'match', $oMatch );	
	}
	
	
	
	// router
	// independent; optional: "match"
	public function compRouter( $aArgs ) {
		
		$sRouterClass = $this->getBestMatch( 'Router' );
		
		$oRouter = new $sRouterClass( GEKO_STANDALONE_URL );
		
		$this->set( 'router', $oRouter );	
	}
	
	
	
	
	// router.file
	// depends on: "router"
	public function compRouter_File( $aArgs ) {
		
		$oRouter = $this->get( 'router' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_File' );
		
		$oRoute = new $sRouteClass();
		$oRouter->addRoute( $oRoute, 3000, 'file' );
		
		$this->set( 'router.file', $oRoute );
	}
	
	
	
	
	// router.service
	// depends on: "router"
	public function compRouter_Service( $aArgs ) {
		
		$oRouter = $this->get( 'router' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_Service' );
		
		$oRoute = new $sRouteClass();
		$oRouter->addRoute( $oRoute, 6000, 'service' );
		
		$this->set( 'router.service', $oRoute );
	}
	
	
	
	// router.layout
	// depends on: "router"
	public function compRouter_Layout( $aArgs ) {
		
		$oRouter = $this->get( 'router' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_Layout' );
		
		$oRoute = new $sRouteClass();
		$oRouter->addRoute( $oRoute, 9000, 'layout' );
		
		if ( $oMatch = $this->get( 'match' ) ) {
			
			$sMatchClass = $this->getBestMatch( 'Match_Rule_Route' );
			
			$oMatch->addRule( new $sMatchClass( $oRouter ) );		
		}
		
		$this->set( 'router.layout', $oRoute );
	}
	
	
	
	
	// auth
	// depends on: "db", "sess"; optional: "router"
	public function compAuth( $aArgs ) {
		
		$oDb = $this->get( 'db' );
		$oSess = $this->get( 'sess' );
		
		$oAuth = Zend_Auth::getInstance();
		
		$oAuthAdapter = new Geko_App_Auth_Adapter(
			$oDb,
			$aArgs[ 'table_name' ],
			$aArgs[ 'identity_column' ],
			$aArgs[ 'credential_column' ],
			$aArgs[ 'credential_treatment' ]
		);
		
		$oAuthStorage = new Geko_App_Auth_Storage( $oSess );
		
		$oAuth->setStorage( $oAuthStorage );
				
		// logout
		if ( $this->doLogout() ) {
			
			$oSess->destroySession();
			$oAuth->clearIdentity();
			
			header( sprintf( 'Location: %s', GEKO_STANDALONE_URL ) );
			die();
		}
		
		$this
			->set( 'auth', $oAuth )
			->set( 'auth.adapter', $oAuthAdapter )
			->set( 'auth.storage', $oAuthStorage )
		;
	}
	
	
	//
	public function doLogout() {
		return ( $_REQUEST[ 'logout' ] ) ? TRUE : FALSE ;
	}
	
	
	// router.auth
	// depends on: "router", "auth"
	public function compRouter_Auth( $aArgs ) {
		
		$oRouter = $this->get( 'router' );
		$oAuth = $this->get( 'auth' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_Auth' );
		
		$oRoute = new $sRouteClass( $oAuth );
		$oRouter->addRoute( $oRoute, 1000, 'auth' );
		
		if ( $aArgs[ 'rules' ] ) {
			$oRoute->addRules( $aArgs[ 'rules' ] );
		}
		
		$this->set( 'router.auth', $oRoute );
	}
	
	
	
	
	//// run the app
	
	//
	public function doRun() {
		$this->get( 'router' )->run();
	}
	
	
	
	
}



