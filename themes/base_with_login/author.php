<?php

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Categories:',
		102 => 'Tags:',
		103 => 'Permalink to %s',
		104 => 'Articles by:',
		105 => 'Items Found:'
	);
	
	//
	public function echoHeadLate() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'stylesheet_directory' ); ?>/styles/pagination.css" />	
		<?php
	}
	
	//
	public function echoContent() {
		
		$oAuthor = $this->newAuthor();
		$aPosts = $this->newPost_Query();
				
		?>
		<h1><?php $this->e_104(); ?> <?php $oAuthor->echoTheTitle(); ?></h1>
		<h2><?php $this->e_105(); ?> <?php echo $aPosts->getTotalRows(); ?></h2>
		
		<?php $this->doPagination(); ?>
		
		<?php foreach ( $aPosts as $oPost ): ?>
			<div id="post-<?php $oPost->echoId(); ?>">
				<div class="entry-content">
					<h2><a href="<?php $oPost->echoUrl(); ?>" title="<?php $this->pw( $this->l_103(), $oPost->escgetTitle() ); ?>" rel="bookmark"><?php $oPost->echoTitle(); ?></a></h2>
					<p><?php $oPost->echoDateCreated(); ?> - <?php $oPost->echoTimeCreated(); ?></p>
					<p><?php $oPost->echoTheExcerpt( 300 ); ?></p>
					<?php $this->pw( '<p><strong>' . $this->l_101() . '</strong> %s</p>', strval( $oPost->getCategories() ) ); ?>
					<?php $this->pw( '<p><strong>' . $this->l_102() . '</strong> %s</p>', strval( $oPost->getTags() ) ); ?>
				</div>
			</div>
		<?php endforeach; ?>
		
		<?php $this->doPagination(); ?>
		
		<?php
	}
	
}

geko_render_template();

