<?php

class Geko_Wp_Cart66 extends Geko_Singleton_Abstract
{
	
	protected $bCalledInit = FALSE;
	
	
	
	//
	public function init() {
		
		if ( !$this->bCalledInit ) {
			
			Geko_Wp_Db::addPrefix( 'cart66_products' );
			
			$this->bCalledInit = TRUE;
		}
	}
	
	
	//
	public function getProductGroupHash() {
		
		global $wpdb;
		
		$sQuery = sprintf(
			'SELECT DISTINCT p.name, MD5( p.name ) AS name_key FROM %s p ORDER BY p.name ASC',
			$wpdb->cart66_products
		);
		
		$aResFmt = array();
		$aRes = $wpdb->get_results( $sQuery, ARRAY_A );
		
		foreach ( $aRes as $aRow ) {
			$aResFmt[ $aRow[ 'name_key' ] ] = $aRow[ 'name' ];
		}
		
		return $aResFmt;
	}
	
	
	//
	public function getProductVarieties( $sProdGroupKey ) {
		
		global $wpdb;
		
		$sQuery = sprintf(
			"SELECT p.id, p.name, p.item_number, p.price_description, p.price FROM %s p WHERE MD5( p.name ) = '%s'",
			$wpdb->cart66_products,
			addslashes( $sProdGroupKey )
		);
		
		return $wpdb->get_results( $sQuery, ARRAY_A );
	}
	
	//
	public function getProductVarietySelectHtml( $sProdGroupKey, $aAtts, $mValue = NULL, $aParams = array() ) {
		
		if ( !isset( $aParams[ 'empty_choice' ] ) ) {
			$aParams[ 'empty_choice' ] = '- Select -';
		}
		
		$aProdVars = $this->getProductVarieties( $sProdGroupKey );
		
		if ( is_array( $aProdVars ) ) {
			
			foreach ( $aProdVars as $aRow ) {
				$aParams[ 'choices' ][ $aRow[ 'id' ] ] = array(
					'atts' => array( 'data-prodname' => $aRow[ 'name' ] ),
					'label' => sprintf( '%s $%s', $aRow[ 'price_description' ], $aRow[ 'price' ] )
				);
			}
			
			$oWidget = Geko_Html_Widget::create( 'select', $aAtts, $mValue, $aParams );
			return strval( $oWidget->get() );
		}
		
		return NULL;
	}
	
	
	//
	public function getCartNumPieces() {
		
		$iTotal = 0;
		
		if ( $oCart = $_SESSION[ 'cart66' ][ 'Cart66Cart' ] ) {
			$aCartItems = $oCart->getItems();
			foreach ( $aCartItems as $oItem ) {
				$iTotal += intval( $oItem->getQuantity() );
			}
		}
		
		return $iTotal;
	}
	
	
	//
	public function outputCart() {
		
		$sCart = do_shortcode( '[cart]' );
		
		$oDoc = Geko_PhpQuery_FormTransform::createDoc( $sCart );
		
		$aTr = $oDoc->find( 'tbody > tr' );
		foreach ( $aTr as $oTr ) {
			
			$oTrPq = pq( $oTr );
			
			if ( !trim( $oTrPq->attr( 'class' ) ) ) {
				
				$aTd = $oTrPq->find( 'td' );
				
				$oProdTdPq = $aTd->eq( 0 );
				$oQtyTdPq = $aTd->eq( 1 );
				$oDescTdPq = $aTd->eq( 2 );
				$oTotalTdPq = $aTd->eq( 3 );
				
				$iQty = intval( $oQtyTdPq->find( 'input' )->val() );
				$fTotal = floatval( trim( str_replace( '$', '', $oTotalTdPq->text() ) ) );
				$sProdDesc = trim( $oDescTdPq->text() );
				
				$oDescTdPq->html( '$' . ( $fTotal / $iQty ) );
				$oProdTdPq->append( sprintf( '<span>- %s</span>', $sProdDesc ) );
			}
		}
		
		echo strval( $oDoc );
	}
	
	
}



