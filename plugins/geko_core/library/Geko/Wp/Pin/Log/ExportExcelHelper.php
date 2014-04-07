<?php

class Geko_Wp_Pin_Log_ExportExcelHelper extends Geko_Wp_Entity_ExportExcelHelper
{
	protected $_sExportedFileName = 'pin_log.xls';
	protected $_sWorksheetName = 'PIN Log';
	
	
	/*
	
	//
	public function __construct( $aParams = array() ) {
		
		parent::__construct( $aParams );
		
		$this->_aColumnMappings = array_merge(
			$this->_aColumnMappings,
			array(
				'log_id' => 'Log ID',
				'session_id' => 'Session Id',
				'user_login' => 'User Login',
				'remote_ip_address' => 'IP Address',
				'url' => 'URL',
				'user_agent' => 'User Agent',
				'date_created' => 'Date Created'
			)
		);
		
	}
	
	*/
	
}


