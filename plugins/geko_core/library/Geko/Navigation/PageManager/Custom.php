<?php

//
class Geko_Navigation_PageManager_Custom
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{	
	
	//
	public function outputStyle() {
		?>
		.type-##type## { background-color: lightgray; border: dotted 1px black; }
		<?php
	}
	
	
	//
	public function outputHtml() {
		?>
		
		<label for="##nvpfx_type##custom_subject">Custom Subject</label>
		<input type="text" name="##nvpfx_type##custom_subject" id="##nvpfx_type##custom_subject" class="text ui-widget-content ui-corner-all" />
		
		<label for="##nvpfx_type##custom_params">Custom Parameters</label>
		<input type="text" name="##nvpfx_type##custom_params" id="##nvpfx_type##custom_params" class="text ui-widget-content ui-corner-all" />
		
		<?php
	}
	
	//
    public static function getDescription() {
    	return 'Custom Navigation Item';
    }

}

