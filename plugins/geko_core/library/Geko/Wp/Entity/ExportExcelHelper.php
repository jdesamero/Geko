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
		
		foreach ( $this->_aColumnMappings as $sKey => $mMapping ) {
			$aRet[] = $this->getTitle( $sKey, $mMapping );
		}
		
		return $aRet;
	}
	
	//
	public function getTitle( $sKey, $mMapping ) {
		
		$sTitle = '';
		
		if ( is_string( $mMapping ) ) {
			
			$sTitle = $mMapping;
			
		} elseif ( is_array( $mMapping ) ) {
			
			if ( $sTitleParam = $mMapping[ 'title' ] ) {
				$sTitle = $sTitleParam;
			}
			
		}
		
		
		// default title, "humanize" the field key
		// some_field --> "Some Field"
		
		if ( !$sTitle ) {
			$sTitle = Geko_Inflector::humanize( $sKey );		
		}
		
		return $sTitle;
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
		
		// defaults
		
		$mValue = '';						// final value
		$aArgs = array();					// allows arbitrary arguments to be passed from the mapping params
		$sTransMethod = NULL;				// local transformation method
		
		
		// get further options from the mapping param
		
		if ( is_array( $mMapping ) ) {
			
			if ( $aMapArgs = $mMapping[ 'args' ] ) {
				$aArgs = $aMapArgs;
			}
			
			// local transformation function
			if ( $mTrans = $mMapping[ 'trans' ] ) {
				
				if ( is_string( $mTrans ) ) {
					$sTransKey = $mTrans;
				} else {
					// default, trans key is same as field key
					$sTransKey = $sKey;
				}
				
				$sTransMethod = sprintf( 'trans%s', Geko_Inflector::camelize( $sTransKey ) );
			}
			
		}
		
		
		
		// obtain values from the entity
		
		$sValueMethod = sprintf( 'get%s', Geko_Inflector::camelize( $sKey ) );
		$mValue = call_user_func_array( array( $oItem, $sValueMethod ), $aArgs );
		
		
		// apply transformation method, if needed
		if ( $sTransMethod && method_exists( $this, $sTransMethod ) ) {
			$mValue = $this->$sTransMethod( $mValue, $oItem, $sKey, $aArgs, $mMapping );
		}
		
		
		// apply encoding, if specified
		if ( $this->_sApplyEncoding ) {
			$mValue = mb_convert_encoding( $mValue, $this->_sApplyEncoding );
		}
		
		
		return $mValue;
	}
	
	
	
	//// transformation modules/plugins
	
	//
	public function transEnum( $mValue, $oItem, $sKey, $aArgs, $aMap ) {
		
		$sEnumKey = $aArgs[ 'key' ];
		$sDest = ( $aArgs[ 'dest' ] ) ? strtolower( $aArgs[ 'dest' ] ) : 'title' ;
		$sSource = ( $aArgs[ 'source' ] ) ? strtolower( $aArgs[ 'source' ] ) : 'value' ;
		
		$aEnum = Geko_Wp_Enumeration_Query::getSet( $sEnumKey );
		
		$sMethod = sprintf( 'get%sFrom%s', ucfirst( $sDest ), ucfirst( $sSource ) );
		
		return $aEnum->$sMethod( $mValue );

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
		
		foreach ( $this->_aColumnMappings as $sKey => $mMapping ) {
			$oWorksheet->write( $j, $i, $this->getTitle( $sKey, $mMapping ), $oBold );
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

