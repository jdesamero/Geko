<?php
/*
Template Name: Login - Activate Account
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	private $oUser = NULL;
	private $sActivationKey = NULL;
	
	protected $_aLabels = array(
		101 => 'Activate account for:',
		102 => 'Activate',
		103 => 'Your account has been activated.',
		104 => 'You can now log-in to your account.',
		105 => 'Invalid activation key given.',
		106 => 'Your account could not be activated. Please try again.'
	);
	
	
	
	//
	public function init( $bUnshift = FALSE ) {
		
		global $user_ID;
		if ( $user_ID ) {
			header( 'Location: ' . Geko_Wp::getUrl() );
			die();
		}
		
		parent::init( $bUnshift );
		
		if ( $this->sActivationKey = $_GET[ 'key' ] ) {
			$oUser = $this->newUser_Query( array( 'geko_activation_key' => $this->sActivationKey ) )->getOne();
			$this->oUser = ( $oUser->isValid() && $oUser->getActivationKey() ) ? $oUser : NULL ;
		}
		
		return $this;
	
	}
	
	//
	public function echoHeadLate() {
		
		// don't bother showing any javascript code if not a valid user
		if ( !$this->oUser ) return;
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => array(
				'activate_account' => Gloc_Service_Profile::STAT_ACTIVATE_ACCOUNT,
				'send_notification_failed' => Gloc_Service_Profile::STAT_SEND_NOTIFICATION_FAILED
			),
			'labels' => $this->_getLabels()
		);
		
		?>
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {

				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				var labels = oParams.labels;
				
				var activateForm = $( '#activateform' );
				var successDiv = $( '#successdiv' );
				
				activateForm.gekoAjaxForm( {
					status: oParams.status,
					process_script: oParams.script.process,
					action: '&action=Gloc_Service_Profile&subaction=activate_account',
					process: function( form, res, status ) {
						if (
							( status.activate_account == parseInt( res.status ) ) || 
							( status.send_notification_failed == parseInt( res.status ) )
						) {
							form.hide();
							successDiv.show();
						} else {
							form.error( labels[ 106 ] );
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
		
		$oUser = $this->oUser;
		
		?>
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<h1><?php $oPage->echoTitle(); ?></h1>
			<div class="entry-content">
					
				<?php $oPage->echoTheContent(); ?>
				
				<p>&nbsp;</p>
				
				<?php if ( $oUser ): ?>
					
					<form id="activateform">
						
						<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
						<div class="error"></div>
						<div class="success"></div>
						
						<p><?php $this->e_101(); ?> <?php $oUser->echoTheTitle(); ?></p>
						
						<input type="hidden" id="key" name="key" value="<?php echo $this->sActivationKey; ?>" />
						<input type="submit" value="<?php $this->e_102(); ?>" />
						
					</form>
					
					<div id="successdiv">
						<?php $this->e_103(); ?><br />
						<?php $this->e_104(); ?>
					</div>
				
				<?php else: ?>
					
					<p><?php $this->e_105(); ?></p>
					
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

