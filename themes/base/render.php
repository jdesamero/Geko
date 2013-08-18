<?php

require_once( TEMPLATEPATH . '/layout_main.php' );
require_once( TEMPLATEPATH . '/layout_widgets.php' );


// initialize the various layout classes
// layout classes are an instance of Geko_Layout

Gloc_Layout_Main::getInstance()->init();
Gloc_Layout_Widgets::getInstance()->init();
Gloc_Layout_Template::getInstance()->init();



// render the final layout
// the layout renderer class is an instance of Geko_Layout_Renderer
Gloc_Layout_Renderer::getInstance()->render();

