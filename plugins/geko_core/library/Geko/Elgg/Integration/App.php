<?php

//
class Geko_Elgg_Integration_App extends Geko_Integration_App_Abstract
{
	const CODE = 'elgg';
	
	//
	public function detect()
	{
		return class_exists('ElggEntity');
	}

	//
	public function _getKey()	
	{
		return 'elgg';
	}
	
	//
	public function getDbConn()
	{
		global $dblink;
		
		if ( isset( $dblink['readwrite'] ) ) {
			return $dblink['readwrite'];
		}
		
		if ( $dblink['read'] ) {
			return $dblink['read'];	
		}
		
	}
	
}



