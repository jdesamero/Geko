<?php
/*
 * "geko_core/library/Geko/Wp/Hooks.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * add more useful hooks
 */

//
class Geko_Wp_Hooks
{
	protected static $bCalledInit = FALSE;
	protected static $bIsBuffering = FALSE;
	
	protected static $bFixHttpsTheme = FALSE;
	protected static $bFixHttpsAdmin = FALSE;
	
	
	
	//
	public static function init() {
		
		if ( !self::$bCalledInit ) {
			
			add_action( 'template_redirect', array( __CLASS__, 'bufferStartTheme' ) );
			add_action( 'get_footer', array( __CLASS__, 'bufferEndTheme' ), 0 );
			
			add_action( 'admin_init', array( __CLASS__, 'bufferStartAdmin' ) );
			add_action( 'shutdown', array( __CLASS__, 'bufferEndAdmin' ), 0 );
			
			// only apply if already in https
			if ( Geko_Uri::isHttps() ) {
				
				if ( self::$bFixHttpsTheme ) {
					add_filter( 'theme_page_source', array( __CLASS__, 'fixHttpsFilter' ) );		
				}
				
				if ( self::$bFixHttpsAdmin ) {
					add_filter( 'admin_page_source', array( __CLASS__, 'fixHttpsFilter' ) );				
				}
				
			}
			
			self::$bCalledInit = TRUE;
		}
	}
	
	
	
	//
	public static function bufferStartTheme() {
		
		self::$bIsBuffering = ( has_action( 'theme_page_source' ) ) ? TRUE : FALSE ;
		
		if ( self::$bIsBuffering ) ob_start();
	}
	

	//
	public static function bufferEndTheme() {
		
		if ( self::$bIsBuffering && did_action( 'template_redirect' ) ) {
			
			$sSource = ob_get_contents();
			ob_end_clean();

			if ( has_filter( 'theme_page_source' ) ) {
				$sSource = apply_filters( 'theme_page_source', $sSource );
			}
			
			echo $sSource;
		}
	}
	
	
	//
	public static function bufferStartAdmin() {
		
		self::$bIsBuffering = (
			has_action( 'admin_body_header' ) ||
			has_action( 'admin_body_footer' ) ||
			has_filter( 'admin_page_source' )
		) ? TRUE : FALSE ;
		
		if ( self::$bIsBuffering ) ob_start();
	}
	
	//
	public static function bufferEndAdmin() {
		
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
	
	
	
	
	
	//
	public static function inject( $sAction, $sSource, $sPatternMatch, $sPatternReplace = '{INJECT}' ) {
		
		ob_start();
		do_action( $sAction );
		$sInject = ob_get_contents();
		ob_end_clean();
		
		return preg_replace( $sPatternMatch, str_replace( '{INJECT}', $sInject, $sPatternReplace ), $sSource );
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
					
					function() use( $sAction ) {
						Geko_Hooks::doAction( $sAction );
					},
					
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
					
					function() use( $sFilter ) {

						$aSubject = func_get_args();
						
						if ( count( $aSubject ) == 1 ) {
							$sRes = Geko_Hooks::applyFilter( $sFilter, $aSubject[ 0 ] );
						} else {
							$aRes = Geko_Hooks::applyFilter( $sFilter, $aSubject );
							$sRes = $aRes[ 0 ];
						}
						
						return $sRes;
					},
					
					$iPriority,
					$iAcceptedArgs
				);
			}
		}
		
	}
	
	
	//// helpers
	
	//
	public static function setFixHttps( $bFixHttpsTheme = TRUE, $bFixHttpsAdmin = TRUE ) {
		self::$bFixHttpsTheme = $bFixHttpsTheme;
		self::$bFixHttpsAdmin = $bFixHttpsAdmin;
	}
	
	
	// filter that replaces http references in document from http to https if url is https
	public static function fixHttpsFilter( $sSource ) {
		
		$oUrl = Geko_Uri::getGlobal();
		
		$sServerName = $oUrl->getHost();
		
		$sHttpsServer = sprintf( 'https://%s', $sServerName );
		$sHttpServer = sprintf( 'http://%s', $sServerName );
		
		$aRegs = array();
		
		// get matching opening tag with src=http://
		if ( preg_match_all( '/<([a-z]+)([^>]+)((src|href)=[\'"])http\:\/\/([^>]+)>/i', $sSource, $aRegs ) ) {
			
			$aFull = $aRegs[ 0 ];
			$aTags = $aRegs[ 1 ];
			
			foreach ( $aTags as $i => $sTag ) {
				
				$sTagNorm = strtolower( $sTag );
				
				$sFull = $aFull[ $i ];
				$sFullNorm = strtolower( $sFull );
				
				// don't replace regular links (<a>) that are not in the same domain
				if (
					( 'a' != $sTagNorm ) || 
					(
						( 'a' == $sTagNorm ) && 
						( FALSE !== strpos( $sFullNorm, $sServerName ) )
					)
				) {
					$sReplace = str_ireplace( 'http://', 'https://', $sFull );
					$sSource = str_replace( $sFull, $sReplace, $sSource );
				}
			}
		}
		
		// any straglers need to be replaced for sure
		return str_replace( $sHttpServer, $sHttpsServer, $sSource );
	}
	
	
}


