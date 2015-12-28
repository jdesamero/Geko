<?php
/*
 * "geko_navigation_management/includes/library/Geko/Wp/NavigationManagement/PageManager/Category.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_PageManager_Category
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{
	//
	protected $_aCatParams = array();
	
	
	
	//
	public function init() {
		
		$aCatsNorm = array();
		
		$aParams = array(
			// 'hide_empty' => FALSE,			// this really has no effect since we're using "use_non_native_query"
			'use_non_native_query' => TRUE,
			'any' => TRUE
		);
		
		$aParams = apply_filters( 'admin_geko_wp_nav_cat_query_params', $aParams );
		
		// normalized categories
		$aCats = new Geko_Wp_Category_Query( $aParams, FALSE );
		$aUsedCats = array();
		
		foreach ( $aCats as $oCat ) {
			
			$sType = $oCat->getTaxonomy();
			
			$aCatsNorm[ $oCat->getId() ] = array(
				'title' => $oCat->getTheTitle(),
				'link' => $oCat->getUrl(),
				'type' => $sType
			);
			
			if ( !in_array( $sType, $aUsedCats ) ) {
				$aUsedCats[] = $sType;
			}
		}
		
		
		// category types
		$aCatTypes = array();
		
		$aTaxonomies = get_taxonomies( array(), 'objects' );
		foreach ( $aTaxonomies as $oTx ) {
			$sType = $oTx->name;
			if ( in_array( $sType, $aUsedCats ) ) {
				$aCatTypes[ $oTx->name ] = $oTx->labels->singular_name;
			}
		}
		
		
		$this->setCatParams( array(
			'cats_norm' => $aCatsNorm,
			'cat_types' => $aCatTypes
		) );
		
	}
	
	
	//
	public function setCatParams( $aCatParams ) {
		
		$this->_aCatParams = $aCatParams;
		
		return $this;
	}
	
	//
	public function getCatParams() {
		return $this->_aCatParams;
	}
	
	
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams[ 'cat_id' ] = key( $this->_aCatParams[ 'cats_norm' ] );
				
		return $aParams;
	}
	
	
	//
	public function getManagementData() {
		
		$aData = parent::getManagementData();
		$aData[ 'cat_params' ] = $this->_aCatParams;
		
		return $aData;
	}

	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: palegreen; border: dotted 1px darkgreen; }
		<?php
	}
	
	
	//
	public function outputHtml() {
		?>
		<label for="##nvpfx_type##cat_type">Category Type</label>
		<select name="##nvpfx_type##cat_type" id="##nvpfx_type##cat_type" class="text ui-widget-content ui-corner-all"></select>		
		
		<label for="##nvpfx_type##cat_id">Category Title</label>
		<select name="##nvpfx_type##cat_id" id="##nvpfx_type##cat_id" class="text ui-widget-content ui-corner-all"></select>		
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Category';
    }
    
}

