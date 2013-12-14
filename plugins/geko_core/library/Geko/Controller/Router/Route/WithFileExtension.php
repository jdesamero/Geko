<?php

//
class Geko_Controller_Router_Route_WithFileExtension
implements Zend_Controller_Router_Route_Interface
{
	public static $sAllowedExtensions = 'html|htm|xml|proc';
	
	public static function setAllowedExtensions($sAllowedExtensions) {
		self::$sAllowedExtensions = $sAllowedExtensions;
	}
	
	
	
    //
    public static function getInstance(Zend_Config $oConfig)
    {
        // we're not using any values form the config file
        return new self();
    }
    
    //
    public function match($sPath)
    {
    	// remove leading/trailing '/' from the path
        $sPath = trim($sPath, '/');
        
        if (TRUE == preg_match('/(.+)\.(' . self::$sAllowedExtensions . ')$/i', $sPath, $aRegs))
        {
            $aParams = array('extension' => $aRegs[2]);
            
            $aExploded = explode('/', $aRegs[1]);
            $iCount = count($aExploded);
            
            if (1 == $iCount)
            {
                $aParams['module'] = 'default';
                $aParams['controller'] = $aExploded[0];
                $aParams['action'] = 'index';            
            }
            elseif (2 == $iCount)
            {
				$aParams['module'] = 'default';
				$aParams['controller'] = $aExploded[0];
				$aParams['action'] = $aExploded[1];
            }
            else
            {
                if (0 != ($iCount % 2))
                {
                    $sModule = array_shift($aExploded);
                    $aParams['module'] = $sModule;
                }
                else
                {
                    $aParams['module'] = 'default';
                }
                
                $sController = array_shift($aExploded);
                $aParams['controller'] = $sController;
                
                $sAction = array_shift($aExploded);
                $aParams['action'] = $sAction;
                
                $iCount = count($aExploded);
                for ($i = 0; $i < $iCount; $i++)
                {
                    $aParams[$aExploded[$i]] = $aExploded[$i + 1];
                    $i++;
                }
            }
            
            return $aParams;
        }
        else
        {
            return FALSE;
        }
    }
    
    //
    public function assemble($aParams = array(), $reset = false, $encode = false)
    {
        $sUrl = '';
        
        if (TRUE == array_key_exists('module', $aParams)) $sUrl = '/' . $aParams['module'];
        if (TRUE == array_key_exists('controller', $aParams)) $sUrl = '/' . $aParams['controller'];
        if (TRUE == array_key_exists('action', $aParams)) $sUrl = '/' . $aParams['action'];
        
		foreach ($aParams as $sKey => $sValue)
		{
			if (FALSE == in_array($sKey, array('module', 'controller', 'action', 'extension')))
			{
				$sUrl .= sprintf('/%s/%s', $sKey, $sValue);
			}
		}
        
        if (TRUE == array_key_exists('extension', $aParams)) $sUrl = '/' . $aParams['extension'];
        
        return $sUrl;
    }
    
}
