<?php

//
class Geko_Plugin
{

	//
	public static function add( $sClassName, $mParams, $oTarget, &$aPlugins, $sSetupMethod = '' ) {
	
		if ( is_string( $sClassName ) && !in_array( $sClassName, $aPlugins ) ) {
			
			$aPlugins[] = $sClassName;
			
			if ( $sSetupMethod ) {
			
				$oPlugin = Geko_Singleton_Abstract::getInstance( $sClassName );
				
				if ( method_exists( $oPlugin, $sSetupMethod ) ) {
					$oPlugin->$sSetupMethod( $oTarget, $mParams );
				}
			}
		}
		
	}
	
	//
	public static function applyFilter( $aArgs, $aPlugins ) {
		
		$sMethod = array_shift( $aArgs );
		
		// perform filtering if there are plugins
		if ( count( $aPlugins ) > 0 ) {
			
			foreach ( $aPlugins as $sPluginClass ) {
				
				$oPlugin = Geko_Singleton_Abstract::getInstance( $sPluginClass );
				
				if ( method_exists( $oPlugin, $sMethod ) ) {
					$mRetVal = call_user_func_array( array( $oPlugin, $sMethod ), $aArgs );
					$aArgs[ 0 ] = $mRetVal;
				}
			}
		}
		
		return $aArgs[ 0 ];	
	}
	
	//
	public static function doAction( $aArgs, $aPlugins ) {
		
		$sMethod = array_shift( $aArgs );
		
		// perform filtering if there are plugins
		if ( count( $aPlugins ) > 0 ) {
			
			foreach ( $aPlugins as $sPluginClass ) {
				
				$oPlugin = Geko_Singleton_Abstract::getInstance( $sPluginClass );
				
				if ( method_exists( $oPlugin, $sMethod ) ) {
					call_user_func_array( array( $oPlugin, $sMethod ), $aArgs );
				}
			}
		}
		
	}

}
