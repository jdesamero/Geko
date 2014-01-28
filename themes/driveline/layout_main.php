<?php

//
class Gloc_Layout_Main extends Gloc_Layout
{
	
	private $oUser = NULL;
	private $bIsLoggedIn = FALSE;
	
	protected $_aLabels = array(
		101 => 'Copyright %s',
		102 => 'Posts RSS feed',
		103 => 'Comments RSS feed',
		104 => 'Search',
		105 => 'Email:',
		106 => 'Password:',
		107 => 'Login',
		108 => 'Register',
		109 => 'Forgot Password?',
		110 => 'Logged in as:',
		111 => 'Update Profile',
		112 => 'Log-out',
		113 => 'Please enter your email address',
		114 => 'Please enter a valid email address',
		115 => 'Please enter a password',
		116 => 'Password must be at least 6 characters long',
		117 => 'Please activate your account first.',
		118 => 'Login failed. Please try again.',
		119 => 'Error',
		120 => 'You must be logged in to access this page.',
		121 => 'This page cannot be accessed while you are logged-in.'
	);
	
	
	
	//
	public function start() {
		
		parent::start();
		
		global $user_ID;
		
		if ( $user_ID ) {
			
			$oUser = $this->newUser( $user_ID );
			$this->bIsLoggedIn = ( $oUser->getIsActivated() ) ? TRUE : FALSE;
			
			if ( $this->bIsLoggedIn ) $this->oUser = $oUser;
			
		}
		
	}
	
	
	//
	public function echoEnqueue() {
		$this->enqueueScript(
			'geko-jquery-geko_ajax_form',
			'geko-jquery-simplemodal'
		);
	}
	
	//
	public function echoMain() {
		
		// register templates
		$this
			->addTemplate( 'page_template:homepage.php', 'custom', 'public' )
			->addTemplate( 'page_template:nosidebar.php', 'no_sidebar', 'public' )
			
			->addTemplate( 'page_template:page_locations.php', 'no_sidebar', 'public' )
			
			->addTemplate( 'page_template:page_login_update_profile.php', 'no_sidebar', 'protected' )
			->addTemplate( 'page_template:page_login.php', 'no_sidebar', 'unprotected' )
			->addTemplate( 'page_template:page_login_register.php', 'no_sidebar', 'unprotected' )
			
			// below uses the default template with sidebar and breadcrumbs
			// ->addTemplate( 'page_template:some_template.php', 'default', ... )
		;
		
		$this->doEnqueue();
		$this->doGetHeader();
		
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		
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
			
			<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory' ); ?>/styles/superfish.css" media="screen">
			<script src="<?php bloginfo( 'stylesheet_directory' ); ?>/js/hoverIntent.js"></script>
			<script src="<?php bloginfo( 'stylesheet_directory' ); ?>/js/superfish.js"></script>
			
			<?php
			
			$this->doHeadEarly();
			$this->doWpHead();
			
			if ( $this->isTemplateList( 'protected' ) ):
				if ( $this->isLoggedIn() ):
					$this->doHeadLate();
				endif;
			else:
				$this->doHeadLate();
			endif;
			
			?>
			
			<script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					// Modal
					$( '#topnav .modal-content' ).hide();
			
					$( '#topnav .modal-link' ).click( function() {
						$( '.modal-content' ).modal( {
							overlayId: 'modal-overlay',
							zIndex: 9001
						} );
						return false;
					} );
					
					$( '.modal-content .close' ).click( function() {
						$.modal.close();
						return false;
					} );
					
					// Dropdowns
					$( '#mainnav ul.navigation' ).superfish();
					
				} );
				
			</script>
							
		</head>
		
		<body class="<?php echo $this->applyBodyClass( '' ); ?>">
			
			<table id="outer"><tr><td class="top">

				<div id="wrapper">
					
					<?php $this->doBodyHeader(); ?>
					
					<?php if ( $this->isTemplateList( 'custom' ) ): ?>
						
						<!-- custom page layout -->
						<?php $this->_echoContent( 'custom' ); ?>
						
					<?php elseif ( $this->isTemplateList( 'no_sidebar' ) ): ?>
						
						<!-- no sidebar page layout -->
						<div id="full-container">
							<?php $this->_echoContent( 'no_sidebar' ); ?>
							<div class="clear"></div>
						</div>
	
					<?php else: ?>
						
						<!-- default page layout -->
						<div id="container">
							<div id="leftnavbox"><?php $this->doSidebar(); $this->doGetSidebar(); ?></div>
							<div id="content">
								<?php $this->_echoContent( 'default' ); ?>
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
	public function _echoContent( $sPageLayout ) {
		// route content display
		if ( $this->isTemplateList( $sPageLayout, 'protected' ) ):
			if ( $this->isLoggedIn() ):
				// show protected page, since user is logged-in
				$this->doContent();
			else:
				// do not show protected page, user is NOT logged-in
				?>
				<div>
					<h1><?php $this->e_119(); ?></h1>
					<div class="entry-content">
						<p><?php $this->e_120(); ?></p>
					</div>
				</div>
				<?php
			endif;
		elseif ( $this->isTemplateList( $sPageLayout, 'unprotected' ) ):
			if ( $this->isLoggedIn() ):
				// do not show unprotected page, user is ALREADY logged-in
				?>
				<div>
					<h1><?php $this->e_119(); ?></h1>
					<div class="entry-content">
						<p><?php $this->e_121(); ?></p>
					</div>
				</div>
				<?php
			else:
				// show unprotected page, since user is NOT logged-in
				$this->doContent();
			endif;
		else:
			// page is public, so show
			$this->doContent();
		endif;
	}
	
	//
	public function echoHeadLate() {
				
		// don't bother showing login javascript if already logged-in
		if ( $this->bIsLoggedIn ) return;
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => array(
				'login' => Gloc_Service_Profile::STAT_LOGIN,
				'not_activated' => Gloc_Service_Profile::STAT_NOT_ACTIVATED
			),
			'labels' => $this->_getLabels()
		);
		
		?>
		<script type="text/javascript">
					
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				var labels = oParams.labels;
				
				var loginForm = $( '#loginform' );
				
				loginForm.gekoAjaxForm( {
					status: oParams.status,
					process_script: oParams.script.process,
					action: '&action=Gloc_Service_Profile&subaction=login',
					validate: function( form, errors ) {
						
						var email = form.getTrimVal( '#email' );
						var password = form.getTrimVal( '#password' );
						
						if ( !email ) {
							errors.push( labels[ 113 ] );
							form.errorField( '#email' );
						} else {
							if ( !form.isEmail( email ) ) {
								errors.push( labels[ 114 ] );
								form.errorField( '#email' );
							}
						}
						
						if ( !password ) {
							errors.push( labels[ 115 ] );
							form.errorField( '#password' );
						} else {
							if ( password.length < 6 ) {
								errors.push( labels[ 116 ] );
								form.errorField( '#password' );
							}
						}
						
						return errors;
						
					},
					process: function( form, res, status ) {
						if ( status.login == parseInt( res.status ) ) {
							// reload page
							window.location = oParams.script.curpage;
						} else if ( status.not_activated == parseInt( res.status ) ) {
							form.error( labels[ 117 ] );
						} else {
							form.error( labels[ 118 ] );
						}
					}
				} );
				
			} );
			
		</script>
		<?php
		
	}
	
	//
	public function echoBodyHeader() {
		
		$oUser = $this->oUser;
		
		?><div id="header">
			<div id="topnav">
				<a class="modal-link contact-link" href="#">Contact</a> 
				<a class="email-link" href="mailto:info@drivelinecentre.com">info@drivelinecentre.com</a> 
				<a class="tel-link" href="tel:">1-800-555-9999</a>
				<?php $this->doQuickContact(); ?>
			</div>
			<div id="search">
				<form id="searchform" method="get" action="<?php Geko_Wp::echoUrl(); ?>">
					<input id="s" name="s" type="text" value="Search" size="20" tabindex="1" />
					<input id="searchsubmit" name="searchsubmit" type="submit" value="" tabindex="2" />
					<?php $this->doHiddenSearchFields(); ?>
				</form>
			</div>
			<div class="clear"></div>
			<a href="<?php Geko_Wp::echoUrl(); ?>" class="logo"><img src="<?php bloginfo( 'template_directory' ); ?>/images/main-logo.png" alt="<?php bloginfo( 'name' ); ?>" width="169" height="82" border="0" /></a>
			<div id="mainnav"><?php $this->doNavMenu( 'main' ); ?></div>
			<div class="fix"></div>
			
		</div>
		<?php	
	}
	
	//
	public function echoQuickContact() {
		
		$oQuickPage = $this->newPage( 'quick-contact' );
		
		?><div class="modal-content">
			<a class="close"></a>
			<h1 class="page-title"><?php $oQuickPage->echoTitle(); ?></h1>
			<?php $oQuickPage->echoTheContent(); ?>
		</div><?php
	}
	
	//
	public function echoSidebar() {
		?><div class="sidenav">
			<?php $this->doNavMenu( 'main', array( 'renderDepth' => 0, 'maxDepth' => 2, 'renderDescendants' => TRUE ) ); ?>
		</div><?php
	}
	
	//
	public function echoBodyFooter() {
		?><div id="footer">
			<div class="bottomcopy">
				<div class="copyright">&copy; <span>Performance Driveline Centre, Inc.</span> All Rights Reserved |</div>
				<div id="bottomnav"><?php $this->doNavMenu( 'bottom', array( 'renderDepth' => 0 ) ); ?></div>
			</div><div class="clear"></div>
		</div><?php	
	}
	
	
	
	//// public accessors!!!
	
	//
	public function getUser() {
		return $this->oUser;
	}
	
	//
	public function isLoggedIn() {
		return $this->bIsLoggedIn;
	}
		
	
	
}


