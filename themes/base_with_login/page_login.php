<?php
/*
Template Name: Login
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{

	protected $_aLabels = array(
		101 => 'Email:',
		102 => 'Password:',
		103 => 'Login',
		104 => 'Register',
		105 => 'Forgot Password?',
		106 => 'Please enter your email address',
		107 => 'Please enter a valid email address',
		108 => 'Please enter a password',
		109 => 'Password must be at least 6 characters long',
		110 => 'Please activate your account first.',
		111 => 'Login failed. Please try again.'
	);
	
	
	
	
	//
	public function echoHeadLate() {
		
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
				
				var loginForm = $( '#pageloginform' );
				
				loginForm.gekoAjaxForm( {
					status: oParams.status,
					process_script: oParams.script.process,
					action: '&action=Gloc_Service_Profile&subaction=login',
					validate: function( form, errors ) {
						
						var email = form.getTrimVal( '#email' );
						var password = form.getTrimVal( '#password' );
						
						if ( !email ) {
							errors.push( labels[ 106 ] );
							form.errorField( '#email' );
						} else {
							if ( !form.isEmail( email ) ) {
								errors.push( labels[ 107 ] );
								form.errorField( '#email' );
							}
						}
						
						if ( !password ) {
							errors.push( labels[ 108 ] );
							form.errorField( '#password' );
						} else {
							if ( password.length < 6 ) {
								errors.push( labels[ 109 ] );
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
							form.error( labels[ 110 ] );
						} else {
							form.error( labels[ 111 ] );
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

geko_render_template();

