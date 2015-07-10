<?php

//
class Geko_Wp_Log_Plugin extends Geko_Wp_Initialize
{
	protected $_sParentClass = '';
	protected $_oParentLog;
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		$this->_sParentClass = Geko_Class::resolveRelatedClass(
			$this, '_Plugin_*', '_Manage', $this->_sParentClass
		);
		
		if ( $this->_sParentClass ) {
			$this->_oParentLog = Geko_Singleton_Abstract::getInstance( $this->_sParentClass );
			$this->_oParentLog->registerPlugin( $this );
		}
		
	}
	
}



