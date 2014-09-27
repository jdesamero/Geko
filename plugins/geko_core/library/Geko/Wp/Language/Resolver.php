<?php

//
class Geko_Wp_Language_Resolver extends Geko_Wp_Plugin
{
	protected $sCurLang = '';
	protected $sLangQueryVar = 'lang';
	
	protected $oLangMgm;
	protected $sDefaultDomain;
	protected $iFrontPage = FALSE;
	
	
	//
	public function init() {
		
		static $bCalled = FALSE;
		
		if ( !$bCalled ) {
			
			parent::init();
			
			add_action( 'admin_init', array( $this, 'resolveAdmin' ) );
			add_filter( 'pre_get_posts', array( $this, 'resolveTheme' ) );
			add_filter( 'pre_option_page_on_front', array( $this, 'getPageOnFront' ) );
						
			add_filter( 'Geko_Wp::getHomepageId', array( $this, 'getHomepageId' ), 10, 2 );
			add_filter( 'Geko_Wp::getHomepageUrl', array( $this, 'getHomepageUrl' ), 10, 2 );
			
			$this->oLangMgm = Geko_Wp_Language_Manage::getInstance();
			
			$oUrl = new Geko_Uri( Geko_Wp::getUrl( TRUE ) );
			$this->sDefaultDomain = $oUrl->getHost();
			
			$bCalled = TRUE;
		}
		
		return $this;
	}
	
	
	//
	public function getLangQueryVar() {
		return $this->sLangQueryVar;
	}
	
	//
	public function getCurLang( $bAllowReturnEmpty = TRUE ) {
		
		// return value is possibly empty (if currently in the default language)
		if ( $bAllowReturnEmpty ) return $this->sCurLang;
		
		// explicitly return the language code of the default language
		return Geko_String::coalesce( $this->sCurLang, $this->oLangMgm->getDefLangCode() );
	}
	
	//
	public function resolveAdmin() {
		
		if (
			( $sLangCode = $_REQUEST[ $this->sLangQueryVar ] ) && 
			( $oLang = $this->oLangMgm->getLanguage( $sLangCode ) )
		) {
			$this->sCurLang = $oLang->getSlug();
		}
	}
	
	//
	public function resolveTheme( $aQuery ) {
		
		static $bDefaultQuery = FALSE;
		
		if ( !$bDefaultQuery ) {
			
			// use backtrace to determine if this is the default query
			
			$aBt = debug_backtrace( FALSE );
			if (
				( 'WP_Query' == $aBt[ 4 ][ 'class' ] ) && 
				( 'query' == $aBt[ 4 ][ 'function' ] )
			) {
				
				////// determine the current language
				
				$sInherentLang = '';
				
				$aParams = array();
				
				// prepare parameters to figure out inherent language, if any
				if ( is_page() ) {
					
					$iPageId = $aQuery->query_vars[ 'page_id' ];
					if ( !$iPageId ) {
						$oPg = get_page_by_path( $aQuery->query_vars[ 'pagename' ] );
						$iPageId = $oPg->ID;
					}
					
					$aParams = array(
						'type' => 'post',
						'obj_id' => $iPageId
					);
										
				} elseif ( is_single() ) {
					
					$iPostId = $aQuery->query_vars[ 'p' ];
					if ( !$iPostId ) {
						
						$oDb = Geko_Wp::get( 'db' );
						
						$iPostId = $oDb->fetchOne( sprintf( "
							SELECT		id
							FROM		##pfx##posts p
							WHERE		( p.post_name = '%s' ) AND 
										( p.post_status = 'publish' ) AND 
										( p.post_type = 'post' )
						", sanitize_title_for_query( $aQuery->query_vars[ 'name' ] ) ) );
					}
					
					$aParams = array(
						'type' => 'post',
						'obj_id' => $iPostId
					);
					
				} elseif ( is_category() ) {
					
					$iCatId = $aQuery->query_vars[ 'cat' ];
					if ( !$iCatId ) {
						$iCatId = Geko_Wp_Category::get_ID( $aQuery->query_vars[ 'category_name' ] );
					}
					
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
					$this->sCurLang = $sInherentLang = $oMember->getLangCode();
				}
				
				// check for query var lang
				if (
					( $sLangCode = $_REQUEST[ $this->sLangQueryVar ] ) && 
					( !$sInherentLang )
				) {
					$this->sCurLang = $this->oLangMgm->getLanguage( $sLangCode )->getSlug();
				}
				
				// check domain
				if ( !$this->sCurLang && !$sInherentLang ) {
					$oUrl = Geko_Uri::getGlobal();
					$this->sCurLang = $this->oLangMgm->getLangCodeFromDomain( $oUrl->getHost() );
				}
				
				
				
				////// resolve entity
				
				// if $this->sCurLang is not empty (current language is not default)
				// then attempt to resolve it by finding it's siblings
				if ( $this->sCurLang && ( $this->sCurLang != $sInherentLang ) ) {
					
					$aParams = array( 'lang' => $this->sCurLang );
					
					if ( $iPageId ) {
						
						$aParams[ 'type' ] = 'post';
						$aParams[ 'sibling_id' ] = $iPageId;
						
						if ( $iSiblingId = $this->getSiblingId( $aParams ) ) {
							
							$aQuery->queried_object = get_page( $iSiblingId );
							$aQuery->queried_object_id = $iSiblingId;
							$aQuery->query_vars[ 'page_id' ] = $iSiblingId;							// re-route to sibling!!!
							
							// make sure homepage in other language resolves
							$iFrontPageId = get_option( 'page_on_front' );
							
							if ( $iSiblingId != $iFrontPageId ) {
								
								$aSibs = new Geko_Wp_Language_Member_Query( array(
									'type' => 'post',
									'sibling_id' => $iSiblingId
								), FALSE );
								
								if ( in_array( $iFrontPageId, $aSibs->gatherObjId() ) ) {
									$this->iFrontPage = $iSiblingId;
									$aQuery->is_home = 1;
								}
							}
							
						}
						
					} elseif ( $iPostId ) {
						
						$aParams[ 'type' ] = 'post';
						$aParams[ 'sibling_id' ] = $iPageId;
						
						if ( $iSiblingId = $this->getSiblingId( $aParams ) ) {
							$aQuery->queried_object = get_page( $iSiblingId );
							$aQuery->queried_object_id = $iSiblingId;
							$aQuery->query_vars[ 'p' ] = $iSiblingId;								// re-route to sibling!!!
						}
						
					} elseif ( $iCatId ) {
						
						$aParams[ 'type' ] = 'category';
						$aParams[ 'sibling_id' ] = $iCatId;
						
						if ( $iSiblingId = $this->getSiblingId( $aParams ) ) {
							$oCat = Geko_Wp_Category( $iSiblingId );
							$aQuery->queried_object_id = $iSiblingId;
							$aQuery->query_vars[ 'cat' ] = $iSiblingId;								// re-route to sibling!!!
							$aQuery->query_vars[ 'category_name' ] = $oCat->getSlug();
							$aQuery->parse_tax_query( $aQuery->query_vars );
						}
						
					}
					
				}
				
				$bDefaultQuery = TRUE;
				
			}
						
		}
		
	}
	
	//
	public function resolveUrl( $mUrl, $iLangId ) {
		
		$oCurUrl = Geko_Uri::getGlobal();
		$oLang = $this->oLangMgm->getLanguage( $iLangId );
		
		$sLangDomain = Geko_String::coalesce( $oLang->getDomain(), $this->sDefaultDomain );
		
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
		if ( $this->iFrontPage ) return $this->iFrontPage;
		return $mRet;
	}
	
	
	//
	public function getHomepageId( $iPageId, $sInvokerClass ) {
		
		if ( $this->sCurLang ) {
			
			$aParams = array(
				'sibling_id' => $iPageId,
				'lang' => $this->sCurLang,
				'type' => 'post'
			);
			
			$oSib = Geko_Wp_Language_Member::getOne( $aParams, FALSE );
			if ( $oSib->isValid() ) return $oSib->getObjId();
		}
		
		return $iPageId;
	}
	
	//
	public function getHomepageUrl( $sUrl, $sInvokerClass ) {
		
		if ( $this->sCurLang ) {
			
			$oUrl = new Geko_Uri( $sUrl );
			
			if ( 1 !== $this->oLangMgm->getLangDomainCount( $oUrl->getHost() ) ) {
				$oUrl->setVar( 'lang', $this->sCurLang );
			}
			
			return strval( $oUrl );
		}
		
		return $sUrl;
	}
	
	
	//// helpers
	
	//
	public function echoLangHiddenField() {
		if ( $this->sCurLang ):
			?><input name="lang" type="hidden" value="<?php echo $this->sCurLang; ?>" /><?php
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


