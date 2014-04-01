<?php

//
class Geko_Wp_Initialize extends Geko_Singleton_Abstract
{
	
	
	
	//
	public function start() {
		
		parent::start();
		
		$this->add();
		
		if ( is_admin() ) {
			
			$this->addAdmin();
			
			if ( $this->isCurrentPage() ) {
				$this->enqueueAdmin();
			}
			
		} else {
			
			$this->addTheme();
			
		}
		
		Geko_Debug::out( $this->_sInstanceClass, __METHOD__ );
	}
	
	
	
	//
	public function isCurrentPage() {
		return FALSE;
	}
	
	
	
	//// hook methods
	
	//
	public function add() {
		
		Geko_Debug::out( $this->_sInstanceClass, __METHOD__ );
		
		return $this;
	}
	
	//
	public function addAdmin() {
		return $this;
	}
	
	//
	public function addAdminHead() {
		return $this;	
	}
	
	//
	public function enqueueAdmin() {
		return $this;
	}

	//
	public function enqueueTheme() {
		return $this;
	}
	
	//
	public function install() {
		
		Geko_Debug::out( $this->_sInstanceClass, __METHOD__ );
		
		return $this;
	}
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
	}
	
	
	
}


