<?php

//
class Geko_Wp_Form_Plugin_Placeholder extends Geko_Entity_Plugin
{

	// constructEnd action hook
	public function constructEnd( $oEntity, $oRawEntity, $oQuery, $aData, $aQueryParams, $oPrimaryTable ) {
		
		// multi-language capability
		if ( $oQuery ) {
						
			if ( $aPlaceholders = $oQuery->getData( 'placeholders' ) ) {
				$oEntity->setData( 'placeholders', $aPlaceholders );
			}
		}
		
	}
	
	
	// filter
	public function getEntityPropertyValue( $mContent, $sProperty, $sIndex, $oEntity ) {
		
		// placeholder replacements, if any
		if ( $aPlaceholders = $oEntity->getData( 'placeholders' ) ) {
			$mContent = Geko_String::replacePlaceholders( $aPlaceholders, $mContent );
		}
		
		return $mContent;
	}
	
	
	
}


