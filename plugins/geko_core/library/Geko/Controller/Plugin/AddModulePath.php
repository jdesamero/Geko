<?php

//
class Geko_Controller_Plugin_AddModulePath extends Zend_Controller_Plugin_Abstract
{
	private $aDirs;
	private $oFront;
	
	public function __construct($aDirs, $oFront)
	{
		$this->aDirs = $aDirs;
		$this->oFront = $oFront;
	}
	
	public function routeShutdown(Zend_Controller_Request_Abstract $oRequest)
	{
		$aDirs = array();
		
		$aReplace = array(':MODULE_DIR', ':module', ':controller', ':action');
		
		$aReplacement = array(
			pathinfo($this->oFront->getControllerDirectory($oRequest->getModuleName()), PATHINFO_DIRNAME),		// move a level up from the controller directory
			$oRequest->getModuleName(),
			$oRequest->getControllerName(),
			$oRequest->getActionName()
		);
		
		
		foreach ($this->aDirs as $sDir) {
			$aDirs[] = str_replace($aReplace, $aReplacement, $sDir);
		}
		
		Geko_Loader::addIncludePaths($aDirs);
	}
}


