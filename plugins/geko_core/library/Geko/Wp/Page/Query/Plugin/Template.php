<?php

//
class Geko_Wp_Page_Query_Plugin_Template extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			
			->field( 'gktmpl.meta_value', 'page_template' )
			
			->joinLeft( '##pfx##postmeta', 'gktmpl' )
				->on( 'gktmpl.post_id = p.ID' )
				->on( 'gktmpl.meta_key = ?', '_wp_page_template' )
			
		;
		
		
		if ( $sTemplate = $aParams[ 'page_template' ] ) {
			$oQuery->where( 'gktmpl.meta_value = ?', $sTemplate );
		}
		
		return $oQuery;
	
	}
	
	
}



