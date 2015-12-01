<?php
/*
Template Name: Login
*/

//
class Gloc_Layout_PageLogin extends Gloc_Layout
{

	protected $_aLabels = array(
		
		101 => 'Email:',
		102 => 'Password:',
		103 => 'Login',
		104 => 'Register',
		105 => 'Forgot Password?',
		110 => 'Please activate your account first.',
		111 => 'Login failed. Please try again.',
		
		200 => 'Please enter your email address',
		201 => 'Please enter a valid email address',
		202 => 'Please enter a password',
		203 => 'Password must be at least 6 characters long',
		204 => 'Invalid credentials provided!',
		205 => "Login successful! Please wait while you're redirected..."
		
	);
	
	
	
	
	//
	public function echoHeadLate() {
		
		$oService = Gloc_Service_Profile::getInstance();
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => $oService->getStatusValues(),
			'labels' => $this->_getLabels(),
			'form_sel' => '#pageloginform'
		);
		
		?>
		<script type="text/javascript">
					
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				
				Gloc.Login.run( oParams );
				
			} );
			
		</script>
		<?php
		
	}
	
	
	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<h1><?php $oPage->echoTitle(); ?></h1>
			<div class="entry-content">
				
				<?php $oPage->echoTheContent(); ?>
				
				<form id="pageloginform" class="loginform">
					<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
					<div class="error"></div>
					<div class="success"></div>
					<table>
						<tr>
							<th><?php $this->e_101(); ?></th>
							<td colspan="2"><input type="text" name="email" id="email" value="" /></td>
						</tr>
						<tr>
							<th><?php $this->e_102(); ?></th>
							<td><input type="password" name="password" id="password" value="" /></td>
							<td><input type="submit" value="<?php $this->e_103(); ?>" /></td>
						</tr>
						<tr>
							<td colspan="2">
								<a href="<?php Geko_Wp::getUrl(); ?>/login/register/"><?php $this->e_104(); ?></a> | 
								<a href="<?php Geko_Wp::getUrl(); ?>/login/forgot-password/"><?php $this->e_105(); ?></a>
							</td>
						</tr>
					</table>
				</form>
				
				<?php $this->doLinkPages(); ?>
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}



