<?php
/*
Template Name: Links Page
*/

//
class Gloc_Layout_Links extends Gloc_Layout
{
	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<h1><?php $oPage->echoTitle(); ?></h1>
			<div class="entry-content">
				<?php $oPage->echoTheContent(); ?>

				<ul id="links-page" class="xoxo">
					<?php $this->listBookmarks( 'title_before=<h3>&title_after=</h3>' ) ?>
				</ul>
				
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}



