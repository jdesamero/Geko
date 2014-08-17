<?php

// a simple class resolver
class Geko_Wp_Resolver
{
	
	protected $_aPaths = array();
	protected $_oCurPath = NULL;
	protected $_sCurPathKey = '';
	
	protected $_aClassMapping = array();
	
	
	
	//
	public function addPath( $sKey, $oPath, $aDeps = array() ) {
		$this->_aPaths[ $sKey ] = array( $oPath, $aDeps );
		return $this;
	}
	
	//
	public function setClassFileMapping( $aClassMapping ) {
		$this->_aClassMapping += $aClassMapping;
		return $this;
	}
	
	//
	public function getClassSuffixes() {
		return array_keys( $this->_aClassMapping );
	}
	
	// iterate through paths then stop if there's a match
	public function run() {
		
		foreach ( $this->_aPaths as $sKey => $aPath ) {
			$oPath = $aPath[ 0 ];
			if ( $oPath->isMatch() && !$this->_oCurPath ) {
				$this->_oCurPath = $oPath;
				$this->_sCurPathKey = $sKey;
				break;
			}
		}
		
		return $this;
	}
	
	//
	public function getClass( $sSuffix ) {
		
		$oCurPath = $this->_oCurPath;
		
		$sClassFile = $this->_aClassMapping[ $sSuffix ];
		
		// load class files and dependencies
		$this->loadFiles( $this->_sCurPathKey, $sClassFile );
		
		return Geko_Class::getBestMatch( $oCurPath->getPrefixes(), array( $sSuffix ) );
	}
	
	// recursive
	public function loadFiles( $sPathKey, $sClassFile ) {
		
		list( $oPath, $aDeps ) = $this->_aPaths[ $sPathKey ];
		
		// load any dependencies first
		if ( count( $aDeps ) > 0 ) {
			foreach ( $aDeps as $sDepKey ) {
				$this->loadFiles( $sDepKey, $sClassFile );
			}
		}
		
		$sClassFile = $oPath->resolvePath( $sClassFile );
		Geko_File::requireOnceIfExists( $sClassFile );
	}
	
}


