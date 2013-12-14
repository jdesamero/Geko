<?php

class Geko_Wp_Integration_Service_GetUserInfo extends Geko_Wp_Integration_Service {
	
	public static function exec( $aParams ) {
		
		global $current_user;
		
		get_currentuserinfo();
		
		if ( $current_user->user_login ) {
			return array(
				'login' => $current_user->user_login,
				'email' => $current_user->user_email,
				'level' => $current_user->user_level,
				'firstname' => $current_user->user_firstname,
				'lastname' => $current_user->user_lastname,
				'display_name' => $current_user->display_name,
				'id' => $current_user->ID
			);
		} else {
			return NULL;
		}
		
	}
	
}


