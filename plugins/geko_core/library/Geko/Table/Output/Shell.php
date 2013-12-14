<?php

class Geko_Table_Output_Shell extends Geko_Table_Output_Default
{
	
	//
	public function echoTitle( $aParams ) {
		echo 'Table: ' . $aParams[ 'table_title' ] . "\n";
		return $this;
	}
		
	//
	public function echoHeadings( $aMeta ) {
		foreach ( $aMeta as $aCol ) {
			echo '| ' . $aCol[ 'title' ] . ' ';
		}
		echo "|\n";
		return $this;
	}
	
	//
	public function echoBeginRow() {
		return $this;	
	}
	
	//
	public function echoField( $aCol, $mRow ) {
		echo '| ' . trim( $this->getFieldVal( $aCol, $mRow ) ) . ' ';
		return $this;	
	}
	
	//
	public function echoEndRow() {
		echo "|\n";
		return $this;	
	}
	
	//
	public function echoEndTable() {
		echo "\n";
		return $this;	
	}
	
}

