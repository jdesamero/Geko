<?php

//
class Gloc_Layout_SingleNews extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Filed Under:'
	);
	
	
	
	//
	public function echoContent() {
		
		$oPost = $this->newPost();
		
		$oMainImage = $oPost->getMainImage();
		
		?>
        <div id="post-<?php $oPost->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<h1><?php $oPost->echoTitle(); ?></h1>
			<div class="entry-content">
				
				<img src="<?php $oMainImage->echoUrl(); ?>" width="<?php $oMainImage->echoWidth(); ?>" width="<?php $oMainImage->echoHeight(); ?>" /><br />
				
				<?php $oPost->echoDateTimeCreated(); ?><br />
				<?php $this->e_101(); ?> <?php $oPost->echoCategories(); ?>
				<?php $oPost->echoTheContent(); ?>
				<?php $this->doLinkPages(); ?>
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPost->getTheEditLink() ); ?><br /><br />
				
			</div>
		</div>
        <?php
        
        // Add a key+value of "comments" to enable comments on this page
		if ( $oPost->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}



