<?php

abstract class Geko_Wp_Rewrite_Abstract
	extends Geko_Singleton_Abstract
	implements Geko_Wp_Rewrite_Interface
{
	
	protected $_sListKeyTag;
	protected $_sListVarName;
	protected $_sListDefaultTemplate;

	protected $_sSingleKeyTag;
	protected $_sSingleVarName;
	protected $_sSingleDefaultTemplate;
	
	protected $_aExtraRules;
	
	
	
	//
	public function start() {
		
		parent::start();
		
		add_action( 'generate_rewrite_rules', array( $this, 'generateRewriteRules' ) );
		add_filter( 'query_vars', array( $this, 'queryVars' ) );
		add_action( 'template_redirect', array( $this, 'templateRedirect' ) );
		
	}
	
	
	//
	public function generateRewriteRules() {
		
		global $wp_rewrite;
		// add rewrite tokens
		
		
		$this->generateRule( $this->_sListVarName, $this->_sListKeyTag );
		$this->generateRule( $this->_sSingleKeyTag, $this->_sSingleKeyTag );
		
		if ( $this->_aExtraRules ) {
			
			foreach ( $this->_aExtraRules as $aRule ) {
				$this->generateRule( $aRule[ 'var_name' ], $aRule[ 'key_tag' ] );
			}
		}
		
		return $wp_rewrite->rules;	
	}
	
	//
	public function generateRule( $sVarName, $sTag ) {

		if ( $sVarName ) {
			
			$sKeyTag = sprintf( '%%%s%%', $sTag );
			
			$wp_rewrite->add_rewrite_tag( $sKeyTag, '(.+?)', sprintf( '%s=', $sVarName ) );
			
			$keywords_structure = sprintf( '%s%s/%s/', $wp_rewrite->root, $sTag, $sKeyTag );
			$keywords_rewrite = $wp_rewrite->generate_rewrite_rules( $keywords_structure );
			
			$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;
			
		}	
	}
	
	
	//
	public function queryVars( $aVars ) {
		
		if ( $this->_sListVarName ) $aVars[] = $this->_sListVarName;
		if ( $this->_sSingleVarName ) $aVars[] = $this->_sSingleVarName;
		
		if ( $this->_aExtraRules ) {
		
			foreach ( $this->_aExtraRules as $aRule ) {
				$aVars[] = $aRule[ 'var_name' ];
			}
		}
		
		return $aVars;	
	}
	
	//
	public function templateRedirect() {
		
		global $wp_query;
		
		$aQv = $wp_query->query_vars;
		
		$sDefaultTemplate = NULL;
		
		
		if ( $this->isList() && isset( $aQv[ $this->_sListVarName ] ) ) {
			
			$aQv[ 'list_key_tag' ] = $this->_sListKeyTag;
			$sDefaultTemplate = sprintf( '%s%s', TEMPLATEPATH, $this->_sListDefaultTemplate );
		}
		
		if ( $this->isSingle() && isset( $aQv[ $this->_sSingleVarName ] ) ) {
			
			$aQv[ 'single_key_tag' ] = $this->_sSingleKeyTag;
			$sDefaultTemplate = sprintf( '%s%s', TEMPLATEPATH, $this->_sSingleDefaultTemplate );
		}
		
		
		if ( $this->_aExtraRules ) {
			
			foreach ( $this->_aExtraRules as $aRule ) {
				
				if ( isset( $aQv[ $aRule[ 'var_name' ] ] ) ) {
					
					$aQv[ 'single_key_tag' ] = $aRule[ 'key_tag' ];
					$sDefaultTemplate = sprintf( '%s%s', TEMPLATEPATH, $aRule[ 'default_template' ] );
				}
			}
			
		}
		
		
		//
		if ( $sDefaultTemplate && is_file( $sDefaultTemplate ) ) {
			include( apply_filters( 'template_include', $sDefaultTemplate ) );
			die();
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
		
		$aQv = $wp_query->query_vars;
		
		
		if ( 0 === strpos(
			strval( $oUrl = new Geko_Uri() ),
			sprintf( '%s/%s/', Geko_Wp::getUrl(), $this->_sListKeyTag )
		) ) {
			
			$bRet = FALSE;
			
			if ( NULL === $mVarVal ) {
				$bRet = isset( $aQv[ $this->_sListVarName ] );
			} else {
				$bRet = ( $aQv[ $this->_sListVarName ] == $mVarVal );
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
		
		$aQv = $wp_query->query_vars;
		
		
		if ( 0 === strpos(
			strval( $oUrl = new Geko_Uri() ),
			sprintf( '%s/%s/', Geko_Wp::getUrl(), $this->_sSingleKeyTag )
		) ) {
			
			$bRet = isset( $aQv[ $this->_sSingleVarName ] );

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
		
		$aQv = $wp_query->query_vars;
		
		
		if ( isset( $aQv[ $this->_sListVarName ] ) ) {
			return $aQv[ $this->_sListVarName ];
		}
		
		return NULL;
	}

	//
	public function getSingleVar() {
		
		global $wp_query;
		
		$aQv = $wp_query->query_vars;
		
		
		if ( isset( $aQv[ $this->_sSingleVarName ] ) ) {
			return $aQv[ $this->_sSingleVarName ];
		}
		
		return NULL;
	}
	
	//
	public function getExtraVar() {
		
		global $wp_query;
		
		$aQv = $wp_query->query_vars;
		
		
		if ( $this->_aExtraRules ) {
			
			foreach ( $this->_aExtraRules as $aRule ) {
				
				if ( isset( $aQv[ $aRule[ 'var_name' ] ] ) ) {
					return $aQv[ $aRule[ 'var_name' ] ];
				}
			}
		}
		
		return NULL;
	}
	
	
	//
	public function getVar() {
		return Geko_String::coalesce( $this->getListVar(), $this->getSingleVar(), $this->getExtraVar() );
	}
	
	//
	public function getSlug( $sTag ) {
		
		if ( $this->hasTag( $sTag ) ) {
			return $this->_aTagHash[ $sTag ][ self::FLD_VARNAME ];
		}
		
		return NULL;
	}
	
	//
	public function getVarValue( $sSlug ) {
		
		global $wp_query;
		
		if ( $sSlug ) {
			return $wp_query->query_vars[ $sSlug ];
		}
		
		return NULL;
	}

	//
	public function getVarValueFromTag( $sTag ) {
		return $this->getVarValue( $this->getSlug( $sTag ) );
	}
	
	
	//
	public function hasTag( $sTag ) {
		return array_key_exists( $sTag, $this->_aTagHash ) ? TRUE : FALSE ;
	}
	
	
}


