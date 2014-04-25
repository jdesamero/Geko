<?php

class Geko_Table_Output_Csv extends Geko_Table_Output_Default
{
	
	protected $_sFilename;
	
	
	
	//
	public function setFilename( $sFileName ) {
		$this->_sFileName = $sFileName;
		return $this;
	}
	
	//
	public function csvEscape( $sValue ) {
		return str_replace( '"', '""', stripslashes( $sValue ) );
	}
	
	//
	public function echoTitle( $aParams ) {
		header( 'Content-Type: text/x-csv' );
		header( sprintf( 'Content-Disposition: attachment; filename="%s"', $this->_sFileName ) );
		return $this;
	}
	
	//
	public function echoBeginTable( $aParams ) {
		return $this;	
	}
	
	//
	public function echoHeadings( $aMeta ) {
		foreach ( $aMeta as $i => $aCol ) {
			if ( $i > 0 ) echo ',';
			printf( '"%s"', $this->csvEscape( trim( $aCol[ 'title' ] ) ) );
		}
		echo "\n";
		return $this;
	}
	
	//
	public function echoBeginRow( $iRow ) {
		return $this;	
	}
	
	//
	public function echoField( $aCol, $mRow, $iRow, $iCol ) {
		printf( '"%s",', $this->csvEscape( trim( $this->getFieldVal( $aCol, $mRow ) ) ) );
		return $this;	
	}
	
	//
	public function echoEndRow( $iRow ) {
		echo "\n";
		return $this;	
	}
	
	//
	public function echoEndTable() {
		die();
	}
	
}


