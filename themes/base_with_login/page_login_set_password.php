<?php
/*
Template Name: Login - Set Password
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	private $oUser = NULL;
	private $sPasswordResetKey = NULL;
	
	protected $_aLabels = array(
		101 => 'Set password for:',
		102 => 'New Password:',
		103 => 'Confirm New Password:',
		104 => 'Set Password',
		105 => 'Your password has been reset.',
		106 => 'Invalid password reset key given.',
		107 => 'Please enter a password',
		108 => 'Password must be at least 6 characters long',
		109 => 'Passwords must match',
		110 => 'Your password cannot be reset. Please try again.'
	);
	
	
	
	//
	public function start() {
		
		global $user_ID;
		if ( $user_ID ) {
			header( 'Location: ' . Geko_Wp::getUrl() );
			die();
		}
		
		if ( $this->sPasswordResetKey = $_GET[ 'key' ] ) {
			$oUser = $this->newUser_Query( array( 'geko_password_reset_key' => $this->sPasswordResetKey ) )->getOne();
			$this->oUser = ( $oUser->isValid() && $oUser->getPasswordResetKey() ) ? $oUser : NULL ;
			if ( $oUser->isValid() && !$oUser->getPasswordResetKey() ) {
				header( 'Location: ' . Geko_Wp::getUrl() );
				die();
			}
		}
		
	}
	
	//
	public function echoHeadLate() {
		
		// don't bother showing any javascript code if not a valid user
		if ( !$this->oUser ) return;
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => array(
				'set_password' => Gloc_Service_Profile::STAT_SET_PASSWORD,
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
				
				var setPasswordForm = $( '#setpasswordform' );
				var successDiv = $( '#successdiv' );
				
				setPasswordForm.gekoAjaxForm( {
					status: oParams.status,
					process_script: oParams.script.process,
					action: '&action=Gloc_Service_Profile&subaction=set_password',
					validate: function( form, errors ) {
						
						var password = form.getTrimVal( '#password' );
						var confirmPass = form.getTrimVal( '#confirm_pass' );
						
						if ( !password ) {
							errors.push( labels[ 107 ] );
							form.errorField( '#password' );
						} else {
							if ( password.length < 6 ) {
								errors.push( labels[ 108 ] );
								form.errorField( '#password' );
							} else {
								if ( password != confirmPass ) {
									errors.push( labels[ 109 ] );
									form.errorField( '#confirm_pass' );							
								}
							}
						}
						
						return errors;
						
					},
					process: function( form, res, status ) {
						if (
							( status.set_password == parseInt( res.status ) ) || 
							( status.send_notification_failed == parseInt( res.status ) )
						) {
							form.hide();
							successDiv.show();
						} else {
							form.error( labels[ 110 ] );
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
				
				<?php if ( $oUser = $this->oUser ): ?>
					
					<form id="setpasswordform" class="loginform">
						
						<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
						<div class="error"></div>
						<div class="success"></div>
						
						<p><?php $this->e_101(); ?> <?php $oUser->echoTheTitle(); ?></p>
						
						<input type="hidden" name="key" value="<?php echo $this->sPasswordResetKey; ?>" />
						
						<table cellspacing="0" cellpadding="0" >
							<tr>
								<th><?php $this->e_102(); ?></th>
								<td><input id="password" name="password" type="password" /></td>
							</tr>
							<tr>
								<th><?php $this->e_103(); ?></th>
								<td><input id="confirm_pass" name="confirm_pass" type="password" /></td>
							</tr>
							<tr>
								<td colspan="2" class="center"><input type="submit" value="<?php $this->e_104(); ?>" /></td>
							</tr>
						</table>
						
					</form>
					
					<div id="successdiv"><?php $this->e_105(); ?></div>
				
				<?php else: ?>
					
					<p><?php $this->e_106(); ?></p>
					
				<?php endif; ?>
				
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

