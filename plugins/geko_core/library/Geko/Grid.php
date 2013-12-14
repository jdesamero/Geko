<?php

// gridify an array
class Geko_Grid
{
	const BASE_ZERO = 0;
	const BASE_ONE = 1;
	
	const GO_ACROSS = 0;
	const GO_DOWN = 1;
	
	protected $aSubject;
	
	protected $iLength;
	protected $iCols;
	protected $iRows;
	
	protected $iBase = self::BASE_ZERO;
	protected $iDirection = self::GO_ACROSS;
	
	protected $bCalculated = FALSE;
	
	
	//
	public function __construct( $aSubject, $iCols, $iRows ) {
		
		$this->aSubject = $aSubject;
		$this->iLength = count( $this->aSubject );
		
		$this->iCols = $iCols;
		$this->iRows = $iRows;
	}
	
	
	//
	public function setNumColumns( $iCols ) {
		$this->iCols = $iCols;
		return $this;
	}

	//
	public function setNumRows( $iRows ) {
		$this->iRows = $iRows;
		return $this;
	}
	
	
	//
	public function setBase( $iBase ) {
		$this->iBase = $iBase;
		return $this;
	}
	
	//
	public function setDirection( $iDirection ) {
		$this->iDirection = $iDirection;
		return $this;
	}
	
	//
	public function cols() {
		$this->calculate();
		return $this->iCols;
	}
	
	//
	public function rows() {
		$this->calculate();
		return $this->iRows;
	}
	
	//
	public function length() {
		$this->calculate();
		return $this->iLength;
	}
	
	//
	public function item( $i, $j ) {
		
		$this->calculate();
		
		// calculate $k
		if ( self::GO_DOWN == $this->iDirection ) {
			if ( self::BASE_ONE == $this->iBase ) {
				$k = ( $this->iRows * ( $j - 1 ) ) + ( $i - 1 );
			} else {
				// self default self::BASE_ZERO
				$k = ( $this->iRows * $j ) + $i;
			}
		} else {
			// default self::GO_ACROSS
			if ( self::BASE_ONE == $this->iBase ) {
				$k = ( $this->iCols * ( $i - 1 ) ) + ( $j - 1 );
			} else {
				// self default self::BASE_ZERO
				$k = ( $this->iCols * $i ) + $j;
			}
		}
		
		// return value
		if ( self::BASE_ONE == $this->iBase ) {
			return ( $k <= $this->iLength ) ? $this->aSubject[ $k ] : NULL;
		} else {
			// self default self::BASE_ZERO
			return ( $k < $this->iLength ) ? $this->aSubject[ $k ] : NULL;
		}		
	}
	
	// do once
	public function calculate() {
		
		if ( !$this->bCalculated ) {
			
			if ( NULL === $this->iCols ) {
				$this->iCols = intval( ceil( $this->iLength / $this->iRows ) );		
			}
			
			if ( NULL === $this->iRows ) {
				$this->iRows = intval( ceil( $this->iLength / $this->iCols ) );		
			}
			
			$this->bCalculated = TRUE;
		}
		
		return $this;
	}
	
	
}


