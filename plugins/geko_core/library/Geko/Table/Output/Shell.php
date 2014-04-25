<?php

class Geko_Table_Output_Shell extends Geko_Table_Output_Default
{
	
	//
	public function echoTitle( $aParams ) {
		printf( "Table: %s\n", $aParams[ 'table_title' ] );
		return $this;
	}
		
	//
	public function echoHeadings( $aMeta ) {
		foreach ( $aMeta as $aCol ) {
			printf( '| %s ', $aCol[ 'title' ] );
		}
		echo "|\n";
		return $this;
	}
	
	//
	public function echoBeginRow( $iRow ) {
		return $this;	
	}
	
	//
	public function echoField( $aCol, $mRow, $iRow, $iCol ) {
		printf( '| %s ', trim( $this->getFieldVal( $aCol, $mRow ) ) );
		return $this;	
	}
	
	//
	public function echoEndRow( $iRow ) {
		echo "|\n";
		return $this;	
	}
	
	//
	public function echoEndTable() {
		echo "\n";
		return $this;	
	}
	
}

