<?php

//
class Geko_Wp_Form_Plugin_LangMeta extends Geko_Entity_Plugin
{
	
	//
	public function setupEntity( $oEntity, $aParams ) {
		
		parent::setupEntity( $oEntity, $aParams );
		
		if ( is_array( $aFields = $aParams[ 'fields' ] ) ) {
			$oEntity->setData( 'lang_meta_fields', $aFields );
		}
	}
	
	
	
	// constructEnd action hook
	public function constructEnd( $oEntity, $oRawEntity, $oQuery, $aData, $aQueryParams, $oPrimaryTable ) {
		
		// multi-language capability
		if ( $oQuery ) {
			
			if ( $aLangMeta = $oQuery->getData( 'lang_meta' ) ) {
				$oEntity->setData( 'lang_meta', $aLangMeta[ $oEntity->getEntityPropertyValue( 'id' ) ] );
			}
			
		}
		
	}
	
	
	// filter
	public function getEntityPropertyValue( $mContent, $sProperty, $sIndex, $oEntity ) {
		
		// multi-language capability
		if (
			( $aLangMeta = $oEntity->getData( 'lang_meta' ) ) && 
			( is_array( $aLangMetaFields = $oEntity->getData( 'lang_meta_fields' ) ) ) && 
			( in_array( $sProperty, $aLangMetaFields ) )
		) {
			$mContent = Geko_String::coalesce( $aLangMeta[ $sProperty ], $mContent );
		}
		
		return $mContent;
	}
	
	
	
}



