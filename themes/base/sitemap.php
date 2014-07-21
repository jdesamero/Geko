<?php
/*
Template Name: Sitemap
*/

//
class Gloc_Layout_Sitemap extends Gloc_Layout
{
	public function echoContent() {		
		
		$oPage = $this->newPage();
		
		?>
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<h1><?php $oPage->echoTitle(); ?></h1>
			<div class="entry-content">
				
				<br />
				<?php $this->doNavMenu( 'main' ); ?>
				
				<br />
				<?php $this->doNavMenu( 'bottom' ); ?>
				
				<br />
				<?php $this->doLinkPages(); ?>
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
				
			</div>
		</div>
		<?php
		
	}
}

geko_render_template();

