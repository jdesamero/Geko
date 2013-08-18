<?php

//
class Geko_Navigation_Renderer_TemplateStack
{
	//
	protected $_oHelperNavigationMenu;
	protected $_oDefaultTemplate;
	protected $_aTemplates = array();
	
	
	
	// constructor
	public function __construct( Zend_View_Helper_Navigation_Menu $oMenu, $mDefaultTemplate = '' )
	{
		$this->_oHelperNavigationMenu = $oMenu;
		
		if ( !$mDefaultTemplate ) $mDefaultTemplate = 'Geko_Navigation_Renderer_Template';
		$this->_oDefaultTemplate = $this->normalizeTemplate( $mDefaultTemplate );
	}
	
	
	
	//// accessors
	
	//
	public function setTemplate( $mTemplate, $mDepths )
	{
		$oTemplate = $this->normalizeTemplate( $mTemplate );	
		
		if ( $oTemplate && ( $oTemplate instanceof Geko_Navigation_Renderer_Template ) ) {
			if ( is_int( $mDepths ) ) {
				$this->_aTemplates[ $mDepths ] = $oTemplate;
			} elseif ( is_array( $mDepths ) ) {
				foreach ( $mDepths as $iDepth ) {
					$this->_aTemplates[ $iDepth ] = $oTemplate;				
				}
			}
		}
		
		return $this;
	}

	//
	public function setDefaultTemplate( $mDefaultTemplate )
	{
		$oDefaultTemplate = $this->normalizeTemplate( $mDefaultTemplate );
		
		if ( $oDefaultTemplate && ( $oDefaultTemplate instanceof Geko_Navigation_Renderer_Template ) ) {
			$this->_oDefaultTemplate = $oDefaultTemplate;
		}
		
		return $this;
	}

	//
	public function setTemplates( $aTemplates )
	{
		foreach ( $aTemplates as $aTemplate ) {
			$this->setTemplate( $aTemplate[0], $aTemplate[1] );
		}
		
		return $this;
	}
	
	// get template for the given depth, otherwise return default template
	public function get( $iDepth )
	{
		if ( isset( $this->_aTemplates[ $iDepth ] ) ) {
			return $this->_aTemplates[ $iDepth ];
		} else {
			return $this->_oDefaultTemplate;
		}
	}
	
	//
	public function getHelperNavigationMenu()
	{
		return $this->_oHelperNavigationMenu;
	}
	
	
	
	//// helper methods
	
	//
	public function normalizeTemplate( $mTemplate )
	{
		$oTemplate = NULL;
		
		if ( is_string( $mTemplate ) && class_exists( $mTemplate ) ) {
			$oTemplate = new $mTemplate;
		} elseif ( is_object( $mTemplate ) ) {
			$oTemplate = $mTemplate;
		}
		
		if ( $oTemplate ) $oTemplate->setStack( $this );
		
		return $oTemplate;
	}
	
}


