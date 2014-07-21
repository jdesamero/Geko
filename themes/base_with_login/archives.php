<?php
/*
Template Name: Archives Page
*/

//
class Gloc_Layout_Archives extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Archives by Category',
		102 => 'Archives by Month',
		103 => 'Archives by Author',
		104 => 'Archives by Tag'
	);
	
	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>">
			<h1><?php $oPage->echoTitle(); ?></h1>
			<div class="entry-content">

				<?php $oPage->echoTheContent(); ?>

				<ul id="archives-page" class="xoxo">
					<li id="category-archives" class="content-column">
						<h3><?php $this->e_101(); ?></h3>
						<ul>
							<?php $this->listCats( 'sort_column=name&optioncount=1&feed=RSS' ); ?> 
						</ul>
					</li>
					<li id="monthly-archives" class="content-column">
						<h3><?php $this->e_102(); ?></h3>
						<ul>
							<?php $this->listArchives( 'type=monthly&show_post_count=1' ); ?>
						</ul>
					</li>
					<li id="author-archives" class="content-column">
						<h3><?php $this->e_103(); ?></h3>
						<ul>
							<?php $this->listAuthors(); ?>
						</ul>
					</li>
					<li id="tag-archives" class="content-column">
						<h3><?php $this->e_104(); ?></h3>
						<?php $this->tagCloud(); ?>
					</li>
				</ul>
				
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
				
			</div>
		</div>
		<?php
		
	}
	
}

geko_render_template();

