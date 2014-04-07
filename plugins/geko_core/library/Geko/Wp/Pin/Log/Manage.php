<?php

//
class Geko_Wp_Pin_Log_Manage extends Geko_Wp_Log_Manage
{
	
	protected $_sTableSuffix = 'pin';
	protected $_sTableName = 'geko_pin_log';
	protected $_sLogTitle = 'PINs';
	protected $_bUseMetaTable = TRUE;
	
	protected $_bTrackSessionId = TRUE;
	protected $_bTrackPostData = TRUE;
	protected $_bTrackGetData = TRUE;
	protected $_bTrackCookieData = TRUE;
	protected $_bTrackServerData = TRUE;
	
	
	
}


