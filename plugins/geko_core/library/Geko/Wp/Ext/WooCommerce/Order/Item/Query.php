<?php

//
class Geko_Wp_Ext_WooCommerce_Order_Item_Query extends Geko_Wp_Entity_Query
{


	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
	
		
		$oQuery
			
			->field( 'oi.order_item_name' )
			->field( 'oi.order_id' )
			->field( 'oi.order_item_id' )
			
			->from( '##pfx##woocommerce_order_items', 'oi' )
			
			
			
			->field( 'o.ID', 'order_number' )
			->field( 'o.post_status', 'order_status' )
			->field( 'o.post_date', 'transaction_date' )
			
			->joinLeft( '##pfx##posts', 'o' )
				->on( 'o.ID = oi.order_id' )
			
			
			->where( 'oi.order_item_type = ?', 'line_item' )
			
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
				->fieldKvp( 'm14.meta_value', 'total:_order_total' )
				
				->joinLeftKvp( '##pfx##postmeta', 'm*' )
					->on( 'm*.post_id = oi.order_id' )
					->on( 'm*.meta_key = ?', '*' )
				
				
				
				->fieldKvp( 'oim1.meta_value', 'quantity:_qty' )
				->fieldKvp( 'oim2.meta_value', 'line_subtotal:_line_subtotal' )
				->fieldKvp( 'oim3.meta_value', 'line_after_discount:_line_total' )
				->fieldKvp( 'oim4.meta_value', 'line_tax:_line_tax' )
				->fieldKvp( 'oim5.meta_value', '_product_id' )
				
				->joinLeftKvp( '##pfx##woocommerce_order_itemmeta', 'oim*' )
					->on( 'oim*.order_item_id = oi.order_item_id' )
					->on( 'oim*.meta_key = ?', '*' )
				
				
				
				->fieldKvp( 'um1.meta_value', 'company:billing_company' )
				
				->joinLeftKvp( '##pfx##usermeta', 'um*' )
					->on( 'um*.user_id = m1.meta_value' )
					->on( 'um*.meta_key = ?', '*' )
				
			;
			
			
			if ( $iProductId = intval( $aParams[ 'product_id' ] ) ) {
				
				$oQuery->where( 'oim5.meta_value = ?', $iProductId );
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


