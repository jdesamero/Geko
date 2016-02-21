<?php

//
class Geko_Wp_Ext_WooCommerce_Order_Item extends Geko_Wp_Entity
{
	
	// prettify the stored wc-order status
	public function getOrderStatusFmt() {
		
		$sStatus = $this->getEntityPropertyValue( 'order_status' );
		$aStatuses = wc_get_order_statuses();
		
		return $aStatuses[ $sStatus ];
	}
	
	
	
	//// discount calculation
	
	//
	public function getDiscount() {
		return floatval( $this->getEntityPropertyValue( 'line_subtotal' ) ) - floatval( $this->getEntityPropertyValue( 'line_after_discount' ) );
	}
	
	
	
	
	//
	public function numberFormat( $sValue ) {
		
		return number_format( floatval( $sValue ), 2 );
	}
	
	//
	public function numberFormatField( $sFieldName ) {
		
		return $this->numberFormat( $this->getEntityPropertyValue( $sFieldName ) );
	}
	
	
	//
	public function getLineSubtotalFmt() {
		return $this->numberFormatField( 'line_subtotal' );
	}
	
	//
	public function getLineAfterDiscountFmt() {
		return $this->numberFormatField( 'line_after_discount' );
	}
	
	//
	public function getLineTaxFmt() {
		return $this->numberFormatField( 'line_tax' );
	}
	
	//
	public function getTotalFmt() {
		return $this->numberFormatField( 'total' );
	}
	
	//
	public function getDiscountFmt() {
		return $this->numberFormat( $this->getDiscount() );
	}
	
	
	
	
}



