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
			->field( 'p.post_excerpt', 'product_excerpt' )
			
			->from( '##pfx##posts', 'p' )
			
			
			->field( 'm1.meta_value', 'sku' )
			->joinLeft( '##pfx##postmeta', 'm1' )
				->on( 'm1.post_id = p.ID' )
				->on( 'm1.meta_key = ?', '_sku' )
				
				
			->where( 'p.post_type = ?', 'product' )
			->where( 'p.post_status != ?', 'trash' )
			->where( 'p.post_status != ?', 'auto-draft' )
			
		;
		
		if ( $mId = $aParams[ 'order_id' ] ) {
		
			$oOrderQuery = new Geko_Sql_Select();
				
			$oOrderQuery
				
				->field( 'oi1.order_id' )
				->field( 'oim.meta_value', 'product_id' )
				
				->from( '##pfx##woocommerce_order_items', 'oi1' )
				
				->joinLeft( '##pfx##woocommerce_order_itemmeta', 'oim' )
					->on( 'oim.order_item_id = oi1.order_item_id' )
					->on( 'oim.meta_key = ?', '_product_id' )
				
				->where( 'oi1.order_id * ($)', $mId )
				->where( 'oi1.order_item_type = ?', 'line_item' )

			;
		
			$oQuery
				
				->field( 'oi.order_id' )
				
				->joinLeft( $oOrderQuery, 'oi' )
					->on( 'oi.product_id = p.ID' )
					
				->where( 'oi.order_id IS NOT NULL' )
			;
		
		}
		
		
		return $oQuery;
	}
	
	
}



