<?php

class Geko_Wp_Log_ExportExcelHelper extends Geko_Wp_Entity_ExportExcelHelper
{	
	protected $_sExportedFileName = 'access_log_##date##.xls';
	protected $_sWorksheetName = 'Access Logs';
	protected $_aColumnMappings = array(
		'log_id' => 'Log ID',
		'remote_ip_address' => 'IP Address',
		'user_agent' => 'User Agent',
		'user_login' => 'User Login',
		'url' => 'URL',
		'date_created' => 'Date Created'
	);

}


