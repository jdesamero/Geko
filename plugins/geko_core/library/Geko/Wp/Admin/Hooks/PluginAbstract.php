<?php

//
abstract class Geko_Wp_Admin_Hooks_PluginAbstract
{
	protected $_aValues = array();
	protected $_sCurrentFilter = '';
	
	
	//
	public function setValue( $sKey, $mValue ) {
		$this->_aValues[ $sKey ] = $mValue;
		return $this;
	}
	
	//
	public function getValue( $sKey ) {
		return $this->_aValues[ $sKey ];
	}
	
	
	//
	public function getStates() {
		return FALSE;
	}
	
	//
	public function applyFilters( $sContent, $sState ) {
		return $sContent;
	}
	
	
	//
	protected function replace( $sContent, $sFilter, $mPattern ) {
		
		if ( is_string( $mPattern ) ) {
			
			$this->_sCurrentFilter = $sFilter;
			$sContent = preg_replace_callback(
				$mPattern, array( $this, 'applyPq' ), $sContent
			);
			
		} elseif ( is_array( $mPattern ) ) {
			
			$aChunks = Geko_String::extractDelimitered(
				$sContent, $mPattern[ 0 ], $mPattern[ 1 ], '##%d##', 0
			);
			
			if ( is_array( $aChunks ) ) {
				$sContent = str_replace(
					'##0##', $this->applyPq( $aChunks[ 1 ], $sFilter ), $aChunks[ 0 ]
				);
			}
			
		}
		
		return $sContent;
	}
	
	
	//
	protected function applyPq( $aMatch, $sFilter = '' ) {
		
		if ( !$sFilter ) $sFilter = $this->_sCurrentFilter;
		
		if ( $sFilter ) {
			$oDoc = phpQuery::newDocument( $aMatch[ 0 ] );
			$oDoc = apply_filters( $sFilter, $oDoc );
			return strval( $oDoc );
		} else {
			return $aMatch[ 0 ];
		}
		
	}
	
	
}

