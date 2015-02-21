<?php

//
class Geko_Wp_Ext_WooCommerce_Product_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		// base query
		
		$oQuery
			
			->field( 'p.ID', 'product_id' )
			->field( 'p.post_title', 'product_name' )
			
			->from( '##pfx##posts', 'p' )
			
			
			->field( 'm1.meta_value', 'sku' )
			->joinLeft( '##pfx##postmeta', 'm1' )
				->on( 'm1.post_id = p.ID' )
				->on( 'm1.meta_key = ?', '_sku' )
				
				
			->where( 'p.post_type = ?', 'product' )
			->where( 'p.post_status != ?', 'trash' )
			->where( 'p.post_status != ?', 'auto-draft' )
			
		;
		
		return $oQuery;
	}
	
	
}



