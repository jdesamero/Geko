<?php

class Geko_View_Helper_Navigation extends Zend_View_Helper_Navigation
{
	
	const NS = 'Geko_View_Helper_Navigation';

    public function findHelper($proxy, $strict = true)
    {
        if (isset($this->_helpers[$proxy])) {
            return $this->_helpers[$proxy];
        }

        if (!$this->view->getPluginLoader('helper')->getPaths(parent::NS)) {
            $this->view->addHelperPath(
                    str_replace('_', '/', parent::NS),
                    parent::NS);
        }
        
        if (!$this->view->getPluginLoader('helper')->getPaths(self::NS)) {
            $this->view->addHelperPath(
                    str_replace('_', '/', self::NS),
                    self::NS);
        }

        
        if ($strict) {
            $helper = $this->view->getHelper($proxy);
        } else {
            try {
                $helper = $this->view->getHelper($proxy);
            } catch (Zend_Loader_PluginLoader_Exception $e) {
                return null;
            }
        }

        if (!$helper instanceof Zend_View_Helper_Navigation_Helper) {
            if ($strict) {
                require_once 'Zend/View/Exception.php';
                throw new Zend_View_Exception(sprintf(
                        'Proxy helper "%s" is not an instance of ' .
                        'Zend_View_Helper_Navigation_Helper',
                        get_class($helper)));
            }

            return null;
        }

        $this->_inject($helper);
        $this->_helpers[$proxy] = $helper;

        return $helper;
    }
    
}

