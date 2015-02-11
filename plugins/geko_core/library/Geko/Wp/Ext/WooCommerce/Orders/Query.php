<?php

//
class Geko_Wp_Ext_WooCommerce_Orders_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			
			->field( 'o.order_id', 'order_number' )
			->field( 'o.order_item_name', 'item' )
			
			->from( '##pfx##woocommerce_order_items', 'o' )
			
			->field( 'm1.meta_value', 'user_id' )
			->joinLeft( '##pfx##postmeta', 'm1' )
				->on( 'm1.post_id = o.order_id' )
				->on( 'm1.meta_key = ?', '_customer_user' )
			
			->field( 'm2.meta_value', 'purchase_date' )
			->joinLeft( '##pfx##postmeta', 'm2' )
				->on( 'm2.post_id = o.order_id' )
				->on( 'm2.meta_key = ?', '_completed_date' )
			
			->field( 'm3.meta_value', 'first_name' )
			->joinLeft( '##pfx##postmeta', 'm3' )
				->on( 'm3.post_id = o.order_id' )
				->on( 'm3.meta_key = ?', '_billing_first_name' )
				
			->field( 'm4.meta_value', 'last_name' )
			->joinLeft( '##pfx##postmeta', 'm4' )
				->on( 'm4.post_id = o.order_id' )
				->on( 'm4.meta_key = ?', '_billing_last_name' )
				
			->field( 'm5.meta_value', 'address1' )
			->joinLeft( '##pfx##postmeta', 'm5' )
				->on( 'm5.post_id = o.order_id' )	
				->on( 'm5.meta_key = ?', '_billing_address_1' )
				
			->field( 'm6.meta_value', 'address2' )
			->joinLeft( '##pfx##postmeta', 'm6' )
				->on( 'm6.post_id = o.order_id' )	
				->on( 'm6.meta_key = ?', '_billing_address_2' )
				
			->field( 'm7.meta_value', 'city' )
			->joinLeft( '##pfx##postmeta', 'm7' )
				->on( 'm7.post_id = o.order_id' )	
				->on( 'm7.meta_key = ?', '_billing_city' )
				
			->field( 'm8.meta_value', 'province' )
			->joinLeft( '##pfx##postmeta', 'm8' )
				->on( 'm8.post_id = o.order_id' )	
				->on( 'm8.meta_key = ?', '_billing_state' )
				
			->field( 'm9.meta_value', 'postal_code' )
			->joinLeft( '##pfx##postmeta', 'm9' )
				->on( 'm9.post_id = o.order_id' )	
				->on( 'm9.meta_key = ?', '_billing_postcode' )
				
			->field( 'm10.meta_value', 'email' )
			->joinLeft( '##pfx##postmeta', 'm10' )
				->on( 'm10.post_id = o.order_id' )	
				->on( 'm10.meta_key = ?', '_billing_email' )
				
			->field( 'm11.meta_value', 'telephone' )
			->joinLeft( '##pfx##postmeta', 'm11' )
				->on( 'm11.post_id = o.order_id' )	
				->on( 'm11.meta_key = ?', '_billing_phone' )
		;
		
		
		//// filters
		
		if ( $sProductName = trim( $aParams[ 'product_name' ] ) ) {
			$oQuery->where( 'o.order_item_name = ?', $sProductName );
		}
		
		if ( $sDateLow = trim( $aParams[ 'date_low' ] ) ) {
			$oQuery->where( 'm2.meta_value >= ?', $sDateLow );
		}
		
		if ( $sDateHigh = trim( $aParams[ 'date_high' ] ) ) {
			$oQuery->where( 'm2.meta_value <= ?', $sDateHigh );
		}
		
		
		return $oQuery;
	}
	
	
}



