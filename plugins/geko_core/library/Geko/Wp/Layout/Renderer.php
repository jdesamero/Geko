<?php

//
class Geko_Wp_Layout_Renderer extends Geko_Layout_Renderer
{

	//
	public function isAjaxContent() {
		return (
			$_REQUEST[ 'ajax_content' ] || 
			Geko_Wp::is( 'page_template:page_ajax.php' )
		) ? TRUE : FALSE ;
	}
	
	
}


