<?php

//
class Geko_Currency_Xml extends Geko_Singleton_Abstract
{
	
	public $_sFile = '';
	
	
	
	//
	public function setFile( $sFile ) {
		$this->_sFile = $sFile;
	}
	
	
	//
	public function loadData() {
		
		if ( $this->_sFile ) {
			
			$oXml = simplexml_load_file( $this->_sFile );
			
			
			$oCurrencyMain = Geko_Currency::getInstance();
			
			$aCurrency = array();
			
			
			//// load currencies
			
			$aCurrencyXml = $oXml->currencies[ 0 ];
			
			foreach ( $aCurrencyXml as $oCurrency ) {
				
				$sTitle = strval( $oCurrency[ 'title' ] );
				$sCode = strval( $oCurrency[ 'code' ] );
				$sSymbol = strval( $oCurrency[ 'symbol' ] );
				
				$iConversionDecimalPlaces = $oCurrency[ 'conversion_decimal_places' ];
				
				if ( NULL === $iConversionDecimalPlaces ) {
					$iConversionDecimalPlaces = 2;
				} else {
					$iConversionDecimalPlaces = intval( $iConversionDecimalPlaces );
				}
				
				
				$sSymbolHtml = '';
				$aSymbols = explode( ',', $sSymbol );
				foreach ( $aSymbols as $iChar ) {
					$sSymbolHtml .= sprintf( '&#%s;', trim( $iChar ) );
				}
				
				$aCurrency[ $sCode ] = array(
					Geko_Currency::FIELD_TITLE => $sTitle,
					Geko_Currency::FIELD_SYMBOL_HTML => $sSymbolHtml,
					Geko_Currency::FIELD_CONVERSION_DECIMAL_PLACES => $iConversionDecimalPlaces
				);
				
			}
			
			
			$oCurrencyMain->set( $aCurrency );
			
			
			//// unset xml
			
			unset( $oXml );
			
		}
		
	}
	
	
	
}


