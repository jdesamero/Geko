<?php
/*
Template Name: Notification
*/

//
class Gloc_Layout_Notification extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Unknown Error',
		102 => 'An unknown error has occured. Tough.'
	);
	
	
	
	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		
		// key | title | type[ notification | error ] | details
		// x   | 0     | 1                            | 2
		
		$aMessages = $oPage->getMetaMulti( 'notification_msg' );
		$aMsgFmt = array();
		
		foreach ( $aMessages as $sMessage ) {
			$aMsg = explode( '|', $sMessage );
			$aMsg = array_map( 'trim', $aMsg );
			
			$sKey = array_shift( $aMsg );
			
			$aMsgFmt[ $sKey ] = $aMsg;
		}
		
		// check if current key is valid
		$sCurrentKey = $_GET[ 'msg' ];
		
		$sType = $aMsgFmt[ $sCurrentKey ][ 1 ];
		$sMainTitle = ucfirst( $sType );
		
		if ( array_key_exists( $sCurrentKey, $aMsgFmt ) ) {
			$sTitle = $aMsgFmt[ $sCurrentKey ][ 0 ];
			$sMsg = $aMsgFmt[ $sCurrentKey ][ 2 ];
		} else {
			$sTitle = $this->l_101();
			$sMsg = $this->l_102();
		}
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( $sType ); ?>">
			<div class="titlebg"><h1><span><?php echo $sMainTitle; ?></span></h1></div>
			
			<div class="entry-content">
				<h2><?php echo $sTitle; ?></h2>
				<?php echo $sMsg; ?>
				<?php $this->doLinkPages(); ?>
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
	
}



