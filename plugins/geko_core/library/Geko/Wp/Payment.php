<?php
/*
 * "geko_core/library/Geko/Wp/Payment.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Payment extends Geko_Wp_Initialize
{
	
	const TRANSTYPE_PURCHASE = 1;
	const TRANSTYPE_PREAUTH = 2;
	const TRANSTYPE_CAPTURE = 3;
	const TRANSTYPE_VOID = 4;
	const TRANSTYPE_REFUND = 5;
	const TRANSTYPE_IND_REFUND = 6;
	const TRANSTYPE_BATCH_CLOSE = 7;
	
	const GATEWAY_ID_MONERIS = 1;
	const GATEWAY_ID_CASH = 2;
	const GATEWAY_ID_BEANSTREAM = 2;
	const GATEWAY_ID_TEST = 999;
	
	
	
	// only one payment object/gateway admin object should be active at a time
	
	protected static $_oPayment = NULL;
	protected static $_oGatewayAdmin = NULL;
	
	protected $_bValidLibrary = FALSE;
	
	protected $_sAdminClass = '';
	protected $_sTransactionClass = '';
	protected $_sResponseClass = '';
	
	
	
	//
	public static function setPayment( $_oPayment ) {
		self::$_oPayment = $_oPayment;
	}
	
	//
	public static function getPayment() {
		return self::$_oPayment;
	}
	
	//
	public static function setGatewayAdmin( $_oGatewayAdmin ) {
		self::$_oGatewayAdmin = $_oGatewayAdmin;
	}
	
	//
	public static function getGatewayAdmin() {
		return self::$_oGatewayAdmin;
	}
	
	
	
	
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		//
		$this->_sAdminClass = Geko_Class::resolveRelatedClass(
			$this, '', '_Admin', $this->_sAdminClass
		);
		
		//
		$this->_sTransactionClass = Geko_Class::resolveRelatedClass(
			$this, '', '_Transaction', $this->_sTransactionClass
		);

		//
		$this->_sResponseClass = Geko_Class::resolveRelatedClass(
			$this, '', '_Response', $this->_sResponseClass
		);
		
	}
	
	
	//// accessors
	
	//
	public function hasValidLibrary() {
		return $this->_bValidLibrary;
	}
	
	//
	public function getAdminClass() {
		return $this->_sAdminClass;
	}
	
	//
	public function getTransactionClass() {
		return $this->_sTransactionClass;
	}
	
	//
	public function getResponseClass() {
		return $this->_sResponseClass;	
	}
	
	//
	public function getAdminInstance() {
		return Geko_Singleton_Abstract::getInstance( $this->_sAdminClass );
	}
	
	//
	public function getGatewayId() {
		return NULL;
	}
	
	
	// convenience method
	public function getOption( $sKey ) {
		$oAdmin = $this->getAdminInstance();
		return $oAdmin->getOption( $sKey );
	}
	
	
	
	
	
	// initialize and return a transaction object
	public function setup( $iTransType, $aParams ) {
		return new $this->_sTransactionClass( $iTransType, $aParams );
	}
	
}



