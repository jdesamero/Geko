<?php

if ( class_exists( 'Geko_Loader' ) ) {
	
	Geko_Loader::addIncludePaths( TEMPLATEPATH . '/includes/library' );
	require_once( TEMPLATEPATH . '/includes/functions.inc.php' );
	
} else {
	
	add_action( 'template_redirect', create_function( '',
		'$a = get_theme_data( TEMPLATEPATH . "/style.css" );
		echo "<p><strong>Error:</strong> The <strong>Geek Oracle Core</strong> plugin is not activated. Please set this up to enable this theme (" . $a[ "Name" ] . ").</p>";
		die;'
	) );
	
	add_action( 'admin_notices', create_function( '',
		'$a = get_theme_data( TEMPLATEPATH . "/style.css" );
		echo "<div class=\"error\"><p><strong>Error:</strong> The <strong>Geek Oracle Core</strong> plugin is not activated. Please set this up to enable this theme (" . $a[ "Name" ] . ").</p></div>";'
	) );
	
}

