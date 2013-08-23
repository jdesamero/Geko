<?php

//
class Geko_Router_Route_Layout extends Geko_Router_Route
{
	
	protected $_aPrefixes = array( 'Tmpl_', 'Gloc_', 'Geko_' );
	
	protected $_sBestMatch = '';
	protected $_aLeftovers = array();
	
	protected $_sDefaultLayout = '';
	protected $_sNotFoundLayout = '';
	
	
	//
	public function __construct( $aPrefixes = NULL ) {
		
		if ( is_array( $aPrefixes ) ) {
			$this->setPrefixes( $aPrefixes );
		}
		
		if ( $sClass = $this->getBestMatch( 'Index' ) ) {
			$this->_sDefaultLayout = $sClass;
		}
		
		if ( $sClass = $this->getBestMatch( 'NotFound' ) ) {
			$this->_sNotFoundLayout = $sClass;
		}
		
	}
	
	
	//// accessors
	
	//
	public function setPrefixes( $aPrefixes ) {
		$this->_aPrefixes = $aPrefixes;	
		return $this;
	}
	
	//
	public function setDefault( $sClass ) {
		$this->_sDefaultLayout = $sClass;
		return $this;
	}

	//
	public function setNotFound( $sClass ) {
		$this->_sNotFoundLayout = $sClass;	
		return $this;
	}
	
	
	//// helpers
	
	//
	public function getBestMatch( $sSuffix ) {
		
		$aClasses = array();
		
		foreach ( $this->_aPrefixes as $sPrefix ) {
			$aClasses[] = $sPrefix . $sSuffix;
		}
		
		return Geko_Class::existsCoalesce( $aClasses );
	}
	
	
	//// functionality
	
	//
	public function isMatch() {
		
		$oRouter = $this->_oRouter;
		
		// print_r( $oRouter->getPathItems() );
		
		$aPathItems = $aPathLeft = $oRouter->getPathItems();
		
		$sClass = '';
		$sBestMatch = '';
		$aBestMatch = array();
		
		$aLeftovers = array();
		
		foreach ( $aPathItems as $i => $sItem ) {
			
			array_shift( $aPathLeft );
			
			$sItem = str_replace( '-', '_', $sItem );
			$sItem = Geko_Inflector::camelize( $sItem );
			
			if ( $sClass ) $sClass .= '_';
			$sClass .= $sItem;
			
			if ( $sCheck = $this->getBestMatch( $sClass ) ) {
				$sBestMatch = $sCheck;
				$aBestMatch = $aPathLeft;
			}
			
		}
		
		$this->_sBestMatch = $sBestMatch;
		$this->_aLeftovers = $aBestMatch;
		
		if ( $this->_sDefaultLayout && ( 0 == count( $aPathItems ) ) ) {
			$this->_sBestMatch = $this->_sDefaultLayout;
		}

		if ( $this->_sNotFoundLayout && ( count( $aPathItems ) > 0 ) && ( !$this->_sBestMatch ) ) {
			$this->_sBestMatch = $this->_sNotFoundLayout;
		}
		
		return ( $this->_sBestMatch ) ? TRUE : FALSE ;		
	}
	
	//
	public function run() {
		if ( $sBestMatch = $this->_sBestMatch ) {
			Geko_Singleton_Abstract::getInstance( $sBestMatch )
				->init()
				->setLeftovers( $this->_aLeftovers )
			;
		}
	}
	
}

