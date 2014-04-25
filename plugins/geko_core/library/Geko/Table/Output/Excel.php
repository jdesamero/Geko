<?php

class Geko_Table_Output_Excel extends Geko_Table_Output_Default
{
	
	protected $_oWorkbook;
	protected $_oWorksheet;
	
	protected $_sExportedFileName = 'worksheet_##date##.xls';
	protected $_sWorksheetName = 'Worksheet';
	protected $_iWorkbookVersion = 8;
	protected $_sApplyEncoding = NULL;
	
	
	
	//
	public function setExportedFileName( $sExportedFileName ) {
		$this->_sExportedFileName = $sExportedFileName;
		return $this;
	}


	//
	public function setWorksheetName( $sWorksheetName ) {
		$this->_sWorksheetName = $sWorksheetName;
		return $this;
	}
	
	//
	public function setWorkbookVersion( $iWorkbookVersion ) {
		$this->_iWorkbookVersion = $iWorkbookVersion;
		return $this;
	}
	
	//
	public function setEncoding( $sEncoding ) {
		$this->_sApplyEncoding = $sEncoding;
		return $this;
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
	public function echoTitle( $aParams ) {
		
		// Creating a workbook
		$oWorkbook = new Spreadsheet_Excel_Writer();
		$oWorkbook->setVersion( $this->_iWorkbookVersion );
		
		// sending HTTP headers
		$oWorkbook->send( $this->getExportedFileName() );
		
		
		// Creating a worksheet
		$oWorksheet = $oWorkbook->addWorksheet( $this->_sWorksheetName );
		
		
		$this->_oWorksheet = $oWorksheet;
		$this->_oWorkbook = $oWorkbook;
		
		return $this;
	}
	
	//
	public function echoBeginTable( $aParams ) {
		return $this;	
	}
	
	//
	public function echoHeadings( $aMeta ) {
		
		$oWorkbook = $this->_oWorkbook;
		$oWorksheet = $this->_oWorksheet;
		
		$oBold = $oWorkbook->addFormat( array(
			'Bold' => 1
		) );
		
		// titles
		$i = 0;		// cols
		
		foreach ( $aMeta as $aCol ) {
			$oWorksheet->write( 0, $i, $aCol[ 'title' ], $oBold );
			$i++;
		}
		
		return $this;
	}
	
	//
	public function echoBeginRow( $iRow ) {
		return $this;	
	}
	
	//
	public function echoField( $aCol, $mRow, $iRow, $iCol ) {

		$oWorksheet = $this->_oWorksheet;
		
		$sValue = $this->getFieldVal( $aCol, $mRow );
		if ( $this->_sApplyEncoding ) {
			$sValue = mb_convert_encoding( $sValue, $this->_sApplyEncoding );
		}
		
		$oWorksheet->write( $iRow + 1, $iCol, $sValue );	
		
		return $this;	
	}
	
	//
	public function echoEndRow( $iRow ) {
		return $this;	
	}
	
	//
	public function echoEndTable() {
		
		$oWorkbook = $this->_oWorkbook;
		
		// Let's send the file
		$oWorkbook->close();
		
		die();
	}
	
}

