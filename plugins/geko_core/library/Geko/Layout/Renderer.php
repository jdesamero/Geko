<?php

//
class Geko_Layout_Renderer extends Geko_Singleton_Abstract
{
	protected $aLayouts = array();
	
	//
	public function addLayout( Geko_Layout $oLayout ) {
		$this->aLayouts[] = $oLayout;
		return $this;
	}
	
	//
	public function addLayoutUnshift( Geko_Layout $oLayout ) {
		array_unshift( $this->aLayouts, $oLayout );
		return $this;
	}
	
	//
	public function render() {
		$this->doMain();
		return $this;
	}
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( 0 === strpos( strtolower( $sMethod ), 'do' ) ) {
			
			$sCall = substr_replace( $sMethod, 'get', 0, 2 );
			$sOut = '';
			
			foreach ( $this->aLayouts as $oLayout ) {
				$sOut .= call_user_func_array( array( $oLayout, $sCall ), $aArgs );
			}
			
			$sCall = substr_replace( $sMethod, 'alter', 0, 2 );
			foreach ( $this->aLayouts as $oLayout ) {
				if ( method_exists( $oLayout, $sCall ) ) {
					$sOut = call_user_func( array( $oLayout, $sCall ), $sOut );
				}
			}
			
			$sCall = substr_replace( $sMethod, 'pq', 0, 2 );
			$oPq = NULL;
			foreach ( $this->aLayouts as $oLayout ) {
				if ( method_exists( $oLayout, $sCall ) ) {
					if ( !$oPq ) $oPq = phpQuery::newDocument( $sOut );
					$oPq = call_user_func( array( $oLayout, $sCall ), $oPq );
				}
			}
			
			if ( $oPq ) $sOut = strval( $oPq );
			
			echo $sOut;
			
			return TRUE;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'apply' ) ) {
			
			$sCall = substr_replace( $sMethod, 'filter', 0, 5 );
			$mRes = ( isset( $aArgs[ 0 ] ) ) ? $aArgs[ 0 ] : NULL;
			
			foreach ( $this->aLayouts as $oLayout ) {
				if ( method_exists( $oLayout, $sCall ) ) {
					$mRes = call_user_func_array( array( $oLayout, $sCall ), $aArgs );
					$aArgs[ 0 ] = $mRes;
				}
			}
			
			return $mRes;
			
		}
		
		throw new Exception( 'Invalid method ' . __CLASS__ . '::' . $sMethod . '() called.' );
	}
	
}


