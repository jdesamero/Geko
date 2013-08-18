<?php

class Geko_Wp_EmailMessage_Log_ExportExcelHelper extends Geko_Wp_Log_ExportExcelHelper
{
	protected $_sExportedFileName = 'email_message_log.xls';
	protected $_sWorksheetName = 'Email Message Log';
	
	//
	public function __construct( $aParams = array() )
	{
		parent::__construct( $aParams );
		
		$this->_aColumnMappings = array_merge(
			$this->_aColumnMappings,
			array(
				'delivery_status' => 'Delivery Status',
				'email_address' => 'Email Address',
				'emsg_id' => 'Email Message Id',
				'emsg_slug' => 'Email Message Slug',
				'scheduled_delivery_date' => 'Scheduled Delivery Date',
				'actual_delivery_date' => 'Actual Delivery Date',
				'batch_id' => 'Batch Id'
			)
		);
		
	}
	
}


