<?php

// abstract
class Geko_Wp_Payment_Cash_Admin extends Geko_Wp_Payment_Admin
{
	
	protected $_sPrefix = 'geko_pay_cash';
	
	protected $_sMenuTitle = 'Cash Transactions';
	protected $_sAdminType = 'Cash Transactions';
	
	protected $_sMenuTitleSuffix = '';
	
	
	
	
	//// init
	
	
	//
	public function add() {
		
		parent::add();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_pay_cash_transaction', 't' )
			->fieldBigInt( 'transaction_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldTinyInt( 'transaction_type_id', array( 'unsgnd' ) )
			->fieldBigInt( 'receipt_id', array( 'unsgnd' ) )
			->fieldBigInt( 'orig_receipt_id', array( 'unsgnd' ) )
			->fieldBigInt( 'customer_id', array( 'unsgnd' ) )
			->fieldVarChar( 'first_name', array( 'size' => 256 ) )
			->fieldVarChar( 'last_name', array( 'size' => 256 ) )
			->fieldVarChar( 'phone_number', array( 'size' => 256 ) )
			->fieldVarChar( 'email', array( 'size' => 256 ) )
			->fieldLongText( 'details' )
			->fieldFloat( 'amount', array( 'size' => '10,2', 'unsgnd' ) )
			->fieldTinyInt( 'status_id', array( 'unsgnd' ) )
			->fieldTinyInt( 'application_id', array( 'unsgnd' ) )
			->fieldBool( 'is_test' )
			->fieldDateTime( 'date_created' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
	}
	
	
	
	
	//// front-end display methods
	
	//
	protected function preWrapDiv() {
		?>
		<style type="text/css">

			.fix {
				clear: both;
				height: 1px;
				margin: 0 0 -1px 0;
				overflow: hidden;
			}
			
			.wrap label.main {
				display: block;
				float: left;
				width: 150px;
			}
			.wrap select {
				width: 175px;
			}
			.wrap select.multi {
				height: 6em !important;
			}
			.wrap input.text {
				width: 250px;
			}
			.wrap input.short {
				width: 70px;
			}
			.wrap input.long {
				width: 400px;
			}
			.wrap textarea {
				width: 400px;
				height: 8em;
			}
			.wrap .checkboxes {
				float: left;
			}
			
		</style>
		<?php
	}
	
	
	//
	protected function formFields() {
		?>
		
		<h3>Configuration</h3>
		
		<p>
			<label class="main">Use Live Server:</label> 
			<input id="use_live_server" name="use_live_server" type="checkbox" value="1" />
		</p>

		<p>
			<label class="main">Enable Test Options:</label> 
			<input id="enable_test_options" name="enable_test_options" type="checkbox" value="1" />
		</p>
		
		<?php
	}
	
	
	//// accessors
	
	// override super-class method
	public function isTestMode() {
		return ( $this->getOption( 'use_live_server' ) ) ? FALSE : TRUE;
	}
	
	//
	public function useLiveServer() {
		return ( $this->getOption( 'use_live_server' ) ) ? TRUE : FALSE;
	}
	
	//
	public function enableTestOptions() {
		return ( $this->getOption( 'enable_test_options' ) ) ? TRUE : FALSE;
	}
	
	
	// USE THIS VERY CAREFULLY!!!
	// output a bad string and kill the script
	public function simulateServerError() {
		if ( 'server_error' == $_POST[ 'simulate_payment_error' ] ) {
			parent::simulateServerError();
		}
	}
	
	
	//// crud methods
	
	//
	public function logResponse(
		Geko_Wp_Payment_Response $oResponse, Geko_Wp_Payment_Transaction $oTransaction
	) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$oPayment = $this->getPaymentInstance();
		
		$aResponseData = $oResponse->getResponseData();
		$sDateTime = $oDb->getTimestamp();
		
		$aInsertValues = array(
			
			'transaction_type_id' => intval( $aResponseData[ 'transaction_type_id' ] ),
			'receipt_id' => intval( $aResponseData[ 'receipt_id' ] ),
			'orig_receipt_id' => intval( $aResponseData[ 'orig_receipt_id' ] ),
			'customer_id' => intval( $aResponseData[ 'customer_id' ] ),
			'first_name' => $aResponseData[ 'first_name' ],
			
			'last_name' => $aResponseData[ 'last_name' ],
			'phone_number' => $aResponseData[ 'phone_number' ],
			'email' => $aResponseData[ 'email' ],
			'details' => $aResponseData[ 'details' ],
			'amount' => floatval( $aResponseData[ 'amount' ] ),
			
			'status_id' => intval( $oResponse->getStatusId() ),
			'application_id' => intval( $oTransaction->getApplicationId() ),
			'is_test' => intval( $this->useLiveServer() ),
			'date_created' => $sDateTime
			
		);
		
		// update the database first
		$oDb->insert(
			'##pfx##geko_pay_cash_transaction',
			$aInsertValues
		);
		
		$oResponse->setTransactionId( $oDb->lastInsertId() );
		
		return $this;
	}
	
	
	//// helper methods
	
	//
	public function getRefundInfo( $iApplicationId, $iOrigOrderId ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$oQuery = new Geko_Sql_Select();
		
		$oQuery
			->field( 't.transaction_id', 'transaction_id' )
			->field( 't.amount', 'amount' )
			->from( '##pfx##geko_pay_cash_transaction', 't' )
			->where( 't.application_id = ?', $iApplicationId )
			->where( 't.status_id = ?', self::STATUS_APPROVED )
			->where( 't.orig_receipt_id = ?', $iOrigOrderId )
			->order( 't.date_created', 'desc' )
			->limit( 1 )
		;
		
		return $oDb->fetchRowAssoc( strval( $oQuery ) );
	}
	
	
}



