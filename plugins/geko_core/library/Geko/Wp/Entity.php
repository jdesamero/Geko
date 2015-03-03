<?php

//
abstract class Geko_Wp_Entity extends Geko_Entity
{
	
	//
	protected $_aMetaHandlers = array();
	
	
	
	//
	public function init() {
		
		parent::init();
		
		do_action( sprintf( '%s::init', $this->_sEntityClass ), $this );
		
		return $this;
		
	}
	
	
	
	//// helper accessors
	
	// getContent() with the_content filters applied
	public function getTheContent() {
		return apply_filters( 'the_content', parent::getTheContent() );
	}
	
	//
	public function getEditUrl() {
		
		$sUrl = sprintf( '%s?page=%s&%s=%d',
			Geko_Uri::getUrl( 'wp_admin' ), $this->_sManageClass, $this->_sEditEntityIdVarName, $this->getId()
		);
		
		if ( $iParentEntityId = $this->getParentEntityId() ) {
			$sUrl .= sprintf( '&%s=%d', $this->_sEditParentEntityIdVarName, $iParentEntityId );
		}
		
		return $sUrl;
	}
	
	// a function that is aware of current logged in user
	public function getTheEditUrl() {
		
		if ( $this->_sManageClass ) {
			$oAdmin = Geko_Singleton_Abstract::getInstance( $this->_sManageClass );
			if ( $oAdmin->userHasManagementCapability() ) {
				return $this->getEditUrl();
			} else {
				return '';
			}
		} else {
			return '';
		}
	}
	
	
	
	
	//
	public function escapeHtml( $sValue ) {
		return wp_specialchars( $sValue, 1 );
	}
	
	
	//
	public function addMetaHandler( $mMetaHandler, $aParams = array() ) {
		
		$oMetaHandler = NULL;
		
		if ( is_string( $mMetaHandler ) ) {
			if ( is_subclass_of( $mMetaHandler, 'Geko_Singleton_Abstract' ) ) {
				$oMetaHandler = Geko_Singleton_Abstract::getInstance( $mMetaHandler );
			} else {
				$oMetaHandler = array( $mMetaHandler, $aParams );		// lazily instantiate
			}
		} elseif ( is_object( $mMetaHandler ) ) {
			$oMetaHandler = $mMetaHandler;
		}
		
		if ( $oMetaHandler ) $this->_aMetaHandlers[] = $oMetaHandler;
		
		return $this;
	}
	
	
	//// helper methods
	
	//
	public function dateTimeFormat( $sSqlDateTime, $sFormat ) {
		$sFormat = Geko_String::coalesce(
			$sFormat,
			sprintf( '%s %s', get_option( 'date_format' ), get_option( 'time_format' ) )
		);
		return $this->mysql2Date( $sSqlDateTime, $sFormat );
	}
	
	//
	public function dateFormat( $sSqlDateTime, $sFormat = '' ) {
		$sFormat = Geko_String::coalesce( $sFormat, get_option( 'date_format' ) );
		return $this->mysql2Date( $sSqlDateTime, $sFormat );
	}
	
	//
	public function timeFormat( $sSqlDateTime, $sFormat = '' ) {
		$sFormat = Geko_String::coalesce( $sFormat, get_option( 'time_format' ) );
		return $this->mysql2Date( $sSqlDateTime, $sFormat );
	}
	
	//
	public function mysql2Date( $sSqlDateTime, $sFormat ) {
		return mysql2date( $sFormat, $sSqlDateTime );	
	}
	
	
	//
	public function getProperyTranslation( $aParams ) {
		
		$oResolver = Geko_Wp_Language_Resolver::getInstance();
		$oLangMgmt = Geko_Wp_Language_Manage::getInstance();
		
		$sCurLang = $oResolver->getCurLang( FALSE );
		$sDefLang = $oLangMgmt->getDefLangCode();
		
		return Geko_String::coalesce(
			$this->getEntityPropertyValue( $aParams[ $sCurLang ] ),
			$this->getEntityPropertyValue( $aParams[ $sDefLang ] )
		);
	}
	
	
	//
	public function getUrlTranslation( $mUrl ) {
		
		if ( is_object( $mUrl ) ) {
			$oUrl = $mUrl;
			$bObject = TRUE;
		} else {
			$oUrl = new Geko_Uri( $mUrl );
			$bObject = FALSE;
		}
		
		$oResolver = Geko_Wp_Language_Resolver::getInstance();
		if ( $sLangCode = $oResolver->getCurLang() ) {
			$oUrl->setVar( 'lang', $sLangCode );
		}
		
		return ( $bObject ) ? $oUrl : strval( $oUrl );
	}
	
	
	//
	public function getMeta( $sMetaKey ) {
		
		foreach ( $this->_aMetaHandlers as $i => $oMeta ) {
			
			if ( is_array( $oMeta ) ) {
				$oMeta[ 1 ][ 'object_id' ] = $this->getId();			// hackish
				$oMeta = call_user_func( array( $oMeta[ 0 ], 'getOne' ), $oMeta[ 1 ] );
				$this->_aMetaHandlers[ $i ] = $oMeta;
			}
			
			if ( $oMeta instanceof Geko_Wp_Options_Meta ) {
				if ( $mMeta = $oMeta->getMeta( $this->getId(), $sMetaKey, TRUE ) ) {
					return $mMeta;
				}
			} elseif ( is_object( $oMeta ) ) {
				if ( $mMeta = $oMeta->getValue( $sMetaKey ) ) {
					return $mMeta;
				}			
			}
		}
		
		return parent::getMeta( $sMetaKey );
	}
	
	//
	public function getMetaMemberIds( $sMetaKey ) {
		
		$mRes = $this->getMeta( $sMetaKey );
		
		if (
			( is_array( $mRes ) ) && 
			( $oItem = $mRes[ 0 ] ) && 
			( property_exists( $oItem, 'member_id' ) )
		) {
			$aRet = array();
			foreach ( $mRes as $oItem ) $aRet[] = $oItem->member_id;
			return $aRet;
		}
		
		return $mRes;
	}
	
	
	
	
	//// image handling methods
	
	
	//
	public function _getBaseUrl() {
		return Geko_Wp::getUrl();
	}
	
	//
	public function _getBasePath() {
		return GEKO_WP_ABSPATH;
	}
	
	//
	public function _getTheImageUrl( $sValue, $sProp, $aArgs ) {		
		if ( $sFile = $this->_getFilePath( $sValue, $sProp, $aArgs ) ) {
			
			$aParams = $aArgs[ 0 ];
			$aParams[ 'src' ] = $sFile;
			
			if ( !$aParams[ 'placeholder' ] ) $aParams[ 'placeholder' ] = '##tmpl_dir##/images/placeholder_thumb.gif';
			if ( !$aParams[ 'placeholder_missing' ] ) $aParams[ 'placeholder_missing' ] = '##tmpl_dir##/images/placeholder_thumb_missing.gif';
			
			return Geko_Wp::getThumbUrl( $aParams );
		}
		return '';	
	}
	
	
	
	// convenience methods for extracting image urls
	
	// get one
	public function getPhotoPath( $sMetaKey, $sMethod = 'getPhotoUrl' ) {
		foreach ( $this->_aMetaHandlers as $oMeta ) {
			if (
				( $oMeta instanceof Geko_Wp_Options_Meta ) && 
				( $oMeta->getHasFileUpload() ) && 
				( $sPhotoUrl = $oMeta->$sMethod( $this->getId(), $sMetaKey ) )
			) {
				return $sPhotoUrl;
			}
		}
	}
	
	// get all
	public function getPhotoPaths( $sMethod = 'getPhotoUrls' ) {
		$aRes = array();
		foreach ( $this->_aMetaHandlers as $oMeta ) {
			if (
				( $oMeta instanceof Geko_Wp_Options_Meta ) && 
				( $oMeta->getHasFileUpload() )
			) {
				$aRes = array_merge( $aRes, $oMeta->$sMethod( $this->getId() ) );
			}
		}
		return $aRes;
	}
	
	//
	public function getPhotoUrl( $sMetaKey = '' ) {
		return $this->getPhotoPath( $sMetaKey );
	}
	
	//
	public function getPhotoUrls() {
		return $this->getPhotoPaths();	
	}

	//
	public function getPhotoDoc( $sMetaKey = '' ) {
		return $this->getPhotoPath( $sMetaKey, 'getPhotoDoc' );	
	}
	
	//
	public function getPhotoDocs() {
		return $this->getPhotoPaths( 'getPhotoDocs' );	
	}
	
	
	
	//
	public function getThePhotoUrl( $aParams ) {
		
		if ( $sPhoto = $this->getPhotoDoc( $aParams[ 'meta_key' ] ) ) {
			
			return $this->buildThumbUrl( $sPhoto, $aParams );
		}
		
		if ( $aParams[ 'noplaceholder' ] ) return '';
		
		$sPlaceholderImage = ( $aParams[ 'placeholder' ] ) ? $aParams[ 'placeholder' ] : '##tmpl_dir##/images/placeholder.gif';
		$sPlaceholderImage = str_replace( array( '##tmpl_dir##', '##template_directory##' ), get_bloginfo( 'template_directory' ), $sPlaceholderImage );
		
		return $sPlaceholderImage;
	}
	
	
	//
	public function getThePhotoUrls( $aParams ) {
		
		$aPhotos = array();
		
		$aPhotoDocs = $this->getPhotoDocs();
		
		foreach ( $aPhotoDocs as $sPhoto ) {
			
			$aPhotos[] = $this->buildThumbUrl( $sPhoto, $aParams );
		}
		
		return $aPhotos;
	}
	
	
	
	// helper
	public function buildThumbUrl( $sPhoto, $aParams ) {
		
		$bPermalink = $aParams[ 'permalink' ];
		$sMetaKey = $aParams[ 'meta_key' ];
		
		
		// resolve image url to absolute file system path
		$aParams[ 'src' ] = Geko_String_Path::getUrlToFile( $sPhoto );
		
		
		$oThumb = new Geko_Image_Thumb( $aParams );
		
		if ( $bPermalink && $sMetaKey ) {
			
			$aThumbInfo = $oThumb->get();
			
			$aPath = pathinfo( $sPhoto );
			
			$sThumbFileName = sprintf(
				'%s-%dx%d.%s',
				$aPath[ 'filename' ],
				$aThumbInfo[ 'width' ],
				$aThumbInfo[ 'height' ],
				$aPath[ 'extension' ]
			);
			
			$sPermDoc = sprintf( '%s/%s', $aPath[ 'dirname' ], $sThumbFileName );
			
			if ( !is_file( $sPermDoc ) ) {
				// attempt to save
				$oThumb->savePermanentFile( $sPermDoc );
			}
			
			if ( is_file( $sPermDoc ) ) {
				
				$sOrigUrl = $this->getPhotoUrl( $sMetaKey );
				
				return sprintf( '%s/%s', dirname( $sOrigUrl ), $sThumbFileName );
			}
			
		}
		
		return $oThumb->buildThumbUrl( Geko_Uri::getUrl( 'geko_thumb' ) );
	}
	
	
	// alias of buildThumbUrl()
	public function getThumbUrl( $sImgUrl, $aParams ) {
		return $this->buildThumbUrl( $sImgUrl, $aParams );
	}
	
	
	
	
	
	//// formatting helper methods
	
	//
	public function wpautopFormat() {
		$aArgs = func_get_args();
		$sValue = call_user_func_array( array( $this, 'getValue' ), $aArgs );
		return wpautop( trim( $sValue ) );
	}
	
	
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		// allow for filtering of dynamic get*() calls
		if ( 0 === strpos( $sMethod, 'get' ) ) {
			
			$mRes = NULL;
			
			if ( !$mRes = parent::__call( $sMethod, $aArgs ) ) {
				$aRegs = array();
				if ( preg_match( '/getThe([a-zA-Z0-9]+)Url/', $sMethod, $aRegs ) ) {
					$sCall = sprintf( 'theimageurl%s', $aRegs[ 1 ] );
					$mRes = $this->__call( $sCall, $aArgs );
				}
			}
			
			$aFilterArgs = array_merge(
				array( sprintf( '%s::%s', get_class( $this ), $sMethod ), $mRes, $this ),
				$aArgs
			);
			
			$mRes = call_user_func_array( 'apply_filters', $aFilterArgs );
			
			return $mRes;	
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'theimageurl' ) ) {
			
			// return the full URL, assuming entity property corresponds to a file
			$sCall = substr_replace( $sMethod, 'thisget', 0, 11 );
			$sProp = substr_replace( $sMethod, '', 0, 11 );
			
			return $this->_getTheImageUrl( $this->__call( $sCall, $aArgs ), $sProp, $aArgs );
			
		} elseif ( 0 === strpos( $sMethod, 'thisget' ) ) {
			
			if ( preg_match( '/thisgetThe([a-zA-Z0-9]+)Url/', $sMethod, $aRegs ) ) {
				
				$sCall = sprintf( 'getThe%sUrl', $aRegs[ 1 ] );
				
				if ( !method_exists( $this, $sCall ) ) {
					return $this->__call( $sCall, $aArgs );
				}
			}
			
		}
		
		return parent::__call( $sMethod, $aArgs );
	}
	
	
	
	
	
	//// rail functionality
	
	//
	public function renderDetail() {
		if ( $this->_sManageClass ) {
			$oMng = Geko_Singleton_Abstract::getInstance( $this->_sManageClass );
			$oMng->renderDetail( $this );
		}	
	}
	
	//
	public function renderDetailForm() {
		if ( $this->_sManageClass ) {
			$oMng = Geko_Singleton_Abstract::getInstance( $this->_sManageClass );
			$oMng->renderDetailForm( $this );
		}	
	}
	
	
	
}



