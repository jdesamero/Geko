<?php

//
class Geko_Router_Route_File extends Geko_Router_Route
{
	
	protected $_sFile = NULL;
	protected $_sMime = NULL;
	
	protected $_aAllowedExtensions = array(
		'css' => 'text/css',
		'js' => 'text/javascript',
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'gif' => 'image/gif',
		'png' => 'image/png'
	);
	
	
	//
	public function isMatch() {
	
		$oRouter = $this->_oRouter;
		
		// $oRouter->getPath() returns an instance of Geko_Router_Path
		$sFile = strval( $oRouter->getPath() );
		
		$sExt = strtolower( pathinfo( $sFile, PATHINFO_EXTENSION ) );
		
		if (
			( $sFile ) && 
			( is_file( $sFile ) ) && 
			( array_key_exists( $sExt, $this->_aAllowedExtensions ) )
		) {
			
			$this->_sFile = $sFile;
			$this->_sMime = $this->_aAllowedExtensions[ $sExt ];
			
			$oRouter->setCurrentRoute( $this );
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	
	//
	public function run() {
	
		if ( $this->_sFile ) {
			
			// ob_clean()
			// flush();
			
			header( sprintf( 'Content-Type: %s', $this->_sMime ) );
			header( sprintf( 'Content-Length: %s', filesize( $this->_sFile ) ) );
			
			readfile( $this->_sFile );
			
			exit;
		}
		
	}
	
	//
	public function getTarget() {
		
		return $this->_sFile;
	}
	
	
}


