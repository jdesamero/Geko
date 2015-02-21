<?php

//
class Geko_Wp_Ext_WooCommerce_Order_Export extends Geko_Wp_Entity_ExportExcelHelper
{
	protected $_sExportedFileName = 'orders_##date##.xls';
	protected $_sWorksheetName = 'Users All';
	
	//
	public function __construct( $aParams = array() ) {
		
		parent::__construct( $aParams );
		
		$this->_aColumnMappings = array_merge(
			$this->_aColumnMappings,
			array(
				'order_number' => 'Order Number',
				'order_status' => array( 'Order Status', array( 'trans' => 'status' ) ),
				'order_items' => 'Order Items',
				'user_id' => 'User ID',
				'transaction_date' => 'Transaction Date',
				'purchase_date' => 'Purchase Date',
				'first_name' => 'First Name',
				'last_name' => 'Last Name',
				'address1' => 'Addresss Line 1',
				'address2' => 'Addresss Line 2',
				'city' => 'City',
				'province' => 'Province',
				'postal_code' => 'Postal Code',
				'email' => 'Email',
				'telephone' => 'Telephone'
			)
		);
		
	}
	
	
	//
	public function transStatus( $mPassVal, $oItem, $sKey, $aParams ) {
		
		$aStatuses = wc_get_order_statuses();
		
		return $aStatuses[ $mPassVal ];
	}
	
	
	
}


