<?php
/*
 * "geko_core/library/Geko/Currency.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Currency extends Geko_Singleton_Abstract
{
	
	const FIELD_TITLE = 1;
	const FIELD_SYMBOL_HTML = 2;
	const FIELD_CONVERSION_DECIMAL_PLACES = 3;
	
	
	
	protected $_bInitDb = FALSE;
	
	protected $_sTableName = '';
	protected $_aCurrencies = NULL;
	
	protected $_oRates = NULL;					// rate converter class
	
	
	
	
	//// initialization
	
	//
	public function start() {
		
		parent::start();
		
		$oCurrencyXml = Geko_Currency_Xml::getInstance();
		
		Geko_Once::run( 'geko_currency_load_xml_data', array( $oCurrencyXml, 'loadData' ) );
		
	}
	
	
	//
	public function initRateCoverter( $aParams ) {
		
		$this->_oRates = new Geko_Currency_Rate_CachedLookup( array( $aParams ) );
		
		return $this;
	}
	
	
	
	// alias of getCurrencies()
	public function get() {
		return $this->getCurrencies();
	}
	
	
	//
	public function set( $aCurrencies ) {
		
		$this->_aCurrencies = $aCurrencies;
		
		return $this;
	}
	
	
	
	//
	public function getCurrencies() {
		
		$this->init();
		
		return $this->_aCurrencies;
	}
	
	
	
	//
	public function getTitle( $sCode ) {
		
		$this->init();
		
		return $this->_aCurrencies[ $sCode ][ self::FIELD_TITLE ];
	}
	
	//
	public function getSymbol( $sCode ) {
		
		$this->init();
		
		return $this->_aCurrencies[ $sCode ][ self::FIELD_SYMBOL_HTML ];	
	}
	
	
	//
	public function getConversionDecimalPlaces( $sCode ) {
		
		$this->init();
		
		return $this->_aCurrencies[ $sCode ][ self::FIELD_CONVERSION_DECIMAL_PLACES ];	
	}
	
	
	// $sBase is USD by default
	public function getConversionRate( $sTarget, $sBase = NULL, $mDecimalPlaces = FALSE ) {
		
		if ( $this->_oRates ) {
			return $this->_oRates->getResult( $sTarget, $sBase );
		}
		
		return NULL;
	}
	
	
	//
	public function getConversionRateFmt( $iDecimalPlaces, $sTarget, $sBase = NULL ) {
		
		if ( $fRate = $this->getConversionRate( $sTarget, $sBase ) ) {
			
			if ( !is_int( $iDecimalPlaces ) ) {
				$iDecimalPlaces = $this->getConversionDecimalPlaces( $sTarget );
			}

			$sFormat = sprintf( '%%.%df', $iDecimalPlaces );
			
			return sprintf( $sFormat, $fRate );			
		}
		
	}
	
	
	
}


