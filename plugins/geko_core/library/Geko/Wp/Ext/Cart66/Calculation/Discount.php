<?php

//
class Geko_Wp_Ext_Cart66_Calculation_Discount extends Geko_Wp_Ext_Cart66_Calculation
{

	
	//
	public function calculate() {
		
		$aData = $this->_aData;
		
		$this->_fSubTotal = Cart66Session::get( 'Cart66Cart' )->getSubTotal();
		
		if ( CART66_PRO && Cart66Setting::getValue( 'use_live_rates' ) ) {
			$liveRates = Cart66Session::get( 'Cart66Cart' )->getLiveRates();
			$liveRates->getSelected();
		}
		
		$this->_fShipping = Cart66Session::get( 'Cart66Cart' )->getShippingCost();
		
		$this->_fDiscountOne = Cart66Session::get( 'Cart66Cart' )->getDiscountAmount();
		
		
		//// tax rate
		
		$oTaxRate = new Cart66TaxRate();
		$oTaxRate->loadByState( $this->getLocation() );
		
		// print_r( $oTaxRate->getData() );
		
		
		$this->_fTaxRate = $oTaxRate->rate / 100 ;
		
	}
	
	
	
}


