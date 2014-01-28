<?php
/*
Template Name: Product/Application Landing
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		
		//
		$aBoxes = $oPage->getChildren( array(
			'orderby' => 'menu_order'
		) );
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<div class="entry-content">
				<h1 class="page-title"><?php $oPage->echoTitle(); ?></h1>
				<div class="intro-text">
					<?php $oPage->echoMeta( 'intro_text' ); ?>
				</div>
				<div class="main-text">
					<?php $oPage->echoTheContent(); ?>
				</div>
				
				<ul class="box-pages">
				<?php foreach ( $aBoxes as $oBoxPage ):
						$oImage = $oBoxPage->getAttachments( array( 'file_group' => 'main_image' ) )->getOne();
					?>
						<li class="box-page">
							<a href="<?php $oBoxPage->echoUrl(); ?>">
								<h3><?php $oBoxPage->echoTitle(); ?></h3>
								<img src="<?php $oImage->echoUrl(); ?>" width="220" height="111" />
							</a>
							<p><?php $oBoxPage->echoMeta( 'intro_text' ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
				<div class="clear"></div>
				
				<?php $this->doLinkPages(); ?>
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
				
				<div class="breadcrumbs"><?php $this->doNavBreadcrumb( 'main' ); ?></div>
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}

geko_render_template();

