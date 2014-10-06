<?php
/*
Template Name: Login - Update Profile
*/

//
class Gloc_Layout_PageLoginUpdateProfile extends Gloc_Layout
{
	
	protected $_aLabels = array(
		102 => 'First Name',
		103 => 'Last Name:',
		104 => 'Email Address:',
		105 => 'Password:',
		106 => 'New Password:',
		107 => 'Confirm New Password:',
		108 => 'Update Profile',
		109 => 'Please enter your first name',
		110 => 'Please enter your last name',
		111 => 'Please enter your email address',
		112 => 'Please enter a valid email address',
		113 => 'You must enter your current password if changing your email or setting a new password',
		114 => 'New password must be at least 6 characters long',
		115 => 'Passwords must match',
		116 => 'Your settings have been saved successfully!',
		117 => 'Your email was changed and you must re-activate your account.',
		118 => 'Your password was changed.',
		119 => 'You will be logged-out in a few seconds and will have to re-login.',
		120 => 'Your password is incorrect. Please try again.',
		121 => 'Your new email already exists. Please use a different email address.',
		122 => "The activation email could not be sent. Please contact the site's administrator.",
		123 => 'Your settings could not be changed. Please try again.'
	);
	
	
	
	//
	public function echoHeadLate() {
		
		$oService = Gloc_Service_Profile::getInstance();
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => $oService->getStatusValues(),
			'labels' => $this->_getLabels()
		);
		
		?>
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				var labels = oParams.labels;
				
				var profileForm = $( '#profileform' );
				
				var curEmail = profileForm.find( '#email' ).val();
				
				profileForm.gekoAjaxForm( {
					status: oParams.status,
					process_script: oParams.script.process,
					action: '&action=Gloc_Service_Profile&subaction=update_profile',
					validate: function( form, errors ) {
						
						var firstName = form.getTrimVal( '#first_name' );
						var lastName = form.getTrimVal( '#last_name' );
						var email = form.getTrimVal( '#email' );
						
						var password = form.getTrimVal( '#password' );
						var newPass = form.getTrimVal( '#new_pass' );
						var confirmNewPass = form.getTrimVal( '#confirm_new_pass' );
						
						if ( !firstName ) {
							errors.push( labels[ 109 ] );
							form.errorField( '#first_name' );
						}
						
						if ( !lastName ) {
							errors.push( labels[ 110 ] );
							form.errorField( '#last_name' );
						}
						
						if ( !email ) {
							errors.push( labels[ 111 ] );
							form.errorField( '#email' );
						} else {
							if ( !form.isEmail( email ) ) {
								errors.push( labels[ 112 ] );
								form.errorField( '#email' );
							}
						}
						
						if ( ( ( curEmail != email ) || newPass ) && !password ) {
							errors.push( labels[ 113 ] );
							form.errorField( '#password' );
						}
						
						if ( newPass ) {
							if ( newPass.length < 6 ) {
								errors.push( labels[ 114 ] );
								form.errorField( '#new_pass' );
							} else {
								if ( newPass != confirmNewPass ) {
									errors.push( labels[ 115 ] );
									form.errorField( '#confirm_new_pass' );							
								}
							}
						}
						
						return errors;
						
					},
					process: function( form, res, status ) {
						if ( status.update_profile == parseInt( res.status ) ) {
							form.success( labels[ 116 ] );
						} else if ( 'array' == $.type( res.status ) ) {
							
							var msg = [];
							
							$.each( res.status, function( i, v ) {
								if ( v == status.change_email ) {
									msg.push( labels[ 117 ] );
								} else if ( v == status.change_password ) {
									msg.push( labels[ 118 ] );
								}
							} );
							
							msg.push( '' );
							msg.push( labels[ 119 ] );
							
							form.successLoading( msg );
							
							setTimeout( function() {
								window.location = oParams.script.curpage;
							}, 5000 );
							
						} else if ( status.bad_password == parseInt( res.status ) ) {
							form.error( labels[ 120 ] );
						} else if ( status.email_exists == parseInt( res.status ) ) {
							form.error( labels[ 121 ] );
						} else if ( status.send_notification_failed == parseInt( res.status ) ) {
							form.error( labels[ 122 ] );
						} else {
							form.error( labels[ 123 ] );
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
		
		$oMainLayout = Gloc_Layout_Main::getInstance();

		$oUser = $oMainLayout->getUser();
		
		$aValues = array(
			'first_name' => $oUser->getFirstName(),
			'last_name' => $oUser->getLastName(),
			'email' => $oUser->getEmail()
		);
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<h1><?php $oPage->echoTitle(); ?></h1>
			<div class="entry-content">
				
				<?php $oPage->echoTheContent(); ?>
				
				<p>&nbsp;</p>
				
				<?php $oPage->echoTheContent(); ?>
				
				<?php echo $this->pf( $this->getSettingsForm(), $aValues ); ?>
								
				<?php $this->doLinkPages(); ?>
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
				
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
	
	//
	public function echoSettingsForm() {
		
		?>
		<form id="profileform" class="loginform">
			
			<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
			<div class="error"></div>
			<div class="success"></div>
			
			<table>
				<tr>
					<th><?php $this->e_102(); ?></th>
					<td><input type="text" id="first_name" name="first_name" value="" /></td>
				</tr>
				<tr>
					<th><?php $this->e_103(); ?></th>
					<td><input type="text" id="last_name" name="last_name" value="" /></td>
				</tr>
				<tr>
					<th><?php $this->e_104(); ?></th>
					<td><input type="text" id="email" name="email" value="" /></td>
				</tr>
				<tr>
					<th><?php $this->e_105(); ?></th>
					<td><input type="password" id="password" name="password" value="" /></td>
				</tr>
				<tr>
					<th><?php $this->e_106(); ?></th>
					<td><input type="password" id="new_pass" name="new_pass" value="" /></td>
				</tr>
				<tr>
					<th><?php $this->e_107(); ?></th>
					<td><input type="password" id="confirm_new_pass" name="confirm_new_pass" value="" /></td>
				</tr>
				<tr>
					<td colspan="2" class="center"><input type="submit" value="<?php $this->e_108(); ?>" /></td>
				</tr>
			</table>
			
		</form>
		<?php
		
	}
	
}



