<?php

//
class Geko_Wp_Cart66_Calculation
{
	
	const DEFAULT_TAX_LOCATION = 'All Sales';
	
	
	
	protected $_aData = array();
	protected $_aVars = array();
	
	
	protected $_fSubTotal = 0;
	protected $_fDiscountOne = 0;
	protected $_fDiscountTwo = 0;
	protected $_fShipping = 0;
	protected $_fTaxRate = 0;
	
	protected $_fTax = 0;
	protected $_fPreTaxTotal = 0;
	protected $_fTotal = 0;
	
	protected $_sLocation = '';			// province or state
	
	
	
	//
	public function __construct() {
	
	}
	
	
	//
	public function setData( $aData ) {
		$this->_aData = $aData;
		return $this;
	}
	
	
	// hook method
	public function calculate() { }
	
	
	//
	public function setVar( $sKey, $mValue ) {
		$this->_aVars[ $sKey ] = $mValue;
		return $this;
	}
	
	//
	public function getVar( $sKey ) {
		return $this->_aVars[ $sKey ];
	}
	
	
	
	//
	public function setLocation( $sLocation ) {
		$this->_sLocation = $sLocation;
		return $this;
	}
	
	//
	public function getLocation() {
		return Geko_String::coalesce( $this->_sLocation, self::DEFAULT_TAX_LOCATION );
	}
	
	
	
	//
	public function getSubTotal() {
		return $this->_fSubTotal;
	}
	
	//
	public function getDiscountOne() {
		return $this->_fDiscountOne;
	}
	
	//
	public function getDiscountTwo() {
		
		$fAmount = $this->getSubTotal() - $this->getDiscountOne();
		
		if ( $this->_fDiscountTwo > $fAmount ) {
			// this is the max allowable discount
			return $fAmount;
		}
		
		return $this->_fDiscountTwo;
	}
	
	//
	public function setDiscountTwo( $fDiscountTwo ) {
		$this->_fDiscountTwo = $fDiscountTwo;
		return $this;
	}
	
	//
	public function getShipping() {
		return $this->_fShipping;
	}
	
	//
	public function getTaxRate() {
		return $this->_fTaxRate;
	}
	
	
	
	//
	public function getTaxRatePercent() {
		return ( $this->getTaxRate() * 100 );
	}
	
	
	//
	public function getDiscount() {
		return (
			$this->getDiscountOne() + 
			$this->getDiscountTwo()
		);
	}
	
	//
	public function getTax() {
		
		if ( $this->_fTax ) {
			return $this->_fTax;
		}
		
		return (
			$this->getPreTaxTotal() * 
			$this->getTaxRate()
		);
	}
	
	//
	public function getDiscountedSubTotal() {
		return $this->getSubTotal() - $this->getDiscount();
	}
	
	//
	public function getPreTaxTotal() {
		
		if ( $this->_fPreTaxTotal ) {
			return $this->_fPreTaxTotal;
		}
		
		return $this->getDiscountedSubTotal() + $this->getShipping();
	}
	
	//
	public function getTotal() {
		
		if ( $this->_fTotal ) {
			return $this->_fTotal;
		}
		
		return $this->getPreTaxTotal() + $this->getTax();
	}
	
	
	//
	public function hasExcessDiscount() {
		return ( $this->_fDiscountTwo > $this->getDiscountTwo() ) ? TRUE : FALSE ;
	}
	
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( 0 === strpos( $sMethod, 'getCurr' ) ) {
			
			$sActual = str_replace( 'getCurr', 'get', $sMethod );
			if ( method_exists( $this, $sActual ) ) {
				return Cart66Common::currency(
					call_user_func_array( array( $this, $sActual ), $aArgs )
				);
			}
		}
		
		throw new Exception( 'Invalid method ' . $this->_sEntityClass . '::' . $sMethod . '() called.' );
	}
	
	
}


