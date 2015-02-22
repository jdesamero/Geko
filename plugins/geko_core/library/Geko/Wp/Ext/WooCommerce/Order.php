<?php

//
class Geko_Wp_Ext_WooCommerce_Order extends Geko_Wp_Entity
{
	
	// prettify the stored wc-order status
	public function getOrderStatusFmt() {
		
		$sStatus = $this->getEntityPropertyValue( 'order_status' );
		$aStatuses = wc_get_order_statuses();
		
		return $aStatuses[ $sStatus ];
	}
	
	
	// calculate the total
	public function getTotal( $mPassVal, $oItem, $sKey ) {
		
		return 
			floatval( $this->getEntityPropertyValue( 'amount' ) ) - 
			floatval( $this->getEntityPropertyValue( 'discount' ) ) + 
			floatval( $this->getEntityPropertyValue( 'tax' ) )
		;
	}
	
	
}

