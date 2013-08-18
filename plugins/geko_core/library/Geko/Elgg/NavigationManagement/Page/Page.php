<?php

//
class Geko_Elgg_NavigationManagement_Page_Page extends Geko_Navigation_Page_Uri
{
	
	//
	public function getCurrentUser()
	{
		return $_SESSION['username'];
	}
	
	//
	public function getUri()
	{
		return str_replace( '[CURRENT_USER]', $this->getCurrentUser(), $this->_uri );
	}
	
	//
	public function getMyUriCompare()
	{
		return str_replace( '[CURRENT_USER]', '*', $this->_uri );	
	}
		
}




