<?php

//
class Geko_Wp_Payment_Transaction
{
	
	protected $_iTransType;
	protected $_aTransParams;
	protected $_aBillingParams;
	protected $_aShippingParams;
	protected $_aItems;
	
	protected $_sPaymentClass = '';
	protected $_sAdminClass = '';
	
	
	/********************************************* /
	
	// parameter formatting:
	
	Geko_Wp_Payment::TRANSTYPE_PURCHASE --> array(
		'application_id' => ,
		'order_id' => ,
		'customer_id' => ,
		'customer_email' => ,
		'amount' => ,
		'card_number' => ,
		'expiration_date' => ,
		'details' => 
	)
	
	Geko_Wp_Payment::TRANSTYPE_REFUND --> array(
		'application_id' => ,
		'order_id' => ,
		'transaction_id' => ,
		'orig_order_id' => ,
		'amount' => 
	)
	
	/**********************************************/
	
	//
	public function __construct( $iTransType, $aTransParams ) {
		
		$this->_iTransType = $iTransType;
		$this->_aTransParams = $aTransParams;
		
		//
		$this->_sPaymentClass = Geko_Class::resolveRelatedClass(
			$this, '_Transaction', '', $this->_sPaymentClass
		);
		
		//
		$this->_sAdminClass = Geko_Class::resolveRelatedClass(
			$this->_sPaymentClass, '', '_Admin', $this->_sAdminClass
		);
		
	}
	
	
	//
	public function getPaymentInstance() {
		return Geko_Singleton_Abstract::getInstance( $this->_sPaymentClass );
	}
	
	//
	public function getAdminInstance() {
		return Geko_Singleton_Abstract::getInstance( $this->_sAdminClass );
	}
	
	
	
	//
	public function getTransactionType() {
		return $this->_iTransType;
	}
	
	//
	public function getTransactionParams() {
		return $this->_aTransParams;
	}
	
	//
	public function getApplicationId() {
		return $this->_aTransParams[ 'application_id' ];	
	}
	
	//
	public function getOrderId() {
		return $this->_aTransParams[ 'order_id' ];	
	}
	
	//
	public function getReceiptId() {
		return $this->getOrderId();	
	}
	
	
	
	/********************************************* /
	
	// parameter formatting:
	
	array(
		'first_name' => ,
		'last_name' => ,
		'company_name' => ,
		'address_line_1' => ,
		'address_line_2' => ,
		'address_line_3' => ,
		'city' => ,
		'province' => ,
		'postal_code' => ,
		'country' => ,
		'phone_number' => ,
		'fax_number' => ,
		'tax_amount_1' => ,
		'tax_amount_2' => ,
		'tax_amount_3' => ,
		'shipping_amount' => 
	)
	
	/**********************************************/
	
	//
	public function setBillingParams( $aBillingParams ) {
		$this->_aBillingParams = $aBillingParams;
		return $this;
	}
	
	//
	public function getBillingParams() {
		return $this->_aBillingParams;
	}
	
	
	/********************************************* /
	
	// parameter formatting:
	
	array(
		'first_name' => ,
		'last_name' => ,
		'company_name' => ,
		'address_line_1' => ,
		'address_line_2' => ,
		'address_line_3' => ,
		'city' => ,
		'province' => ,
		'postal_code' => ,
		'country' => ,
		'phone_number' => ,
		'fax_number' => ,
		'tax_amount_1' => ,
		'tax_amount_2' => ,
		'tax_amount_3' => ,
		'shipping_amount' => 
	)
	
	/**********************************************/
	
	//
	public function setShippingParams( $aShippingParams ) {
		$this->_aShippingParams = $aShippingParams;	
		return $this;
	}
	
	//
	public function getShippingParams() {
		return $this->_aShippingParams;
	}
	
	
	
	/********************************************* /
	
	// parameter formatting:
	
	array(
		'product_code' => ,
		'product_name' => ,
		'quantity' => ,
		'price_per_item' => 
	)
	
	/**********************************************/
	
	//
	public function setItem( $aItem ) {
		$this->_aItems[] = $aItem;	
		return $this;
	}
	
	//
	public function getItems() {
		return $this->_aItems;
	}
	
	
	
	// perform the transaction, to be implemented by sub-class
	// returns a response object
	public function perform() {
	
		$oPayment = Geko_Wp_Payment::getPayment();
		$sResponseClass = $oPayment->getResponseClass();
		
		return new $sResponseClass();
		
	}
	
	
	
}


