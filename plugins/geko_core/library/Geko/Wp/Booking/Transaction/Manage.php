<?php

//
class Geko_Wp_Booking_Transaction_Manage extends Geko_Wp_Options_Manage
{
	
	const TRANSTYPE_PURCHASE = 1;
	const TRANSTYPE_REFUND = 2;
	const TRANSTYPE_BULK_PURCHASE = 3;
	const TRANSTYPE_CORRECTION_ADD = 4;
	const TRANSTYPE_CORRECTION_SUBTRACT = 5;
	const TRANSTYPE_UNKNOWN = 99;
	
	const STATUS_SUCCESS = 1;
	const STATUS_FAILED = 2;
	const STATUS_PENDING = 3;
	const STATUS_UNKNOWN = 99;
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'bktrn_id';
	
	protected $_sSubject = 'Booking Transactions';
	protected $_sDescription = 'A table that keeps a permanent record of all transactions.';
	protected $_sType = 'bktrn';
	
	protected $_bHasDisplayMode = FALSE;
	
	//// init
	
	
	//
	public function affix() {
		Geko_Wp_Db::addPrefix( 'geko_bkng_transaction' );
		return $this;
	}
	
	
	
	//
	public function add() {
		
		parent::add();
		
		add_action( 'admin_geko_bkitm_add', array( $this, 'addItem' ), 10 );
		add_action( 'admin_geko_bkitm_delete', array( $this, 'deleteItem' ), 10 );
		
		return $this;
	}
	
	
	
	// create table
	public function install() {
		$sSql = '
			CREATE TABLE %s
			(
				bktrn_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				orig_trn_id BIGINT UNSIGNED,
				transaction_type_id TINYINT UNSIGNED,
				gateway_id TINYINT UNSIGNED,
				status_id TINYINT UNSIGNED,
				is_test TINYINT UNSIGNED,
				bkitm_id BIGINT UNSIGNED,
				details LONGTEXT,
				units FLOAT(5,2) UNSIGNED,
				cost FLOAT(10,2) UNSIGNED,
				discount FLOAT(10,2) UNSIGNED,
				tax FLOAT(10,2) UNSIGNED,
				amount FLOAT(10,2) UNSIGNED,
				user_id BIGINT UNSIGNED,
				date_created DATETIME,
				PRIMARY KEY(bktrn_id)
			)
		';
		
		Geko_Wp_Db::createTable( 'geko_bkng_transaction', $sSql );
				
		return $this;
	}
	
	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	//// accessors

	//
	public function getTransactionTypeId( $sTransactionType ) {
		
		if ( 'purchase' == $sTransactionType ) {
			return self::TRANSTYPE_PURCHASE;		
		} elseif ( 'refund' == $sTransactionType ) {
			return self::TRANSTYPE_REFUND;		
		} elseif ( 'bulk_purchase' == $sTransactionType ) {
			return self::TRANSTYPE_BULK_PURCHASE;
		} elseif ( 'correction_add' == $sTransactionType ) {
			return self::TRANSTYPE_CORRECTION_ADD;
		} elseif ( 'correction_subtract' == $sTransactionType ) {
			return self::TRANSTYPE_CORRECTION_SUBTRACT;
		}
		
		return self::TRANSTYPE_UNKNOWN;
		
	}
	
	//
	public function getStatusId( $sStatus ) {
		
		if ( 'success' == $sStatus ) {
			return self::STATUS_SUCCESS;
		} elseif ( 'failed' == $sStatus ) {
			return self::STATUS_FAILED;		
		} elseif ( 'pending' == $sStatus ) {
			return self::STATUS_PENDING;		
		}
		
		return self::STATUS_UNKNOWN;
		
	}
	
	
	//// crud methods
	
	//
	public function doAddAction( $aParams ) {
		
		global $wpdb;
		
		$oItem = $aParams[ 'item_entity' ];
		$oUser = $aParams[ 'user_entity' ];
		
		$sProductName = sprintf(
			'%s, %s; %s : %s - %s; %s hr(s)',
			$oItem->getBookingName(),
			$oItem->getScheduleName(),
			$oItem->getDateItem( 'M d, Y' ),
			$oItem->getTimeStart(),
			$oItem->getTimeEnd(),
			$oItem->getUnit()
		);
		
		$aParams[ 'product_name' ] = $sProductName;
		
		if ( $sTransType = $aParams[ 'transaction_type' ] ) {
			$aParams[ 'transaction_type_id' ] = $this->getTransactionTypeId( $sTransType );
		}
		
		$iTransTypeId = $aParams[ 'transaction_type_id' ];
		$iGatewayId = $aParams[ 'gateway_id' ];
		
		// default values
		$fUnits = 0;
		$fCost = 0;
		$fDiscount = 0;
		$fTax = 0;
		$fAmount = 0;
		
		// set-up values depending on the transaction type
		if ( self::TRANSTYPE_PURCHASE == $iTransTypeId ) {
			
			$aRes = $this->calculate( $aParams );			// perform calculation
			$aParams[ 'calculate_res' ] = $aRes;
			
			$sDetails = 'Purchase: ' . $sProductName;
			$fUnits = $aRes[ 'units' ];
			$fCost = $aRes[ 'cost' ];
			$fDiscount = $aRes[ 'discount' ];
			$fTax = $aRes[ 'tax' ];
			$fAmount = $aRes[ 'total' ];
			
		} elseif ( self::TRANSTYPE_REFUND == $iTransTypeId ) {
			
			$sDetails = 'Refund: ' . $sProductName;
			$fAmount = $aParams[ 'refund_amount' ];
			
		} else {

			$sDetails = 'Unknown: ' . $sProductName;
			
		}
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aInsertValues = array(
			
			'transaction_type_id' => $iTransTypeId,
			'gateway_id' => $iGatewayId,
			'is_test' => $aParams[ 'is_test' ],
			'bkitm_id' => $oItem->getId(),
			'details' => $sDetails,
			
			'units' => $fUnits,
			'cost' => $fCost,
			'discount' => $fDiscount,
			'tax' => $fTax,
			'amount' => $fAmount,
			
			'user_id' => $oUser->getId(),
			'date_created' => $sDateTime
			
		);
		
		$aInsertFormat = array(
			'%d', '%d', '%d', '%d', '%s',
			'%d', '%f', '%f', '%f', '%f',
			'%d', '%s'
		);
		
		// update the database first
		$wpdb->insert(
			$wpdb->geko_bkng_transaction,
			$aInsertValues,
			$aInsertFormat
		);
		
		$aParams[ 'entity_id' ] = $wpdb->get_var( 'SELECT LAST_INSERT_ID()' );
		
		return $aParams;
	}
	
	//
	public function doEditAction( $aParams ) { }
	public function doDelAction( $aParams ) { }
	
	
	
	//// helper methods
	
	//
	public function calculate( $aParams ) {
		
		$oItem = $aParams[ 'item_entity' ];
		$oUser = $aParams[ 'user_entity' ];
		$iProvId = $aParams[ 'province_id' ];
		
		// calculate price
		$fDiscountDec = ( intval( $oUser->getValue( 'discount' ) ) / 100 );			// make this optional
		
		$fTaxDec = 0;
		$sTaxLabel = '';
		
		if ( $iProvId ) {
			$oGatewayAdmin = Geko_Wp_Payment::getGatewayAdmin();
			$aRates = $oGatewayAdmin->getTaxRates();
			$fTaxDec = floatval( $aRates[ $iProvId ][ 'pct' ] / 100 );
			$sTaxLabel = $aRates[ $iProvId ][ 'label' ];
		}
		
		$fUnits = floatval( $oItem->getUnit() );
		$fCost = number_format( floatval( $oItem->getCostFmt() ), 2 );
		
		if ( $fCostOverride = $aParams[ 'cost_override' ] ) {
			$fCost = number_format( floatval( $fCostOverride ), 2 );
		}
		
		$fSubTotal = $fCost;
		$fDiscount = number_format( $fSubTotal * $fDiscountDec, 2 );
		$fBeforeTax = number_format( $fSubTotal - $fDiscount, 2 );		
		$fTax = number_format( ( $fBeforeTax ) * $fTaxDec, 2 );
		$fTotal = number_format( $fBeforeTax + $fTax, 2 );
		
		return array(
			'units' => $fUnits,
			'cost' => $fCost,
			'sub_total' => $fSubTotal,
			'discount' => $fDiscount,
			'discount_dec' => $fDiscountDec,
			'discount_pct' => ( $fDiscountDec * 100 ),
			'before_tax' => $fBeforeTax,
			'tax_label' => $sTaxLabel,
			'tax' => $fTax,
			'tax_dec' => $fTaxDec,
			'tax_pct' => ( $fTaxDec * 100 ),
			'total' => $fTotal
		);
	}
	
	//
	public function setStatus( $iBktrnId, $sStatus, $iOrigTrnId = 0 ) {
		
		global $wpdb;
		
		$aUpdate = array( 'status_id' => $this->getStatusId( $sStatus ) );
		$aUpdateFmt = array( '%d' );
		
		if ( $iOrigTrnId ) {
			$aUpdate[ 'orig_trn_id' ] = $iOrigTrnId;
			$aUpdateFmt[] = '%d';
		}
		
		$wpdb->update(
			$wpdb->geko_bkng_transaction,
			$aUpdate,
			array( 'bktrn_id' => $iBktrnId ),
			$aUpdateFmt,
			array( '%d' )
		);
		
		return $this;
	}
	
	//
	public function getRefundInfo( $aParams ) {

		global $wpdb;
		
		$oItem = $aParams[ 'item_entity' ];
		$oUser = $aParams[ 'user_entity' ];
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'btr.bktrn_id' )
			->field( 'btr.amount' )
			->field( 'btr.date_created' )
			->field( 'btr.status_id' )
			->field( 'btr.gateway_id' )
			->from( $wpdb->geko_bkng_transaction, 'btr' )
			->joinLeft( $wpdb->geko_bkng_transaction, 'btc' )
				->on( 'btc.orig_trn_id = btr.bktrn_id' )
				->on( 'btc.status_id = ?', $this->getStatusId( 'success' ) )
				->on( 'btc.transaction_type_id = ?', $this->getTransactionTypeId( 'refund' ) )
			->where( 'btr.bkitm_id = ?', $oItem->getId() )
			->where( 'btr.transaction_type_id = ?', $this->getTransactionTypeId( 'purchase' ) )
			->where(
				'( btr.status_id = :success ) OR ( btr.status_id = :pending )',
				array(
					'success' => $this->getStatusId( 'success' ),
					'pending' => $this->getStatusId( 'pending' )
				)
			)
			->where( 'btr.user_id = ?', $oUser->getId() )
			->where( 'btc.orig_trn_id IS NULL' )
			->order( 'btr.date_created', 'DESC' )
		;
		
		$aRes = $wpdb->get_results( strval( $oQuery ), ARRAY_A );
		
		return ( is_array( $aRes ) ) ? $aRes : array();
		
	}
	
	
	//
	public function getRefundableItems( $aRefundInfo ) {
		
		$aRes = array();
		
		foreach ( $aRefundInfo as $aItem ) {
			if ( $this->getStatusId( 'success' ) == $aItem[ 'status_id' ] ) {
				$aRes[] = $aItem[ 'bktrn_id' ];
			}
		}
		
		return $aRes;
		
	}
	
	//
	public function getRefundItem( $iOrigOrderId, $aRefundInfo ) {
		foreach ( $aRefundInfo as $aItem ) {
			if ( $iOrigOrderId == $aItem[ 'bktrn_id' ] ) {
				return $aItem;
			}
		}
	}
	
	// DEPRACATED ???
	public function getRefundAmount( $iOrigOrderId, $aRefundInfo ) {
		foreach ( $aRefundInfo as $aItem ) {
			if ( $iOrigOrderId == $aItem[ 'bktrn_id' ] ) {
				return $aItem[ 'amount' ];
			}
		}
	}
	
	
	// DEPRACATED ???
	public function getLatestSuccessfulTransactionQuery( $aParams = array() ) {
		
		global $wpdb;
		
		// prepare sub-query that determines the latest successful (!)
		// transaction date made by a user for an event
		
		$oSubQuery = new Geko_Sql_Select();
		$oSubQuery
			->field( 'btr2.bkitm_id' )
			->field( 'MAX( btr2.date_created )', 'date_last_transaction' )
			->from( $wpdb->geko_bkng_transaction, 'btr2' )
			->where( 'btr2.status_id = ?', $this->getStatusId( 'success' ) )
			->group( 'btr2.bkitm_id' )
		;
		
		// re-join to determine the transaction type and other details
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'btr.bktrn_id' )
			->field( 'btr.transaction_type_id' )
			->field( 'btr.bkitm_id' )
			->field( 'btr.user_id' )
			->from( $wpdb->geko_bkng_transaction, 'btr' )
			->joinInner( $oSubQuery, 'blt' )
				->on( 'blt.bkitm_id = btr.bkitm_id' )
				->on( 'blt.date_last_transaction = btr.date_created' )
		;
		
		if ( $aParams[ 'per_user' ] ) {
			
			$oSubQuery
				->field( 'btr2.user_id' )
				->group( 'btr2.user_id' )		
			;
			
			$oQuery
				->on( 'blt.user_id = btr.user_id', NULL, 'btr' )
			;
			
		}
		
		return $oQuery;
		
	}
	
	
	//
	public function getSlotsTakenQuery( $aParams = array() ) {
		
		global $wpdb;
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'bsl.bkitm_id' )
			->field( 'COUNT(*)', 'slots_taken' )
			->from( $wpdb->geko_bkng_transaction, 'bsl' )
			->joinLeft( $wpdb->geko_bkng_transaction, 'bsc' )
				->on( 'bsc.orig_trn_id = bsl.bktrn_id' )
				->on( 'bsc.status_id = ?', $this->getStatusId( 'success' ) )
				->on( 'bsc.transaction_type_id = ?', $this->getTransactionTypeId( 'refund' ) )
			->where(
				'( bsl.status_id = :success ) OR ( bsl.status_id = :pending )',
				array(
					'success' => $this->getStatusId( 'success' ),
					'pending' => $this->getStatusId( 'pending' )
				)
			)
			->where( 'bsl.transaction_type_id = ?', $this->getTransactionTypeId( 'purchase' ) )
			->where( 'bsc.orig_trn_id IS NULL' )
			->group( 'bsl.bkitm_id' )
		;
		
		if ( $aParams[ 'per_user' ] ) {	
			$oQuery
				->field( 'bsl.user_id' )
				->group( 'bsl.user_id' )		
			;
		}
		
		return $oQuery;
	}
	
	
	//
	public function cleanupStalePendingItems() {
	
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare(
			"
				UPDATE					$wpdb->geko_bkng_transaction
				SET						status_id = %d
				WHERE					( status_id = %d ) AND 
										( date_created < %s )
			",
			$this->getStatusId( 'failed' ),
			$this->getStatusId( 'pending' ),
			Geko_Db_Mysql::getTimestamp( time() - ( 60 * 15 ) )			// anything pending for more than 15 mins should be cleaned-up
		) );
		
	}
	
	
	//
	public function recordPrivateTransaction( $aParams ) {
		
		global $wpdb;
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aInsertValues = array(
			
			'transaction_type_id' => $this->getTransactionTypeId( 'purchase' ),
			'status_id' => $this->getStatusId( 'success' ),
			'gateway_id' => Geko_Wp_Payment::GATEWAY_ID_CASH,
			'is_test' => $aParams[ 'is_test' ],
			'bkitm_id' => $aParams[ 'bkitm_id' ],
			
			'details' => $aParams[ 'details' ],
			'units' => $aParams[ 'units' ],
			'amount' => $aParams[ 'amount' ],
			'user_id' => $aParams[ 'user_id' ],
			'date_created' => $sDateTime
			
		);
		
		$aInsertFormat = array(
			'%d', '%d', '%d', '%d', '%d',
			'%s', '%d', '%f', '%d', '%s'
		);
		
		// update the database first
		$wpdb->insert(
			$wpdb->geko_bkng_transaction,
			$aInsertValues,
			$aInsertFormat
		);
		
	}
	
	//
	public function deletePrivateTransactions( $iScheduleId ) {
		
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM $wpdb->geko_bkng_transaction WHERE bkitm_id IN (
				SELECT bkitm_id FROM $wpdb->geko_bkng_item WHERE bksch_id = %d
			)",
			$iScheduleId
		) );
		
	}
	
	//
	public function addItem( $oBkitm ) {
		
		if ( $oBkitm->isPrivate() ) {
			
			global $user_ID;
			
			$sDetails = sprintf(
				'Private Booking Purchase: %s, %s; %s : %s - %s; %s hr(s)',
				$oBkitm->getBookingName(),
				$oBkitm->getScheduleName(),
				$oBkitm->getDateItem( 'M d, Y' ),
				$oBkitm->getTimeStart(),
				$oBkitm->getTimeEnd(),
				$oBkitm->getUnit()
			);
			
			$this->recordPrivateTransaction( array(
				// 'is_test' => ???,			// how to pass this???
				'bkitm_id' => $oBkitm->getId(),
				'details' => $sDetails,
				'units' => $oBkitm->getUnit(),
				'amount' => floatval( $oBkitm->getCost() ),
				'user_id' => $user_ID
			) );
			
		}
		
	}
	
	//
	public function deleteItem( $oBkitm ) {

		if ( $oBkitm->isPrivate() ) {
			
			global $wpdb;
			
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM $wpdb->geko_bkng_transaction WHERE bkitm_id = %d",
				$oBkitm->getId()
			) );
			
		}
		
	}
	
}



