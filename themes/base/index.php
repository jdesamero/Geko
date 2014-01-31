<?php

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Categories:',
		102 => 'Tags:',
		103 => 'Permalink to %s',
		104 => 'Items Found:'
	);
	
	
	
	//
	public function echoEnqueue() {
		$this->enqueueStyle( 'base-pagination' );
	}
	
	
	//
	public function echoContent() {
		
		$aPosts = $this->newPost_Query();
		
		?>
		<h1><a href="<?php Geko_Wp::echoUrl(); ?>" title="<?php bloginfo( 'name' ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
		<h2><?php $this->e_104(); ?> <?php echo $aPosts->getTotalRows(); ?></h2>
		
		<?php $this->doPagination(); ?>
		
		<?php foreach ( $aPosts as $oPost ): ?>
			<div id="post-<?php $oPost->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
				<div class="entry-content">
					<h2><a href="<?php $oPost->echoUrl(); ?>" title="<?php $this->pw( $this->l_103(), $oPost->escgetTitle() ); ?>" rel="bookmark"><?php $oPost->echoTitle(); ?></a></h2>
					<p><?php $oPost->echoDateCreated(); ?> - <?php $oPost->echoTimeCreated(); ?></p>
					<p><?php $oPost->echoTheExcerpt( 300 ); ?></p>
					<?php $this->pw( '<p><strong>%s$1</strong> %s$0</p>', strval( $oPost->getCategories() ), $this->l_101() ); ?>
					<?php $this->pw( '<p><strong>%s$1</strong> %s$0</p>', strval( $oPost->getTags() ), $this->l_102() ); ?>
				</div>
			</div>
		<?php endforeach; ?>
		
		<?php $this->doPagination(); ?>
		
		<?php
	}
}

geko_render_template();


