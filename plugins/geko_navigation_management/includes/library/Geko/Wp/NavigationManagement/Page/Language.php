<?php

class Geko_Wp_NavigationManagement_Page_Language
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
    //// object methods
    
    //
    public function getHref() {
		
		global $wp_query;
		
		// assuming there are two languages, display the "non-current" language
		
		$oLangRes = Geko_Wp_Language_Resolver::getInstance();
		$oLangMgmt = Geko_Wp_NavigationManagement_Language::getInstance();
		
		$aLangs = $oLangMgmt->getLanguages();
		
		foreach ( $aLangs as $oLang ) {
			if ( $oLang->getSlug() != $oLangRes->getCurLang( FALSE ) ) {
				
				$aParams = array( 'lang' => $oLang->getSlug() );
				
				// find the sibling
				if ( is_singular() ) {
					
					$oPost = new Geko_Wp_Post( $wp_query->post );
					
					// var_dump( $iPageId );
					
					$aParams[ 'type' ] = 'post';
					$aParams[ 'sibling_id' ] = $oPost->getId();
					
					$oSib = Geko_Wp_Language_Member::getOne( $aParams );
					
					if (
						$oSib->isValid() && 
						( $iSiblingId = $oSib->getObjId() )
					) {
						$oPost = new Geko_Wp_Post( $iSiblingId );
						return $oPost->getUrl();
					}
					
				} elseif ( is_category() ) {
					
					$aParams[ 'type' ] = 'category';
					$aParams[ 'sibling_id' ] = intval( get_query_var( 'cat' ) );
					
					$oSib = Geko_Wp_Language_Member::getOne( $aParams );
					
					if (
						$oSib->isValid() && 
						( $iSiblingId = $oSib->getObjId() )
					) {
						$oCat = new Geko_Wp_Category( $iSiblingId );
						return $oCat->getUrl();
					}
					
				}
				
				// default, set "lang" query var to the current url
				$oUrl = new Geko_Uri();
				$oUrl->setVar( 'lang', $oLang->getSlug() );
				return strval( $oUrl );
				
			}
		}
		
		return '';
    }
    
    //
	public function getImplicitLabel() {
		
		// assuming there are two languages, display the "non-current" language
		
		$oLangRes = Geko_Wp_Language_Resolver::getInstance();
		$oLangMgmt = Geko_Wp_NavigationManagement_Language::getInstance();
		
		$aLangs = $oLangMgmt->getLanguages();
		
		foreach ( $aLangs as $oLang ) {
			if ( $oLang->getSlug() != $oLangRes->getCurLang( FALSE ) ) {
				// display language
				return $oLang->getTitle();
			}
		}
		
		return '';
	}
	
    //
    public function isActive( $recursive = FALSE ) {
		return FALSE;
	}
	
	
}

