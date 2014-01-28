<?php
/*
Template Name: Login - Register
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Email Address:',
		102 => 'First Name:',
		103 => 'Last Name:',
		104 => 'Password:',
		105 => 'Confirm Password:',
		106 => 'Register',
		107 => 'Thank you for registering!',
		108 => 'A confirmation email was sent to you.',
		109 => 'Please follow the link to activate your account.',
		110 => 'Please enter your email address',
		111 => 'Please enter a valid email address',
		112 => 'Please enter your first name',
		113 => 'Please enter your last name',
		114 => 'Please enter a password',
		115 => 'Password must be at least 6 characters long',
		116 => 'Passwords must match',
		117 => 'That email address is already registered. Please try again.',
		118 => "The activation email could not be sent. Please contact the site's administrator.",
		119 => 'Your registration could not be completed. Please try again.'
	);
	
	
	
	//
	public function echoHeadLate() {
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => array(
				'register' => Gloc_Service_Profile::STAT_REGISTER,
				'email_exists' => Gloc_Service_Profile::STAT_EMAIL_EXISTS,
				'send_notification_failed' => Gloc_Service_Profile::STAT_SEND_NOTIFICATION_FAILED
			),
			'labels' => $this->_getLabels()
		);
		
		?>
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {

				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				var labels = oParams.labels;
				
				var regForm = $( '#regform' );
				var successDiv = $( '#successdiv' );
				
				regForm.gekoAjaxForm( {
					status: oParams.status,
					process_script: oParams.script.process,
					action: '&action=Gloc_Service_Profile&subaction=register',
					validate: function( form, errors ) {
						
						var email = form.getTrimVal( '#email' );
							
						var firstName = form.getTrimVal( '#first_name' );
						var lastName = form.getTrimVal( '#last_name' );
							
						var password = form.getTrimVal( '#password' );
						var confirmPass = form.getTrimVal( '#confirm_pass' );
	
						if ( !email ) {
							errors.push( labels[ 110 ] );
							form.errorField( '#email' );
						} else {
							if ( !form.isEmail( email ) ) {
								errors.push( labels[ 111 ] );
								form.errorField( '#email' );
							}
						}
						
						if ( !firstName ) {
							errors.push( labels[ 112 ] );
							form.errorField( '#first_name' );
						}
						
						if ( !lastName ) {
							errors.push( labels[ 113 ] );
							form.errorField( '#last_name' );
						}
						
						if ( !password ) {
							errors.push( labels[ 114 ] );
							form.errorField( '#password' );
						} else {
							if ( password.length < 6 ) {
								errors.push( labels[ 115 ] );
								form.errorField( '#password' );
							} else {
								if ( password != confirmPass ) {
									errors.push( labels[ 116 ] );
									form.errorField( '#confirm_pass' );							
								}
							}
						}
						
						return errors;
						
					},
					process: function( form, res, status ) {
						if ( status.register == parseInt( res.status ) ) {
							form.hide();
							successDiv.show();
						} else if ( status.email_exists == parseInt( res.status ) ) {
							form.error( labels[ 117 ] );
						} else if ( status.send_notification_failed == parseInt( res.status ) ) {
							form.error( labels[ 118 ] );
						} else {
							form.error( labels[ 119 ] );				
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
				
				<form id="regform" class="loginform">
					
					<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
					<div class="error"></div>
					<div class="success"></div>
					
					<table cellspacing="0" cellpadding="0" >
						<tr>
							<th><?php $this->e_101(); ?></th>
							<td><input type="text" id="email" name="email" /></td>
						</tr>
						<tr>
							<th><?php $this->e_102(); ?></th>
							<td><input type="text" id="first_name" name="first_name" /></td>
						</tr>
						<tr>
							<th><?php $this->e_103(); ?></th>
							<td><input type="text" id="last_name" name="last_name" /></td>
						</tr>
						
						<tr>
							<th><?php $this->e_104(); ?></th>
							<td><input type="password" id="password" name="password" /></td>
						</tr>
						<tr>
							<th><?php $this->e_105(); ?></th>
							<td><input type="password" id="confirm_pass" name="confirm_pass" /></td>
						</tr>
						<tr>
							<td colspan="2" class="center"><input type="submit" value="<?php $this->e_106(); ?>" /></td>
						</tr>
					</table>
					
				</form>
				
				<div id="successdiv">
					<?php $this->e_107(); ?><br />
					<?php $this->e_108(); ?><br />
					<?php $this->e_109(); ?>
				</div>
				
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

