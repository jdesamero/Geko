<?php

//
class Geko_Wp_Cart66_Mock_Item
{


	public $product_id = '1234';
	public $product_price = 25;
	public $quantity = 3;
	public $description = 'Test Product';
	
	
	//
	public function __construct( $id = NULL, $price = NULL, $qty = NULL, $desc = NULL ) {
		
		if ( NULL !== $id ) $this->product_id = $id;
		if ( NULL !== $price ) $this->product_price = $price;
		if ( NULL !== $qty ) $this->quantity = $qty;
		if ( NULL !== $desc ) $this->description = $desc;
		
	}
	
	
	
}



