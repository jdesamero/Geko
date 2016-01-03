<?php
/*
 * "geko_core/library/Geko/Wp/PasswordProtect/PluginAdmin.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_PasswordProtect_PluginAdmin extends Geko_Wp_Plugin_Admin
{
	
	
	//
	public function getMenuTitle() {
		return 'Password Protect';
	}
	
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
				width: 375px;
			}
			.wrap textarea {
				width: 400px;
				height: 8em;
			}
			.wrap .checkboxes {
				float: left;
			}
			.wrap .help, .wrap .help2 {
				font-size: 10px;
			}
			.wrap .help {
				padding-left: 150px;
			}
			
		</style>
		<?php
	}
	
	//
	protected function formFields() {
		?>
		
		<h3>Settings</h3>
		
		<p>
			<label class="main">User</label> 
			<input id="user" name="user" type="text" class="text" value="" />
		</p>
		<p>
			<label class="main">Password</label> 
			<input id="pass" name="pass" type="text" class="text" value="" />
		</p>
		<p>
			<label class="main">Blurb</label> 
			<textarea id="blurb" name="blurb" cols="35" rows="7"></textarea>
		</p>
		<p>
			<label class="main">Use Custom Form</label> 
			<input id="use_custom_form" name="use_custom_form" type="checkbox" value="1" />
		</p>
		
		<p>&nbsp;</p>
		
		<?php
	}
	
	
}


