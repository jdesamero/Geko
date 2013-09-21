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
		'sess' => array( 'db' ),
		'auth' => array( 'sess' )
	);
	
	protected $_aConfig = array(					// config flags for desired modules
		'match' => TRUE,
		'router' => TRUE
	);
	
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
	public function config( $aParams ) {
		$this->_aConfig = array_merge( $this->_aConfig, $aParams );
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
	
	//
	public function getDeps( $aConfig, $sKey ) {
		
		if ( $aDeps = $this->_aDeps[ $sKey ] ) {
			foreach ( $aDeps as $sDep ) {
				$aConfig = array_merge( array( $sDep => TRUE ), $aConfig );
				$aConfig = $this->getDeps( $aConfig, $sDep );
			}
		}
		
		return $aConfig;
	}
	
	
	//
	public function init() {
		
		if ( !$this->_bCalledInit ) {
			
			$this->doInitPre();
			
			//// run the requested components
			
			$aConfig = $this->resolveConfig();
			
			// $mArgs, use if needed later
			foreach ( $aConfig as $sComp => $mArgs ) {
				$sMethod = 'comp' . Geko_Inflector::camelize( $sComp );
				if ( method_exists( $this, $sMethod ) ) {
					$this->$sMethod();
				}
			}
			
			$this->doInitPost();
			
			$this->_bCalledInit = TRUE;
		}
		
		return $this;
	}
	
	// hook methods
	public function doInitPre() { }
	public function doInitPost() { }
	
	
	
	
	
	//// default components
	
	
	// database connection
	// independent
	public function compDb() {

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
	public function compSess() {
		
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
	public function compMatch() {
		
		$sClass = $this->getBestMatch( 'Match' );
		
		$oMatch = new $sClass();
		
		call_user_func( array( $sClass, 'set' ), $oMatch );
		
		self::set( 'match', $oMatch );	
	}
	
	
	
	// router
	// independent; optional: "match"
	public function compRouter() {
		
		$sRouterClass = $this->getBestMatch( 'Router' );
		
		$oRouter = new $sRouterClass( GEKO_STANDALONE_URL );
		
		
		
		
		//// service stuff
		
		$sRouteServiceClass = $this->getBestMatch( 'Router_Route_Service' );
		
		$oServiceRoute = new $sRouteServiceClass();
		$oRouter->addRoute( $oServiceRoute, 6000, 'service' );
		
		
		
		//// layout stuff
		
		$sRouteLayoutClass = $this->getBestMatch( 'Router_Route_Layout' );
		
		$oLayoutRoute = new $sRouteLayoutClass();
		$oRouter->addRoute( $oLayoutRoute, 9000, 'layout' );
		
		if ( $oMatch = self::get( 'match' ) ) {
			
			$sMatchClass = $this->getBestMatch( 'Match_Rule_Route' );
			
			$oMatch->addRule( new $sMatchClass( $oRouter ) );		
		}
		
		
		
		
		self::set( 'router', $oRouter );	
	}
	
	
	
	// auth
	// depends on: "db", "sess"; optional: "router"
	public function compAuth() {
		
		$oDb = self::get( 'db' );
		$oSess = self::get( 'sess' );
		
		$oAuth = Zend_Auth::getInstance();
		
		$oAuthAdapter = new Geko_App_Auth_Adapter( $oDb );
		$oAuthStorage = new Geko_App_Auth_Storage( $oSess );
		
		$oAuth->setStorage( $oAuthStorage );
		
		if ( $oRouter = self::get( 'router' ) ) {
			$oRouter->addRoute( new Geko_App_Auth_Route( $oAuth ), 1000 );
		}
		
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




