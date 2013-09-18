<?php

//
class Geko_App extends Geko_Singleton_Abstract
{
	
	protected static $aRegistry = array();
	
	protected $bCalledInit = FALSE;
	
	
	
	//
	public static function set( $sKey, $mValue ) {
		self::$aRegistry[ $sKey ] = $mValue;
	}
	
	//
	public static function get( $sKey ) {
		return self::$aRegistry[ $sKey ];
	}
	
	
	//
	public function init() {
		
		if ( !$this->bCalledInit ) {
			
			// database connection
			
			$oDb = Geko_Db::factory( 'Pdo_Mysql', array(
				'host' => GEKO_DB_HOST,
				'username' => GEKO_DB_USER,
				'password' => GEKO_DB_PWD,
				'dbname' => GEKO_DB_NAME,
				'table_prefix' => GEKO_DB_TABLE_PREFIX
			) );
			
			self::set( 'db', $oDb );
			
			
			// session handler
			$oSess = Geko_Session::getInstance()->init()->setDb( $oDb );
			
			self::set( 'sess', $oSess );
			
			
			// router
			
			$oLayoutRoute = new Gloc_Router_Route_Layout();
			
			$oRouter = new Gloc_Router( GEKO_STANDALONE_URL );
			$oRouter->addRoute( $oLayoutRoute );
			
			self::set( 'router', $oRouter );
			
			// matcher
			
			$oMatch = new Gloc_Match();
			$oMatch->addRule( new Gloc_Match_Rule_Route( $oRouter ) );
			
			Geko_Match::set( $oMatch );
			self::set( 'match', $oMatch );
			
			
			$this->bCalledInit = TRUE;
		}
		
		return $this;
	}
	
	//
	public function run() {

		// run the router
		self::get( 'router' )->run();
		
		return $this;
	}
		
	
	
}




