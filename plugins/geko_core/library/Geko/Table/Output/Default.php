<?php

class Geko_Table_Output_Default extends Geko_Singleton_Abstract
{
	
	
	//
	public function getFieldVal( $aCol, $mRow ) {
		
		if (
			( is_object( $mRow ) ) && 
			( is_a( $mRow, 'Geko_Entity' ) )
		) {
			return $mRow->getEntityPropertyValue( $aCol[ 'field' ] );
		}
		
		return $mRow[ $aCol[ 'field' ] ];
	}
	
	//
	public function echoTitle( $aParams ) {
		print_r( $aParams );
		return $this;
	}
	
	//
	public function echoBeginTable( $aParams ) {
		echo "\n";
		return $this;	
	}
	
	//
	public function echoHeadings( $aMeta ) {
		print_r( $aMeta );
		return $this;
	}

	//
	public function echoBeginRow() {
		echo "\n";
		return $this;	
	}
	
	//
	public function echoField( $aCol, $mRow ) {
		print_r( $this->getFieldVal( $aCol, $mRow ) );
		return $this;	
	}
	
	//
	public function echoEndRow() {
		echo "\n";
		return $this;	
	}
	
	//
	public function echoEndTable() {
		echo "\n";
		return $this;	
	}
	
	//
	public function echoOutput( $oTable ) {
		
		$aData = $oTable->getData();
		$aMeta = $oTable->getTheMeta();					// !!!
		$aParams = $oTable->getParams();
		
		$this->echoTitle( $aParams );
		
		$this->echoBeginTable( $aParams );
		
		$this->echoHeadings( $aMeta );
		
		foreach ( $aData as $mRow ) {
			$mRow = $oTable->getTheRow( $mRow );		// !!!
			$this->echoBeginRow();
			foreach ( $aMeta as $aCol ) {
				$this->echoField( $aCol, $mRow );
			}
			$this->echoEndRow();
		}

		$this->echoEndTable();
		
		return $this;
	}
	
	//
	public function getOutput( $oTable ) {
		return Geko_String::fromOb(
			array( $this, 'echoOutput' ),
			array( $oTable )
		);
	}
	
	//
	public function output( $oTable ) {
		// echo $this->getOutput( $oTable );
		$this->echoOutput( $oTable );
		return $this;
	}
	
}

