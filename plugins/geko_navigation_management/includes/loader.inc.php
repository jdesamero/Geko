<?php

if ( !function_exists( 'geko_load_plugin' ) ) {
	
	//
	function geko_load_plugin( $sPluginClass, $sFile ) {
		
		if ( class_exists( 'Geko_Loader' ) ) {
			
			Geko_Loader::addIncludePaths( sprintf( '%s/includes/library', dirname( $sFile ) ) );
			
			$oPlugin = Geko_Singleton_Abstract::getInstance( $sPluginClass );
			add_action( 'init', array( $oPlugin, 'init' ) );
			
		} else {
			
			add_action( 'admin_notices', function() use( $sFile ) {
				$s = str_replace( sprintf( '%s%s', ABSPATH, PLUGINDIR ), '', $sFile );
				$a = current( get_plugins( substr( $s, 0, strpos( $s, DIRECTORY_SEPARATOR, 1 ) ) ) );
				echo sprintf( '<div class="error"><p><strong>Error:</strong> The <strong>Geek Oracle Core</strong> plugin is not activated. Please set this up to enable this plugin (%s).</p></div>', $a[ 'Name' ] );
			} );
			
		}
		
	};
	
}

