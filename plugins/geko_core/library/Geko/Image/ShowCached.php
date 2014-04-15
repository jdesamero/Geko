<?php

//
class Geko_Image_ShowCached extends Geko_Image_CachedAbstract
{
	private $sCacheFileKey;
	
	
	//
	public function __construct( $sCacheFileKey ) {
		
		$this->setCacheFileKey( $sCacheFileKey );
	}
	
	//
	public function setCacheFileKey( $sCacheFileKey ) {
		
		$this->sCacheFileKey = $sCacheFileKey;
	}
	
	
	//
	public function getCacheFileKey() {
		
		return $this->sCacheFileKey;
	}
	
	
	
	
	//
	public function getMimeType() {
		
		$sCacheFilePath = $this->getCacheFilePath();
		
		if ( is_file( $sCacheFilePath ) ) {
			
			$aSize = getimagesize( $sCacheFilePath );
			
			return $aSize[ 'mime' ];
		
		}
		
		Geko_Debug::out( sprintf( 'Cache file does not exist: %s', $sCacheFilePath ), __METHOD__ );
		
		return '';
	}
	
	
	// override
	public function output() {
		
		// flush the output buffer
		ob_end_clean();
		
		// generate a path to the cache file
		$sCacheFilePath = $this->getCacheFilePath();
		
		
		if ( is_file( $sCacheFilePath ) ) {
			
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
	public function get() {
		
		$sCacheFileKey = $this->getCacheFileKey();
		$sCacheFilePath = $this->getCacheFilePath();
		
		if ( is_file( $sCacheFilePath ) ) {
			
			return array(
				'fullpath' => $sCacheFilePath,
				'cachekey' => $sCacheFileKey,
				'size' => getimagesize( $sCacheFilePath )
			);
			
		} else {
			
			// cached image does not exist
			return FALSE;
		}
		
	}
	
	
	
}