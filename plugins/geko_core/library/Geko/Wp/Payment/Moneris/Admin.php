<?php

require_once( sprintf(
	'%s/external/libs/moneris/mpgClasses.php',
	dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) )
) );

// abstract
class Geko_Wp_Payment_Moneris_Admin extends Geko_Wp_Payment_Admin
{
	
	protected $_sPrefix = 'geko_pay_mon';
	
	protected $_sMenuTitle = 'Moneris';
	protected $_sAdminType = 'Moneris Payment Gateway';
	
	protected $_sMenuTitleSuffix = '';
	
	
	
	
	//// init
	
	
	//
	public function add() {
		
		parent::add();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_pay_moneris_transaction', 'm' )
			->fieldVarChar( 'receipt_id', array( 'size' => 64 ) )
			->fieldVarChar( 'card_type', array( 'size' => 16 ) )
			->fieldVarChar( 'amount', array( 'size' => 32 ) )
			->fieldVarChar( 'transaction_id', array( 'size' => 32 ) )
			->fieldVarChar( 'transaction_type', array( 'size' => 32 ) )
			->fieldVarChar( 'reference_number', array( 'size' => 64 ) )
			->fieldVarChar( 'response_code', array( 'size' => 16 ) )
			->fieldVarChar( 'iso_code', array( 'size' => 16 ) )
			->fieldLongText( 'message' )
			->fieldVarChar( 'authorization_code', array( 'size' => 32 ) )
			->fieldLongText( 'complete' )
			->fieldVarChar( 'transaction_date', array( 'size' => 16 ) )
			->fieldVarChar( 'transaction_time', array( 'size' => 16 ) )
			->fieldLongText( 'ticket' )
			->fieldLongText( 'timed_out' )
			->fieldBigInt( 'orig_receipt_id', array( 'unsgnd' ) )
			->fieldTinyInt( 'status_id', array( 'unsgnd' ) )
			->fieldTinyInt( 'application_id', array( 'unsgnd' ) )
			->fieldBool( 'is_test' )
			->fieldDateTime( 'date_created' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
	}
		
	
	
	// create table
	public function install() {
		
		parent::install();
		
		$this->createTableOnce();
		
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
	protected function preOptionsFormDiv() {
		
		$oPayment = $this->getPaymentInstance();
		
		if ( $oPayment->hasValidLibrary() ): ?>
		
			<p>API Version: <strong><?php echo $oPayment->getGlobal( 'API_VERSION' ); ?></strong></p>
		
		<?php else: ?>
			
			<p style="color: red;">The Moneris Payment Library is not installed!!!</p>
			
		<?php endif;
		
	}
	
	
	//
	protected function formFields() {
		?>
		
		<h3>Live Settings</h3>
		
		<p>
			<label class="main">Host:</label> 
			<input id="live_host" name="live_host" type="text" class="text" value="www3.moneris.com" />
		</p>
		<p>
			<label class="main">Store ID:</label> 
			<input id="live_store_id" name="live_store_id" type="text" class="text" value="" />
		</p>
		<p>
			<label class="main">API Token:</label> 
			<input id="live_api_token" name="live_api_token" type="text" class="text" value="" />
		</p>
		<p>
			<label class="main">Crypt Type:</label> 
			<input id="live_crypt_type" name="live_crypt_type" type="text" class="text short" value="7" />
		</p>
		<p>
			<label class="main">Client Timeout:</label> 
			<input id="live_client_timeout" name="live_client_timeout" type="text" class="text short" value="60" />
		</p>
		<p>
			<a href="https://<?php echo Geko_String::coalesce( $this->getOption( 'live_host' ), 'www3.moneris.com' ); ?>/mpg" target="_blank">View Live Merchant Resource Centre</a>
		</p>
		
		<h3>Test Settings</h3>
		
		<p>
			<label class="main">Host:</label> 
			<input id="test_host" name="test_host" type="text" class="text" value="esqa.moneris.com" />
		</p>
		<p>
			<label class="main">Store ID:</label> 
			<input id="test_store_id" name="test_store_id" type="text" class="text" value="store1" />
		</p>
		<p>
			<label class="main">API Token:</label> 
			<input id="test_api_token" name="test_api_token" type="text" class="text" value="yesguy" />
		</p>
		<p>
			<label class="main">Crypt Type:</label> 
			<input id="test_crypt_type" name="test_crypt_type" type="text" class="text short" value="7" />
		</p>
		<p>
			<label class="main">Client Timeout:</label> 
			<input id="test_client_timeout" name="test_client_timeout" type="text" class="text short" value="60" />
		</p>
		<p>
			<a href="https://<?php echo Geko_String::coalesce( $this->getOption( 'test_host' ), 'esqa.moneris.com' ); ?>/mpg" target="_blank">View Test Merchant Resource Centre</a>
		</p>
		
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
	
	
	//
	public function echoTestOptions() {
		
		if ( $this->enableTestOptions() ): ?>
			<strong>Simulate Error</strong>
			<div>
				<input id="simulate_error-1" name="simulate_payment_error" type="radio" value="no_error" /> <label>No Error</label><br />
				<input id="simulate_error-2" name="simulate_payment_error" type="radio" value="server_error" /> <label>Server Error</label><br />
				<input id="simulate_error-3" name="simulate_payment_error" type="radio" value="malformed_request" /> <label>Malformed Request</label><br />
				<input id="simulate_error-4" name="simulate_payment_error" type="radio" value="timeout_error_real" /> <label>Timeout Error (Real)</label><br />
				<input id="simulate_error-5" name="simulate_payment_error" type="radio" value="declined" /> <label>Declined</label><br />
				<input id="simulate_error-6" name="simulate_payment_error" type="radio" value="hold_card" /> <label>Hold Card</label><br />
				<input id="simulate_error-7" name="simulate_payment_error" type="radio" value="system_timeout" /> <label>System Timeout</label><br />
			</div>
		<?php endif;
		
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
	public function getHost() {
		if ( $this->useLiveServer() ) {
			return Geko_String::coalesce( $this->getOption( 'live_host' ), 'www3.moneris.com' );
		}
		return Geko_String::coalesce( $this->getOption( 'test_host' ), 'esqa.moneris.com' );
	}
	
	//
	public function getStoreId() {
		if ( $this->useLiveServer() ) {
			return $this->getOption( 'live_store_id' );
		}
		return Geko_String::coalesce( $this->getOption( 'test_store_id' ), 'store1' );
	}
	
	//
	public function getApiToken() {
		if ( $this->useLiveServer() ) {
			return $this->getOption( 'live_api_token' );
		}
		return Geko_String::coalesce( $this->getOption( 'test_api_token' ), 'yesguy' );
	}
	
	//
	public function getCryptType() {
		if ( $this->useLiveServer() ) {
			return $this->getOption( 'live_crypt_type' );
		}
		return Geko_String::coalesce( $this->getOption( 'test_crypt_type' ), 7 );
	}
	
	//
	public function getClientTimeout() {
		if ( $this->useLiveServer() ) {
			return Geko_String::coalesce( $this->getOption( 'live_client_timeout' ), 60 );
		}
		return Geko_String::coalesce( $this->getOption( 'test_client_timeout' ), 60 );
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
		
		$oPayment = $this->getPaymentInstance();
		
		if ( $oPayment->hasValidLibrary() ) {
			
			$oDb = Geko_Wp::get( 'db' );
			
			$aResponseData = $oResponse->getResponseData();
			$sDateTime = $oDb->getTimestamp();
			
			$aInsertValues = array(
				
				'receipt_id' => $aResponseData[ 'receipt_id' ],
				'card_type' => $aResponseData[ 'card_type' ],
				'amount' => $aResponseData[ 'amount' ],
				'transaction_id' => $aResponseData[ 'transaction_id' ],
				'transaction_type' => $aResponseData[ 'transaction_type' ],
				
				'reference_number' => $aResponseData[ 'reference_number' ],
				'response_code' => $aResponseData[ 'response_code' ],
				'iso_code' => $aResponseData[ 'iso_code' ],
				'message' => $aResponseData[ 'message' ],
				'authorization_code' => $aResponseData[ 'authorization_code' ],
				
				'complete' => $aResponseData[ 'complete' ],
				'transaction_date' => $aResponseData[ 'transaction_date' ],
				'transaction_time' => $aResponseData[ 'transaction_time' ],
				'ticket' => $aResponseData[ 'ticket' ],
				'timed_out' => $aResponseData[ 'timed_out' ],
				
				'orig_receipt_id' => intval( $oTransaction->getReceiptId() ),
				'status_id' => intval( $oResponse->getStatusId() ),
				'application_id' => intval( $oTransaction->getApplicationId() ),
				'is_test' => intval( $this->useLiveServer() ),
				'date_created' => $sDateTime
				
			);
			
			// update the database first
			$oDb->insert( '##pfx##geko_pay_moneris_transaction', $aInsertValues );
			
		}
		
		return $this;
	}
	
	
	//// helper methods
	
	//
	public function getRefundInfo( $iApplicationId, $iOrigOrderId ) {

		$oDb = Geko_Wp::get( 'db' );
		
		$oQuery = new Geko_Sql_Select();
		
		$oQuery
			->field( 'mt.transaction_id', 'transaction_id' )
			->field( 'mt.reference_number', 'reference_number' )
			->field( 'mt.amount', 'amount' )
			->from( '##pfx##geko_pay_moneris_transaction', 'mt' )
			->where( 'mt.application_id = ?', $iApplicationId )
			->where( 'mt.status_id = ?', self::STATUS_APPROVED )
			->where( 'mt.orig_receipt_id = ?', $iOrigOrderId )
			->order( 'mt.date_created', 'desc' )
			->limit( 1 )
		;
		
		return $oDb->fetchRowAssoc( strval( $oQuery ) );
	}
	
	
}



