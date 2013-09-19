<?php

//
class Geko_App extends Geko_Singleton_Abstract
{
	
	protected static $_aRegistry = array();
	
	protected $_bCalledInit = FALSE;
	
	protected $_aConfig = array();			// config flags for desired modules
	
	
	
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
	
	//
	public function init() {
		
		if ( !$this->_bCalledInit ) {
			
			//
			$this->doInitPre();
			
			//// database connection
			
			$oDb = Geko_Db::factory( 'Pdo_Mysql', array(
				'host' => GEKO_DB_HOST,
				'username' => GEKO_DB_USER,
				'password' => GEKO_DB_PWD,
				'dbname' => GEKO_DB_NAME,
				'table_prefix' => GEKO_DB_TABLE_PREFIX
			) );
			
			self::set( 'db', $oDb );
			
			
			//// session handler
			$oSess = Geko_App_Session::getInstance()->init()->setDb( $oDb );
			
			self::set( 'sess', $oSess );
			
						
			
			//// router
			
			$oLayoutRoute = new Gloc_Router_Route_Layout();
			
			$oRouter = new Gloc_Router( GEKO_STANDALONE_URL );
			$oRouter->addRoute( $oLayoutRoute );
			
			self::set( 'router', $oRouter );
			
			//// matcher
			
			$oMatch = new Gloc_Match();
			$oMatch->addRule( new Gloc_Match_Rule_Route( $oRouter ) );
			
			Geko_Match::set( $oMatch );
			self::set( 'match', $oMatch );
			
			
			
			//// optionals
			$aConfig = $this->_aConfig;
			
			if ( $aConfig[ 'auth' ] ) {
			
				$oAuth = Zend_Auth::getInstance();
				
				$oAuthAdapter = new Geko_App_Auth_Adapter( $oDb );
				$oAuthStorage = new Geko_App_Auth_Storage( $oSess );
				
				$oAuth->setStorage( $oAuthStorage );
				
				self::set( 'auth', $oAuth );
				self::set( 'auth_adapter', $oAuthAdapter );
				
				$oRouter->prependRoute( new Geko_App_Auth_Route( $oAuth ) );
			}
			
			
			//
			$this->doInitPost();
			
			$this->_bCalledInit = TRUE;
		}
		
		return $this;
	}
	
	// hook methods
	public function doInitPre() { }
	public function doInitPost() { }
	
	
	
	
	//
	public function run() {
		
		//
		$this->doRunPre();
		
		// run the router
		self::get( 'router' )->run();
		
		//
		$this->doRunPost();
		
		return $this;
	}
	
	// hook methods
	public function doRunPre() { }
	public function doRunPost() { }
	
	
	
	
}




