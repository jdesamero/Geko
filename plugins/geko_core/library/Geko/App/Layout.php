<?php

//
class Geko_App_Layout extends Geko_Layout
{

	protected $_sRenderer = 'Geko_App_Layout_Renderer';
	protected $_aPrefixes = array( 'Gloc_', 'Geko_App_', 'Geko_' );
	
	
	
	//// layout parts
	
	//
	public function echoHead() {
		
		Geko_Once::run( __METHOD__, function() {
			
			$this
				->renderStyleTags()
				->renderScriptTags()
			;
			
		} );
		
	}
	
	
	//
	public function getBodyClassCb() {
		
		$aBodyClass = array();
		
		if ( $oRouter = Geko_App::get( 'router' ) ) {
			
			$aPathItems = $oRouter->getPathItems();
			
			foreach ( $aPathItems as $i => $sItem ) {
				$aBodyClass[] = sprintf( 'path_lv%d_%s', $i + 1, $sItem );
			}
			
			$aBodyClass[] = sprintf( 'path_full_%s', implode( '_', $aPathItems ) );
		}
		
		
		return implode( ' ', $aBodyClass );
	}
	
	
	
}



