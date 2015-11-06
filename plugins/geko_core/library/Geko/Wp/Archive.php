<?php
/*
 * "geko_core/library/Geko/Wp/Archive.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

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
		
		$this->setEntityMapping( ???, ??? );
		
		return $this;
	}
	/* */	
	
	
	//
	public function getMonth( $sFormat = '' ) {
		
		$sMonth = $this->getEntityPropertyValue( 'month' );
		
		if ( $sFormat ) {
			return date( $sFormat, strtotime( sprintf( '1980-%s-01', $sMonth ) ) );
		}
		
		return $sMonth;
	}
	
}


