<?php

class Geko_Table_Output_Csv extends Geko_Table_Output_Default
{
	
	
	//
	public function setFilename( $sFileName ) {
		$this->sFileName = $sFileName;
		return $this;
	}
	
	//
	public function csvEscape( $sValue ) {
		return str_replace( '"', '""', stripslashes( $sValue ) );
	}
	
	//
	public function echoTitle( $aParams ) {
		header( 'Content-Type: text/x-csv' );
		header( 'Content-Disposition: attachment; filename="' . $this->sFileName . '"' );
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
			echo '"' . $this->csvEscape( trim( $aCol[ 'title' ] ) ) . '"';
		}
		echo "\n";
		return $this;
	}
	
	//
	public function echoBeginRow() {
		return $this;	
	}
	
	//
	public function echoField( $aCol, $mRow ) {
		echo '"' . $this->csvEscape( trim( $this->getFieldVal( $aCol, $mRow ) ) ) . '",';
		return $this;	
	}
	
	//
	public function echoEndRow() {
		echo "\n";
		return $this;	
	}
	
	//
	public function echoEndTable() {
		die();
		return $this;	
	}
	
}

