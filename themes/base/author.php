<?php

//
class Gloc_Layout_Template extends Gloc_Layout
{
	//
	public function echoHeadLate() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_directory'); ?>/styles/pagination.css" />	
		<?php
	}
	
	//
	public function echoContent() {
		
		$oAuthor = $this->newAuthor();
		$aPosts = $this->newPost_Query();
		
		$sCatLabel = $this->_t( 'Categories:' );
		$sTagLabel = $this->_t( 'Tags:' );
		
		$sPermTitle = $this->_t( 'Permalink to %s' );
		
		?>
		<h1><?php $this->_e( 'Articles by:' ); ?> <?php // $oAuthor->echoTheTitle(); ?></h1>
		<h2><?php $this->_e( 'Items Found:' ); ?> <?php echo $aPosts->getTotalRows(); ?></h2>
		
		<?php $this->doPagination(); ?>
		
		<?php foreach ( $aPosts as $oPost ): ?>
			<div id="post-<?php $oPost->echoId(); ?>">
				<div class="entry-content">
					<h2><a href="<?php $oPost->echoUrl(); ?>" title="<?php $this->pw( $sPermTitle, $oPost->escgetTitle() ); ?>" rel="bookmark"><?php $oPost->echoTitle(); ?></a></h2>
					<p><?php $oPost->echoDateCreated(); ?> - <?php $oPost->echoTimeCreated(); ?></p>
					<p><?php $oPost->echoTheExcerpt( 300 ); ?></p>
					<?php $this->pw( '<p><strong>' . $sCatLabel . '</strong> %s</p>', strval( $oPost->getCategories() ) ); ?>
					<?php $this->pw( '<p><strong>' . $sTagLabel . '</strong> %s</p>', strval( $oPost->getTags() ) ); ?>
				</div>
			</div>
		<?php endforeach; ?>
		
		<?php $this->doPagination(); ?>
		
		<?php
	}
	
}

geko_render_template();