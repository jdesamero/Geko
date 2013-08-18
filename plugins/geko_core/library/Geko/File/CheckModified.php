<?php

//
class Geko_File_CheckModified
{
	
	protected $aFiles = array();
	protected $sFileHash;
	
	
	//
	public function __construct( $sDir, $iLevels = NULL )
	{
		$this->findAndHash( $sDir, $iLevels );
	}
	
	//
	public function findAndHash( $sDir, $iLevels = NULL )
	{
		$this->aFiles = self::find( $sDir, $iLevels );
		$this->sFileHash = self::createHash( $this->aFiles );
		
		return $this;
	}
	
	//
	public function compareHash( $sCompareHash )
	{
		return ( $this->sFileHash == $sCompareHash );
	}
	
	//
	public function getFileHash()
	{
		return $this->sFileHash;
	}
	
	//
	public function getFiles( $bFilesOnly = TRUE )
	{
		if ( $bFilesOnly ) {
			$aRet = array();
			foreach ( $this->aFiles as $aFile ) $aRet[] = $aFile['path'];
			return $aRet;
		} else {
			return $this->aFiles;
		}
	}
	
	
	////// static methods
	
	
	// find all parseable files and put into a flat array
	public static function find( $sDir, $iLevels = NULL )
	{	
		$aConcat = array();
		$aFiles = scandir( $sDir );
		
		foreach ( $aFiles as $sFile ) {
			$sFullPath = $sDir . '/' . $sFile;
			
			// TO DO: Add file filtering capabilities
			// right now hard-coded to look at PHP files only
			if ( 'php' == pathinfo( $sFile, PATHINFO_EXTENSION ) ) {
				
				// echo $i . ': ' . $sFile . ' (' . filemtime($sFullPath) . ') (' . fileinode($sFullPath) . ')<br />';
				
				// track file modification time, inode, and full path
				$aConcat[] = array(
					'mtime' => filemtime( $sFullPath ),
					'inode' => fileinode( $sFullPath ),
					'path' => $sFullPath
				);
				
			} else {
				
				if (
					( ( is_dir($sFullPath) ) && ( '.' != $sFile ) && ( '..' != $sFile ) ) &&
					( ( NULL === $iLevels ) || ( $iLevels > 0 ) )
				) {
					// recursive call
					$iLevels = ( NULL === $iLevels ) ? NULL : $iLevels - 1;
					$aConcat = array_merge( $aConcat, getParseableFiles( $sFullPath, $iLevels ) );
				}
			}
		}
		
		return $aConcat;
	}
	
	// create hash from a list of files
	public static function createHash( $aFiles )
	{
		$aVals = array();
		
		foreach ( $aFiles as $aRow ) {
			$aVals[] = $aRow['mtime'];
			$aVals[] = $aRow['inode'];
		}
		
		return md5( implode( '_', $aVals ) );
	}
	
}


