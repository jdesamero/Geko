<?php

//
class Gloc_Layout_Main extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Copyright %s',
		102 => 'Posts RSS feed',
		103 => 'Comments RSS feed',
		104 => 'Search'
	);
	
	
	
	//
	public function echoEnqueue() {
		$this->enqueueScript( 'jquery' );
	}
	
	//
	public function echoMain() {
		
		// register templates
		$this
			->addTemplate( 'page_template:homepage.php', 'custom' )
			->addTemplate( 'page_template:nosidebar.php', 'no_sidebar' )
		;
		
		$this->doEnqueue();
		$this->doGetHeader();
		
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes() ?>>
		
		<head profile="http://gmpg.org/xfn/11">
			
			<title><?php echo $this->applyTitle( '' ); ?></title>
			
			<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
			<meta name="Description" content="<?php bloginfo( 'description' ); ?>" />
			<meta name="generator" content="WordPress <?php bloginfo( 'version' ); ?>" /><!-- Please leave for stats -->
			
			<?php $this->doMeta(); ?>
			
			<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
			<link rel="alternate" type="application/rss+xml" href="<?php bloginfo( 'rss2_url' ); ?>" title="<?php echo wp_specialchars( get_bloginfo( 'name' ), 1 ); ?> <?php $this->e_102(); ?>" />
			<link rel="alternate" type="application/rss+xml" href="<?php bloginfo( 'comments_rss2_url' ); ?>" title="<?php echo wp_specialchars( get_bloginfo( 'name' ), 1 ); ?> <?php $this->e_103(); ?>" />
			<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
			
			<?php $this->doHeadEarly(); ?>
			<?php $this->doWpHead(); ?>
			<?php $this->doHeadLate(); ?>
			
		</head>
		
		<body class="<?php echo $this->applyBodyClass( '' ); ?>">
			
			<table id="outer"><tr><td class="top">

				<div id="wrapper">
					
					<?php $this->doBodyHeader(); ?>
					
					<?php if ( $this->isTemplateList( 'custom' ) ): ?>
						
						<!-- custom page layout -->
						<?php $this->doContent(); ?>
						
					<?php elseif ( $this->isTemplateList( 'no_sidebar' ) ): ?>
						
						<!-- no sidebar page layout -->
						<div id="container">
							<?php $this->doContent(); ?>
							<div class="clear"></div>
						</div>
	
					<?php else: ?>
						
						<!-- default page layout -->
						<div id="container">
							<div id="leftnavbox"><?php $this->doSidebar(); $this->doGetSidebar(); ?></div>
							<div id="content">
								
								<div class="breadcrumbs"><?php $this->doNavBreadcrumb( 'main' ); ?></div>
								
								<?php $this->doContent(); ?>
								
								<div class="breadcrumbs"><?php $this->doNavBreadcrumb( 'main' ); ?></div>
							
							</div>
							<div class="clear"></div>
						</div>
						
					<?php endif; ?>
										
				</div>
			
				<?php $this->doBodyFooter(); ?>
				
			</td></tr></table>
					
			<?php $this->doWpFooter(); ?>
			
		</body>
		
		</html><?php
		
		$this->doGetFooter();
		$this->doFooterLate();
		
	}
	
	//
	public function echoBodyHeader() {
		?><div id="topnav"><?php $this->doNavMenu( 'top', array( 'renderDepth' => 0 ) ); ?></div>
		<div id="header">
			<a href="<?php Geko_Wp::echoUrl(); ?>"><img src="<?php bloginfo( 'template_directory' ); ?>/images/toplogo.gif" alt="<?php bloginfo('name') ?>" width="221" height="69" border="0" /></a>
			<div id="search">
				<form id="searchform" method="get" action="<?php Geko_Wp::echoUrl(); ?>">
					<input id="s" name="s" type="text" value="<?php $this->doSearchTerm(); ?>" size="20" tabindex="1" />
					<input id="searchsubmit" name="searchsubmit" type="submit" value="<?php $this->e_104(); ?>" tabindex="2" />
					<?php $this->doHiddenSearchFields(); ?>
				</form>
			</div>
		</div>
		<div id="mainnav"><?php $this->doNavMenu( 'main', array( 'renderDepth' => 0 ) ); ?></div><?php	
	}
	
	//
	public function echoSidebar() {
		?><div class="sidenav">
			<?php $this->doNavMenu( 'main', array( 'renderDepth' => 1 ) ); ?>
		</div><?php
	}
	
	//
	public function echoBodyFooter() {
		?><div id="footer">
			<div id="bottomnav"><?php $this->doNavMenu( 'bottom', array( 'renderDepth' => 0 ) ); ?></div>
			<div class="bottomcopy">
				<div class="copyright"><?php $this->pw( $this->l_101(), '2010' ); ?></div>
				<div class="blogo"><a href="<?php Geko_Wp::echoUrl(); ?>"><img src="<?php bloginfo( 'template_directory' ); ?>/images/bottomlogo.gif" alt="Bottom Logo" border="0" /></a></div>
			</div><div class="clear"></div>
		</div><?php	
	}
	
}