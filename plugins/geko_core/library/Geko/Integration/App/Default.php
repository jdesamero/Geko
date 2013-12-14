<?php

//
class Geko_Integration_App_Default extends Geko_Integration_App_Abstract
{
	//
	protected $oDbConn;
	
	
	//
	public function detect()
	{
		return TRUE;
	}
	
	//
	public function _getKey()
	{
		return 'default';
	}
	
	//
	public function getDbConn()
	{
		if ( !$this->oDbConn ) {
			
			$this->oDbConn = @mysql_connect(
				$this->aParams['db_host'],
				$this->aParams['db_user'],
				$this->aParams['db_pass']
			);
			
			@mysql_select_db( $this->aParams['db_name'], $this->oDbConn );
		}
		return $this->oDbConn;
	}
	
}


