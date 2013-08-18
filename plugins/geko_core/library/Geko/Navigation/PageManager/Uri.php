<?php

//
class Geko_Navigation_PageManager_Uri
	extends Geko_Navigation_PageManager_PluginAbstract
{
	private $_sDefaultLabel = 'Untitled Link';
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams['uri'] = '/index.html';
		
		return $aParams;
	}
	
	
	//
	public function getManagementData() {
		
		$aData = parent::getManagementData();
		$aData['default_label'] = $this->_sDefaultLabel;
		
		return $aData;
	}
	
	
	//
	public function outputStyle() {
		?>
		.type-##type## { background-color: lightgray; border: solid 1px black; }
		<?php
	}
	
	
	
	//
	public function outputHtml() {
		?>
		<label for="##nvpfx_type##uri">Uri</label>
		<input type="text" id="##nvpfx_type##uri" name="##nvpfx_type##uri" class="text ui-widget-content ui-corner-all" />		

		<label for="##nvpfx_type##strict_match">Strict Match</label>
		<input type="checkbox" name="##nvpfx_type##strict_match" id="##nvpfx_type##strict_match" class="text ui-widget-content ui-corner-all" />

		<label for="##nvpfx_type##ignore_vars">Ignore Vars</label>
		<input type="text" id="##nvpfx_type##ignore_vars" name="##nvpfx_type##ignore_vars" class="text ui-widget-content ui-corner-all" />
		<?php
	}
	
	//
    public static function getDescription() {
    	return 'Standard URL';
    }
    
}

