<?php

//
class Geko_Wp_Ext_AcfPro extends Geko_Singleton_Abstract
{
	
	
	//
	public function getPostMetaValue( $sMetaKey, $iPostId ) {

		if ( $aRes = $this->getFieldObject( $sMetaKey, $iPostId ) ) {
			
			return $this->getFormattedValue(
				$aRes[ 'value' ], $aRes[ 'type' ], $aRes[ 'return_format' ], $aRes[ 'sub_fields' ]
			);
		}
		
		return NULL;
	}
	
	
	// wrap for future possible enhancements and easier re-factoring
	public function getFieldObject( $sMetaKey, $iPostId ) {
		
		// business-end
		return get_field_object( $sMetaKey, $iPostId );
	}
	

	//
	public function getFormattedValue( $mValue, $sType, $sReturnFormat, $aSubFields = NULL ) {
		
		if ( 'repeater' == $sType ) {
			
			// wrap as Geko_Wp_Anonymous_Query/Geko_Wp_Anonymous
			
			$aRepVals = ( is_array( $mValue ) ) ? $mValue : array() ;
			
			$mValue = new Geko_Wp_Anonymous_Query( NULL, FALSE );
			$mValue->setRawEntities( $aRepVals );
			
			if ( is_array( $aSubFields ) ) {
				
				// re-format, "name" as keys is way more useful
				$aSubFieldsFmt = array();
				foreach ( $aSubFields as $aRow ) {
					$aSubFieldsFmt[ $aRow[ 'name' ] ] = $aRow;
				}
				
				$mValue->setData( 'acfpro_fields', $aSubFieldsFmt );
			}
			
		} elseif ( ( 'image' == $sType ) && ( 'array' == $sReturnFormat ) ) {
			
			// wrap as Geko_Wp_Media
			$aMediaVals = ( is_array( $mValue ) ) ? $mValue : array() ;
			
			// TO DO: figure out parent_id of image attachment, maybe it's not required?
			// $aMediaVals[ 'ID' ] = $iPostId;						// parent_id, which is the post id
			
			if ( count( $aMediaVals ) > 0 ) {
				$mValue = new Geko_Wp_Media( $aMediaVals );
			}
			
		} elseif ( 'gallery' == $sType ) {
			
			// wrap as Geko_Wp_Media_Query
			$aGalleryVals = ( is_array( $mValue ) ) ? $mValue : array() ;

			// TO DO: figure out parent_id of image attachment, maybe it's not required?
			// foreach ( $aGalleryVals as $i => $a ) {
			// 	$aGalleryVals[ $i ][ 'ID' ] = $iPostId;				// parent_id, which is the post id
			// }
			
			$mValue = new Geko_Wp_Media_Query( NULL, FALSE );
			$mValue->setRawEntities( $aGalleryVals );
			
		} elseif ( 'post_object' == $sType ) {

			// wrap as Geko_Wp_Post_Query
			$aPostObjects = ( is_array( $mValue ) ) ? $mValue : array() ;
			
			$mValue = new Geko_Wp_Post_Query( NULL, FALSE );
			$mValue->setRawEntities( $aPostObjects );
			
		}
		
		return $mValue;
	}


}
