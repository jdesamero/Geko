<?php

// listing
class Geko_Wp_Contact_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		//// by contact_id
		
		if ( $aParams[ 'contact_id' ] ) {	
			$oQuery->where( 'c.contact_id = ?', $aParams[ 'contact_id' ] );
		}
		
		//// by object_id / objtype_id
		
		if ( $aParams[ 'object_type' ] ) {
			$aParams[ 'objtype_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'object_type' ] );
		}
		
		if ( $aParams[ 'object_id' ] && $aParams[ 'objtype_id' ] ) {
			
			$oQuery
				->where( 'c.object_id * ($)', $aParams[ 'object_id' ] )
				->where( 'c.objtype_id = ?', $aParams[ 'objtype_id' ] )
			;
			
		}
		
		//// by subtype_id
		
		if ( $aParams[ 'sub_type' ] ) {
			$aParams[ 'subtype_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'sub_type' ] );
		}
		
		if ( $aParams[ 'subtype_id' ] ) {
			$oQuery->where( 'c.subtype_id = ?', $aParams[ 'subtype_id' ] );
		}
		
		
		return $oQuery;
	}
	
	
}

