<?php

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Not Found',
		102 => 'Apologies, but we were unable to find what you were looking for. Perhaps  searching will help.',
		103 => 'Find'
	);
	
	//
	public function echoContent() {
		
		?><div id="post-0" class="post error404">
			<h2 class="entry-title"><?php $this->e_101(); ?></h2>
			<div class="entry-content">
				<p><?php $this->e_102(); ?></p>
			</div>
			<form id="error404-searchform" method="get" action="<?php Geko_Wp::echoUrl(); ?>">
				<div>
					<input id="error404-s" name="s" type="text" value="<?php $this->doSearchTerm(); ?>" size="40" />
					<input id="error404-searchsubmit" name="searchsubmit" type="submit" value="<?php $this->e_103(); ?>" />
					<?php $this->doHiddenSearchFields(); ?>
				</div>
			</form>
		</div><?php
		
	}
}

geko_render_template();

