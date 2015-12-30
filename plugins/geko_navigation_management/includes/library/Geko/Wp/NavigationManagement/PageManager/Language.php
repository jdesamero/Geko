<?php

//
class Geko_Wp_NavigationManagement_PageManager_Language
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{	
	
	//
	protected $_aLangParams = array();
	
	
	
	//
	public function init() {
		
		//// custom post types normalized
		
		$aLangsNorm = array();
		
		$aLangs = new Geko_Wp_Language_Query( array(), FALSE );
		
		$aLangsNorm[ '' ] = '(Default)';
		
		foreach ( $aLangs as $oLang ) {
			$aLangsNorm[ $oLang->getSlug() ] = $oLang->getTitle();
		}
		
		$this->setLangParams( $aLangsNorm );
		
	}
	
	
	//
	public function setLangParams( $aLangParams ) {
		$this->_aLangParams = $aLangParams;
		return $this;
	}
	
	
	//
	public function getLangParams() {
		return $this->_aLangParams;
	}

	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams[ 'lang' ] = key( $this->_aLangParams );
		
		return $aParams;
	}
	
	
	//
	public function getManagementData() {
		
		$aData = parent::getManagementData();
		$aData[ 'lang_params' ] = $this->_aLangParams;
		
		return $aData;
	}
	
	
	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: lavenderblush; border: dotted 1px purple; }
		<?php
	}
	
	
	
	//
	public function outputHtml() {
		?>
		<label for="##nvpfx_type##lang">Language</label>
		<select name="##nvpfx_type##lang" id="##nvpfx_type##lang" class="text ui-widget-content ui-corner-all"></select>
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Language Toggle';
    }
	
	
	
}

