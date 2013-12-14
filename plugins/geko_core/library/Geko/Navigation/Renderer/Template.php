<?php

//
class Geko_Navigation_Renderer_Template
{
	//
	protected $_oNavigationHelper;
	protected $_iDepth;
	protected $_oStack;
	
	
	
	//// accessors

	//
	public function setStack( Geko_Navigation_Renderer_TemplateStack $oStack ) {
		$this->_oStack = $oStack;
		return $this;
	}
	
	
	
	//// template methods
	
	//
	public function containerStart( $aParams ) {
		
		$sUlClass = $aParams[ 'ulClass' ];
		
		$sUlClass = trim( $sUlClass );
		$sUlClass = ( $sUlClass ) ? ' class="' . $sUlClass . '"' : '';
		return '<ul' . $sUlClass . '>';
	}
	
	//
	public function containerEnd( $aParams ) {
		return '</ul>';
	}
	
	//
	public function itemStart( $aParams ) {
		
		$oPage = $aParams[ 'page' ];
		
		$sLiClass = $aParams[ 'liClass' ];
		
		if ( $sCustomClass = trim( $oPage->getCssClass() ) ) {
			$sLiClass .= ' ' . $sCustomClass;
		}
		
		$sLiClass = trim( $sLiClass );
		$sLiClass = ( $sLiClass ) ? ' class="' . $sLiClass . '"' : '';
		
		return '<li' . $sLiClass . '>';
	}
	
	//
	public function itemEnd( $aParams ) {
		return '</li>';	
	}
	
	//
	public function link( $aParams ) {
		
		$oPage = $aParams[ 'page' ];
		
		return $this->htmlifyPage( $oPage );
	}
	
	
	
	//// helper methods
	
	//
	public function htmlifyPage( $oPage ) {
		return $this->_oStack->getHelperNavigationMenu()->htmlify( $oPage );
	}
	
}


