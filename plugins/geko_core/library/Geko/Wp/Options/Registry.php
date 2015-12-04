<?php
/*
 * "geko_core/library/Geko/Wp/Options/Registry.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * A singleton class responsible checking the state of option/management classes (eg: table exists)
 */

//
class Geko_Wp_Options_Registry extends Geko_Singleton_Abstract
{
	
	const REG_OPT_KEY = 'geko_options_registry';
	
	
	protected $_aParams = array();
	
	protected $_bParamsUpdated = FALSE;
	
	
	
	//
	public function start() {
		
		parent::start();
		
		$sParams = get_option( self::REG_OPT_KEY );
		
		if (
			( $sParams ) &&
			( is_array( $aParams = Geko_Json::decode( $sParams ) ) )
		) {
			$this->_aParams = $aParams;
		} else {
			// force init
			$this->setParamsUpdated();
		}
		
		
		// set-up
		if ( !array_key_exists( 'initialized', $this->_aParams ) ) {
			$this->_aParams[ 'initialized' ] = array();
			$this->setParamsUpdated();
		}
		
		
		// trigger on shutdown
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}
	
	
	
	//
	public function setParamsUpdated( $bParamsUpdated = TRUE ) {
		
		$this->_bParamsUpdated = $bParamsUpdated;
		
		return $this;
	}
	
	
	
	
	//
	public function wasInitialized( $sKey ) {
		
		return in_array( $sKey, $this->_aParams[ 'initialized' ] );
	}
	
	//
	public function setInitialized( $sKey ) {
		
		$this->_aParams[ 'initialized' ][] = $sKey;
		$this->setParamsUpdated();
		
		return $this;
	}
	
	
	//
	public function shutdown() {
		
		if ( $this->_bParamsUpdated ) {
			
			update_option( self::REG_OPT_KEY, Geko_Json::encode(
				$this->_aParams
			) );
			
		}
		
	}
	

}



