<?php

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Tags:',
		102 => 'Permalink to %s',
		103 => 'Items in Category:',
		104 => 'Items Found:'
	);
	
	//
	public function echoHeadLate() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_directory'); ?>/styles/pagination.css" />
		<?php
	}
	
	//
	public function echoContent() {
		
		$oCat = $this->newCategory();
		$aPosts = $this->newPost_Query();
		
		?>
		<h1><?php $this->e_103(); ?> <?php $oCat->echoTitle(); ?></h1>
		<h2><?php $this->e_104(); ?> <?php echo $aPosts->getTotalRows(); ?></h2>
		
		<?php $this->doPagination(); ?>
		
		<?php foreach ( $aPosts as $oPost ):
			
			//
			$oImg = $oPost->getAttachments( array(
				'file_group' => 'main_image'
			) )->getOne();
			
			?><div id="post-<?php $oPost->echoId(); ?>" class="<?php echo $this->applyPostClass(''); ?>">
				<div class="entry-content">
					<h2><a href="<?php $oPost->echoUrl(); ?>" title="<?php $this->pw( $this->l_102(), $oPost->escgetTitle() ); ?>" rel="bookmark"><?php $oPost->echoTitle(); ?></a></h2>
					<p><?php $oPost->echoDateCreated(); ?> - <?php $oPost->echoTimeCreated(); ?></p>
					<p>
						<?php if ( $oImg->isValid() ): ?>
							<img src="<?php $oImg->echoTheImageUrl( array( 'w' => 200, 'h' => 125 ) ); ?>" class="test" />
						<?php endif; ?>
						<?php $oPost->echoTheExcerpt( 600 ); ?>
					</p>
					<div class="fix"></div>
					<?php $this->pw( '<p><strong>' . $this->l_101() . '</strong> %s</p>', strval( $oPost->getTags() ) ); ?>
				</div>
			</div>
		<?php endforeach; ?>
		
		<?php $this->doPagination(); ?>
		
		<?php
	}
}

geko_render_template();

