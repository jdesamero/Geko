<?php

//
class Geko_Wp_Cart66_Calculation
{
	
	protected $_aData = array();
	
	
	protected $_fSubTotal = 0;
	protected $_fDiscountOne = 0;
	protected $_fDiscountTwo = 0;
	protected $_fShipping = 0;
	protected $_fTaxRate = 0;
	
	protected $_fTax = 0;
	protected $_fPreTaxTotal = 0;
	protected $_fTotal = 0;
	
	
	
	
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
	public function getSubTotal() {
		return $this->_fSubTotal;
	}
	
	//
	public function getDiscountOne() {
		return $this->_fDiscountOne;
	}
	
	//
	public function getDiscountTwo() {
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
	public function getPreTaxTotal() {
		
		if ( $this->_fPreTaxTotal ) {
			return $this->_fPreTaxTotal;
		}
		
		return (
			$this->getSubTotal() - 
			$this->getDiscount()
		) + 
		$this->getShipping();
	}
	
	//
	public function getTotal() {
		
		if ( $this->_fTotal ) {
			return $this->_fTotal;
		}
		
		return $this->getPreTaxTotal() + $this->getTax();
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


