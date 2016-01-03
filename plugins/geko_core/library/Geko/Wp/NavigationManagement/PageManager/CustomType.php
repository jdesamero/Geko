<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/PageManager/CustomType.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_PageManager_CustomType
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{
	//
	protected $_aCptParams = array();
	
	
	
	//
	public function init() {
		
		//// custom post types normalized
		
		$aCptsNorm = array();
		
		$aCpts = get_post_types( array( 'show_ui' => TRUE ), 'objects' );
		
		foreach ( $aCpts as $oCpt ) {
			
			$sCptName = $oCpt->name;
			
			$aCptsNorm[ $sCptName ] = array(
				'label' => $oCpt->labels->singular_name,
				'link' => get_post_type_archive_link( $sCptName )
			);
		}
		
		$this->setCptParams( $aCptsNorm );
		
	}
	
	
	//
	public function setCptParams( $aCptParams ) {
		$this->_aCptParams = $aCptParams;
		return $this;
	}
	
	
	//
	public function getCptParams() {
		return $this->_aCptParams;
	}
	
	
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams[ 'custom_post_type' ] = key( $this->_aCptParams );
		
		return $aParams;
	}
	
	
	//
	public function getManagementData() {
		
		$aData = parent::getManagementData();
		$aData[ 'cpt_params' ] = $this->_aCptParams;
		
		return $aData;
	}

	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: lightcyan; border: dotted 1px steelblue; }
		<?php
	}
	
	
	//
	public function outputHtml() {
		?>
		<label for="##nvpfx_type##custom_post_type">Custom Post Type</label>
		<select name="##nvpfx_type##custom_post_type" id="##nvpfx_type##custom_post_type" class="text ui-widget-content ui-corner-all"></select>
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Custom Type';
    }
    
}

