<?php

class Geko_Wp_Form_ItemType extends Geko_Wp_Entity
{
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'fmitmtyp_id' )
			->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'slug', 'slug' )
		;
		
		return $this;
	}
	
	//
	public function render( $oItem, $aValues, $aResponse ) {
		
		echo strval( $this->get( $oItem, $aValues, $aResponse ) );
		
		return $this;
	}
	
	// format item/values for use with widget factory
	public function get( $oItem, $aValues, $aResponse ) {
		
		$aResponse = ( is_array( $aResponse ) ) ? $aResponse : array() ;
		$aResponse = ( $this->getHasMultipleResponse() ) ? $aResponse : stripslashes( $aResponse[ 0 ] );
		
		// HACK!!!
		$sItemTypeSlug = $this->getSlug();
		$sElemName = $oItem->getElemName();
		$sItemCss = trim( $oItem->getCss() );
		
		if ( 'select_multi' == $sItemTypeSlug ) $sElemName .= '[]';
		
		$aAtts = array(
			'id' => $oItem->getElemId(),
			'name' => $sElemName
		);
		
		if ( $sItemCss ) $aAtts[ 'class' ] = $sItemCss;
		
		if (
			( !$this->getHasMultipleValues() ) && 
			( $oVal = $aValues[ 0 ] )
		) {
			if ( 'checkbox' == $sItemTypeSlug ) $aAtts[ 'value' ] = 1;
		}
				
		$aParams = array();
		
		if ( $this->getHasMultipleValues() ) {
		
			$aChoices = array();
			
			foreach ( $aValues as $oVal ) {
				
				$sElem = $oVal->getElemValue();
				$sChoiceName = $oItem->getElemName();
				
				if ( 'checkbox_multi' == $sItemTypeSlug ) $sChoiceName .= '[]';
				
				$aChoice = array(
					'label' => $oVal->getTitle(),
					'atts' => array(
						'id' => $oItem->getElemId() . '-' . $oVal->getElemId(),
						'name' => $sChoiceName,
						'value' => $sElem
					)
				);
				
				if ( $sItemCss ) $aChoice[ 'class' ] = $sItemCss;
				
				$aChoices[ $sElem ] = $aChoice;
			}
			
			$aParams[ 'choices' ] = $aChoices;
			
		}
		
		return _gw( $sItemTypeSlug, $aAtts, $aResponse, $aParams )->get();
		
	}
	
	//
	public function getResponse( $oItem, $aValues, $aResponse ) {
	
		$sItemTypeSlug = $this->getSlug();
		$sElemName = $oItem->getElemName();
		
		if ( $this->getHasMultipleValues() ) {
			
			$aRes = array();
			foreach ( $aValues as $oVal ) {
				if ( in_array( $oVal->getSlug(), $aResponse ) ) {
					$aRes[] = $oVal->getTitle();
				}
			}
			
			return implode( ', ', $aRes );
		}
		
		return stripslashes( $aResponse[ 0 ] );
	}
	
	
}


