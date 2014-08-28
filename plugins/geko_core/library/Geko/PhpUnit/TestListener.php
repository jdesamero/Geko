<?php

require_once( sprintf(
	'%s/external/libs/pearpkgs/PHPUnit-3.4.14/library/PHPUnit/Framework.php',
	dirname( dirname( dirname( dirname( __FILE__ ) ) ) )
) );

//
class Geko_PhpUnit_TestListener extends Geko_Entity
{
	
	//
	public function init() {
		
		parent::init();
		
		$this->setEntityMapping( 'id', 'idx' );
		
		return $this;
	}
	
	
	
}

