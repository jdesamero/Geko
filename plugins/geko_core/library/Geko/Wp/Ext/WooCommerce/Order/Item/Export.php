<?php

//
class Geko_Wp_Ext_WooCommerce_Order_Item_Export extends Geko_Wp_Entity_ExportExcelHelper
{
	
	protected $_sExportedFileName = 'order_items_##date##.xls';
	protected $_sWorksheetName = 'Order Items';
	
	
	
	//
	public function __construct( $aParams = array() ) {
		
		parent::__construct( $aParams );
		
		$this->_aColumnMappings = array_merge(
			$this->_aColumnMappings,
			array(
				'order_number' => TRUE,
				'order_status_fmt' => 'Order Status',
				'order_item_name' => TRUE,
				'user_id' => 'User ID',
				'transaction_date' => TRUE,
				'purchase_date' => TRUE,
				'first_name' => TRUE,
				'last_name' => TRUE,
				'address1' => 'Address Line 1',
				'address2' => 'Address Line 2',
				'city' => TRUE,
				'province' => TRUE,
				'postal_code' => TRUE,
				'email' => TRUE,
				'telephone' => TRUE,
				'referral_type' => TRUE,
				'referral_name' => TRUE,
				'quantity' => TRUE,
				'line_subtotal_fmt' => 'Line Subtotal',
				'discount_fmt' => 'Discount',
				'line_after_discount_fmt' => 'Line After Discount',
				'line_tax_fmt' => 'Line Tax',
				'total_fmt' => 'Total'
			)
		);
		
		// apply filters for custom fields
		$this->_aColumnMappings = apply_filters( sprintf( '%s::columnMappings', __METHOD__ ), $this->_aColumnMappings, $this );
		
	}


}

