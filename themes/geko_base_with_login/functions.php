<?php

if ( class_exists( 'Geko_Loader' ) ) {
	
	Geko_Loader::addIncludePaths( sprintf( '%s/includes/library', TEMPLATEPATH ) );
	
	// load theme specific customizations
	require_once( sprintf( '%s/includes/functions.inc.php', TEMPLATEPATH ) );
	
} else {
	
	add_action( 'template_redirect', function() {
		$a = get_theme_data( sprintf( '%s/style.css', TEMPLATEPATH ) );
		printf( '<p><strong>Error:</strong> The <strong>Geek Oracle Core</strong> plugin is not activated. Please set this up to enable this theme (%s).</p>', $a[ 'Name' ] );
		die();
	} );
	
	add_action( 'admin_notices', function() {
		$a = get_theme_data( sprintf( "%s/style.css", TEMPLATEPATH ) );
		printf( '<div class="error"><p><strong>Error:</strong> The <strong>Geek Oracle Core</strong> plugin is not activated. Please set this up to enable this theme (%s).</p></div>', $a[ 'Name' ] );
	} );
	
}

