<?php

//
class Geko_Wp_Post_Location_Manage extends Geko_Wp_Location_Manage
{
	
	protected $_bHasDisplayMode = TRUE;
	
	protected $_sObjectType = 'post';
	protected $_sPostType = 'post';
	protected $_sSubAction = 'post';
	protected $_sSectionLabel = 'Address';
	
	protected $_oCurPost;
	
	
	
	// return a prefix
	public function getPrefix() {
		return $this->_sPostType;
	}
	
	
	//
	public function add() {
		
		parent::add();
		
		$aPrefixes = array( 'Gloc_', 'Geko_Wp_' );
		
		$sPostQueryClass = Geko_Class::getBestMatch( $aPrefixes, array( 'Post_Query' ) );		
		add_action( sprintf( '%s::init', $sPostQueryClass ), array( $this, 'initQuery' ) );
		
		return $this;
	}
	
	//
	public function initQuery( $oQuery ) {
		$oQuery->addPlugin( 'Geko_Wp_Post_Location_QueryPlugin' );
	}

	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		$this->_sCurrentDisplayMode = Geko_Wp_Admin_Hooks::getDisplayMode();
		
		add_action( 'save_post', array( $this, 'savePostdata' ) );	
		add_action( 'delete_post', array( $this, 'deletePostdata' ) );
		
		return $this;
	}
	
	//
	public function isCurrentPage() {
		return TRUE;
	}
	
	//
	public function initEntities( $oMainEnt = NULL, $aParams = array() ) {
		// HACKISH!!!
		$this->_iObjectId = intval( Geko_String::coalesce( $_REQUEST[ 'post' ], $_REQUEST[ 'post_ID' ] ) );
		return parent::initEntities( $oMainEnt, $aParams );
	}
	
	
	//// accessors
	
	//
	public function getCurPost() {
		if ( !$this->_oCurPost ) {
			$this->_oCurPost = $this->newPost( Geko_String::coalesce( $_REQUEST[ 'post' ], $_REQUEST[ 'post_ID' ] ) );
		}
		return $this->_oCurPost;
	}
	
	
	
	// Adds a custom section to the "advanced" Post and Page edit screens
	public function attachPage() {
		$this->initEntities();
		if ( function_exists( 'add_meta_box' ) ) {			
			$this->addMetaBox( 'post', 'advanced' );
		} else {
			add_action( 'dbx_post_advanced', array( $this, 'oldCustomBox' ) );
		}
	}
	
	//
	protected function addMetaBox( $sPage = 'post', $sContext = 'advanced' ) {
		add_meta_box(
			'geko-post-location',
			__( $this->_sSectionLabel, 'geko-post-location_textdomain' ),
			array( $this, 'outputForm' ),
			$sPage,
			$sContext
		);		
	}

	//// front-end display methods
	
	// Prints the edit form for pre-WordPress 2.5 post/page
	public function oldCustomBox() {
		?>
		<div class="dbx-b-ox-wrapper">
			<fieldset id="geko-post-location_fieldsetid" class="dbx-box">
				<div class="dbx-h-andle-wrapper">
					<h3 class="dbx-handle"><?php echo __( $this->_sSectionLabel, 'geko-post-location_textdomain' ); ?></h3>
				</div>
				<div class="dbx-c-ontent-wrapper"><div class="dbx-content">
					<?php $this->outputForm(); ?>
				</div></div>
			</fieldset>
		</div>
		<?php
	}
	
	
	//
	public function outputForm() {
		?>
		<input type="hidden" name="geko-post-location_noncename" id="geko-post-location_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		<?php $this->preFormFields( $oPlugin ); ?>
		<table class="form-table">
			<?php echo $this->setupFields( $oPlugin ); ?>
		</table>
		<?php
	}
	
	
	
	//// sub crud methods
	
	// When the post is saved, saves our custom data
	public function savePostdata( $iPostId ) {
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		
		if (
			( FALSE == wp_verify_nonce( $_POST[ 'geko-post-location_noncename' ], plugin_basename( __FILE__ ) ) ) || 
			( FALSE == current_user_can( 'edit_post', $iPostId ) )
		) {
			return $iPostId;
		}
		
		//// DB stuff
		$this->_iObjectId = $iPostId;
		$this->save( array(), 'update' );
		
		return TRUE;	// ???
	}
	
	// When post is deleted
	public function deletePostdata( $iPostId ) {
		
		$this->_iObjectId = $iPostId;
		$this->delete();
		
		return TRUE;	// ???		
	}
	
	
}




