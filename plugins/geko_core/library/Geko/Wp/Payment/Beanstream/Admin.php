<?php

// abstract
class Geko_Wp_Payment_Beanstream_Admin extends Geko_Wp_Payment_Admin
{
	
	protected $_sPrefix = 'geko_pay_beanstream';
	
	protected $_sMenuTitle = 'Beanstream';
	protected $_sAdminType = 'Beanstream Payment Gateway';
	
	protected $_sMenuTitleSuffix = '';
	
	
	//
	protected function preWrapDiv() {
		?>
		<style type="text/css">

			.fix {
				clear: both;
				height: 1px;
				margin: 0 0 -1px 0;
				overflow: hidden;
			}
			
			.wrap label.main {
				display: block;
				float: left;
				width: 150px;
			}
			.wrap select {
				width: 175px;
			}
			.wrap select.multi {
				height: 6em !important;
			}
			.wrap input.text {
				width: 250px;
			}
			.wrap input.short {
				width: 70px;
			}
			.wrap input.long {
				width: 400px;
			}
			.wrap textarea {
				width: 400px;
				height: 8em;
			}
			.wrap .checkboxes {
				float: left;
			}
			
		</style>
		<?php
	}
	
	
	//
	protected function formFields() {
		?>
		
		<h3>Transaction Settings</h3>
		
		<p>
			<label class="main">Setting One:</label> 
			<input id="setting_one" name="setting_one" type="text" class="text long" value="" />
		</p>
		<p>
			<label class="main">Setting Two:</label> 
			<input id="setting_two" name="setting_two" type="text" class="text" value="" />
		</p>
		
		<?php
	}
	
	
}


