<?php

class Geko_Elgg_Integration_Service_GetUserInfo extends Geko_Elgg_Integration_Service {
	
	public static function exec( $aParams ) {
		
		if ( isset($_SESSION['user']) ) {
			return array(
				'id' => $_SESSION['id'],
				'username' => $_SESSION['username'],
				'name' => $_SESSION['name']
			);
		} else {
			return NULL;
		}
	}
	
}


