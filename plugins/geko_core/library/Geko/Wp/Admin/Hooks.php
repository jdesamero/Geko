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
	public static function init() {
		
		if ( !self::$bCalledInit ) {
			
			if ( is_admin() ) {
				
				// generate list of plugin classes
				$aPlugins = array_map(
					create_function(
						'$sVal',
						'return "' . __CLASS__ . '_" . str_replace( ".php", "", $sVal );'
					),
					array_diff(
						scandir( dirname( __FILE__ ) . '/Hooks' ),
						array( '.', '..', 'PluginAbstract.php' )
					)				
				);
								
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
						self::$sDisplayMode = str_replace( self::$aStates[ 0 ] . '_', '', self::$aStates[ 1 ] );
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
				$sAction = 'admin_' . $sType . '_' . $sState;
				do_action( $sAction );
			}
		}
	}
	
	
	//
	public static function applyFilters( $sContent ) {
		
		if ( self::$aStates ) {
			foreach ( self::$aStates as $sState ) {
				add_filter( 'admin_page_source_' . $sState, array( self::$oCurrentPlugin, 'applyFilters' ), 10, 2 );
				$sContent = apply_filters( 'admin_page_source_' . $sState, $sContent, $sState );
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


