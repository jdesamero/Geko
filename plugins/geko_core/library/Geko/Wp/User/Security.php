<?php

// user security fields for registration/password retrieval
class Geko_Wp_User_Security extends Geko_Wp_User_Meta
{
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		$this->_sPrefix = '';
		$this->_sPrefixSeparator = '';
		
	}
	
	//
	public function getTitle() {
		return 'Security Fields';
	}
	
	
	//// front-end display methods
	
	//
	public function formFields() {	
		
		?>
		<p>
			<label class="main">Activation Key</label> 
			<input type="text" id="geko_activation_key" name="geko_activation_key" />
		</p>
		<p>
			<label class="main">Password Reset Key</label> 
			<input type="text" id="geko_password_reset_key" name="geko_password_reset_key" />
		</p>
		<?php
		
	}
	
	
		
	
}


