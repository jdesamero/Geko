<?php

// abstract
class Geko_Wp_Payment_Admin extends Geko_Wp_Options_Admin
{
	
	const STATUS_APPROVED = 1;				// transaction went through
	const STATUS_DECLINED = 2;				// transaction was declined
	const STATUS_FAILED = 3;				// transaction failed for other reason
	const STATUS_ERROR = 4;					// error sending transaction
	const STATUS_UNKNOWN = 5;				// not sure what happened
	
	
	protected $_sMenuTitle = 'Payment Gateway';
	protected $_sAdminType = 'Payment Gateway';
	protected $_sIconId = 'icon-options-general';
	protected $_sSubAction = 'options-general';
	
	protected $_sPaymentClass;
	
	protected $_aRates = NULL;
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		//
		$this->_sPaymentClass = Geko_Class::resolveRelatedClass(
			$this, '_Admin', '', $this->_sPaymentClass
		);
		
	}
	
	
	
	//// init
	
	//
	public function setAsActive() {
		
		Geko_Wp_Payment::setGatewayAdmin( $this );
		
		Geko_Wp_Payment::setPayment(
			Geko_Singleton_Abstract::getInstance( $this->_sPaymentClass )->init()
		);
		
		return $this;
	}
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		$this->coft_install();
		
		return $this;
	}
	
	//
	public function attachPage() {
		add_options_page(
			$this->getPageTitle(), $this->getMenuTitle(), 8, $this->_sInstanceClass, array( $this, 'outputForm' )
		);
	}
	
	
	
	//
	public function affix() {
		Geko_Wp_Db::addPrefix( 'geko_pay_tax_rate' );
		return $this;
	}
	
	
	// create table
	public function install() {
		
		// table structure specific to moneris
		$sSql = '
			CREATE TABLE %s
			(
				province_id INT UNSIGNED,
				tax_label VARCHAR(255),
				tax_pct FLOAT(10,6) UNSIGNED
			)
		';
		
		Geko_Wp_Db::createTable( 'geko_pay_tax_rate', $sSql );
				
		return $this;
	}
	
	
	
	//// accessors
	
	//
	public function getPaymentInstance() {
		return Geko_Singleton_Abstract::getInstance( $this->_sPaymentClass );
	}
	
	// get the plugin url
	public function getUrl() {
		return get_settings( 'siteurl' ) . '/wp-admin/options-general.php?page=' . $this->_sInstanceClass;
	}
	
	//
	public function isTestMode() {
		return TRUE;
	}
	
	//
	public function enableTestOptions() {
		return TRUE;
	}
	
	// USE THIS VERY CAREFULLY!!!
	// output a bad string and kill the script
	public function simulateServerError() {
		if ( $this->isTestMode() ) {
			echo 'Simulating a server error.';
			die();
		}
	}
	
	// return an array of tax rates, index being the province id
	public function getTaxRates() {
		
		global $wpdb;
		
		if ( NULL == $this->_aRates ) {
		
			$aRatesFmt = array();
			$aRates = $wpdb->get_results( "
				SELECT				province_id,
									tax_label,
									tax_pct
				FROM				$wpdb->geko_pay_tax_rate
			" );
			
			foreach ( $aRates as $oRate ) {
				$aRatesFmt[ $oRate->province_id ] = array(
					'label' => $oRate->tax_label,
					'pct' => $oRate->tax_pct
				);
			}
			
			$this->_aRates = $aRatesFmt;
			
		}
		
		return $this->_aRates;
	}
	
	
	//// front-end display methods
	
	
	// to be implemented by sub-class
	public function echoTestOptions() { }
	
	
	
	//// crud methods
	
	//
	public function logResponse(
		Geko_Wp_Payment_Response $oResponse, Geko_Wp_Payment_Transaction $oTransaction
	) {
		return $this;
	}
	
	
	
	//// helper methods
	
	//
	public function getRefundInfo( $iApplicationId, $iOrigOrderId ) {
		return NULL;
	}
	
	
}



