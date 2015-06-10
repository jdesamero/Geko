<?php

require_once( sprintf(
	'%s/external/libs/scssphp/scss.inc.php',
	dirname( dirname( dirname( dirname( __FILE__ ) ) ) )
) );

//
class Geko_Scss_Server extends scss_server
{
	
	protected $_sInputFile;
	
	//
	public function setInputFile( $sInputFile ) {
		
		$this->_sInputFile = $sInputFile;
		
		return $this;
	}
	
	//
	protected function inputName() {
		
		if ( $this->_sInputFile ) {
			return $this->_sInputFile;
		}
		
		return parent::inputName();
	}

}



