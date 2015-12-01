<?php
/*
Template Name: Login - Forgot Password
*/

//
class Gloc_Layout_PageLoginForgotPassword extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Email:',
		102 => 'Get &quot;Set New Password&quot; Link',
		103 => 'A link to reset your password has been emailed to you.',
		104 => 'Please enter your email address',
		105 => 'Please enter a valid email address',
		106 => "The password reset email could not be sent. Please contact the site's administrator.",
		107 => 'Invalid email specified. Please try again.',
		108 => 'There was a problem with your request!'
	);
	
	protected $_mScripts = 'gloc_forgot_password';
	
	
	
	//
	public function echoHeadLate() {
		
		$oService = Gloc_Service_Profile::getInstance();
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => $oService->getStatusValues(),
			'labels' => $this->_getLabels(),
			'form_sel' => '#forgotpassform',
			'success_div_sel' => '#successdiv'
		);
		
		?>
				
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				
				Gloc.ForgotPassword.run( oParams );
				
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
				
				<p>&nbsp;</p>
				
				<form id="forgotpassform" class="loginform">
					
					<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
					<div class="error"></div>
					<div class="success"></div>
					
					<table cellspacing="0" cellpadding="0" >
						<tr>
							<th><?php $this->e_101(); ?></th>
							<td><input id="email" name="email" type="text" /></td>
						</tr>
						<tr>
							<td colspan="2" class="center"><input type="submit" value="<?php $this->e_102(); ?>" /></td>
						</tr>
					</table>
					
				</form>
				
				<div id="successdiv"><?php $this->e_103(); ?></div>
				
				<?php $this->doLinkPages(); ?>
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
				
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}



