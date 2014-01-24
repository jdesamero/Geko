<?php

//
class Geko_App_Layout extends Geko_Layout
{

	protected $_sRenderer = 'Geko_App_Layout_Renderer';
	
	//// helpers
	
	
	
	//
	public function init( $bUnshift = FALSE ) {

		$this->_aMapMethods = array_merge( $this->_aMapMethods, array(
			'appGet' => array( Geko_Class::existsCoalesce( 'Gloc_App', 'Geko_App' ), 'get' )
		) );
		
		parent::init( $bUnshift );
		
		return $this;
	}
	
	//
	public function resolveClass( $sClass ) {
		return Geko_Class::existsCoalesce( $sClass, sprintf( 'Gloc_%s', $sClass ), sprintf( 'Geko_App_%s', $sClass ) );
	}
	
	
}

