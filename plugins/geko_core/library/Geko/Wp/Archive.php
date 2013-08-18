<?php

//
class Geko_Wp_Archive extends Geko_Wp_Entity
{

	// protected $_sEntityIdVarName = ???;
	// protected $_sEntitySlugVarName = ???;

	// protected $_sEditEntityIdVarName = ???;
	
	
	//
	/* /
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( ???, ??? )
		;
		
		return $this;
	}
	/* */	
	
	
	//
	public function getMonth( $sFormat = '' ) {
		$sMonth = $this->getEntityPropertyValue( 'month' );
		
		if ( $sFormat ) {
			return date( $sFormat, strtotime( '1980-' . $sMonth . '-01' ) );
		}
		
		return $sMonth;
	}
	
}


