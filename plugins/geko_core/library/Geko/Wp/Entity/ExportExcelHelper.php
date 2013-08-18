<?php

abstract class Geko_Wp_Entity_ExportExcelHelper
{
	
	protected $_sExportedFileName = 'worksheet_##date##.xls';
	protected $_sWorksheetName = 'Worksheet';
	protected $_aColumnMappings = array();
	
	protected $_aParams = array();
	
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
		
		if ( is_array( $mMapping ) ) {
			if ( $sMethod = $mMapping[ 1 ] ) {
				$sMethod = 'get' . ucfirst( $sMethod );
				$mValue = $oItem->$sMethod();
			}
		} else {
			$mValue = $oItem->getEntityPropertyValue( $sKey );
		}
		
		return $mValue;
	}
	
	// $aRes is an entity query object
	// TO DO: add hooks
	public function exportToExcel( $aRes ) {
		
		// Creating a workbook
		$oWorkbook = new Spreadsheet_Excel_Writer();
		
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

