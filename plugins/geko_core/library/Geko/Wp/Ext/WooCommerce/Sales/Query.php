<?php

//
class Geko_Wp_Ext_WooCommerce_Sales_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		// base query
	if ( $iProdId = $aParams[ 'prod_id' ] ) {
		
			$oQuery
			->field( 'COUNT(*)', 'num_sales' )
			
			->from( '##pfx##woocommerce_order_items', 'oi' )
			
			->joinLeft( '##pfx##woocommerce_order_itemmeta', 'oim' )
				->on( 'oi.order_item_id = oim.order_item_id' )
				->on( 'oim.meta_key = ?', '_product_id' )
			
			->where( 'oi.order_item_type = ?', 'line_item' )
			->where( sprintf( 'oim.meta_value = %d', $iProdId ) )
			
			->group( 'oi.order_item_name' )
		;
		
		}
					
		
		return $oQuery;
	}
	
	
}



