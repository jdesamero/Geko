<?php

//
class Geko_Wp_Template extends Geko_Singleton_Abstract
{
	const CACHE_REGISTRY_KEY = 'geko-template-cache-registry';
	const HASH_PREFIX = 'geko-template-modified-hash-';
	
	protected $aParseChecks = array();
	protected $aChanged = array();
	
	protected $aCaches = NULL;
	protected $aCacheUpdate = array();
	protected $bUpdateCacheRegistry = FALSE;
	
	
	
	//
	public function getDirKey( $sDirectory, $iLevels ) {
		return md5( sprintf( '%s_%d', $sDirectory, intval( $iLevels ) ) );
	}
	
	//
	public function getParseCheck( $sDirectory, $iLevels ) {
		
		$sDirKey = $this->getDirKey( $sDirectory, $iLevels );
		if ( !$this->aParseChecks[ $sDirKey ] ) {
			$this->aParseChecks[ $sDirKey ] = new Geko_Wp_CheckModifiedFiles( $sDirectory, $iLevels );
		}
		
		return $this->aParseChecks[ $sDirKey ];
	}
	
	//
	public function getChanged( $sDirectory, $iLevels, $sPrefix ) {
		
		$sDirKey = $sDirKey = $this->getDirKey( $sDirectory, $iLevels );
		if ( !$this->aChanged[ $sDirKey ] ) {
			
			$sHashKey = ( TEMPLATEPATH == $sDirectory ) ? 
				sprintf( '%s%s', self::HASH_PREFIX, Geko_Wp_Theme::getPrefix() ) : 				// retrieve template files from current theme
				sprintf( '%s%s', self::HASH_PREFIX, $sPrefix )									// retrieve template files from a plugin directory or some other source
			;
			
			$this->aChanged[ $sDirKey ] = $this->getParseCheck( $sDirectory, $iLevels )->changed( $sHashKey );
		}
		
		return $this->aChanged[ $sDirKey ];
	}
	
	//
	public function getTemplateValues( $aParams ) {
		
		$aParams = array_merge( array(
			'prefix' => 'geko',
			'directory' => TEMPLATEPATH,
			'levels' => 0,
			'callback' => array( $this, 'defaultTemplateValuesCallback' ),
			'post_callback' => NULL
		), $aParams);
		
		$sDirectory = $aParams[ 'directory' ];
		$sPrefix = $aParams[ 'prefix' ];
		$iLevels = $aParams[ 'levels' ];
		$fCallback = $aParams[ 'callback' ];
		$fPostCallback = $aParams[ 'post_callback' ];
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		// retrieve the cache registry
		if ( NULL === $this->aCaches ) {
			$this->aCaches = Zend_Json::decode( get_option( self::CACHE_REGISTRY_KEY ) );
			if ( !is_array( $this->aCaches ) ) $this->aCaches = array();
			add_action( 'shutdown', array( $this, 'updateCacheRegistry' ) );
		}
		
		// check if the corresponding directory changed
		$sDirKey = $this->getDirKey( $sDirectory, $iLevels );
		
		if ( $this->getChanged( $sDirectory, $iLevels, $sPrefix ) ) {
			// clear from the cache registry
			if ( $this->aCaches[ $sDirKey ] ) unset( $this->aCaches[ $sDirKey ] );
		}
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$sCacheKey = sprintf( '%s-cache', $sPrefix );
		
		$mRet = NULL;
		
		if ( !$this->aCaches[ $sDirKey ][ $sCacheKey ] ) {
			
			// perform the expensive operation
			$oParseCheck = $this->getParseCheck( $sDirectory, $iLevels );
			
			$aFiles = $oParseCheck->getFiles();
			
			foreach ( $aFiles as $iIdx => $sTemplateFile ) {
				$mRet = call_user_func( $fCallback, $mRet, $iIdx, $sTemplateFile, $aParams );
			}
			
			if ( $fPostCallback ) $mRet = call_user_func( $fPostCallback, $mRet, $aParams );
			
			// update the cache
			if ( '' == $oParseCheck->getFileHash() ) {
				add_option( $sCacheKey, Zend_Json::encode( $mRet ) );
			} else {
				update_option( $sCacheKey, Zend_Json::encode( $mRet ) );
			}
			
			// update the cache registry
			$this->aCacheUpdate[ $sDirKey ][ $sCacheKey ] = TRUE;
			$this->bUpdateCacheRegistry = TRUE;
			
		} else {
			
			// retrieve from the cache
			$mRet = Zend_Json::decode( get_option( $sCacheKey ) );
			
		}
		
		return $mRet;
	}
	
	//
	public function defaultTemplateValuesCallback( $mRet, $iIdx, $sTemplateFile, $aParams ) {
		
		$sAttributeName = $aParams[ 'attribute_name' ];
		
		if ( !is_array( $mRet ) ) $mRet = array(); 
		
		$sTemplateFileContents = file_get_contents( $sTemplateFile );
		
		$sName = '';
		
		if ( preg_match( sprintf( '|%s:(.*)$|mi', $sAttributeName ), $sTemplateFileContents, $sName ) ) {
			$sName = _cleanup_header_comment( $sName[ 1 ] );
		}
		
		if ( $sName ) {
			if ( preg_match( '/^[A-Za-z0-9 -_]+$/', $sName ) ) {
				$mRet[ trim( $sName ) ] = basename( $sTemplateFile );
			}
		}
		
		return $mRet;
	}
	
	//
	public function introspectTemplateValuesCallback( $mRet, $iIdx, $sTemplateFile, $aParams ) {
		
		// TO DO: Gloc_Layout is hard-coded, do something about this
		
		if ( $fIntrospectCallback = $aParams[ 'introspect_callback' ] ) {
			
			$sPhpCode = file_get_contents( $sTemplateFile );
			
			$aRegs = array();
			if ( preg_match( '/class\s([a-zA-Z0-1_]+)\sextends\sGloc_Layout/si', $sPhpCode, $aRegs ) ) {
				
				// we have a layout class
				$sClass = $aRegs[ 1 ];
				
				if ( 0 === strpos( $sClass, 'Gloc_Layout' ) ) {
					
					// load the class so it can be introspected
					require_once( $sTemplateFile );
				}
				
				if (
					( class_exists( $sClass ) ) && 
					( $oLayout = Geko_Singleton_Abstract::getInstance( $sClass ) ) && 
					( $oLayout instanceof Geko_Wp_Layout )
				) {
					// DEPRACATED: Can break things easily when called
					// $oLayout->introspect();
					$mRet = call_user_func( $fIntrospectCallback, $mRet, $oLayout, $aParams );
				}
				
			}
			
		}
		
		return $mRet;
	}
	
	
	// did not use __destruct() since update_option() no longer worked
	public function updateCacheRegistry() {
		
		if ( $this->bUpdateCacheRegistry ) {
			
			// merge the updated cache values with the cache array
			foreach ( $this->aCacheUpdate as $sKey => $aValues ) {
				if ( !$this->aCaches[ $sKey ] ) $this->aCaches[ $sKey ] = array();
				$this->aCaches[ $sKey ] = array_merge( $this->aCaches[ $sKey ], $aValues );
			}
			
			update_option( self::CACHE_REGISTRY_KEY, Zend_Json::encode( $this->aCaches ) );
		}
	}
	
}


