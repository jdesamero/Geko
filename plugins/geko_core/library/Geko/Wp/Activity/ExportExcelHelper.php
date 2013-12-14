<?php

class Geko_Wp_Activity_ExportExcelHelper extends Geko_Wp_Log_ExportExcelHelper
{
	protected $_sExportedFileName = 'activity_log.xls';
	protected $_sWorksheetName = 'Activity Log';
	
	//
	public function __construct( $aParams = array() ) {
		
		parent::__construct( $aParams );
		
		$this->_aColumnMappings = array_merge(
			$this->_aColumnMappings,
			array(
				'session_id' => 'Session Id',
				'get_data' => 'GET Data',
				'post_data' => 'POST Data'
			)
		);
		
	}
	
}


