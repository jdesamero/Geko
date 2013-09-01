<?php

// static class container for WP functions for Geek Oracle themes
class Geko_Wp
{	
	
	private static $bUseIsHome = TRUE;
	private static $aStandardPlaceholders = FALSE;
	private static $bInitLoader = FALSE;
	
	
	//
	public static function setUseIsHome( $bUseIsHome ) {
		self::$bUseIsHome = $bUseIsHome;
	}
	
	//
	public static function slugify( $sValue ) {
		$sValue = trim( $sValue );
		$sValue = self::htmlDecode( $sValue );
		$sValue = str_replace( "'", '', $sValue );
		$sValue = str_replace( array( '&', '-' ), ' ', $sValue );
		return strtolower( preg_replace( '/[\s]+/', '-', $sValue ) );
	}
	
	// TO DO: wp_specialchars()
	public static function htmlDecode( $sValue ) {
		return str_replace( array( '&amp;', '&#038;' ), '&', $sValue );
	}
	
	//
	public static function getTitleTag( $sTitle ) {
		
		$sRet = ( $sTitle ? $sTitle : get_bloginfo( 'name' ) ) . ' | ';
		
		if ( self::isHome() ) {
			$sRet .= get_bloginfo('description');
		} elseif ( is_search() ) {
			$sRet .= 'Search Results';
		} elseif ( is_author() ) {
			$oAuthor = new Geko_Wp_Author();
			$sRet .= 'Author Archives | ' . $oAuthor->getFullName();
		} elseif ( is_single() || is_page() ) {
			$sRet .= wp_title('', FALSE);
		} elseif ( is_category() ) {
			$oCat = new Geko_Wp_Category();
			$sRet .= 'Category Archives | ' . $oCat->getTitle();
		} elseif ( is_month() ) {
			$sRet .= 'Monthly Archives | ' . get_the_time( 'F Y' );
		} elseif ( is_year() ) {
			$sRet .= 'Yearly Archives | ' . get_the_time( 'Y' );
		} elseif ( ( function_exists( 'is_tag' ) ) && ( is_tag() ) ) {
			$oTag = new Geko_Wp_Tag();
			$sRet .= 'Tag Archives | ' . $oTag->getTitle();
		} elseif ( is_404() ) {
			$sRet .= 'Not Found';
		}
		
		return $sRet;
	}
	
	//
	public static function getBodyClass( $sClass ) {
		
		if ( function_exists( 'get_body_class' ) ) {
			
			$sRet = implode( ' ', get_body_class( $sClass ) );
			
		} else {
			
			$sRet = ( $sClass ? $sClass . ' ' : '' );
			
			if ( self::isHome() ) {
				$sRet .= 'home';
			} elseif ( is_search() ) {
				$sRet .= 'search';
			} elseif ( is_author() ) {
				$sRet .= 'author';
			} elseif ( is_single() ) {
				$sRet .= 'single';
			} elseif ( is_page() ) {
				$sRet .= 'page ' . self::slugify( wp_title('', FALSE) );
			} elseif ( is_category() ) {
				$sRet .= 'category ' . self::slugify( single_cat_title('', FALSE) );
			} elseif ( is_month() ) {
				$sRet .= 'month';
			} elseif ( ( function_exists( 'is_tag' ) ) && ( is_tag() ) ) {
				$sRet .= 'tag';
			} elseif ( is_404() ) {
				$sRet .= 'error404';
			}
			
		}
		
		return $sRet;
	}
	
	//
	public static function getPostClass( $sClass ) {
		
		if ( function_exists( 'get_post_class' ) ) {
			$sRet = implode( ' ', get_post_class( $sClass ) );
		} else {
			$sRet = ( $sClass ? $sClass . ' ' : '' );
		}
		
		return $sRet;
		
	}
	
	//
	public static function getCommentClass( $sClass ) {
	
	}
	
	//
	public static function is( $mParams ) {
		
		if ( is_string( $mParams ) ) {
			$aParams = Geko_Array::explodeTrimEmpty( '|', $mParams );
			foreach ( $aParams as $i => $sSubParam ) {
				$aSubParam = Geko_Array::explodeTrimEmpty( ':', $sSubParam );
				if ( $aSubParam[ 1 ] ) {
					$aSubParam[ 1 ] = Geko_Array::explodeTrimEmpty( ',', $aSubParam[ 1 ] );
					if (
						( !in_array( $aSubParam[ 0 ], array( 'page_template', 'category_template', 'category_post_template' ) ) ) && 
						( 1 == count( $aSubParam[ 1 ] ) )
					) {
						$aSubParam[ 1 ] = $aSubParam[ 1 ][ 0 ];
					}
				}
				$aParams[ $i ] = $aSubParam;
			}
		} else {
			$aParams = $mParams;
		}
		
		foreach ( $aParams as $aSubParam ) {
			
			$sCond = $aSubParam[ 0 ];
			
			if ( 'page_template' == $sCond ) {
				
				foreach ( $aSubParam[ 1 ] as $sTemplate ) {
					if ( is_page_template( $sTemplate ) ) return TRUE;
				}
			
			} elseif ( 'category_template' == $sCond ) {
				
				foreach ( $aSubParam[ 1 ] as $sTemplate ) {
					if ( Geko_Wp_Category_Template::getInstance()->isTemplate( $sTemplate ) ) return TRUE;
				}
				
			} elseif ( 'category_post_template' == $sCond ) {
				
				foreach ( $aSubParam[ 1 ] as $sTemplate ) {
					if ( Geko_Wp_Category_PostTemplate::getInstance()->isTemplate( $sTemplate ) ) return TRUE;
				}
				
			} elseif ( 'rewrite' == $sCond ) {
				
				$sCheck1 = $aSubParam[ 1 ];
				$sCheck2 = 'Wp_' . $aSubParam[ 1 ] . '_Rewrite';
				$sCheck3 = 'Geko_Wp_' . $aSubParam[ 1 ] . '_Rewrite';
				
				if (
					( $sClass = Geko_Class::existsCoalesce( $sCheck1, $sCheck2, $sCheck3 ) ) && 
					( is_subclass_of( $sClass, 'Geko_Wp_Rewrite_Interface' ) )
				) {
					$oRewrite = Geko_Singleton_Abstract::getInstance( $sClass );
					if ( $sMethod = $aSubParam[ 2 ] ) {
						if ( $oRewrite->$sMethod() ) return TRUE;
					} else {
						if ( $oRewrite->isList() ) return TRUE;
						if ( $oRewrite->isSingle() ) return TRUE;
					}
				}
				
			} else {
				
				$fTest = 'is_' . $sCond;
				if (
					( $aSubParam[ 1 ] ) ? $fTest( $aSubParam[ 1 ] ) : $fTest()
				) return TRUE;
				
			}
			
		}
		
		return FALSE;
	}
	
	//
	public static function version() {
		return explode( '.', get_bloginfo('version') );
	}
	
	
	//
	public static function getSessionPath() {
		
		$sPath = parse_url( get_bloginfo( 'url' ), PHP_URL_PATH );
		
		if ( !$sPath ) {
			$sPath = '/';
		} else {
			$sPath = '/' . trim( $sPath, '/' ) . '/';
		}
		
		return $sPath;
	}
	
	
	
	//// homepage functions
	
	//
	public static function isHome( $sInvokerClass = NULL ) {
		
		static $bRet = FALSE;
		static $bOnce = FALSE;
		
		if ( FALSE == $bOnce ) {	
			
			$bTitleIsHome = ( 'home' == trim( strtolower( wp_title( '', FALSE ) ) ) );
			
			if ( function_exists( 'is_front_page' ) ) {
				$bIsFrontPage = is_front_page();
			} else {
				$bIsFrontPage = FALSE;
			}
			
			$bIsHome = ( self::$bUseIsHome ) ? is_home() : FALSE ;
			
			$bRet = ( $bIsHome || $bTitleIsHome || $bIsFrontPage );			
			$bOnce = TRUE;
		}
		
		return $bRet;
	}
	
	//
	public static function getHomepageId( $sInvokerClass = NULL ) {
		return apply_filters( __METHOD__, get_option( 'page_on_front' ), $sInvokerClass );
	}
	
	//
	public static function getHomepageTitle( $sInvokerClass = NULL ) {
		
		$sRes = 'Home';
		$bIsPage = FALSE;
		
		if ( 'page' == get_option( 'show_on_front' ) ) {
			if ( $iPageId = self::getHomepageId( $sInvokerClass ) ) {
				$sRes = get_the_title( $iPageId );
				$bIsPage = TRUE;
			}
		}
		
		return apply_filters( __METHOD__, $sRes, $bIsPage, $iPageId, $sInvokerClass );
	}
	
	//
	public static function getHomepageUrl( $sInvokerClass = NULL ) {
		return apply_filters( __METHOD__, get_bloginfo( 'url' ) . '/', $sInvokerClass );
	}
	
	// ensures get_query_var( 'paged' ) is set correctly when in the homepage
	public static function pageHome() {
		
		if ( self::isHome() ) {
			
			$oUrl = Geko_Uri::getGlobal();
			$sUrl = strval( $oUrl );
			$aRegs = array();
			
			if ( preg_match( '/page\/([0-9]+)/', $sUrl, $aRegs ) ) {
				set_query_var( 'paged', $aRegs[1] );
			}
			
		}
		
	}
	
	
	
	// wordpress layer for generating thumbnail image urls
	public static function getThumbUrl( $aParams ) {
		
		if ( $sImageFullPath = trim( $aParams[ 'src' ] ) ) {
			$aPath = pathinfo( $sImageFullPath );
			$sImageFileName = $aPath[ 'basename' ];
			$sImageSrcDir = $aPath[ 'dirname' ];
		} else {
			$sImageFileName = trim( $aParams[ 'image_file_name' ] );
			$sImageSrcDir = trim( $aParams[ 'image_src_dir' ] );
			if ( $sImageFileName ) {
				$sImageFullPath = $sImageSrcDir . basename( $sImageFileName );			
			}
		}
		
		$sPlaceholder = $aParams[ 'placeholder' ];
		$sPlaceholderMissing = $aParams[ 'placeholder_missing' ];
		
		if ( $sImageFileName ) {
			
			if ( is_file( $sImageFullPath ) ) {
				
				$aParams[ 'src' ] = urlencode( $sImageFullPath );
				$oThumb = new Geko_Image_Thumb( $aParams );
				
				return $oThumb->buildThumbUrl( Geko_Uri::getUrl( 'geko_thumb' ) );
				
			} else {
				
				// actual image file could not be found (the above check failed), so image is missing
				if ( $aParams[ 'noplaceholder_missing' ] ) return '';
				
				$sPlaceholderMissingImage = ( $sPlaceholderMissing ) ? $sPlaceholderMissing : '##tmpl_dir##/images/placeholder_missing.gif';
				$sPlaceholderMissingImage = str_replace( array( '##tmpl_dir##', '##template_directory##' ), get_bloginfo( 'template_directory' ), $sPlaceholderMissingImage );
				
				return $sPlaceholderMissingImage;
				
			}
			
		}
		
		if ( $aParams[ 'noplaceholder' ] ) return '';
		
		$sPlaceholderImage = ( $sPlaceholder ) ? $sPlaceholder : '##tmpl_dir##/images/placeholder.gif';
		$sPlaceholderImage = str_replace( array( '##tmpl_dir##', '##template_directory##' ), get_bloginfo( 'template_directory' ), $sPlaceholderImage );
		
		return $sPlaceholderImage;
	}
	
	//
	public static function registerNavMenu( $aNavMenu ) {
		if (
			!class_exists( 'Geko_Wp_NavigationManagement' ) && 
			function_exists( 'wp_nav_menu' )
		) {
			register_nav_menus( $aNavMenu );
		}
	}
	
	
	// register styles and scripts
	public static function registerExternalFiles( $sFile ) {
		
		$oLoader = Geko_Loader_ExternalFiles::getInstance();
		
		// call this once
		if ( !self::$bInitLoader ) {
			$oLoader->setMergeParams( self::getStandardPlaceholders() );
			self::$bInitLoader = TRUE;
		}
		
		$oLoader->registerFromXmlConfigFile( $sFile, array(
			'script' => 'wp_register_script',
			'style' => 'wp_register_style'
		) );
		
	}
	
	
	//
	public static function initStandardPlaceholders() {
		
		if ( !self::$aStandardPlaceholders ) {
			
			$aRet = array();
			
			// bloginfo() convenience merge params
			$aBloginfo = array(
				'url', 'name', 'description', 'admin_email',
				'stylesheet_url', 'stylesheet_directory',
				'template_url', 'template_directory'
			);
			
			foreach ( $aBloginfo as $sBloginfo ) {
				$aRet[ '__bloginfo_' . $sBloginfo ] = get_bloginfo( $sBloginfo );
			}
			
			$aRet[ '__bloginfo_server' ] = parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
			
			self::$aStandardPlaceholders = $aRet;
		}
	}
	
	//
	public static function setStandardPlaceholders() {
		
		self::initStandardPlaceholders();
		
		$aArgs = func_get_args();
		
		if ( is_array( $aArgs[ 0 ] ) ) {
			self::$aStandardPlaceholders = array_merge(
				self::$aStandardPlaceholders, $aArgs[ 0 ]
			);
		} else {
			self::$aStandardPlaceholders[ $aArgs[ 0 ] ] = $aArgs[ 1 ];
		}
	}
	
	//
	public static function getStandardPlaceholders() {
		
		// creates an associative array for standard placeholder replacement:
		// array( '__bloginfo_url' => <some val>, ... )
		
		if ( !function_exists( 'get_bloginfo' ) ) return array();
		
		self::initStandardPlaceholders();
		
		return self::$aStandardPlaceholders;
	}
	
	//
	public static function getScriptUrls( $aOther = NULL ) {
		
		$oUrl = Geko_Uri::getGlobal();
		
		$aRet = array(

			'export' => Geko_Uri::getUrl( 'geko_export' ),
			'pdf' => Geko_Uri::getUrl( 'geko_pdf' ),
			'process' => Geko_Uri::getUrl( 'geko_process' ),
			'thumb' => Geko_Uri::getUrl( 'geko_thumb' ),
			'upload' => Geko_Uri::getUrl( 'geko_upload' ),
			
			'styles' => Geko_Uri::getUrl( 'geko_styles' ),
			'ext_styles' => Geko_Uri::getUrl( 'geko_ext_styles' ),
			'ext_swf' => Geko_Uri::getUrl( 'geko_ext_swf' ),
			
			'curpage' => strval( $oUrl ),
			'template_dir' => get_bloginfo( 'template_directory' ),
			'url' => get_bloginfo( 'url' )

		);
		
		if ( is_array( $aOther ) ) {
			$aRet = array_merge( $aRet, $aOther );
		}
		
		return $aRet;	
	}
	
	
}



