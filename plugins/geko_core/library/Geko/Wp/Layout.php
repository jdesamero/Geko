<?php

//
class Geko_Wp_Layout extends Geko_Layout
{
	
	const URL = 1;
	const REPLACE = 2;							// replacement pattern is stripped if in the default language
	const FORCE_REPLACE = 3;					// replacement pattern is set always
	
	
	
	protected $_aUnprefixedActions = array(
		'get_header', 'wp_head', 'get_sidebar', 'wp_footer', 'get_footer'
	);
	protected $_aUnprefixedFilters = array( 'body_class', 'post_class' );
	
	protected $_sRenderer = 'Geko_Wp_Layout_Renderer';
	
	protected $_aMapMethods = array(
		
		'sw' => array( 'Geko_String', 'sprintfWrap' ),
		'pw' => array( 'Geko_String', 'printfWrap' ),
		'st' => array( 'Geko_String', 'truncate' ),
		'pt' => array( 'Geko_String', 'ptruncate' ),
		'sn' => 'number_format',
		'pn' => array( 'Geko_String', 'printNumberFormat' ),
		'tm' => array( 'Geko_String', 'mbTrim' ),
		
		'pf' => array( 'Geko_Html', 'populateForm' ),
		
		'is' => array( 'Geko_Wp', 'is' ),
		
		'listCats' => 'wp_list_cats',
		'listArchives' => 'wp_get_archives',
		'listAuthors' => 'wp_list_authors',
		'listBookmarks' => 'wp_list_bookmarks',
		'tagCloud' => 'wp_tag_cloud'
		
	);
	
	protected $_aTranslatedValues = array();
	
	protected $_aTemplates = array();
	
	protected $_aLinks = array();
	
	
	
	//// helpers
	
	//
	public function resolveClass( $sClass ) {
		return Geko_Class::existsCoalesce( $sClass, 'Gloc_' . $sClass, 'Geko_Wp_' . $sClass );
	}
	
	//
	public function escapeHtml( $sValue ) {
		return wp_specialchars( $sValue, 1 );
	}
	
	
	//// language translation handling
	
	//
	public function _t( $sValue = '', $iFlag = NULL ) {
		
		if ( $this->_bIntrospect && $sValue && ( NULL === $iFlag ) ) {
			
			// introspection mode, so track what was called
			$this->_aTranslatedValues[ $sValue ] = TRUE;
			return NULL;
			
		}
		
		$oResolver = Geko_Wp_Language_Resolver::getInstance();
		$oLangMgmt = Geko_Wp_Language_Manage::getInstance();
		
		if ( !$sValue ) return $oResolver->getCurLang( FALSE );
		
		$sCurLang = $oResolver->getCurLang();
		
		if ( ( self::REPLACE == $iFlag ) || ( self::FORCE_REPLACE == $iFlag ) ) {
			
			// look for replacement pattern
			$aRegs = array();
			if ( preg_match( '/##(.+)##/', $sValue, $aRegs ) ) {
				
				if ( self::FORCE_REPLACE == $iFlag ) {
					// force lang code value if current language is empty
					if ( !$sCurLang ) $sCurLang = $oLangMgmt->getDefLangCode();
				} else {
					// if lang code is default then make it empty
					if ( $sCurLang && ( $sCurLang == $oLangMgmt->getDefLangCode() ) ) $sCurLang = '';				
				}
				
				$sToReplace = $aRegs[ 0 ];
				$sReplaceWith = ( $sCurLang ) ? str_replace( '[lang]', $sCurLang, $aRegs[ 1 ] ) : '';
				return str_replace( $sToReplace, $sReplaceWith, $sValue );
			}
			
		}
		
		if ( $sCurLang ) {
			
			if ( self::URL == $iFlag ) {
				
				$oUrl = new Geko_Uri( $sValue );
				$oUrl->setVar( 'lang', $sCurLang );
				return strval( $oUrl );
				
			} else {
			
				if ( !$this->_aTranslatedValues ) {
					
					$this->_aTranslatedValues = array();
					
					$iLangId = $oLangMgmt->getLanguage( $sCurLang )->getId();
					$aStrings = new Geko_Wp_Language_String_Query( array( 'lang_id' => $iLangId ) );
					
					foreach ( $aStrings as $oString ) {
						$this->_aTranslatedValues[ $oString->getKeyId() ] = $oString->getContent();
					}
					
				}
				
				$iKeyId = Geko_Wp_Options_MetaKey::getId( $sValue );
				
				if ( $this->_aTranslatedValues[ $iKeyId ] ) {
					return $this->_aTranslatedValues[ $iKeyId ];
				}
			
			}
			
		}
		
		return $sValue;
	}
	
	//
	public function _e( $sValue = '', $iFlag = NULL ) {
		echo $this->_t( $sValue, $iFlag );
	}
	
	//
	public function getTranslatedValues() {
		return $this->_aTranslatedValues;
	}
	
	// translate labels
	public function _getLabel( $iIdx ) {
		return $this->_t( parent::_getLabel( $iIdx ) );
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
	
	
	
	
	
	//// template routing methods
	
	// usage: addTemplate( <some template>, <grouping 1>, <grouping 2>, ... <n> )
	public function addTemplate() {
		
		$aArgs = func_get_args();
		
		$sTemplate = array_shift( $aArgs );
		
		$this->_aTemplates[ $sTemplate ] = $aArgs;
		
		return $this;
	}
	
	// usage: getTemplates( <grouping 1>, <grouping 2>, ... <n> )
	public function getTemplates() {

		$aArgs = func_get_args();
		$aRet = array();
		
		foreach ( $this->_aTemplates  as $sTemplate => $aGroup ) {
			$bMatch = TRUE;
			foreach ( $aArgs as $sMatchGroup ) {
				if ( !in_array( $sMatchGroup, $aGroup ) ) {
					$bMatch = FALSE;
					break;
				}
			}
			if ( $bMatch ) $aRet[] = $sTemplate;
		}
		
		return $aRet;
	}
	
	// same as getTemplates(), return imploded string
	public function getTemplateList() {
		$aArgs = func_get_args();
		$aRet = call_user_func_array( array( $this, 'getTemplates' ), $aArgs );
		return implode( '|', $aRet );
	}
	
	// convenience method
	public function isTemplateList() {
		$aArgs = func_get_args();
		return $this->is(
			call_user_func_array( array( $this, 'getTemplateList' ), $aArgs )
		);
	}
	
	//
	public function getTemplateGrouping() {
		foreach ( $this->_aTemplates as $sTemplate => $aGroup ) {
			if ( $this->is( $sTemplate ) ) {
				return $aGroup;
			}
		}
		return array();
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
	
	
	
	
	
	//// ajax content methods
	
	//
	public function echoAjaxContent() {
		
		$sSection = trim( $_GET[ 'section' ] );
		$sMethod = '';
		
		if ( $sSection ) {
			$sMethod = sprintf( 'get%sAjax', Geko_Inflector::camelize( $sSection ) );
			if ( !method_exists( $this, $sMethod ) ) $sMethod = '';
		}
		
		if ( $sMethod ) {
			$aAjaxResponse = $this->$sMethod();
			echo Zend_Json::encode( $aAjaxResponse );
		}
	}
	
	
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( 0 === strpos( strtolower( $sMethod ), 'do' ) ) {
			
			$sAction = Geko_Inflector::underscore(
				substr_replace( $sMethod, '', 0, 2 )
			);
			
			if ( !in_array( $sAction, $this->_aUnprefixedActions ) ) {
				$sAction = 'theme_' . $sAction;
			}
			
			parent::__call( $sMethod, $aArgs );
			
			do_action_ref_array( $sAction, $aArgs );
			
			return NULL;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'apply' ) ) {

			$sFilter = Geko_Inflector::underscore(
				substr_replace( $sMethod, '', 0, 5 )
			);
			
			if ( !in_array( $sFilter, $this->_aUnprefixedFilters ) ) {
				$sFilter = 'theme_' . $sFilter;
			}
			
			$mRes = parent::__call( $sMethod, $aArgs );
			$aArgs[ 0 ] = $mRes;
			
			return apply_filters_ref_array( $sFilter, $aArgs );
			
		}
		
		return parent::__call( $sMethod, $aArgs );
	}
	
	
	
}

