<?php

// http://framework.zend.com/wiki/display/ZFPROP/Zend_View_PhpTal+-+Matthew+Ratzloff
// http://blog.realmofzod.com/2008/04/14/how-to-make-them-work-together/

class Zend_View_PHPTAL extends Zend_View_Abstract
{
	private $_engine = null;
	private $_variables = array();
	private $_template = '';
	private $_encoding = PHPTAL_DEFAULT_ENCODING;
	private $_outputMode = PHPTAL_XHTML;
	private $_stripComments = false;
	
	public function __construct($pConfig = array())
	{
		$this->_engine = new PHPTAL();
		
		$this->this = $this;
		
		if (isset($config['outputMode'])) {
			$this->setOutputMode($config['outputMode']);
		}
		
		if (isset($config['stripComments'])) {
			$this->setStripComments($config['stripComments']);
		}
		
		if (isset($config['forceReparse'])) {
			$this->setForceReparse($config['forceReparse']);
		}
		
		parent::__construct($pConfig);
	}
	
	public function setBasePath($path, $classPrefix = 'Zend_View')
	{
		$path = rtrim($path, '/');
		$path = rtrim($path, '\\');
		$path .= DIRECTORY_SEPARATOR;
		$classPrefix = rtrim($classPrefix, '_') . '_';
		$this->setScriptPath($path . 'templates');
		$this->setHelperPath($path . 'helpers', $classPrefix . 'Helper');
		$this->setFilterPath($path . 'filters', $classPrefix . 'Filter');
		return $this;
	}
	
	public function addBasePath($path, $classPrefix = 'Zend_View')
	{
		$path = rtrim($path, '/');
		$path = rtrim($path, '\\');
		$path .= DIRECTORY_SEPARATOR;
		$classPrefix = rtrim($classPrefix, '_') . '_';
		$this->addScriptPath($path . 'templates');
		$this->addHelperPath($path . 'helpers', $classPrefix . 'Helper');
		$this->addFilterPath($path . 'filters', $classPrefix . 'Filter');
		return $this;
	}
	
	public function assign($mixed, $value = null)
	{
		if (is_string($mixed)) {
			$this->_variables[$mixed] = $value;
		} elseif (is_array($mixed)) {
			foreach ($mixed as $key => $value) {
				$this->_variables[$key] = $value;
			}
		} else {
			throw new Zend_View_Exception('assign() expects a string or array, received ' . gettype($mixed));
		}
	}
	
	public function getVars()
	{
		return $this->_variables;
	}
	
	public function clearVars()
	{
		$this->_variables = array('this' => $this);
	}
	
	public function render($template)
	{
		// Find the script file name using the parent private method
		$this->_template = $this->_script($template);
		unset($template); // Remove $template from local scope
		$this->_engine->setTemplate($this->_template);
		$this->_assignAll($this->_variables);
		
		return $this->_engine->execute();
	}
	
	public function __set($key, $value)
	{
		if ($key[0] != '_') {
			$this->_variables[$key] = $value;
		}
	}
	
	public function __get($key)
	{
		if ($this->__isset($key)) {
			return $this->_variables[$key];
		}
		
		return null;
	}
	
	public function __isset($key)
	{
		return array_key_exists($key, $this->_variables) and ($key[0] != '_');
	}
	
	public function __unset($key)
	{
		if ($this->__isset($key)) {
			unset($this->_vars[$key]);
		}
	}
	
	public function __clone()
	{
		$this->_engine = clone $this->_engine;
	}
	
	public function getEngine()
	{
		return $this->_engine;
	}
	
	public function getErrors()
	{
		return $this->_engine->getErrors();
	}
	
	public function setEncoding($encoding = PHPTAL_DEFAULT_ENCODING)
	{
		$this->_engine->setEncoding($encoding);
		$this->_encoding = $encoding;
		return $this;
	}
	
	public function getEncoding()
	{
		return $this->_encoding;
	}
	
	public function setOutputMode($mode)
	{
		$this->_engine->setOutputMode($mode);
		$this->_outputMode = $mode;
		return $this;
	}
	
	public function getOutputMode()
	{
		return $this->_outputMode;
	}
	
	public function setStripComments($flag = true)
	{
		$this->_engine->stripComments($flag);
		$this->_stripComments = $flag;
		return $this;
	}
	
	public function getStripComments()
	{
		return $this->_stripComments;
	}
	
	public function setCompilePath($pDir)
	{
		if (defined('PHPTAL_PHP_CODE_DESTINATION')) {
			throw new Zend_View_Exception('setCompilePath() defines a constant, and cannot be called twice');
		}
		
		define('PHPTAL_PHP_CODE_DESTINATION', $pDir);
		return $this;
	}
	
	public function getCompilePath()
	{
		if (defined('PHPTAL_PHP_CODE_DESTINATION')) {
			return PHPTAL_PHP_CODE_DESTINATION;
		}
		
		return null;
	}
	
	public function setForceReparse($flag)
	{
		if (defined('PHPTAL_FORCE_REPARSE')) {
			throw new Zend_View_Exception('setForceReparse() defines a constant, and cannot be called twice');
		}
		
		define('PHPTAL_FORCE_REPARSE', (int) $flag);
		return $this;
	}
	
	public function getForceReparse()
	{
		if (defined('PHPTAL_FORCE_REPARSE')) {
			return (bool) PHPTAL_FORCE_REPARSE;
		}
		
		return false;
	}
	
	public function getCodePath()
	{
		return PHPTAL_PHP_CODE_DESTINATION;
	}
	
	public function getCodeExtension()
	{
		return PHPTAL_PHP_CODE_EXTENSION;
	}
	
	protected function _assignAll(array $variables = array())
	{
		foreach ($variables as $key => $value) {
			$this->_engine->set($key, $value);
		}
	}
	
	protected function _run()
	{
	
	}

}

