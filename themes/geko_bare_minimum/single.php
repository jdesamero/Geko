<?php

//
class Gloc_Layout_Single extends Gloc_Layout
{
	
	//
	public function echoContent() {
		
		$oPost = $this->newPost();
		
		?>
		<div id="post-<?php $oPost->echoId(); ?>">
			<h1><?php $oPost->echoTitle(); ?></h1>
			<div class="entry-content">
				<?php $oPost->echoDateTimeCreated(); ?><br />
				<?php $oPost->echoTheContent(); ?>
			</div>
		</div>
		<?php
		
	}
	
}


