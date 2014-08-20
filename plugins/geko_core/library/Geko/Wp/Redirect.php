<?php

//
class Geko_Wp_Redirect
{
	public static function templateRedirect() {
		
		if ( is_page() || is_single() ) {
			
			global $post;
			
			$sRedirect = get_post_meta( $post->ID, 'Redirect', TRUE );
			
			if ( $sRedirect ) {
				header( sprintf( 'Location: %s', $sRedirect ) );
				die();
			}
			
		}
		
	}
	
	
	public static function register() {
		add_action( 'template_redirect', array( __CLASS__, 'templateRedirect' ) );
	}
	
}


