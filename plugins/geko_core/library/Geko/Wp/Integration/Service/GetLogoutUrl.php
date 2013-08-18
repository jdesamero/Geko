<?php

class Geko_Wp_Integration_Service_GetLogoutUrl extends Geko_Wp_Integration_Service {
	
	public static function exec( $aParams ) {
		return wp_logout_url();
	}
	
}


