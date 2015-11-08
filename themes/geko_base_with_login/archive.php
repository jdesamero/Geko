<?php

//
class Gloc_Layout_Archive extends Gloc_Layout
{

	protected $_aLabels = array(
		101 => 'Categories:',
		102 => 'Tags:',
		103 => 'Permalink to %s',
		104 => 'Archives For:',
		105 => 'Items Found:'
	);
	
	protected $_mStyles = 'gloc-pagination';
	
	
	
	//
	public function echoContent() {
		
		$aPosts = $this->newPost_Query();
		
		$sArchiveDate = '';
		if ( $this->is( 'year' ) ) $sArchiveDate = get_the_time( 'Y' );
		if ( $this->is( 'month' ) ) $sArchiveDate = get_the_time( 'F Y' );
		
		?>
		<h1><?php $this->l_104(); ?> <?php echo $sArchiveDate; ?></h1>
		<h2><?php $this->l_105(); ?> <?php echo $aPosts->getTotalRows(); ?></h2>
		
		<?php $this->doPagination(); ?>
		
		<?php foreach ( $aPosts as $oPost ): ?>
			<div id="post-<?php $oPost->echoId(); ?>" class="<?php echo $this->applyPostClass(''); ?>">
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



