<?php

abstract class Geko_Wp_Rewrite_Abstract
	extends Geko_Singleton_Abstract
	implements Geko_Wp_Rewrite_Interface
{
	protected $_bCalledInit = FALSE;
	
	protected $_sListKeyTag;
	protected $_sListVarName;
	protected $_sListDefaultTemplate;

	protected $_sSingleKeyTag;
	protected $_sSingleVarName;
	protected $_sSingleDefaultTemplate;
	
	
	//
	public function init() {
		
		if ( !$this->_bCalledInit ) {
			
			add_action( 'generate_rewrite_rules', array( $this, 'generateRewriteRules' ) );
			add_filter( 'query_vars', array( $this, 'queryVars' ) );
			add_action( 'template_redirect', array( $this, 'templateRedirect' ) );
			
			$this->_bCalledInit = TRUE;
		}
	}
	
	
	//
	public function generateRewriteRules() {
		
		global $wp_rewrite;
		// add rewrite tokens
		
		if ( $this->_sListVarName ) {
			
			$keytag = sprintf( '%%%s%%', $this->_sListKeyTag );
			
			$wp_rewrite->add_rewrite_tag( $keytag, '(.+?)', sprintf( '%s=', $this->_sListVarName ) );
			
			$keywords_structure = sprintf( '%s%s/%s/', $wp_rewrite->root, $this->_sListKeyTag, $keytag );
			$keywords_rewrite = $wp_rewrite->generate_rewrite_rules( $keywords_structure );
			
			$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;
		}

		if ( $this->_sSingleVarName ) {
			
			$keytag = sprintf( '%%%s%%', $this->_sSingleKeyTag );
			
			$wp_rewrite->add_rewrite_tag( $keytag, '(.+?)', sprintf( '%s=', $this->_sSingleVarName ) );
			
			$keywords_structure = sprintf( '%s%s/%s/', $wp_rewrite->root, $this->_sSingleKeyTag, $keytag );
			$keywords_rewrite = $wp_rewrite->generate_rewrite_rules( $keywords_structure );
			
			$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;
		}
		 
		return $wp_rewrite->rules;	
	}
	
	//
	public function queryVars( $aVars ) {
		
		if ( $this->_sListVarName ) $aVars[] = $this->_sListVarName;
		if ( $this->_sSingleVarName ) $aVars[] = $this->_sSingleVarName;
		
		return $aVars;	
	}
	
	//
	public function templateRedirect() {
		
		global $wp_query;
		
		if ( $this->isList() ) {
			
			if ( isset( $wp_query->query_vars[ $this->_sListVarName ] ) ) {
				
				$wp_query->query_vars[ 'list_key_tag' ] = $this->_sListKeyTag;
				$sDefaultTemplate = sprintf( '%s%s', TEMPLATEPATH, $this->_sListDefaultTemplate );
				
				if ( is_file( $sDefaultTemplate ) ) {
					include( apply_filters( 'template_include', $sDefaultTemplate ) );
					die();
				}
			}
		}
		
		if ( $this->isSingle() ) {
			
			if ( isset( $wp_query->query_vars[ $this->_sSingleVarName ] ) ) {
				
				$wp_query->query_vars[ 'single_key_tag' ] = $this->_sSingleKeyTag;
				$sDefaultTemplate = sprintf( '%s%s', TEMPLATEPATH, $this->_sSingleDefaultTemplate );
				
				if ( is_file( $sDefaultTemplate ) ) {
					include( apply_filters( 'template_include', $sDefaultTemplate ) );
					die();
				}
			}
		}
		
	}
	
	//
	public function getListPermastruct( $bFullUrl = TRUE ) {
		
		if ( $this->_sListKeyTag ) {
			$sPermastruct = sprintf( '/%s/%%s/', $this->_sListKeyTag );
			if ( $bFullUrl ) {
				return sprintf( '%s%s', Geko_Wp::getUrl(), $sPermastruct );
			} else {
				return $bFullUrl;
			}
		} else {
			return '';
		}
	}

	//
	public function getSinglePermastruct( $bFullUrl = TRUE ) {
		
		if ( $this->_sSingleKeyTag ) {
			$sPermastruct = sprintf( '/%s/%%s/', $this->_sSingleKeyTag );
			if ( $bFullUrl ) {
				return sprintf( '%s%s', Geko_Wp::getUrl(), $sPermastruct );
			} else {
				return $bFullUrl;
			}
		} else {
			return '';
		}
	}
	
	// analogous to is_category() and in_category()
	
	//
	public function isList( $mVarVal = NULL ) {
		
		global $wp_query;
		
		if ( 0 === strpos(
			strval( $oUrl = new Geko_Uri() ),
			sprintf( '%s/%s/', Geko_Wp::getUrl(), $this->_sListKeyTag )
		) ) {
			
			$bRet = FALSE;
			
			if ( NULL === $mVarVal ) {
				$bRet = isset( $wp_query->query_vars[ $this->_sListVarName ] );
			} else {
				$bRet = ( $wp_query->query_vars[ $this->_sListVarName ] == $mVarVal );
			}
			
			if ( $bRet ) {
				$wp_query->is_home = FALSE;
				return TRUE;
			} else {
				return FALSE;
			}
			
		} else {
			return FALSE;
		}
	}
	
	// TO DO: not yet implemented!
	public function inList( $mVarVal = NULL ) {
		return FALSE;
	}
	
	//
	public function isSingle() {
		
		global $wp_query;
		
		if ( 0 === strpos(
			strval( $oUrl = new Geko_Uri() ),
			sprintf( '%s/%s/', Geko_Wp::getUrl(), $this->_sSingleKeyTag )
		) ) {
			
			$bRet = isset( $wp_query->query_vars[ $this->_sSingleVarName ] );

			if ( $bRet ) {
				$wp_query->is_home = FALSE;
				return TRUE;
			} else {
				return FALSE;
			}
			
		} else {
			return FALSE;
		}
	}
	
	
	//// accessors
	
	//
	public function getListVar() {
		
		global $wp_query;
		
		if ( isset( $wp_query->query_vars[ $this->_sListVarName ] ) ) {
			return $wp_query->query_vars[ $this->_sListVarName ];
		}
		
		return NULL;
	}

	//
	public function getSingleVar() {
		
		global $wp_query;
		
		if ( isset( $wp_query->query_vars[ $this->_sSingleVarName ] ) ) {
			return $wp_query->query_vars[ $this->_sSingleVarName ];
		}
		
		return NULL;
	}
	
	
}


