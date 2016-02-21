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
				
				->fieldKvp( 'm1.meta_value', 'user_id:_customer_user' )
				->fieldKvp( 'm2.meta_value', 'purchase_date:_completed_date' )
				->fieldKvp( 'm3.meta_value', 'first_name:_billing_first_name' )
				->fieldKvp( 'm4.meta_value', 'last_name:_billing_last_name' )
				->fieldKvp( 'm5.meta_value', 'address1:_billing_address_1' )
				->fieldKvp( 'm6.meta_value', 'address2:_billing_address_2' )
				->fieldKvp( 'm7.meta_value', 'city:_billing_city' )
				->fieldKvp( 'm8.meta_value', 'province:_billing_state' )
				->fieldKvp( 'm9.meta_value', 'postal_code:_billing_postcode' )
				->fieldKvp( 'm10.meta_value', 'email:_billing_email' )
				->fieldKvp( 'm11.meta_value', 'telephone:_billing_phone' )
				->fieldKvp( 'm12.meta_value', 'referral_type:_billing_referral' )
				->fieldKvp( 'm13.meta_value', 'referral_name:_billing_referral_name' )
				
				->joinLeftKvp( '##pfx##postmeta', 'm*' )
					->on( 'm*.post_id = o.ID' )	
					->on( 'm*.meta_key = ?', '*' )
				
				
				
				->fieldKvp( 'um1.meta_value', 'company:billing_company' )
				
				->joinLeftKvp( '##pfx##usermeta', 'um*' )
					->on( 'um*.user_id = m1.meta_value' )
					->on( 'um*.meta_key = ?', '*' )
				
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
					
					
					
					->fieldKvp( 'oim1.meta_value', '_product_id' )
					->fieldKvp( 'oim2.meta_value', '_line_subtotal' )
					->fieldKvp( 'oim3.meta_value', 'discount_amount' )
					->fieldKvp( 'oim4.meta_value', 'tax_amount' )
					
					->joinLeftKvp( '##pfx##woocommerce_order_itemmeta', 'oim*' )
						->on( 'oim*.order_item_id = oi1.order_item_id' )
						->on( 'oim*.meta_key = ?', '*' )
					
					
					
					->joinLeft( '##pfx##postmeta', 'om1' )
						->on( 'om1.post_id = oim1.meta_value' )
						->on( 'om1.meta_key = ?', '_sku' )
						
						
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



