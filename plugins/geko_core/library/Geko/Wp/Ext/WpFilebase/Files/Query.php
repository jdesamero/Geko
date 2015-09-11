<?php

//
class Geko_Wp_Ext_WpFilebase_Files_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			
			->field( 'f.file_id', 'id' )
			->field( 'f.file_name' )
			->field( 'f.file_category_name', 'category' )
			->field( 'f.file_custom_license_terms', 'terms' )
			->field( 'f.file_platform' )
			
			->from( '##pfx##wpfb_files', 'f' )
			
		;
		
		return $oQuery;
	}
	
	
}

