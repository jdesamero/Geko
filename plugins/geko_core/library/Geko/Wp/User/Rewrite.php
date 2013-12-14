<?php


class Geko_Wp_User_Rewrite extends Geko_Wp_Rewrite_Abstract
{

	//
	protected function __construct()
	{
		$this->_sListKeyTag = 'userlist';
		$this->_sListVarName = 'geko_role_slug';
		$this->_sListDefaultTemplate = '/userlist.php';
		
		$this->_sSingleKeyTag = 'userdetails';
		$this->_sSingleVarName = 'geko_user_slug';
		$this->_sSingleDefaultTemplate = '/userdetails.php';
	}
	
}


