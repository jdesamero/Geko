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
	public function affix() {
		Geko_Wp_Db::addPrefix( 'geko_pay_cash_transaction' );
		return $this;
	}
	
	
	
	// create table
	public function install() {
		
		// table structure specific to cash transactions
		$sSql = '
			CREATE TABLE %s
			(
				transaction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				transaction_type_id TINYINT UNSIGNED,
				receipt_id BIGINT UNSIGNED,
				orig_receipt_id BIGINT UNSIGNED,
				customer_id BIGINT UNSIGNED,
				first_name VARCHAR(255),
				last_name VARCHAR(255),
				phone_number VARCHAR(255),
				email VARCHAR(255),
				details LONGTEXT,
				amount FLOAT(10,2) UNSIGNED,
				status_id TINYINT UNSIGNED,
				application_id TINYINT UNSIGNED,
				is_test TINYINT UNSIGNED,
				date_created DATETIME,
				PRIMARY KEY(transaction_id)
			)
		';
		
		Geko_Wp_Db::createTable( 'geko_pay_cash_transaction', $sSql );
				
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
		
		global $wpdb;
		
		$oPayment = $this->getPaymentInstance();
		
		$aResponseData = $oResponse->getResponseData();
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		
		$aInsertValues = array(
			
			'transaction_type_id' => $aResponseData[ 'transaction_type_id' ],
			'receipt_id' => $aResponseData[ 'receipt_id' ],
			'orig_receipt_id' => $aResponseData[ 'orig_receipt_id' ],
			'customer_id' => $aResponseData[ 'customer_id' ],
			'first_name' => $aResponseData[ 'first_name' ],
			
			'last_name' => $aResponseData[ 'last_name' ],
			'phone_number' => $aResponseData[ 'phone_number' ],
			'email' => $aResponseData[ 'email' ],
			'details' => $aResponseData[ 'details' ],
			'amount' => $aResponseData[ 'amount' ],
			
			'status_id' => $oResponse->getStatusId(),
			'application_id' => $oTransaction->getApplicationId(),
			'is_test' => ( $this->useLiveServer() ? FALSE : TRUE ),
			'date_created' => $sDateTime
			
		);
		
		$aInsertFormat = array(
			'%d', '%d', '%d', '%d', '%s',
			'%s', '%s', '%s', '%s', '%f',
			'%d', '%d', '%d', '%s'
		);
		
		// update the database first
		$wpdb->insert(
			$wpdb->geko_pay_cash_transaction,
			$aInsertValues,
			$aInsertFormat
		);
		
		$oResponse->setTransactionId(
			$wpdb->get_var( 'SELECT LAST_INSERT_ID()' )
		);
		
		return $this;
	}
	
	
	//// helper methods
	
	//
	public function getRefundInfo( $iApplicationId, $iOrigOrderId ) {

		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare(
			"
				SELECT				transaction_id,
									amount
				FROM				$wpdb->geko_pay_cash_transaction
				WHERE				( application_id = %d ) AND 
									( status_id = %d ) AND 
									( orig_receipt_id = %d )
				ORDER BY			date_created DESC
				LIMIT				1
			",
			$iApplicationId,
			self::STATUS_APPROVED,
			$iOrigOrderId
		), ARRAY_A );
		
	}
	
}



