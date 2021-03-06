<?php
/*
Template Name: News
*/

//
class Gloc_Layout_PageNews extends Gloc_Layout
{
	
	protected $_aLabels = array(
		102 => 'Permalink to %s',
		104 => 'Items Found:'
	);
	
	protected $_mStyles = 'gloc-pagination';
	
	
	
	//
	public function echoContent() {
		
		$oPage = $this->newPage();
		
		$aPosts = $this->newPost_Query( array(
			'post_type' => 'news'
		), FALSE );
		
		?>
		<h1><?php $oPage->echoTitle(); ?></h1>
		<h2><?php $this->e_104(); ?> <?php echo $aPosts->getTotalRows(); ?></h2>
		
		<?php $this->doPagination(); ?>
		
		<?php foreach ( $aPosts as $oPost ):
			
			//
			$oImg = $oPost->getMainImage();
			
			?><div id="post-<?php $oPost->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
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
				</div>
			</div>
		<?php endforeach; ?>
		
		<?php $this->doPagination(); ?>
		
		<?php
	}
}


