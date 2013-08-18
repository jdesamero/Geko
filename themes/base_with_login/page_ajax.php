<?php
/*
Template Name: Ajax
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
		
	// .../ajax-content/?section=blog
	public function getBlogAjax() {
		
		ob_start();
		
		?>
		
		<ul>
			<li>Blog item one.</li>
			<li>Blog item two.</li>
			<li>Blog item three.</li>
		</ul>
		
		<?php
		
		$sOut = ob_get_contents();
		ob_end_clean();
		
		return array(
			'content' => $sOut
		);
	}
	
	// .../ajax-content/?section=news
	public function getNewsAjax() {
		
		ob_start();
		
		?>
		
		<ul>
			<li>News item one.</li>
			<li>News item two.</li>
			<li>News item three.</li>
		</ul>
		
		<?php
		
		$sOut = ob_get_contents();
		ob_end_clean();
		
		return array(
			'content' => $sOut
		);
	}


	// .../ajax-content/?section=some_foo
	// public function getSomeFooAjax() ...
	
	
}

geko_render_template();