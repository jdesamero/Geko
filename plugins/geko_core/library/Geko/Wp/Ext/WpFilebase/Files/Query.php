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
			->field( 'f.file_name', 'filename' )
			->field( 'f.file_display_name', 'display_name' )
			->field( 'f.file_category_name', 'category' )
			->field( 'f.file_custom_license_terms', 'terms' )
			->field( 'f.file_platform', 'platform' )
			->field( 'f.file_version', 'version' )
			
			->field( 'c.cat_folder', 'folder' )
			
			->from( '##pfx##wpfb_files', 'f' )

			->joinLeft( '##pfx##wpfb_cats', 'c' )
				->on( 'c.cat_id = f.file_category' )
			
		;
		
		return $oQuery;
	}
	
	
}

