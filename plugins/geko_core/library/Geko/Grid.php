<?php

// gridify an array
class Geko_Grid
{
	const BASE_ZERO = 0;
	const BASE_ONE = 1;
	
	const GO_ACROSS = 0;
	const GO_DOWN = 1;
	
	private $aSubject;
	
	private $iLength;
	private $iCols;
	private $iRows;
	
	private $iBase = self::BASE_ZERO;
	private $iDirection = self::GO_ACROSS;
	
	//
	public function __construct($aSubject, $iCols)
	{
		$this->aSubject = $aSubject;
		$this->iCols = $iCols;
		
		$this->iLength = count($this->aSubject);
		$this->iRows = ceil($this->iLength / $this->iCols);
	}
	
	//
	public function setNumColumns($iCols)
	{
		$this->iCols = $iCols;
		return $this;
	}
	
	//
	public function setBase($iBase)
	{
		$this->iBase = $iBase;
		return $this;
	}
	
	//
	public function setDirection($iDirection)
	{
		$this->iDirection = $iDirection;
		return $this;
	}
	
	//
	public function cols()
	{
		return $this->iCols;
	}
	
	//
	public function rows()
	{
		return $this->iRows;
	}
	
	//
	public function length()
	{
		return $this->iLength;
	}
	
	//
	public function item($i, $j)
	{
		// calculate $k
		if (self::GO_DOWN == $this->iDirection) {
			if (self::BASE_ONE == $this->iBase) {
				$k = ($this->iRows * ($j - 1)) + ($i - 1);
			} else {
				// self default self::BASE_ZERO
				$k = ($this->iRows * $j) + $i;
			}
		} else {
			// default self::GO_ACROSS
			if (self::BASE_ONE == $this->iBase) {
				$k = ($this->iCols * ($i - 1)) + ($j - 1);
			} else {
				// self default self::BASE_ZERO
				$k = ($this->iCols * $i) + $j;
			}
		}
		
		// return value
		if (self::BASE_ONE == $this->iBase) {
			return ($k <= $this->iLength) ? $this->aSubject[$k] : NULL;
		} else {
			// self default self::BASE_ZERO
			return ($k < $this->iLength) ? $this->aSubject[$k] : NULL;
		}		
	}
	
}


