<?php

//
class Geko_App_Bootstrap extends Geko_Bootstrap
{
	
	
	//// properties
	
	protected $_aPrefixes = array( 'Gloc_', 'Geko_App_', 'Geko_' );
	
	
	
	
	//// methods
	
	//
	public function doInitPre() {
		
		parent::doInitPre();
		
		Geko_App::init( $this );
		
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
			->set( 'app', $this )				// reference to myself ???
		;
		
	}
	
	
	
	//// default components
	
	
	
	// database connection
	// independent
	public function compDb( $mArgs ) {
		
		if ( is_array( $mArgs ) ) {
			$oDb = Geko_Db::factory( $mArgs[ 0 ], $mArgs[ 1 ] );
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
	public function compSess( $mArgs ) {
		
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
	public function compMatch( $mArgs ) {
		
		$sClass = $this->getBestMatch( 'Match' );
		
		$oMatch = new $sClass();
		
		call_user_func( array( $sClass, 'set' ), $oMatch );
		
		$this->set( 'match', $oMatch );	
	}
	
	
	
	// router
	// independent; optional: "match"
	public function compRouter( $mArgs ) {
		
		$sRouterClass = $this->getBestMatch( 'Router' );
		
		$oRouter = new $sRouterClass( GEKO_STANDALONE_URL );
		
		$this->set( 'router', $oRouter );	
	}
	
	
	
	
	// router.file
	// depends on: "router"
	public function compRouter_File( $mArgs ) {
		
		$oRouter = $this->get( 'router' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_File' );
		
		$oRoute = new $sRouteClass();
		$oRouter->addRoute( $oRoute, 3000, 'file' );
		
		$this->set( 'router.file', $oRoute );
	}
	
	
	
	
	// router.service
	// depends on: "router"
	public function compRouter_Service( $mArgs ) {
		
		$oRouter = $this->get( 'router' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_Service' );
		
		$oRoute = new $sRouteClass();
		$oRouter->addRoute( $oRoute, 6000, 'service' );
		
		$this->set( 'router.service', $oRoute );
	}
	
	
	
	// router.layout
	// depends on: "router"
	public function compRouter_Layout( $mArgs ) {
		
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
	public function compAuth( $mArgs ) {
		
		$oDb = $this->get( 'db' );
		$oSess = $this->get( 'sess' );
		
		$oAuth = Zend_Auth::getInstance();
		
		$oAuthAdapter = new Geko_App_Auth_Adapter( $oDb );
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
	public function compRouter_Auth( $mArgs ) {
		
		$oRouter = $this->get( 'router' );
		$oAuth = $this->get( 'auth' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_Auth' );
		
		$oRoute = new $sRouteClass( $oAuth );
		$oRouter->addRoute( $oRoute, 1000, 'auth' );
		
		$this->set( 'router.auth', $oRoute );
	}
	
	
	
	
	//// run the app
	
	//
	public function doRun() {
		$this->get( 'router' )->run();
	}
	
	
	
	
}



