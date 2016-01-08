<?php

//
class Geko_Wp_Language_Resolver extends Geko_Wp_Plugin
{
	protected $_sCurLang = '';
	protected $_sLangQueryVar = 'lang';
	
	protected $_oLangMgm;
	protected $_iFrontPage = FALSE;
	
	protected $_sDefaultDomain = '';
	protected $_sDefaultLangCode = '';
	
	
	//
	public function init() {
		
		static $bCalled = FALSE;
		
		if ( !$bCalled ) {
			
			parent::init();
			
			add_action( 'admin_init', array( $this, 'resolveAdmin' ) );
			add_action( 'pre_get_posts', array( $this, 'resolveTheme' ) );
			add_filter( 'pre_option_page_on_front', array( $this, 'getPageOnFront' ) );
						
			add_filter( 'Geko_Wp::getHomepageId', array( $this, 'getHomepageId' ), 10, 2 );
			add_filter( 'Geko_Wp::getHomepageUrl', array( $this, 'getHomepageUrl' ), 10, 2 );
			
			$oLangMgm = Geko_Wp_Language_Manage::getInstance();
			
			$oUrl = new Geko_Uri( Geko_Wp::getUrl( TRUE ) );
			
			
			$this->_oLangMgm = $oLangMgm;
			$this->_sDefaultDomain = $oUrl->getHost();
			$this->_sDefaultLangCode = $oLangMgm->getDefLangCode();
			
			
			$bCalled = TRUE;
		}
		
		return $this;
	}
	
	
	//
	public function getLangQueryVar() {
		return $this->_sLangQueryVar;
	}
	
	//
	public function getCurLang( $bReturnEmptyIfDefault = TRUE ) {
		
		$sCurLang = $this->_sCurLang;
		$sDefLang = $this->_sDefaultLangCode;
		
		if ( $bReturnEmptyIfDefault ) {
			
			if ( $sCurLang && ( $sCurLang == $sDefLang ) ) {
				// force empty return
				return '';
			}
			
			return $sCurLang;
		}
		
		// explicitly return the language code of the default language
		return Geko_String::coalesce( $sCurLang, $sDefLang );
	}
	
	//
	public function resolveAdmin() {
		
		if (
			( $sLangCode = $_REQUEST[ $this->_sLangQueryVar ] ) && 
			( $oLang = $this->_oLangMgm->getLanguage( $sLangCode ) )
		) {
			$this->_sCurLang = $oLang->getSlug();
		}
	}
	
	//
	public function resolveTheme( $oWpQuery ) {
		
		if ( $oWpQuery->is_main_query() ) {
						
			////// determine the current language
			
			$sInherentLang = '';
			
			$aParams = array();
			
			// prepare parameters to figure out inherent language, if any
			if ( is_page() ) {
				
				$iPageId = $oWpQuery->query_vars[ 'page_id' ];
				if ( !$iPageId ) {
					$oPg = get_page_by_path( $oWpQuery->query_vars[ 'pagename' ] );
					$iPageId = $oPg->ID;
				}
				
				$aParams = array(
					'type' => 'post',
					'obj_id' => $iPageId
				);
									
			} elseif ( is_single() ) {
				
				$iPostId = $oWpQuery->query_vars[ 'p' ];
				if ( !$iPostId ) {
					
					$oDb = Geko_Wp::get( 'db' );
					
					$iPostId = $oDb->fetchOne( sprintf( "
						SELECT		id
						FROM		##pfx##posts p
						WHERE		( p.post_name = '%s' ) AND 
									( p.post_status = 'publish' )
					", sanitize_title_for_query( $oWpQuery->query_vars[ 'name' ] ) ) );
				}
				
				$aParams = array(
					'type' => 'post',
					'obj_id' => $iPostId
				);
				
			} elseif ( is_category() ) {
				
				$iCatId = $oWpQuery->query_vars[ 'cat' ];
				if ( !$iCatId ) {
					$iCatId = Geko_Wp_Category::get_ID( $oWpQuery->query_vars[ 'category_name' ] );
				}
				
				$aParams = array(
					'type' => 'category',
					'obj_id' => $iCatId
				);
				
			} elseif ( is_tax() ) {
				
				$aTaxonomies = get_taxonomies( array(), 'names' );
				
				foreach ( $aTaxonomies as $sTx ) {
					if ( $sTerm = $oWpQuery->query[ $sTx ] ) {
						break;
					}
				}
				
				$oTx = get_term_by( 'slug', $sTerm, $sTx );
				$iCatId = $oTx->term_id;
				
				$aParams = array(
					'type' => 'category',
					'obj_id' => $iCatId
				);
				
			}
			
			
			
			// check for inherent lang
			if (
				( count( $aParams ) > 0 ) && 
				( $oMember = Geko_Wp_Language_Member::getOne( $aParams ) ) && 
				( $oMember->isValid() ) && 
				( !$oMember->getLangIsDefault() )
			) {
				$this->_sCurLang = $sInherentLang = $oMember->getLangCode();
			}
			
			// check for query var lang
			if (
				( $sLangCode = $_REQUEST[ $this->_sLangQueryVar ] ) && 
				( !$sInherentLang )
			) {
				$this->_sCurLang = $this->_oLangMgm->getLanguage( $sLangCode )->getSlug();
			}
			
			// check domain
			if ( !$this->_sCurLang && !$sInherentLang ) {
				
				$sCurHost = Geko_Uri::getGlobal()->getHost();
				
				if ( $sCurHost ) {
					$this->_sCurLang = $this->_oLangMgm->getLangCodeFromDomain( $sCurHost );
				}
			}
			
			
			
			////// resolve entity
			
			// if $this->_sCurLang is not empty (current language is not default)
			// then attempt to resolve it by finding it's siblings
			if ( $this->_sCurLang && $sInherentLang && ( $this->_sCurLang != $sInherentLang ) ) {
				
				$aParams = array( 'lang' => $this->_sCurLang );
				
				if ( $iPageId ) {
					
					$aParams[ 'type' ] = 'post';
					$aParams[ 'sibling_id' ] = $iPageId;
					
					if ( $iSiblingId = $this->getSiblingId( $aParams ) ) {
						
						$oWpQuery->queried_object = get_page( $iSiblingId );
						$oWpQuery->queried_object_id = $iSiblingId;
						$oWpQuery->query_vars[ 'page_id' ] = $iSiblingId;							// re-route to sibling!!!
						
						// make sure homepage in other language resolves
						$iFrontPageId = get_option( 'page_on_front' );
						
						if ( $iSiblingId != $iFrontPageId ) {
							
							$aSibs = new Geko_Wp_Language_Member_Query( array(
								'type' => 'post',
								'sibling_id' => $iSiblingId
							), FALSE );
							
							if ( in_array( $iFrontPageId, $aSibs->gatherObjId() ) ) {
								$this->_iFrontPage = $iSiblingId;
								$oWpQuery->is_home = 1;
							}
						}
						
					}
					
				} elseif ( $iPostId ) {
					
					$aParams[ 'type' ] = 'post';
					$aParams[ 'sibling_id' ] = $iPageId;
					
					if ( $iSiblingId = $this->getSiblingId( $aParams ) ) {
						$oWpQuery->queried_object = get_page( $iSiblingId );
						$oWpQuery->queried_object_id = $iSiblingId;
						$oWpQuery->query_vars[ 'p' ] = $iSiblingId;								// re-route to sibling!!!
					}
					
				} elseif ( $iCatId ) {
					
					$aParams[ 'type' ] = 'category';
					$aParams[ 'sibling_id' ] = $iCatId;
					
					if ( $iSiblingId = $this->getSiblingId( $aParams ) ) {
						$oCat = Geko_Wp_Category( $iSiblingId );
						$oWpQuery->queried_object_id = $iSiblingId;
						$oWpQuery->query_vars[ 'cat' ] = $iSiblingId;								// re-route to sibling!!!
						$oWpQuery->query_vars[ 'category_name' ] = $oCat->getSlug();
						$oWpQuery->parse_tax_query( $oWpQuery->query_vars );
					}
					
				}			
			}									
		}
		
	}
	
	
	//
	public function resolveUrl( $mUrl, $iLangId ) {
		
		$oCurUrl = Geko_Uri::getGlobal();
		$oLang = $this->_oLangMgm->getLanguage( $iLangId );
		
		$sLangDomain = Geko_String::coalesce( $oLang->getDomain(), $this->_sDefaultDomain );
		
		if ( $oCurUrl->getHost() != $sLangDomain ) {
			
			if ( $mUrl instanceof Geko_Uri ) {
				
				$mUrl->setHost( $sLangDomain );
			
			} elseif ( is_string( $mUrl ) ) {
				
				$oUrl = new Geko_Uri( $mUrl );
				$oUrl->setHost( $sLangDomain );
				
				return strval( $oUrl );
			}
			
		}
		
		return $mUrl;
	}
	
	
	//
	public function getPageOnFront( $mRet ) {
		if ( $this->_iFrontPage ) return $this->_iFrontPage;
		return $mRet;
	}
	
	
	//
	public function getHomepageId( $iPageId, $sInvokerClass ) {
		
		if ( $this->_sCurLang ) {
			
			$aParams = array(
				'sibling_id' => $iPageId,
				'lang' => $this->_sCurLang,
				'type' => 'post'
			);
			
			$oSib = Geko_Wp_Language_Member::getOne( $aParams, FALSE );
			if ( $oSib->isValid() ) return $oSib->getObjId();
		}
		
		return $iPageId;
	}
	
	//
	public function getHomepageUrl( $sUrl, $sInvokerClass ) {
		
		if ( $this->_sCurLang ) {
			
			$oUrl = new Geko_Uri( $sUrl );
			
			if ( 1 !== $this->_oLangMgm->getLangDomainCount( $oUrl->getHost() ) ) {
				$oUrl->setVar( 'lang', $this->_sCurLang );
			}
			
			return strval( $oUrl );
		}
		
		return $sUrl;
	}
	
	
	//// helpers
	
	//
	public function echoLangHiddenField() {
		if ( $this->_sCurLang ):
			?><input name="lang" type="hidden" value="<?php echo $this->_sCurLang; ?>" /><?php
		endif;
	}
	
	//
	public function getSiblingId( $aParams ) {
		
		$oSib = Geko_Wp_Language_Member::getOne( $aParams );
		
		if (
			$oSib->isValid() && 
			( $iSiblingId = $oSib->getObjId() )
		) {
			return $iSiblingId;
		}
		
		return FALSE;
	}
	
	
	
}


