<?php

//
class Geko_Wp_Cart66_Mock_Order
{
	
	public $trans_id = 'ABC123DEF456GHI789J0';
	
	public $bill_first_name = 'John';
	public $bill_last_name = 'Doe';
	public $bill_address = '1234 My Street';
	public $bill_address2 = 'Apt. 2A';
	public $bill_city = 'Grandville';
	public $bill_state = 'NE';
	public $bill_zip = '69835';
	public $bill_country = 'States';
	
	
	
	//
	public function getItems() {
		return array(
			new Geko_Wp_Cart66_Mock_Item(),
			new Geko_Wp_Cart66_Mock_Item( '456', 39, 2, 'Another Product' ),
			new Geko_Wp_Cart66_Mock_Item( '8910', 5, 12, 'Cheap Stuff' )
		);
	}
	
	
	//
	public function hasShippingInfo() {
	
	
	}
	
	
}



