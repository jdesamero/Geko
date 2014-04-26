<?php

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Search Results for',
		102 => 'Items Found:',
		103 => 'Categories:',
		104 => 'Tags:',
		105 => 'Permalink to %s',
		106 => 'Nothing Found',
		107 => 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.',
		108 => 'Find'
	);
	
	
	
	//
	public function echoEnqueue() {
		$this->enqueueStyle( 'gloc-pagination' );
	}
	
	
	//
	public function echoContent() {
		
		$aPosts = $this->newPost_Query();
		
		if ( $aPosts->count() > 0 ) : ?>
		
			<h2><?php $this->e_101(); ?> <span id="search-terms"><?php $this->doSearchTerm(); ?></span></h2>
			<h3><?php $this->e_102(); ?> <?php echo $aPosts->getTotalRows(); ?></h3>
			
			<?php $this->doPagination(); ?>
			
			<?php foreach ( $aPosts as $oPost ): ?>
				
				<div id="post-<?php $oPost->echoId(); ?>" class="<?php echo $this->applyPostClass(''); ?>">
					<h3 class="entry-title"><a href="<?php $oPost->echoUrl(); ?>" title="<?php $this->pw( $this->l_105(), $oPost->escgetTitle() ); ?>" rel="bookmark"><?php $oPost->echoTitle(); ?></a></h3>
					<div class="entry-date"><?php $oPost->echoDateCreated(); ?> - <?php $oPost->echoTimeCreated(); ?></div>
					<div class="entry-content"><?php $oPost->echoTheExcerpt( 300 ); ?></div>
					<?php $this->pw( '<p><strong>%s$1</strong> %s$0</p>', strval( $oPost->getCategories() ), $this->l_103() ); ?>
					<?php $this->pw( '<p><strong>%s$1</strong> %s$0</p>', strval( $oPost->getTags() ), $this->l_104() ); ?>
				</div>
				
			<?php endforeach; ?>
		
			<?php $this->doPagination(); ?>
			
		<?php else : ?>
			
			<div id="post-0" class="post noresults">
				<h2 class="entry-title"><?php $this->e_106(); ?></h2>
				<div class="entry-content">
					<p><?php $this->e_107(); ?></p>
				</div>
				<form id="noresults-searchform" method="get" action="<?php Geko_Wp::echoUrl(); ?>">
					<div>
						<input id="noresults-s" name="s" type="text" value="<?php $this->doSearchTerm(); ?>" size="40" />
						<input id="noresults-searchsubmit" name="searchsubmit" type="submit" value="<?php $this->e_108(); ?>" />
						<?php $this->doHiddenSearchFields(); ?>
					</div>
				</form>
			</div>
			
		<?php endif;
		
	}
}

geko_render_template();

