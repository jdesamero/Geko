<?php

//
class Geko_Wp_Language_Translate extends Geko_Singleton_Abstract
{
	
	const LANG_URL = 1;
	const LANG_REPLACE = 2;							// replacement pattern is stripped if in the default language
	const LANG_FORCE_REPLACE = 3;					// replacement pattern is set always
	
	
	
	protected $_aTranslatedValues = array();
	
	protected $_sLang = NULL;
	
	
	
	
	
	//// static methods
	
	//
	public static function _getValue( $sValue = '', $iFlag = NULL, $sCurLang = NULL ) {
		$oTrans = self::getInstance( __CLASS__ );
		return $oTrans->getValue( $sValue, $iFlag, $sCurLang );
	}
	
	//
	public static function _echoValue( $sValue = '', $iFlag = NULL, $sCurLang = NULL ) {
		$oTrans = self::getInstance( __CLASS__ );
		$oTrans->echoValue( $sValue, $iFlag, $sCurLang );	
	}
	
	
	
	
	//// accessors
	
	//
	public function setLang( $sLang ) {
		
		$this->_sLang = $sLang;
		
		return $this;
	}
	
	
	
	//
	public function getTrans( $sValue = '', $iFlag = NULL ) {
		return $this->getValue( $sValue, $iFlag, $this->_sLang );
	}
	
	
	
	// language translation handling	
	public function getValue( $sValue = '', $iFlag = NULL, $sCurLang = NULL ) {
		
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
				
				if ( !is_array( $this->_aTranslatedValues[ $iLangId ] ) ) {
					
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
	public function echoValue( $sValue = '', $iFlag = NULL, $sCurLang = NULL ) {
		echo $this->getValue( $sValue, $iFlag, $sCurLang );
	}
	
	
	



}

