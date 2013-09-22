<?php

//
class Geko_Router_Route_Layout extends Geko_Router_Route
{
	
	protected $_aSkip = array( 'Aux' );
	
	protected $_sBestMatch = '';
	protected $_aLeftovers = array();
	
	protected $_sDefaultLayout = '';
	protected $_sNotFoundLayout = '';
	protected $_aBaseLayouts = array(
		array( 'Aux_Main', 'Main' ),
		array( 'Aux_Widgets', 'Widgets' )
	);
	protected $_sRenderer = '';
	
	
	
	
	//
	public function __construct( $aPrefixes = NULL ) {
		
		if ( is_array( $aPrefixes ) ) {
			$this->setPrefixes( $aPrefixes );
		}
		
		if ( $sClass = $this->getBestMatch( 'Index' ) ) {
			$this->_sDefaultLayout = $sClass;
		}
		
		if ( $sClass = $this->getBestMatch( 'Aux_NotFound', 'NotFound' ) ) {
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
	
	//
	public function setBase( $aBase ) {
		$this->_aBaseLayouts = $aBase;
		return $this;
	}
	
	//
	public function setRenderer( $sRenderer ) {
		$this->_sRenderer = $sRenderer;	
		return $this;
	}
	
	
	
	
	//// functionality
	
	//
	public function isMatch() {
		
		$oRouter = $this->_oRouter;
		
		// print_r( $oRouter->getPathItems() );
		
		if ( TRUE == $oRouter->getToken( 'AUTH_REQUIRED' ) ) {
			$this->_sBestMatch = $this->getBestMatch( 'Aux_Auth', 'Auth' );
			return TRUE;	// for security
		}
		
		$aPathItems = $aPathLeft = $oRouter->getPathItems();
		
		$sClass = '';
		$sBestMatch = '';
		$aBestMatch = array();
		
		foreach ( $aPathItems as $i => $sItem ) {
			
			array_shift( $aPathLeft );
			
			$sItem = str_replace( '-', '_', $sItem );
			$sItem = Geko_Inflector::camelize( $sItem );
			
			if ( $sClass ) $sClass .= '_';
			$sClass .= $sItem;
			
			if ( $sCheck = $this->getBestMatch( $sClass ) ) {
				
				if ( $this->skipClass( $sCheck ) ) break;
				
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
		
		$oRouter = $this->_oRouter;
		
		//// base layouts
		
		foreach ( $this->_aBaseLayouts as $aBase ) {
			$sLayoutClass = call_user_func_array( array( $this, 'getBestMatch' ), $aBase );
			if ( $sLayoutClass ) {
				Geko_Singleton_Abstract::getInstance( $sLayoutClass )
					->init()
					->setLeftovers( $this->_aLeftovers )
				;			
			}
		}
		
		//// routed layout
		
		if ( $sBestMatch = $this->_sBestMatch ) {
			Geko_Singleton_Abstract::getInstance( $sBestMatch )
				->init()
				->setLeftovers( $this->_aLeftovers )
			;
		} else {
			throw new Exception( 'A valid layout class was not found!' );
		}
		
		//// renderer
		
		if ( !$sRendererClass = $this->_sRenderer ) {
			$sRendererClass = Geko_Class::getBestMatch( array( 'Gloc_', 'Geko_App_', 'Geko_' ), array( 'Layout_Renderer' ) );
		}
		
		if ( $sRendererClass ) {
			Geko_Singleton_Abstract::getInstance( $sRendererClass )->render();
		} else {
			throw new Exception( 'A valid layout renderer class was not found!' );
		}
		
		$oRouter->setStopRunning( TRUE );
	}
	
	
}

