<?php

//
class Geko_Wp_Ext_WooCommerce_Order_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		// base query
		
		$oQuery
			
			->from( '##pfx##posts', 'o' )
			->where( 'o.post_type = ?', 'shop_order' )
			->where( 'o.post_status != ?', 'trash' )
			->where( 'o.post_status != ?', 'auto-draft' )
			
		;
		
		// there is aggregate mode and standard result set
		
		if ( $aParams[ 'aggregate_mode' ] ) {

			$oQuery
				->field( 'COUNT(*)', 'num_res' )
				->field( 'MIN( o.post_date )', 'transaction_min_date' )
				->field( 'MAX( o.post_date )', 'transaction_max_date' )
			;
		
		} else {
			
			$oQuery
				
				->field( 'o.ID', 'order_number' )
				->field( 'o.post_status', 'order_status' )
				->field( 'o.post_date', 'transaction_date' )
							
				
				// meta fields
				
				->field( 'm1.meta_value', 'user_id' )
				->joinLeft( '##pfx##postmeta', 'm1' )
					->on( 'm1.post_id = o.ID' )
					->on( 'm1.meta_key = ?', '_customer_user' )
				
				->field( 'm2.meta_value', 'purchase_date' )
				->joinLeft( '##pfx##postmeta', 'm2' )
					->on( 'm2.post_id = o.ID' )
					->on( 'm2.meta_key = ?', '_completed_date' )
				
				->field( 'm3.meta_value', 'first_name' )
				->joinLeft( '##pfx##postmeta', 'm3' )
					->on( 'm3.post_id = o.ID' )
					->on( 'm3.meta_key = ?', '_billing_first_name' )
					
				->field( 'm4.meta_value', 'last_name' )
				->joinLeft( '##pfx##postmeta', 'm4' )
					->on( 'm4.post_id = o.ID' )
					->on( 'm4.meta_key = ?', '_billing_last_name' )
					
				->field( 'm5.meta_value', 'address1' )
				->joinLeft( '##pfx##postmeta', 'm5' )
					->on( 'm5.post_id = o.ID' )	
					->on( 'm5.meta_key = ?', '_billing_address_1' )
					
				->field( 'm6.meta_value', 'address2' )
				->joinLeft( '##pfx##postmeta', 'm6' )
					->on( 'm6.post_id = o.ID' )	
					->on( 'm6.meta_key = ?', '_billing_address_2' )
					
				->field( 'm7.meta_value', 'city' )
				->joinLeft( '##pfx##postmeta', 'm7' )
					->on( 'm7.post_id = o.ID' )	
					->on( 'm7.meta_key = ?', '_billing_city' )
					
				->field( 'm8.meta_value', 'province' )
				->joinLeft( '##pfx##postmeta', 'm8' )
					->on( 'm8.post_id = o.ID' )	
					->on( 'm8.meta_key = ?', '_billing_state' )
					
				->field( 'm9.meta_value', 'postal_code' )
				->joinLeft( '##pfx##postmeta', 'm9' )
					->on( 'm9.post_id = o.ID' )	
					->on( 'm9.meta_key = ?', '_billing_postcode' )
					
				->field( 'm10.meta_value', 'email' )
				->joinLeft( '##pfx##postmeta', 'm10' )
					->on( 'm10.post_id = o.ID' )	
					->on( 'm10.meta_key = ?', '_billing_email' )
					
				->field( 'm11.meta_value', 'telephone' )
				->joinLeft( '##pfx##postmeta', 'm11' )
					->on( 'm11.post_id = o.ID' )	
					->on( 'm11.meta_key = ?', '_billing_phone' )
				
				->field( 'm12.meta_value', 'referral_type' )
				->joinLeft( '##pfx##postmeta', 'm12' )
					->on( 'm12.post_id = o.ID' )	
					->on( 'm12.meta_key = ?', '_billing_referral' )
					
				->field( 'm13.meta_value', 'referral_name' )
				->joinLeft( '##pfx##postmeta', 'm13' )
					->on( 'm13.post_id = o.ID' )	
					->on( 'm13.meta_key = ?', '_billing_referral_name' )

			;
			
			
			//// joins
			
			if ( $aParams[ 'add_item_fields' ] ) {
				
				$oItemsQuery = new Geko_Sql_Select();
				$oItemsQuery
					
					->field( 'oi1.order_id' )
					->field( "GROUP_CONCAT(
						IF( oi1.order_item_type = 'line_item', CONCAT( oi1.order_item_name, ' (SKU: ', om1.meta_value, ')' ), NULL ) 
					SEPARATOR ', ' )", 'order_items' )
					
					->field( "SUM( IF( oi1.order_item_type = 'line_item', CAST( oim2.meta_value AS DECIMAL( 12, 2 ) ), 0 ) )", 'amount' )
					->field( "SUM( IF( oi1.order_item_type = 'coupon', CAST( oim3.meta_value AS DECIMAL( 12, 2 ) ), 0 ) )", 'discount' )
					->field( "SUM( IF( oi1.order_item_type = 'tax', CAST( oim4.meta_value AS DECIMAL( 12, 2 ) ), 0 ) )", 'tax' )
					
					->from( '##pfx##woocommerce_order_items', 'oi1' )
					
					
					->joinLeft( '##pfx##woocommerce_order_itemmeta', 'oim1' )
						->on( 'oim1.order_item_id = oi1.order_item_id' )
						->on( 'oim1.meta_key = ?', '_product_id' )
					
					->joinLeft( '##pfx##postmeta', 'om1' )
						->on( 'om1.post_id = oim1.meta_value' )
						->on( 'om1.meta_key = ?', '_sku' )
					
					
					
					->joinLeft( '##pfx##woocommerce_order_itemmeta', 'oim2' )
						->on( 'oim2.order_item_id = oi1.order_item_id' )
						->on( 'oim2.meta_key = ?', '_line_subtotal' )
					
					->joinLeft( '##pfx##woocommerce_order_itemmeta', 'oim3' )
						->on( 'oim3.order_item_id = oi1.order_item_id' )
						->on( 'oim3.meta_key = ?', 'discount_amount' )
					
					->joinLeft( '##pfx##woocommerce_order_itemmeta', 'oim4' )
						->on( 'oim4.order_item_id = oi1.order_item_id' )
						->on( 'oim4.meta_key = ?', 'tax_amount' )
					
					->group( 'oi1.order_id' )
				;				
				
				
				$oQuery
					
					->field( 'oi.order_items' )
					->field( 'oi.amount' )
					->field( 'oi.discount' )
					->field( 'oi.tax' )
					
					->joinLeft( $oItemsQuery, 'oi' )
						->on( 'o.ID = oi.order_id' )
				;

				
				if ( $iProductId = intval( $aParams[ 'product_id' ] ) ) {
					
					$oItemsQuery->field( sprintf( 'SUM( IF( oim1.meta_value = %d, 1, 0 ) )', $iProductId ), 'prod_check' );
					
					$oQuery->where( 'oi.prod_check > 0' );
				}
				
				
			}
			
			
		}
		
		
		//// filters
		
		if ( $sStatus = trim( $aParams[ 'status' ] ) ) {
			$oQuery->where( 'o.post_status = ?', $sStatus );
		}
		
		if ( $sDateLow = trim( $aParams[ 'transaction_min_date' ] ) ) {
			$oQuery->where( 'o.post_date >= ?', $sDateLow );
		}
		
		if ( $sDateHigh = trim( $aParams[ 'transaction_max_date' ] ) ) {
			$oQuery->where( 'o.post_date <= ?', $sDateHigh );
		}
		
		
		// apply filters for custom fields
		$oQuery = apply_filters( __METHOD__, $oQuery, $aParams, $this );
		
		
		return $oQuery;
	}
	
	
}



