<?php

//
class Geko_Image_ShowCached extends Geko_Image_CachedAbstract
{
	private $sCacheFileKey;
	
	
	//
	public function __construct($sCacheFileKey)
	{
		$this->setCacheFileKey($sCacheFileKey);
	}
	
	//
	public function setCacheFileKey($sCacheFileKey)
	{
		$this->sCacheFileKey = $sCacheFileKey;
	}

	//
	public function getCacheFileKey()
	{
		return $this->sCacheFileKey;
	}
	
	//
	protected function generateCacheFile()
	{
		// do nothing
	}
	
	//
	public function getMimeType()
	{
		if (TRUE == is_file($this->sCacheFilePath)) {
			$aSize = getimagesize($this->sCacheFilePath);
			return $aSize['mime'];
		} else {
			if (self::$bLogging) $this->logMessage(__METHOD__, 'Cache file does not exist: ' . $this->sCacheFilePath);
			return '';
		}	
	}
	
	// override
	public function output()
	{
		// flush the output buffer
		ob_end_clean();
		
		// generate a path to the cache file
		$this->sCacheFilePath = self::$sCacheDir . $this->sCacheFileKey;
		
		if (TRUE == is_file($this->sCacheFilePath)) {
			// show the cached image file
			$this->showCachedImage();
		} else {
			// there were problems generating the cached image file
			$this->showBlankImage();
		}
		
		// kill the script
		die();
	}
	
	// override
	public function get()
	{
		$this->sCacheFilePath = self::$sCacheDir . $this->$sCacheFileKey;

		if (TRUE == is_file($this->sCacheFilePath)) {
			return array(
				'fullpath' => $this->sCacheFilePath,
				'cachekey' => $sCacheFileKey,
				'size' => getimagesize($this->sCacheFilePath)
			);
		} else {
			// cached image does not exist
			return FALSE;
		}
		
	}
	
	
}