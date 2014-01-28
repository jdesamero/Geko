<?php
/*
Category Post Template: Locations
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Filed Under:'
	);
	
	
	
	//
	public function echoContent() {
		
		$oPost = $this->newPost();
		
		$aLocation = $this->newPost_Location_Query( array(
			'object_id' => $oPost->getId(),
			'object_type' => 'post'
		), FALSE );
		
		if ( $aLocation->count() == 1 ) {
			
			$oLocation = $aLocation->getOne();
			
			$sAddress = $oLocation->getAddressLine1();
			$sCity = $oLocation->getCity();
			$sProvince = $oLocation->getProvinceName();
			$sPostalCode = $oLocation->getPostalCode();
			
		}
		
		$sEmail = $oPost->getMeta( 'email' );
		$sWebsite = $oPost->getMeta( 'website' );
		
		$aHours = $oPost->getValueAsArray( 'hours' );
		
		?>
        <div id="post-<?php $oPost->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<h1>Store Details</h1>
			<div class="entry-content">
				
				<h2><?php $oPost->echoTitle(); ?></h2>

				<p>
					<?php echo $sAddress; ?><br />
					<?php echo $sCity; ?>, <?php echo $sProvince; ?><br />
					<?php echo $sPostalCode; ?>
				</p>
				
				<p>
					<?php $this->pw( 'Email: <a href="mailto:%s">%s</a><br />', $sEmail, $sEmail ); ?>
					<?php $this->pw( 'Phone: %s<br />', $oPost->getMeta( 'phone' ) ); ?>
					<?php $this->pw( 'Fax: %s<br />', $oPost->getMeta( 'fax' ) ); ?>
					<?php $this->pw( 'Website: <a href="%s" target="_blank">%s</a><br />', $sWebsite, $sWebsite ); ?>
				</p>
				
				<?php if ( count( $aHours ) ): ?>
					<p>Hours:</p>
					<?php foreach ( $aHours as $sLine ): ?>
						<ul><?php echo $sLine; ?></ul>
					<?php endforeach; ?>
				<?php endif; ?>
				
				
				<?php $oPost->echoTheContent(); ?>
				
				
				<?php $this->doLinkPages(); ?>
				
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPost->getTheEditLink() ); ?><br /><br />
			</div>
		</div>
        <?php
        
        // Add a key+value of "comments" to enable comments on this page
		if ( $oPost->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}

geko_render_template();

