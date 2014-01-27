<?php

//
class Geko_Wp_Ext_Cart66_View_ErrorMessages extends Geko_Wp_Ext_Cart66_View
{

	
	//
	public function render() {
		
		$this->_sThisFile = __FILE__;
		
		$data = $this->getParam( 'data' );
		$notices = $this->getParam( 'notices' );
		$minify = $this->getParam( 'minify' );
		
		
		?>
		<div class="alert-message alert-error Cart66AjaxMessage">
			<p><strong><?php echo $data[ 'errorMessage' ]; ?></strong></p>
			<?php 
				
				$mExeption = $data[ 'exception' ];
				
				if ( is_array( $mExeption ) ): ?>
					
					<ul><?php
						
						if ( isset( $mExeption[ 'error_code' ] ) ):
							
							?><li><?php echo $mExeption[ 'error_code' ]; ?><?php
							
							unset( $mExeption[ 'error_code' ] );
							
						endif;
						
						foreach( $mExeption as $exception ): ?>
							<li><?php echo $exception; ?></li>
						<?php endforeach; ?>
					</ul>
					<?php
					
				else: ?>
					
					<p><?php echo $mExeption; ?></p>
					
					<?php
				endif;
				
			?>
		</div>
		<?php
		
	}
	

}
