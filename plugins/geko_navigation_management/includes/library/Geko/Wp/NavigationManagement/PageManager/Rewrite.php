<?php

//
class Geko_Wp_NavigationManagement_PageManager_Rewrite
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{	
	
	//
	public function outputStyle() {
		?>
		.type-##type## { background-color: red; border: dotted 1px black; }
		<?php
	}
	
	
	//
	public function outputHtml() {
		?>
		<label for="##nvpfx_type##rw_subj">Subject</label>
		<input type="text" name="##nvpfx_type##rw_subj" id="##nvpfx_type##rw_subj" class="text ui-widget-content ui-corner-all" />
		<label for="##nvpfx_type##rw_type">Type</label>
		<select name="##nvpfx_type##rw_type" id="##nvpfx_type##rw_type" class="text ui-widget-content ui-corner-all">
			<option value="list">List</option>
			<option value="single">Single</option>
			<option value="custom_method">Custom Method</option>
		</select>		
		<label for="##nvpfx_type##rw_cmthd">Custom Method</label>
		<input type="text" name="##nvpfx_type##rw_cmthd" id="##nvpfx_type##rw_cmthd" class="text ui-widget-content ui-corner-all" />
		<?php
	}
	
	//
    public static function getDescription() {
    	return 'Wordpress Rewrite';
    }

}

