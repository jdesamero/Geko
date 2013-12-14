<?php

//
class Geko_Wp_Redirect
{
	public static function templateRedirect() {
		
		if ( is_page() || is_single() ) {
			
			global $post;
			
			$sRedirect = get_post_meta($post->ID, 'Redirect', true);
			
			if ('' != $sRedirect) {
				header('Location: ' . $sRedirect);
				die();
			}
			
		}
		
	}
	
	
	public static function register() {
		add_action('template_redirect', array(__CLASS__, 'templateRedirect'));
	}
	
}


