<?php

//
class Geko_Wp_Ext_Cart66_Mock_Order
{
	
	public $trans_id = 'ABC123DEF456GHI789J0';
	
	public $bill_first_name = 'John';
	public $bill_last_name = 'Doe';
	public $bill_address = '1234 My Street';
	public $bill_address2 = 'Apt. 2A';
	public $bill_city = 'Grandville';
	public $bill_state = 'NE';
	public $bill_zip = '69835';
	public $bill_country = 'United States';
	
	public $phone = '(900) 123-6598';
	public $email = 'johndoe@mydomain.min';
	public $ordered_on = '2013-03-16 22:00:40';
	
	public $ship_first_name = 'Jane';
	public $ship_last_name = 'Cooper';
	public $ship_address = '456 Some Avenue';
	public $ship_address2 = 'Suite 503';
	public $ship_city = 'Toronto';
	public $ship_state = 'ON';
	public $ship_zip = 'M7G 3F6';
	public $ship_country = 'Canada';
	
	public $shipping_method = 'FedEx Free';
	
	public $subtotal = NULL;
	public $shipping = 3.95;
	public $discount_amount = 4.25;
	public $tax_rate = 0.13;
	public $tax = NULL;
	public $total = NULL;
	
	public $ouid = '1234567890';
	
	
	
	//
	public function __construct() {
		
		$aItems = $this->getItems();
		
		$subtotal = 0;
		
		foreach ( $aItems as $oItem ) {
			$subtotal += $oItem->product_price * $oItem->quantity;
		}
		
		$beforetax = $subtotal - $this->discount_amount;
		
		$tax = $beforetax * $this->tax_rate;
		
		$total = $beforetax + $tax + $this->shipping;
		
		// assign
		
		$this->subtotal = $subtotal;
		$this->tax = $tax;
		$this->total = $total;
	}
	
	
	//
	public function getItems() {
		return array(
			new Geko_Wp_Ext_Cart66_Mock_Item(),
			new Geko_Wp_Ext_Cart66_Mock_Item( '456', 39, 2, 'Another Product' ),
			new Geko_Wp_Ext_Cart66_Mock_Item( '8910', 5, 12, 'Cheap Stuff' )
		);
	}
	
	
	//
	public function hasShippingInfo() {
	
	
	}
	
	
}



