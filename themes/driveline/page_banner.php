<?php
/*
Template Name: Default with Banner
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		$oImage = $oPage->getAttachments( array( 'file_group' => 'main_image' ) )->getOne();
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<div class="entry-content">
				
				<img class="banner-image" src="<?php $oImage->echoUrl(); ?>" />
				
				<h1 class="page-title"><?php $oPage->echoTitle(); ?></h1>
				
				<div class="dk-gray">
					<?php $oPage->echoTheContent(); ?>
					<?php $this->doLinkPages(); ?>
					<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
				</div>
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}

geko_render_template();

