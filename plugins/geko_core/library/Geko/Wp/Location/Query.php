<?php

// listing
class Geko_Wp_Location_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			
			->field( 'p.province_name' )
			->field( 'p.province_abbr' )
			->field( 'p.country_id' )
			->joinLeft( $wpdb->geko_location_province, 'p' )
				->on( 'p.province_id = a.province_id' )

			->field( 'c.continent_id' )
			->joinLeft( $wpdb->geko_location_country, 'c' )
				->on( 'c.country_id = p.country_id' )
			
		;
		
		
		//// by address_id
		
		if ( $aParams[ 'address_id' ] ) {	
			$oQuery->where( 'a.address_id = ?', $aParams[ 'address_id' ] );
		}
		
		//// by object_id / objtype_id
		
		if ( $aParams[ 'object_type' ] ) {
			$aParams[ 'objtype_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'object_type' ] );
		}
		
		if ( $aParams[ 'object_id' ] && $aParams[ 'objtype_id' ] ) {
			
			$oQuery
				->where( 'a.object_id * ($)', $aParams[ 'object_id' ] )
				->where( 'a.objtype_id = ?', $aParams[ 'objtype_id' ] )
			;
			
		}
		
		//// by subtype_id
		
		if ( $aParams[ 'sub_type' ] ) {
			$aParams[ 'subtype_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'sub_type' ] );
		}
		
		if ( $aParams[ 'subtype_id' ] ) {
			$oQuery->where( 'a.subtype_id = ?', $aParams[ 'subtype_id' ] );
		}
		
		
		return $oQuery;
	}
	
	
}


