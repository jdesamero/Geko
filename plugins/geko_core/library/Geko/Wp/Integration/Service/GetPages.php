<?php

class Geko_Wp_Integration_Service_GetPages extends Geko_Wp_Integration_Service {
	
	public static function exec( $aParams ) {
		
		return get_pages();
		
	}
	
}


