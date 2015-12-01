<?php
/*
Template Name: Login - Set Password
*/

//
class Gloc_Layout_PageLoginSetPassword extends Gloc_Layout
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
		110 => 'Your password cannot be reset. Please try again.',
		111 => 'There was a problem with your request!'
	);
	
	protected $_mScripts = 'gloc_set_password';
	
	
	
	//
	public function start() {
		
		parent::start();
		
		global $user_ID;
		if ( $user_ID ) {
			header( sprintf( 'Location: %s', Geko_Wp::getUrl() ) );
			die();
		}
		
		if ( $this->sPasswordResetKey = $_GET[ 'key' ] ) {
			$oUser = $this->newUser_Query( array( 'geko_password_reset_key' => $this->sPasswordResetKey ) )->getOne();
			$this->oUser = ( $oUser->isValid() && $oUser->getPasswordResetKey() ) ? $oUser : NULL ;
			if ( $oUser->isValid() && !$oUser->getPasswordResetKey() ) {
				header( sprintf( 'Location: %s', Geko_Wp::getUrl() ) );
				die();
			}
		}
		
	}
	
	//
	public function echoHeadLate() {
		
		// don't bother showing any javascript code if not a valid user
		if ( !$this->oUser ) return;
		
		$oService = Gloc_Service_Profile::getInstance();
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => $oService->getStatusValues(),
			'labels' => $this->_getLabels(),
			'form_sel' => '#setpasswordform',
			'success_div_sel' => '#successdiv'
		);
		
		?>
				
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				///// form
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				
				Gloc.SetPassword.run( oParams );
				
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



