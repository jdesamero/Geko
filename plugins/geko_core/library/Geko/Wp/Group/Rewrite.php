<?php


class Geko_Wp_Group_Rewrite extends Geko_Wp_Rewrite_Abstract
{

	//
	protected function __construct()
	{
		$this->_sListKeyTag = 'grouplist';
		$this->_sListVarName = 'geko_role_slug';
		$this->_sListDefaultTemplate = '/grouplist.php';
		
		$this->_sSingleKeyTag = 'groupdetails';
		$this->_sSingleVarName = 'geko_group_slug';
		$this->_sSingleDefaultTemplate = '/groupdetails.php';
	}
	
}


