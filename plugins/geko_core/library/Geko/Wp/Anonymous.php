<?php
/*
 * "geko_core/library/Geko/Wp/Anonymous.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Anonymous extends Geko_Wp_Entity
{

	//// magic methods
	
	
	// wrap
	public function __call( $sMethod, $aArgs ) {
		
		$mRes = parent::__call( $sMethod, $aArgs );
		
		// see if ACF Pro formatting can be applied
		if (
		
			( $oQuery = $this->_oQuery ) &&
			( $aFields = $oQuery->getData( 'acfpro_fields' ) ) &&
			( 0 === strpos( $sMethod, 'get' ) ) && 
			
			// see if a corresponding entity value can be found
			( $sEntityProperty = Geko_Inflector::underscore( substr( $sMethod, 3 ) ) ) && 
			( $aField = $aFields[ $sEntityProperty ] )
			
		) {
			
			// allow for intelligent formatting of data based on field meta-data
			$oAcfPro = Geko_Wp_Ext_AcfPro::getInstance();
			
			return $oAcfPro->getFormattedValue(
				$mRes, $aField[ 'type' ], $aField[ 'return_format' ], $aField[ 'sub_fields' ]
			);
		}
		
		return $mRes;
	}
	

}


