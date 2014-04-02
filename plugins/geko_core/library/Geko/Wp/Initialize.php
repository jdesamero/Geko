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
	
	
	
	////// hook methods
	
	
	
	//// all
	
	//
	public function add() {
		
		Geko_Debug::out( $this->_sInstanceClass, __METHOD__ );
		
		return $this;
	}
	
	
	
	//// admin
	
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
	public function install() {
		
		Geko_Debug::out( $this->_sInstanceClass, __METHOD__ );
		
		return $this;
	}
	
	
	//// theme
	
	//
	public function addTheme() {
		return $this;
	}
	
	//
	public function enqueueTheme() {
		return $this;
	}
	
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( 0 === strpos( $sMethod, 'get' ) ) {
			
			// return results of an echo method as a string if there is a corresponding method
			$sCall = substr_replace( $sMethod, 'echo', 0, 3 );
			
			if ( method_exists( $this, $sCall ) ) {
				ob_start();
				call_user_func_array( array( $this, $sCall ), $aArgs );
				$s = ob_get_contents();
				ob_end_clean();
				return $s;
			}
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', get_class( $this ), $sMethod ) );
	}
	
	
	
}


