<?php

//
class Geko_App extends Geko_Singleton_Abstract
{
	
	protected static $_aRegistry = array();
	
	
	protected $_bCalledInit = FALSE;
	
	protected $_aDeps = array(						// dependency tree for the various app components
		'db' => NULL,
		'match' => NULL,
		'router' => NULL,
		'router.service' => array( 'router' ),
		'router.layout' => array( 'router' ),
		'sess' => array( 'db' ),
		'auth' => array( 'sess' ),
		'router.auth' => array( 'router', 'auth' ),
	);
	
	protected $_aExtComponents = array();
	
	protected $_aConfig = array(					// config flags for desired modules
		'match' => TRUE,
		'router' => TRUE,
		'router.layout' => TRUE,
		'router.service' => TRUE
	);
	
	protected $_aLoadedComponents = NULL;
	
	protected $_aPrefixes = array( 'Gloc_', 'Geko_App_', 'Geko_' );
	
	
	
	
	//// static methods
	
	//
	public static function set( $sKey, $mValue ) {
		self::$_aRegistry[ $sKey ] = $mValue;
	}
	
	//
	public static function get( $sKey ) {
		return self::$_aRegistry[ $sKey ];
	}
	
	
	
	//// functionality
	
	//
	public function init() {
		
		if ( !$this->_bCalledInit ) {
			
			self::set( 'app', $this );		// reference to myself
			
			$this->doInitPre();
			
			//// run the requested components
			
			$aConfig = $this->resolveConfig();
			
			$this->_aLoadedComponents = $aConfig;
			
			// $mArgs, use if needed later
			foreach ( $aConfig as $sComp => $mArgs ) {
				
				if ( $fComponent = $this->_aExtComponents[ $sComp ] ) {
					
					// call external component first
					call_user_func( $fComponent, $mArgs );
					
				} else {
					
					// call internal, method-based component
					
					$aComp = explode( '.', $sComp );
					array_walk( $aComp, array( 'Geko_Inflector', 'camelize' ) );
					$sComp = implode( '_', $aComp );
					
					$sMethod = 'comp' . $sComp;
					
					if ( method_exists( $this, $sMethod ) ) {
						$this->$sMethod( $mArgs );
					}
				}
				
			}
			
			$this->doInitPost();
			
			$this->_bCalledInit = TRUE;
		}
		
		return $this;
	}
		
	// resolve any dependencies
	public function resolveConfig( $aConfig = NULL ) {
		
		if ( NULL === $aConfig ) {
			$aConfig = $this->_aConfig;
		}
		
		foreach ( $aConfig as $sKey => $mArgs ) {
			if ( $mArgs ) {
				$aConfig[ $sKey ] = $mArgs;
				$aConfig = $this->getDeps( $aConfig, $sKey );
			}
		}
		
		return $aConfig;
	}
	
	// recursive function
	public function getDeps( $aConfig, $sKey ) {
		
		if ( $aDeps = $this->_aDeps[ $sKey ] ) {
			foreach ( $aDeps as $sDep ) {
				$aConfig = array_merge( array( $sDep => TRUE ), $aConfig );
				$aConfig = $this->getDeps( $aConfig, $sDep );
			}
		}
		
		return $aConfig;
	}
	
	
	//// accessors
		
	//
	public function config( $aParams ) {
		$this->_aConfig = array_merge( $this->_aConfig, $aParams );
		return $this;
	}
	
	
	//
	public function getLoadedComponents() {
		return $this->_aLoadedComponents;
	}
	
	//
	public function registerComponent( $sKey, $fComponent, $aDeps = NULL ) {
		
		$this->_aExtComponents[ $sKey ] = $fComponent;
		
		if ( is_array( $aDeps ) ) {
			$this->_aDeps[ $sKey ] = $aDeps;
		}
		
		return $this;
	}

	
	// hook methods
	public function doInitPre() { }
	public function doInitPost() { }
	
	
	
	
	
	//// default components
	
	
	// database connection
	// independent
	public function compDb( $mArgs ) {

		$oDb = Geko_Db::factory( 'Pdo_Mysql', array(
			'host' => GEKO_DB_HOST,
			'username' => GEKO_DB_USER,
			'password' => GEKO_DB_PWD,
			'dbname' => GEKO_DB_NAME,
			'table_prefix' => GEKO_DB_TABLE_PREFIX
		) );
		
		self::set( 'db', $oDb );
	}
	
	
	// session handler
	// depends on: "db"
	public function compSess( $mArgs ) {
		
		$oDb = self::get( 'db' );
		
		$oSess = Geko_App_Session::getInstance()->setDb( $oDb )->init();
		
		if ( $this->doRegenerateSessionKey() ) {
			$oSess->regenerateSessionKey();
		}
		
		self::set( 'sess', $oSess );	
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
		
		self::set( 'match', $oMatch );	
	}
	
	
	
	// router
	// independent; optional: "match"
	public function compRouter( $mArgs ) {
		
		$sRouterClass = $this->getBestMatch( 'Router' );
		
		$oRouter = new $sRouterClass( GEKO_STANDALONE_URL );
		
		self::set( 'router', $oRouter );	
	}
	
	
	// router.service
	// depends on: "router"
	public function compRouter_Service( $mArgs ) {
		
		$oRouter = self::get( 'router' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_Service' );
		
		$oRoute = new $sRouteClass();
		$oRouter->addRoute( $oRoute, 6000, 'service' );
		
		self::set( 'router.service', $oRoute );
	}
	
	
	
	// router.layout
	// depends on: "router"
	public function compRouter_Layout( $mArgs ) {
		
		$oRouter = self::get( 'router' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_Layout' );
		
		$oRoute = new $sRouteClass();
		$oRouter->addRoute( $oRoute, 9000, 'layout' );
		
		if ( $oMatch = self::get( 'match' ) ) {
			
			$sMatchClass = $this->getBestMatch( 'Match_Rule_Route' );
			
			$oMatch->addRule( new $sMatchClass( $oRouter ) );		
		}
		
		self::set( 'router.layout', $oRoute );
	}
	
	
	
	
	// auth
	// depends on: "db", "sess"; optional: "router"
	public function compAuth( $mArgs ) {
		
		$oDb = self::get( 'db' );
		$oSess = self::get( 'sess' );
		
		$oAuth = Zend_Auth::getInstance();
		
		$oAuthAdapter = new Geko_App_Auth_Adapter( $oDb );
		$oAuthStorage = new Geko_App_Auth_Storage( $oSess );
		
		$oAuth->setStorage( $oAuthStorage );
				
		// logout
		if ( $this->doLogout() ) {
			
			$oSess->destroySession();
			$oAuth->clearIdentity();
			
			header( 'Location: ' . GEKO_STANDALONE_URL );
			die();
		}
		
		self::set( 'auth', $oAuth );
		self::set( 'auth_adapter', $oAuthAdapter );	
	}
	
	//
	public function doLogout() {
		return ( $_REQUEST[ 'logout' ] ) ? TRUE : FALSE ;
	}
	
	
	// router.auth
	// depends on: "router", "auth"
	public function compRouter_Auth( $mArgs ) {
		
		$oRouter = self::get( 'router' );
		$oAuth = self::get( 'auth' );
		
		$sRouteClass = $this->getBestMatch( 'Router_Route_Auth' );
		
		$oRoute = new $sRouteClass( $oAuth );
		$oRouter->addRoute( $oRoute, 1000, 'auth' );
		
		self::set( 'router.auth', $oRoute );
	}
	
	
	
	//// run the app
	
	//
	public function run() {
		
		$this->doRunPre();
		$this->doRun();
		$this->doRunPost();
		
		return $this;
	}
	
	// hook methods
	public function doRunPre() { }
	public function doRunPost() { }
	
	//
	public function doRun() {
		self::get( 'router' )->run();
	}
	
	
	
	//// helpers
	
	//
	public function getBestMatch() {
		$aSuffixes = func_get_args();
		return Geko_Class::getBestMatch( $this->_aPrefixes, $aSuffixes );
	}
	
	
	
}




