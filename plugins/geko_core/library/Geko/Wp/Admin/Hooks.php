<?php

//
class Geko_Wp_Admin_Hooks
{
	
	//
	protected static $bCalledInit = FALSE;
	protected static $sCurrentFilter;
	
	protected static $aStates = FALSE;
	protected static $oCurrentPlugin;
	protected static $sDisplayMode = FALSE;
	
	
	
	//
	public static function init( $aPlugins = array() ) {
		
		if ( !self::$bCalledInit ) {
			
			if ( is_admin() ) {
				
				// generate list of default plugin classes
				$aDefaultPlugins = array_map(
					function( $sVal ) {
						return sprintf( '%s_%s', __CLASS__, str_replace( '.php', '', $sVal ) );
					},
					array_diff(
						scandir( sprintf( '%s/Hooks', dirname( __FILE__ ) ) ),
						array( '.', '..', 'PluginAbstract.php' )
					)	
				);
				
				$aPlugins = array_merge( $aPlugins, $aDefaultPlugins );
				
				// check for any matching states
				foreach ( $aPlugins as $sPluginClass ) {
					$oPlugin = new $sPluginClass();
					if ( $aStates = $oPlugin->getStates() ) {
						self::$aStates = $aStates;
						self::$oCurrentPlugin = $oPlugin;
						break;
					}
				}
				
				if ( self::$aStates ) {
					
					// attach filters and actions to matching states
					
					add_action( 'admin_init', array( __CLASS__, 'adminInit' ) );
					add_action( 'admin_head', array( __CLASS__, 'adminHead' ) );
					
					add_filter( 'admin_page_source', array( __CLASS__, 'applyFilters' ) );
					
					// Hacky!!!
					if ( self::$aStates && ( count( self::$aStates ) == 2 ) ) {
						self::$sDisplayMode = str_replace( sprintf( '%s_', self::$aStates[ 0 ] ), '', self::$aStates[ 1 ] );
					}
				}
				
			}
			
			self::$bCalledInit = TRUE;
		}
	}
	
	
	//
	public static function adminInit() {
		self::adminHook( 'init' );
	}
	
	//
	public static function adminHead() {
		self::adminHook( 'head' );
	}
	
	
	//
	public static function adminHook( $sType ) {
		if ( self::$aStates ) {
			foreach ( self::$aStates as $sState ) {
				$sAction = sprintf( 'admin_%s_%s', $sType, $sState );
				do_action( $sAction );
			}
		}
	}
	
	
	//
	public static function applyFilters( $sContent ) {
		
		if ( self::$aStates ) {
			foreach ( self::$aStates as $sState ) {
				$sFilter = sprintf( 'admin_page_source_%s', $sState );
				add_filter( $sFilter, array( self::$oCurrentPlugin, 'applyFilters' ), 10, 2 );
				$sContent = apply_filters( $sFilter, $sContent, $sState );
			}
		}
		
		return $sContent;
	}
	
	//
	public static function getDisplayMode() {
		return self::$sDisplayMode;
	}
	
	//
	public static function getCurrentPlugin() {
		return self::$oCurrentPlugin;
	}
	
}


