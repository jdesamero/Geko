<?php

//
class Geko_App_Layout extends Geko_Layout
{

	protected $_sRenderer = 'Geko_App_Layout_Renderer';
	protected $_aPrefixes = array( 'Gloc_', 'Geko_App_', 'Geko_' );
	
	
	
	//// layout parts
	
	//
	public function echoHead() {
		
		$oThis = $this;
		
		Geko_Once::run( __METHOD__, function() use ( $oThis ) {

			$oThis
				->renderStyleTags()
				->renderScriptTags()
			;
			
		} );
		
	}
	
	
	
	
}

