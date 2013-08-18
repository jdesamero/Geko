<?php

// add more useful hooks
class Geko_Wp_Hooks
{
	protected static $bCalledInit = FALSE;
	protected static $bIsBuffering = FALSE;
	
	
	//
	public static function init() {
		
		if ( !self::$bCalledInit ) {
			add_action( 'admin_init', array( __CLASS__, 'bufferStart' ) );
			add_action( 'shutdown', array( __CLASS__, 'bufferEndAdminInit' ), 0 );
			
			self::$bCalledInit = TRUE;
		}
	}
	
	//
	public static function bufferStart() {
		
		self::$bIsBuffering = (
			has_action( 'admin_body_header' ) ||
			has_action( 'admin_body_footer' ) ||
			has_filter( 'admin_page_source' )
		);
		
		if ( self::$bIsBuffering ) ob_start();
	}
	
	//
	public static function inject( $sAction, $sSource, $sPatternMatch, $sPatternReplace = '{INJECT}' ) {
		
		ob_start();
		do_action( $sAction );
		$sInject = ob_get_contents();
		ob_end_clean();
		
		return preg_replace( $sPatternMatch, str_replace( '{INJECT}', $sInject, $sPatternReplace ), $sSource );
	}
	
	//
	public static function bufferEndAdminInit() {
		
		if ( self::$bIsBuffering && did_action( 'admin_init' ) ) {
			
			$sSource = ob_get_contents();
			ob_end_clean();
			
			if ( has_action( 'admin_body_header' ) ) {
				$sSource = self::inject( 'admin_body_header', $sSource, '/<body.*?>/is', '\0{INJECT}' );
			}
			
			if ( has_action( 'admin_body_footer' ) ) {
				$sSource = self::inject( 'admin_body_footer', $sSource, '/<\/body>/is', '{INJECT}\0' );
			}
			
			if ( has_filter( 'admin_page_source' ) ) {
				$sSource = apply_filters( 'admin_page_source', $sSource );
			}
			
			echo $sSource;
			
		}
	}
	
	
	// create equivalent Wordpress hooks to Geko_Hooks
	
	// according to: http://codex.wordpress.org/Function_Reference/add_action
	// add_action(Ê$tag,Ê$function_to_add,Ê$priority = 10,Ê$accepted_args = 1Ê);
	public static function attachGekoHookActions() {
		
		static $aHooked = array();
		
		$aActions = func_get_args();
		foreach ( $aActions as $mAction ) {
			
			if ( is_array( $mAction ) ) {
				$sAction = Geko_String::coalesce( $mAction[ 'tag' ], $mAction[ 0 ] );
				$iPriority = Geko_String::coalesce( $mAction[ 'priority' ], $mAction[ 1 ] );
				$iAcceptedArgs = Geko_String::coalesce( $mAction[ 'accepted_args' ], $mAction[ 2 ] );
			} else {
				$sAction = $mAction;
				$iPriority = 10;
				$iAcceptedArgs = 1;
			}
			
			// only hook once
			if ( !isset( $aHooked[ $sAction ] ) ) {
				
				$aHooked[ $sAction ] = 1;
				
				add_action(
					$sAction,
					create_function( '', "Geko_Hooks::doAction('" . $sAction . "');" ),
					$iPriority,
					$iAcceptedArgs
				);
			}
			
		}
	}
	

	// according to: http://codex.wordpress.org/Function_Reference/add_filter
	// add_filter(Ê$tag,Ê$function_to_add,Ê$priority = 10,Ê$accepted_args = 1Ê);
	public static function attachGekoHookFilters() {
		
		static $aHooked = array();
		
		$aFilters = func_get_args();
		
		foreach ( $aFilters as $mFilter ) {
			
			if ( is_array( $mFilter ) ) {
				$sFilter = Geko_String::coalesce( $mFilter[ 'tag' ], $mFilter[ 0 ] );
				$iPriority = Geko_String::coalesce( $mFilter[ 'priority' ], $mFilter[ 1 ] );
				$iAcceptedArgs = Geko_String::coalesce( $mFilter[ 'accepted_args' ], $mFilter[ 2 ] );
			} else {
				$sFilter = $mFilter;
				$iPriority = 10;
				$iAcceptedArgs = 1;
			}
			
			// only hook once
			if ( !isset( $aHooked[ $sFilter ] ) ) {
				
				$aHooked[ $sFilter ] = 1;
				
				add_filter(
					$sFilter,
					create_function(
						'',
						'
							$aSubject = func_get_args();
							if ( count( $aSubject ) == 1 ) {
								$sRes = Geko_Hooks::applyFilter( \'' . $sFilter . '\', $aSubject[ 0 ] );
							} else {
								$aRes = Geko_Hooks::applyFilter( \'' . $sFilter . '\', $aSubject );
								$sRes = $aRes[ 0 ];
							}
							return $sRes;
						'
					),
					$iPriority,
					$iAcceptedArgs
				);
			}
		}
		
	}
	
	
}

