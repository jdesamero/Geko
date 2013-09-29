<?php

$oResolve = new Geko_Wp_Resolver();
$oResolve
	->setClassFileMapping( array(
		'Main' => TEMPLATEPATH . '/layout_main.php',
		'Widgets' => TEMPLATEPATH . '/layout_widgets.php',
		'Template' => get_page_template()
	) )
	->addPath( 'default', new Geko_Wp_Resolver_Path_Default() )
	->run()
;



// initialize the various layout classes
// layout classes are an instance of Geko_Layout

$aSuffixes = $oResolve->getClassSuffixes();
foreach ( $aSuffixes as $sSuffix ) {
	Geko_Singleton_Abstract::getInstance( $oResolve->getClass( $sSuffix ) )->init();
}



// render the final layout
// the layout renderer class is an instance of Geko_Layout_Renderer
Geko_Singleton_Abstract::getInstance( $oResolve->getClass( 'Renderer' ) )->render();


