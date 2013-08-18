<?php

//
class Geko_Wp_Form_Render_Items extends Geko_Singleton_Abstract
{

	//
	public function render( $oOl, $aItems, $aItemValues, $aResponses, $aParams = array() ) {
				
		// create item hash
		$aItemHash = array();
		$aItemChildren = array();
		$aItemRoot = array();
		
		foreach ( $aItems as $oItem ) {
			
			$iItemId = $oItem->getId();
			$aItemHash[ $iItemId ] = $oItem;
			
			if (
				( $iParItemId = $oItem->getParentItmId() ) && 
				( $iParItmValIdx = $oItem->getParentItmvalidxId() )
			) {
				// is a sub item
				$aItemChildren[ $iParItemId ][ $iParItmValIdx ][] = $iItemId;
			} else {
				// is a root item
				$aItemRoot[] = $iItemId;
			}
		}
		
		$aArgs = array(
			'item_hash' => $aItemHash,
			'item_ids' => $aItemRoot,
			'item_children' => $aItemChildren,
			'item_types' => Geko_Wp_Form::getItemTypes(),
			'item_values' => $aItemValues,
			'responses' => $aResponses,
			'params' => $aParams
		);
		
		$oOl = $this->getItems( $aArgs, $oOl );
		
		echo strval( $oOl );
	}
	
	
	//
	public function getItems( $aArgs, $oOl = NULL ) {
		
		$aItemHash = $aArgs[ 'item_hash' ];
		$aItemRoot = $aArgs[ 'item_ids' ];
		$aItemChildren = $aArgs[ 'item_children' ];
		$aItemTypes = $aArgs[ 'item_types' ];
		$aItemValues = $aArgs[ 'item_values' ];
		$aResponses = $aArgs[ 'responses' ];
		
		
		$aParams = $aArgs[ 'params' ];
		$sListType = ( $aParams[ 'list_type' ] ) ? $aParams[ 'list_type' ] : 'ol' ;
		$fItemFilter = $aParams[ 'item_filter' ];
		$fLiCallback = $aParams[ 'li_callback' ];
		
		
		if ( NULL == $oOl ) {
			
			$oParItem = $aArgs[ 'parent_item' ];
			$oChoice = $aArgs[ 'choice' ];
			
			$sUlId = sprintf( 'sub-%s-%s', $oParItem->getSlug(), $oChoice->getSlug() );
			
			$oOl = _ge( $sListType, array( 'id' => $sUlId, 'class' => 'geko-form-sub' ) );
		}
		
		//
		foreach ( $aItemRoot as $iItemId ) {
			
			$oItem = $aItemHash[ $iItemId ];
			
			$iTypeId = $oItem->getItemTypeId();
			$sSlug = $oItem->getSlug();
			
			$aValues = $aItemValues->subsetItemId( $iItemId );
			$oItemType = $aItemTypes->subsetId( $iTypeId )->getOne();
			
			if (
				( $fItemFilter ) && 
				( call_user_func( $fItemFilter, $oItem ) )
			) {
				// skip if item filter returns true
				continue;
			}
			
			$oLi = _ge( 'li' );
			
			// item body
			$oLi->append( $oItem->getTitle() );
			
			// help text, if any
			if ( $sHelp = $oItem->getHelp() ) {
				$oSpan = _ge( 'span', array(), 'Help' );
				$oA = _ge( 'a', array( 'href' => '#', 'title' => $sHelp, 'class' => 'geko-form-item-help' ) );
				$oA->append( $oSpan );
				$oLi->append( $oA );
			}
			
			// widget
			$oLi
				->append( _ge( 'br' ) )
				->append( $oItemType->get( $oItem, $aValues, $aResponses[ $sSlug ] ) )
			;
			
			// child <li>'s, if any
			if ( $aChildIds = $aItemChildren[ $iItemId ] ) {
				
				// group by choice
				foreach ( $aChildIds as $iItmValIdx => $aIds ) {
					
					$aArgs[ 'item_ids' ] = $aIds;
					
					$aArgs[ 'parent_item' ] = $oItem;
					$aArgs[ 'choice' ] = $aValues->subsetFmitmvalIdx( $iItmValIdx )->getOne();
					
					$oChildUl = $this->getItems( $aArgs );		// recursive
					$oLi->append( $oChildUl );
				}
				
			}
			
			if ( $fLiCallback ) {
				$oLi = call_user_func( $fLiCallback, $oLi, $oItem );
			}
			
			$oOl->append( $oLi );
			
		}
		
		return $oOl;
	}
	
	
}

