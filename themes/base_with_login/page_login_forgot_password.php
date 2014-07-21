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
		107 => 'Invalid email specified. Please try again.'
	);
	
	
	
	//
	public function echoHeadLate() {
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => array(
				'forgot_password' => Gloc_Service_Profile::STAT_FORGOT_PASSWORD,
				'send_notification_failed' => Gloc_Service_Profile::STAT_SEND_NOTIFICATION_FAILED
			),
			'labels' => $this->_getLabels()
		);
		
		?>
				
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				///// form
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				var labels = oParams.labels;
				
				var forgotPassForm = $( '#forgotpassform' );
				var successDiv = $( '#successdiv' );
				
				forgotPassForm.gekoAjaxForm( {
					status: oParams.status,
					process_script: oParams.script.process,
					action: '&action=Gloc_Service_Profile&subaction=forgot_password',
					validate: function( form, errors ) {
						
						var email = form.getTrimVal( '#email' );
						
						if ( !email ) {
							errors.push( labels[ 104 ] );
							form.errorField( '#email' );
						} else {
							if ( !form.isEmail( email ) ) {
								errors.push( labels[ 105 ] );
								form.errorField( '#email' );
							}
						}
						
						return errors;
						
					},
					process: function( form, res, status ) {
						if ( status.forgot_password == parseInt( res.status ) ) {
							form.hide();
							successDiv.show();
						} else if ( status.send_notification_failed == parseInt( res.status ) ) {
							form.error( labels[ 106 ] );
						} else {
							form.error( labels[ 107 ] );
						}
					}
					
				} );
				
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

geko_render_template();

