<?php

class Geko_Wp_NavigationManagement_Page_Language
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
	//
	protected $_sLang;
	protected $_oLang;
	
	
	
	//// object methods
	
	//
	public function setLang( $sLang ) {
		
		$oLangMgmt = Geko_Wp_NavigationManagement_Language::getInstance();
		
		if ( !$sLang ) {
			
			// assuming 2 language toggle mode
			$oLangRes = Geko_Wp_Language_Resolver::getInstance();
			$sCurLang = $oLangRes->getCurLang( FALSE );
			
			$aLangs = $oLangMgmt->getLanguages();
			
			foreach ( $aLangs as $oLang ) {
				
				$sLang = $oLang->getSlug();
				
				if ( $sLang != $sCurLang ) {
					// $sLang set to the "opposite language"
					break;
				}
			}
			
		}
		
		$this->_sLang = $sLang;
		$this->_oLang = $oLangMgmt->getLanguage( $sLang );
		
		return $this;
	}
	
	//
	public function getLang() {
		return $this->_sLang;
	}
	
	
	
	
    //// object methods
    
    //
    public function getHref() {
		
		global $wp_query;
		
		if ( $sLang = $this->_sLang ) {

			$aParams = array( 'lang' => $sLang );
			$sEntityClass = '';
			
			// find the sibling
			if ( is_singular() ) {
				
				$oPost = new Geko_Wp_Post( $wp_query->post );
				
				// var_dump( $iPageId );
				
				$aParams[ 'type' ] = 'post';
				$aParams[ 'sibling_id' ] = $oPost->getId();
				
				$sEntityClass = 'Geko_Wp_Post';
				
			} elseif ( is_category() ) {
				
				$aParams[ 'type' ] = 'category';
				$aParams[ 'sibling_id' ] = intval( get_query_var( 'cat' ) );
				
				$sEntityClass = 'Geko_Wp_Category';
				
			} elseif ( is_tax() ) {
				
				// get current taxonomy
				$sTx = $wp_query->query_vars[ 'taxonomy' ];
				$sTerm = get_query_var( $sTx );
				
				$oTx = get_term_by( 'slug', $sTerm, $sTx );

				$aParams[ 'type' ] = 'category';
				$aParams[ 'sibling_id' ] = intval( $oTx->term_id );
				
				$sEntityClass = 'Geko_Wp_Category';
				
			}
			
			
			if ( $sEntityClass ) {
				
				$oSib = Geko_Wp_Language_Member::getOne( $aParams );
				
				if (
					$oSib->isValid() && 
					( $iSiblingId = $oSib->getObjId() )
				) {
					$oEntity = new $sEntityClass( $iSiblingId );
					return $oEntity->getUrl();
				}			
			}
			
			
			// default, set "lang" query var to the current url
			$oUrl = new Geko_Uri();
			$oUrl->setVar( 'lang', $sLang );
			
			return strval( $oUrl );		
		}
		
		return '';
    }
    
    //
	public function getImplicitLabel() {
		
		if ( $oLang = $this->_oLang ) {
			// exact mode
			return $oLang->getTitle();
		}
		
		return '';
	}
	
	
	
	
	//
	public function toArray() {
		
		return array_merge(
			parent::toArray(),
			array( 'lang' => $this->_sLang )
		);
	}
	
	
	
	
	
    //
    public function isActive( $bRecursive = FALSE ) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {

			$oLangRes = Geko_Wp_Language_Resolver::getInstance();
			$sCurLang = $oLangRes->getCurLang( FALSE );
			
			$this->_active = ( $this->_sLang == $sCurLang );
		}
		
		return parent::isActive( $bRecursive );
	}
	
	
}

