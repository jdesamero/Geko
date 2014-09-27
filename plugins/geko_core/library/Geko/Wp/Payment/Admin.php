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
	
	
	// NOTE: Not required anymore???
	
	/* /
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		$this->coft_install();
		
		return $this;
	}

	/* */
	
	//
	public function attachPage() {
		add_options_page(
			$this->getPageTitle(), $this->getMenuTitle(), 8, $this->_sInstanceClass, array( $this, 'outputForm' )
		);
	}
	
	
	
	//
	public function add() {
		
		parent::add();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_pay_tax_rate', 'tx' )
			->fieldInt( 'province_id', array( 'unsgnd' ) )
			->fieldVarChar( 'tax_label', array( 'size' => 256 ) )
			->fieldFloat( 'tax_pct', array( 'size' => '10,6', 'unsgnd' ) )
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
	
	
	
	//// accessors
	
	//
	public function getPaymentInstance() {
		return Geko_Singleton_Abstract::getInstance( $this->_sPaymentClass );
	}
	
	// get the plugin url
	public function getUrl() {
		return sprintf( '%s/wp-admin/options-general.php?page=%s', Geko_Wp::getUrl(), $this->_sInstanceClass );
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
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( NULL == $this->_aRates ) {
		
			$aRatesFmt = array();
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'tr.province_id', 'province_id' )
				->field( 'tr.tax_label', 'tax_label' )
				->field( 'tr.tax_pct', 'tax_pct' )
				->from( '##pfx##geko_pay_tax_rate', 'tr' )
			;
			
			$aRates = $oDb->fetchAllObj( strval( $oQuery ) );
			
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



