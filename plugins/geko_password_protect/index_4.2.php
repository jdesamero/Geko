<?php

$sPluginClass = 'Geko_Wp_PasswordProtect';

require_once( 'includes/loader_4.2.inc.php' );

add_action( 'plugins_loaded', create_function( '', $sPluginFunc ) );


