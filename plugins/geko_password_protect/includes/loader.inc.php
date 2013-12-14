<?php

$sPluginFunc = '
	if ( class_exists( "Geko_Loader" ) ) {
		Geko_Loader::addIncludePaths( dirname( __FILE__ ) . "/includes/library" );
		$oPlugin = Geko_Singleton_Abstract::getInstance( "' . $sPluginClass . '" );
		add_action( "init", array( $oPlugin, "init" ) );
	} else {
		add_action( "admin_notices", create_function( "", \'
			$s = str_replace( ABSPATH . PLUGINDIR, "", __FILE__ );
			$a = current( get_plugins( substr( $s, 0, strpos( $s, DIRECTORY_SEPARATOR, 1 ) ) ) );
			echo "<div class=\"error\"><p><strong>Error:</strong> The <strong>Geek Oracle Core</strong> plugin is not activated. Please set this up to enable this plugin (" . $a[ "Name" ] . ").</p></div>";
		\' ) );
	}
';

