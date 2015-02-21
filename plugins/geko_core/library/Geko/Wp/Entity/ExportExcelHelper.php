<?php

abstract class Geko_Wp_Entity_ExportExcelHelper
{
	
	//// properties
	
	protected $_sExportedFileName = 'worksheet_##date##.xls';
	protected $_sWorksheetName = 'Worksheet';
	protected $_aColumnMappings = array();
	
	protected $_aParams = array();
	
	protected $_iWorkbookVersion = 8;
	
	
	// http://www.php.net/manual/en/mbstring.supported-encodings.php
	// Common values: Windows-1251, Windows-1252, UTF-8, ISO-8859-1
	
	protected $_sApplyEncoding = NULL;
	
	
	
	
	
	
	//// methods
	
	//
	public function __construct( $aParams = array() ) {
		$this->_aParams = $aParams;
	}
	
	//
	public function getExportedFileName() {
		
		$sFileName = $this->_sExportedFileName;
		
		// perform replacements
		if ( FALSE !== strpos( $sFileName, '##date##' ) ) {
			$sFileName = str_replace( '##date##', date( 'Y-m-d' ), $sFileName );
		}
		
		return $sFileName;
	}
	
	//
	public function getWorksheetName() {
		return $this->_sWorksheetName;
	}
	
	//
	public function getColumnMappings() {
		return $this->_aColumnMappings;
	}
	
	//
	public function getTitles() {
		
		$aRet = array();
		
		foreach ( $this->_aColumnMappings as $mMapping ) {
			if ( is_array( $mMapping ) ) {
				$aRet[] = $mMapping[ 0 ];
			} else {
				$aRet[] = $mMapping;
			}
		}
		
		return $aRet;
	}
	
	//
	public function getTitle( $mMapping ) {
		return ( is_array( $mMapping ) ) ? $mMapping[ 0 ] : $mMapping ;
	}
	
	//
	public function getValues( $oItem ) {
		
		$aRet = array();
		
		foreach ( $this->_aColumnMappings as $sKey => $mMapping ) {
			$aRet[] = $this->getValue( $oItem, $sKey, $mMapping );
		}
		
		return $aRet;
	}
	
	
	//
	public function getValue( $oItem, $sKey, $mMapping ) {
		
		$mValue = '';
		$mPassVal = $oItem->getEntityPropertyValue( $sKey );
		
		
		if ( is_array( $mMapping ) ) {
			
			if ( $mMap = $mMapping[ 1 ] ) {
				
				if ( is_array( $mMap ) ) {
					
					$aMap = $mMap;
					
					if ( $sTransKey = $aMap[ 'trans' ] ) {
						
						$sMethodFmt = sprintf( 'trans%s', ucfirst( strtolower( $sTransKey ) ) );

						if ( method_exists( $this, $sMethodFmt ) ) {
						
							$mValue = $this->$sMethodFmt( $mPassVal, $oItem, $sKey, $aMap );
						}
						
					}
					
				} else {
					
					$sMethod = $mMap;
					
					$sMethodFmt = sprintf( 'get%s', ucfirst( strtolower( $sMethod ) ) );
					
					if ( method_exists( $this, $sMethodFmt ) ) {
						
						$mValue = $this->$sMethodFmt( $mPassVal, $oItem, $sKey );
						
					} else {
						
						$mValue = $oItem->$sMethodFmt();
					}
				
				}
				
			}
			
		} else {
			
			$mValue = $mPassVal;
		}
		
		
		// apply encoding, if specified
		if ( $this->_sApplyEncoding ) {
			$mValue = mb_convert_encoding( $mValue, $this->_sApplyEncoding );
		}
		
		
		return $mValue;
	}
	
	
	
	//// transformation modules/plugins
	
	//
	public function transEnum( $mPassVal, $oItem, $sKey, $aParams ) {
		
		$sEnumKey = $aParams[ 'key' ];
		$sDest = ( $aParams[ 'dest' ] ) ? strtolower( $aParams[ 'dest' ] ) : 'title' ;
		$sSource = ( $aParams[ 'source' ] ) ? strtolower( $aParams[ 'source' ] ) : 'value' ;
		
		$aEnum = Geko_Wp_Enumeration_Query::getSet( $sEnumKey );
		
		$sMethod = sprintf( 'get%sFrom%s', ucfirst( $sDest ), ucfirst( $sSource ) );
		
		return $aEnum->$sMethod( $mPassVal );

	}
	
	
	
	// $aRes is an entity query object
	// TO DO: add hooks
	public function exportToExcel( $aRes ) {
		
		// Creating a workbook
		$oWorkbook = new Spreadsheet_Excel_Writer();
		$oWorkbook->setVersion( $this->_iWorkbookVersion );
		
		// sending HTTP headers
		$oWorkbook->send( $this->getExportedFileName() );
		
		// Creating a worksheet
		$oWorksheet = $oWorkbook->addWorksheet( $this->_sWorksheetName );
		
		$oBold = $oWorkbook->addFormat( array(
			'Bold' => 1
		) );
		
		// titles
		$i = 0;		// cols
		$j = 0;		// rows
		
		foreach ( $this->_aColumnMappings as $mMapping ) {
			$oWorksheet->write( $j, $i, $this->getTitle( $mMapping ), $oBold );
			$i++;
		}
		
		$j++;
		
		foreach ( $aRes as $oItem ) {
			$i = 0;
			foreach ( $this->_aColumnMappings as $sKey => $mMapping ) {
				$mValue = $this->getValue( $oItem, $sKey, $mMapping );
				$oWorksheet->write( $j, $i, $mValue );	
				$i++;
			}
			$j++;
		}
		
		// Let's send the file
		$oWorkbook->close();
	}
	
	
}

