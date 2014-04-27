<?php

//
class Geko_Wp_Layout extends Geko_Layout
{
	
	const LANG_URL = 1;
	const LANG_REPLACE = 2;							// replacement pattern is stripped if in the default language
	const LANG_FORCE_REPLACE = 3;					// replacement pattern is set always
	
	
	
	protected $_aUnprefixedActions = array(
		'get_header', 'wp_head', 'get_sidebar', 'wp_footer', 'get_footer'
	);
	protected $_aUnprefixedFilters = array( 'body_class', 'post_class' );
	
	protected $_sRenderer = 'Geko_Wp_Layout_Renderer';
	protected $_aPrefixes = array( 'Gloc_', 'Geko_Wp_', 'Geko_' );
	
	protected $_aTranslatedValues = array();
	
	protected $_aLinks = array();
	
	
	
	
	
	//
	public function start() {
		
		parent::start();
		
		$this->_aMapMethods = array_merge( $this->_aMapMethods, array(
			
			'is' => array( 'Geko_Wp', 'is' ),
			
			'listCats' => 'wp_list_cats',
			'listArchives' => 'wp_get_archives',
			'listAuthors' => 'wp_list_authors',
			'listBookmarks' => 'wp_list_bookmarks',
			'tagCloud' => 'wp_tag_cloud'
			
		) );
		
	}
	
	
	//// helpers
	
	
	//
	public function escapeHtml( $sValue ) {
		return wp_specialchars( $sValue, 1 );
	}
	
	
	//// language translation handling
	
	//
	public function _t( $sValue = '', $iFlag = NULL, $sCurLang = NULL ) {
		
		$oResolver = Geko_Wp_Language_Resolver::getInstance();
		$oLangMgmt = Geko_Wp_Language_Manage::getInstance();
		
		if ( !$sValue ) return $oResolver->getCurLang( FALSE );
		
		if ( NULL === $sCurLang ) {
			$sCurLang = $oResolver->getCurLang();
		}
		
		if ( ( self::LANG_REPLACE == $iFlag ) || ( self::LANG_FORCE_REPLACE == $iFlag ) ) {
			
			// look for replacement pattern
			$aRegs = array();
			if ( preg_match( '/##(.+)##/', $sValue, $aRegs ) ) {
				
				if ( self::LANG_FORCE_REPLACE == $iFlag ) {
					// force lang code value if current language is empty
					if ( !$sCurLang ) $sCurLang = $oLangMgmt->getDefLangCode();
				} else {
					// if lang code is default then make it empty
					if ( $sCurLang && ( $sCurLang == $oLangMgmt->getDefLangCode() ) ) $sCurLang = '';				
				}
				
				$sToReplace = $aRegs[ 0 ];
				$sReplaceWith = ( $sCurLang ) ? str_replace( '[lang]', $sCurLang, $aRegs[ 1 ] ) : '' ;
				return str_replace( $sToReplace, $sReplaceWith, $sValue );
			}
			
		}
		
		if ( $sCurLang ) {
			
			if ( self::LANG_URL == $iFlag ) {
				
				$oUrl = new Geko_Uri( $sValue );
				$oUrl->setVar( 'lang', $sCurLang );
				return strval( $oUrl );
				
			} else {
			
				if ( !$this->_aTranslatedValues[ $iLangId ] ) {
					
					$this->_aTranslatedValues[ $iLangId ] = array();
					
					$iLangId = $oLangMgmt->getLanguage( $sCurLang )->getId();
					$aStrings = new Geko_Wp_Language_String_Query( array( 'lang_id' => $iLangId ) );
					
					foreach ( $aStrings as $oString ) {
						$this->_aTranslatedValues[ $iLangId ][ $oString->getKeyId() ] = $oString->getContent();
					}
					
				}
				
				$oStrMgmt = Geko_Wp_Language_String_Manage::getInstance()->init();
				$iKeyId = $oStrMgmt->getTransId( $sValue );
				
				if ( $this->_aTranslatedValues[ $iLangId ][ $iKeyId ] ) {
					return $this->_aTranslatedValues[ $iLangId ][ $iKeyId ];
				}
			
			}
			
		}
		
		return $sValue;
	}
	
	//
	public function _e( $sValue = '', $iFlag = NULL, $sCurLang = NULL ) {
		echo $this->_t( $sValue, $iFlag, $sCurLang );
	}
	
	//
	public function getTranslatedValues() {
		return $this->_aTranslatedValues;
	}
	
	// translate labels
	public function _getLabel() {

		$aArgs = func_get_args();
		
		$sCurLang = $aArgs[ 1 ];
		$sLabel = call_user_func_array( array( parent, '_getLabel' ), $aArgs );
		
		return $this->_t( $sLabel, NULL, $sCurLang );
	}
	
	//
	public function _getLabels() {
		
		$aLabels = parent::_getLabels();
		$aRet = array();
		
		foreach ( $aLabels as $iIdx => $sValue ) {
			$aRet[ $iIdx ] = $this->_t( $sValue );
		}
		
		return $aRet;
	}
	
	//
	public function getScriptUrls( $aOther = NULL ) {
		return Geko_Wp::getScriptUrls( $aOther );
	}
	
	
	
	
	//// link methods
	
	//
	public function addLink( $sKey, $aLink ) {
		$this->_aLinks[ $sKey ] = $aLink;
		return $this;
	}
	
	//
	public function echoLinks() {
		
		$aArgs = func_get_args();
		
		if ( count( $aArgs ) == 0 ) {
			$aLinkKeys = array_keys( $this->_aLinks );		
		} elseif ( is_array( $aArgs[ 0 ] ) ) {
			$aLinkKeys = $aArgs[ 0 ];
		} else {
			$aLinkKeys = $aArgs;
		}
		
		$oA = new Geko_Html_Element_A();
		
		if ( count( $aLinkKeys ) > 0 ): ?>
			<p><?php foreach ( $aLinkKeys as $i => $sKey ) {
				$aLink = $this->_aLinks[ $sKey ];
				if ( 0 != $i ) echo ' | ';
				$oA
					->reset()
					->_setAtts( $aLink )
					->append( $aLink[ 'title' ] )
				;
				echo strval( $oA );
			} ?></p>
		<?php endif;
		
	}
		
	
	
	//// render tags
	
	//
	public function getEnqueueScriptCb() {
		return 'wp_enqueue_script';
	}
	
	//
	public function getEnqueueStyleCb() {
		return 'wp_enqueue_style';
	}
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( 0 === strpos( strtolower( $sMethod ), 'do' ) ) {
			
			$sAction = Geko_Inflector::underscore(
				substr_replace( $sMethod, '', 0, 2 )
			);
			
			if ( !in_array( $sAction, $this->_aUnprefixedActions ) ) {
				$sAction = sprintf( 'theme_%s', $sAction );
			}
			
			parent::__call( $sMethod, $aArgs );
			
			do_action_ref_array( $sAction, $aArgs );
			
			return NULL;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'apply' ) ) {

			$sFilter = Geko_Inflector::underscore(
				substr_replace( $sMethod, '', 0, 5 )
			);
			
			if ( !in_array( $sFilter, $this->_aUnprefixedFilters ) ) {
				$sFilter = sprintf( 'theme_%s', $sFilter );
			}
			
			$mRes = parent::__call( $sMethod, $aArgs );
			$aArgs[ 0 ] = $mRes;
			
			return apply_filters_ref_array( $sFilter, $aArgs );
			
		}
		
		return parent::__call( $sMethod, $aArgs );
	}
	
	
	
}

