<?php

class Geko_Debug_Introspect
{
	private $sClassName;
	
	//
	public function __construct( $sClassName ) {
		$this->sClassName = $sClassName;
	}
	
	//
	public function getClassName() {
		return $this->sClassName;
	}
	
}

