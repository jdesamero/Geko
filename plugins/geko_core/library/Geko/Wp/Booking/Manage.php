<?php

//
class Geko_Wp_Booking_Manage extends Geko_Wp_Options_Manage
{	
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing
	
	protected $_sEntityIdVarName = 'bkng_id';
	
	protected $_sSubject = 'Booking';
	protected $_sListingTitle = 'Subject';	
	protected $_sDescription = 'An API/UI for scheduling/booking of events.';
	protected $_sIconId = 'icon-users';
	protected $_sType = 'bkng';
	
	protected $_aSubOptions = array(
		// 'Geko_Wp_Booking_Item_Manage',
		'Geko_Wp_Booking_Transaction_Manage',
		'Geko_Wp_Booking_Request_Manage'
	);
	
	protected $_iEntitiesPerPage = 10;
	
	
	//// init
	
	
	//
	public function affix() {
		Geko_Wp_Db::addPrefix( 'geko_booking' );
		return $this;
	}
		
	
	
	// create table
	public function install() {
		
		$sSql = '
			CREATE TABLE %s
			(
				bkng_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				name LONGTEXT,
				slug VARCHAR(255),
				description LONGTEXT,
				date_created DATETIME,
				date_modified DATETIME,
				PRIMARY KEY(bkng_id)
			)
		';
		
		Geko_Wp_Db::createTable( 'geko_booking', $sSql );
				
		return $this;
	}
	
	
	
	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {
			// wp_enqueue_script( 'geko_wp_booking_manage' );
		}
		
		return $this;
	}
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {
			
			?><script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
				} );
				
			</script><?php
		}
		
		return $this;
	}
	
	
	
	
	
	//// accessors

	//
	public function getStoredOptions() {
		
		$aRet = array();
		
		if ( $this->_oCurrentEntity ) {
			
			$oEntity = $this->_oCurrentEntity;
			$aRet = array(
				'bkng_name' => $oEntity->getName(),
				'bkng_slug' => $oEntity->getSlug(),
				'bkng_description' => $oEntity->getDescription()
			);
			
			$aRet = apply_filters( 'admin_geko_bkngs_getstoredopts', $aRet, $oEntity );
			$aRet = apply_filters( 'admin_geko_bkngs_getstoredopts' . $oEntity->getSlug(), $aRet, $oEntity );
						
		}
		
		return $aRet;
	}
	
	
	
	
	//// error message handling
	
	//
	protected function getErrorMsgs() {
		return array_merge(
			parent::getErrorMsgs(),
			array()
		);
	}
	
	
	
	//// front-end display methods
	
	//
	public function listingPage() {
		
		$aParams = array(
			'paged' => $this->getPageNum(),
			'posts_per_page' => $this->_iEntitiesPerPage
		);
		
		$oUrl = new Geko_Uri();
		$oUrl
			->unsetVar( 'action' )
			->unsetVar( $this->_sEntityIdVarName )
		;
		
		$sThisUrl = strval( $oUrl );
		
		$sQueryClass = $this->_sQueryClass;
		$aEntities = new $sQueryClass( $aParams );
		
		$iTotalRows = $aEntities->getTotalRows();
		$sPaginateLinks = $this->getPaginationLinks( $iTotalRows );
		
		?>
		<div class="wrap">
			
			<?php $this->outputHeading(); ?>
			
			<form id="geko-bkng-filter" method="get" action="">
				
				<div class="tablenav">
					<?php echo Geko_String::sw( '<div class="tablenav-pages">%s</div>', $sPaginateLinks ); ?>
					<br class="clear"/>
				</div>
				
				<table class="widefat fixed" cellspacing="0">
				
					<thead>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-title"><?php echo $this->_sListingTitle; ?></th>
							<?php $this->columnTitle(); ?>
							<th scope="col" class="manage-column column-date">Date Created</th>
							<th scope="col" class="manage-column column-date">Date Modified</th>
						</tr>
					</thead>
					
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-title"><?php echo $this->_sListingTitle; ?></th>	
							<?php $this->columnTitle(); ?>
							<th scope="col" class="manage-column column-date">Date Created</th>
							<th scope="col" class="manage-column column-date">Date Modified</th>
						</tr>
					</tfoot>
					
					<?php
					
					foreach ( $aEntities as $oEntity ):

						$oUrl
							->setVar( $this->_sEntityIdVarName, $oEntity->getId() )
							->unsetVar( 'action' )
						;
						$sEditLink = strval( $oUrl );
						
						$oUrl->setVar( 'action', $this->_sDelAction );
						$sDeleteLink = strval( $oUrl );
						
						if ( function_exists( 'wp_nonce_url' ) ) {
							$sDeleteLink = wp_nonce_url( $sDeleteLink,  $this->_sInstanceClass . $this->_sDelAction );
							$sDeleteLink .= '&_wp_http_referer=' . urlencode( $sThisUrl );
						}
						
						?><tbody>
							<tr id="bkng-<?php $oEntity->echoId(); ?>" class='alternate author-self status-publish iedit' valign="top">
								<th scope="row" class="check-column"><input type="checkbox" name="bkng[]" value="<?php $oEntity->echoId(); ?>" /></th>
								<td class="bkng-title column-title">
									<strong><a class="row-title" href="<?php echo $sEditLink; ?>" title="<?php echo htmlspecialchars( $oEntity->getTitle() ); ?>"><?php echo htmlspecialchars( $oEntity->getTitle() ); ?></a></strong><br />
									<div class="row-actions">
										<span class="edit"><a href="<?php echo $sEditLink; ?>">Edit</a></span>
										<!-- TO DO: implement delete restrictions -->
										<?php if ( TRUE ): ?>
											<span class="delete"> | <a class="delete:the-list:bkng-<?php $oEntity->echoId(); ?> submitdelete" href="<?php echo $sDeleteLink; ?>">Delete</a></span>
										<?php endif; ?>
									</div>
								</td>
								<?php $this->columnValue( $oEntity ); ?>
								<td class="date column-date"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
								<td class="date column-date"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
							</tr>
						</tbody><?php
					endforeach;
					
					?>
					
				</table>

				<div class="tablenav">
					<?php echo Geko_String::sw( '<div class="tablenav-pages">%s</div>', $sPaginateLinks ); ?>
					<br class="clear"/>
				</div>
				
			</form>
			
			<?php $this->outputAddButton(); ?>
			
		</div>
		
		<?php
	}


	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-slug">Slug</th>
		<th scope="col" class="manage-column column-description">Description</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		?>
		<td class="column-title"><?php $oEntity->echoSlug(); ?></td>
		<td class="column-title"><?php $oEntity->echoDescription(); ?></td>
		<?php
	}
	
	
	
	//
	public function detailsPage( $oEntity = NULL ) {
		
		// default to "add" mode
		$iBkngId = 0;
		$sOp = 'add';
		$sSubmit = 'Add';
		
		// edit mode
		if ( $oEntity ) {
			$iBkngId = $oEntity->getId();
			$sOp = 'edit';
			$sSubmit = 'Update';
		}
		
		$sAction = $sOp . 'bkng';
		$sNonceField = $this->_sInstanceClass . $sAction;
		$sSubmit .= ' ' . $this->_sSubject;
		
		
		$oUrl = new Geko_Uri();

		$oUrl
			->unsetVar( 'action' )
			->unsetVar( $this->_sEntityIdVarName )
		;
		
		?>
		<div class="wrap">
			
			<?php $this->outputHeading(); ?>
			
			<form id="editform" name="<?php echo $sAction; ?>" method="post" action="<?php echo $oUrl; ?>" class="validate" enctype="multipart/form-data">
				
				<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( $sNonceField ); ?>
				
				<input type="hidden" name="action" value="<?php echo $sAction; ?>">
				<?php echo Geko_String::sw( '<input type="hidden" name="%s$1" value="%d$0">', $iBkngId, $this->_sEntityIdVarName ); ?>
				
				<?php
					$this->outputForm();
					do_action( 'admin_geko_bkng_extra_fields', $oEntity, 'extra' );
					do_action( 'admin_geko_bkng_extra_fields_' . $this->_sSlug, $oEntity, 'extra', $this->_sSlug );
				?>
				
				<p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php echo $sSubmit; ?>"></p>
			
			</form>
			
		</div>
		<?php
	}
	
	
	//
	public function formFields() {
		
		$oEntity = $this->_oCurrentEntity;
		
		?>
		<h3><?php echo $this->_sSubject; ?> Options</h3>
		<style type="text/css">
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}
			.short {
				width: 10em !important;
			}
		</style>
		<table class="form-table">
			<?php if ( $oEntity ): ?>
				<tr>
					<th>Date Created</th>
					<td><?php $oEntity->echoDateTimeCreated(); ?></td>
				</tr>
				<tr>
					<th>Date Modified</th>
					<td><?php $oEntity->echoDateTimeModified(); ?></td>
				</tr>
			<?php endif; ?>
			<tr>
				<th><label for="bkng_name">Product Name</label></th>
				<td>
					<input id="bkng_name" name="bkng_name" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="bkng_slug">Product Slug</label></th>
				<td>
					<input id="bkng_slug" name="bkng_slug" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="bkng_description">Product Description</label></th>
				<td>
					<textarea cols="30" rows="5" id="bkng_description" name="bkng_description" />
				</td>
			</tr>
			<?php
				do_action( 'admin_geko_bkng_main_fields', $oEntity, 'pre' );
				do_action( 'admin_geko_bkng_main_fields_' . $this->_sSlug, $oEntity, 'pre', $this->_sSlug );
			?>
			<?php
				$this->customFields();
				do_action( 'admin_geko_bkng_main_fields', $oEntity, 'main' );
				do_action( 'admin_geko_bkng_main_fields_' . $this->_sSlug, $oEntity, 'main', $this->_sSlug );
			?>
		</table>
		<?php
		
		if ( $this->_iCurrentParentEntityId ):
			?><input type="hidden" id="parent_id" name="parent_id" value="<?php echo $this->_iCurrentParentEntityId; ?>" /><?php
		endif;
		
	}
	
	
	// to be implemented by sub-class as needed
	public function customFields() { }
	
	
		
	
	//// crud methods
	
	//
	public function doAddAction( $aParams ) {
		
		global $wpdb;
		
		$bContinue = TRUE;
		$sName = stripslashes( $_POST[ 'bkng_name' ] );
		$sSlug = ( $_POST[ 'bkng_slug' ] ) ? $_POST[ 'bkng_slug' ] : $sName;
		$sDescription = stripslashes( $_POST[ 'bkng_description' ] );
		
		//// do checks
		
		// check title
		if ( $bContinue && !$sName ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty product name was given
		}
		
		//// do operation !!!
		
		if ( $bContinue ) {
			$sSlug = Geko_Wp_Db::generateSlug( $sSlug, 'geko_booking', 'slug' );
			
			$sDateTime = Geko_Db_Mysql::getTimestamp();
			$aInsertValues = array(
				'name' => $sName,
				'slug' => $sSlug,
				'description' => $sDescription,
				'date_created' => $sDateTime,
				'date_modified' => $sDateTime
			);
			
			$aInsertFormat = array( '%s', '%s', '%s', '%s', '%s' );
			
			// update the database first
			$wpdb->insert(
				$wpdb->geko_booking,
				$aInsertValues,
				$aInsertFormat
			);
			
			$aParams[ 'entity_id' ] = $wpdb->get_var( 'SELECT LAST_INSERT_ID()' );
			
			// rewrite the referer url
			$oUrl = new Geko_Uri( $aParams[ 'referer' ] );
			$oUrl
				->setVar( $this->_sEntityIdVarName, $aParams[ 'entity_id' ] )
				->setVar( 'page', $this->_sInstanceClass )
			;
			
			$aParams[ 'referer' ] = strval( $oUrl );
			
			$sEntityClass = $this->_sEntityClass;			
			$oInsertedBkng = new $sEntityClass( $aParams[ 'entity_id' ] );
			
			do_action( 'admin_geko_bkngs_add', $oInsertedBkng );
			do_action( 'admin_geko_bkngs_add_' . $this->_sSlug, $oInsertedBkng );				
			
			$this->triggerNotifyMsg( 'm101' );										// success!!!
		}
		
		return $aParams;
	}
	
	
	//
	public function doEditAction( $aParams ) {
		
		global $wpdb;
		
		$bContinue = TRUE;
		$sName = stripslashes( $_POST[ 'bkng_name' ] );
		$sSlug = ( $_POST[ 'bkng_slug' ] ) ? $_POST[ 'bkng_slug' ] : $sName;
		$sDescription = stripslashes( $_POST[ 'bkng_description' ] );
		
		//// do checks
		
		// check title
		if ( $bContinue && !$sName ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty product name was given
		}
		
		// check the enity id given
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );										// bad bkng id given
		}
		
		//// do operation !!!
		
		if ( $bContinue ) {
			
			$sCurSlug = $oEntity->getSlug();
						
			//
			if ( $sCurSlug != $sSlug ) {
				// slug was changed, ensure it's unique
				$sSlug = Geko_Wp_Db::generateSlug( $sSlug, 'geko_booking', 'slug' );
			}
			
			$sDateTime = Geko_Db_Mysql::getTimestamp();
			$aUpdateValues = array(
				'name' => $sName,
				'slug' => $sSlug,
				'description' => $sDescription,
				'date_modified' => $sDateTime
			);
			
			$aUpdateFormat = array( '%s', '%s', '%s', '%s' );
			
			
			// update the database first
			$wpdb->update(
				$wpdb->geko_booking,
				$aUpdateValues,
				array( 'bkng_id' => $aParams[ 'entity_id' ] ),
				$aUpdateFormat,
				array( '%d' )
			);
			
			$sEntityClass = $this->_sEntityClass;			
			$oUpdatedBkng = new $sEntityClass( $aParams[ 'entity_id' ] );
			
			do_action( 'admin_geko_bkngs_edit', $oEntity, $oUpdatedBkng );
			do_action( 'admin_geko_bkngs_edit_' . $this->_sSlug, $oEntity, $oUpdatedBkng );
			
			$this->triggerNotifyMsg( 'm102' );										// success!!!
			
		}		
		
		return $aParams;
	}
	
	
	//
	public function doDelAction( $aParams ) {
		
		global $wpdb;
		
		// check the bkng id given
		$bContinue = TRUE;
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );										// bad bkng id given
		}
		
		// TO DO: ensure bkng has no member objects
		
		//// do operation !!!

		if ( $bContinue ) {
			
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->geko_booking WHERE bkng_id = %d", $aParams[ 'entity_id' ] ) );
			
			do_action( 'admin_geko_bkngs_delete', $oEntity );
			do_action( 'admin_geko_bkngs_delete' . $this->_sSlug, $oEntity );
			
			$this->triggerNotifyMsg( 'm103' );										// success!!!
		}
		
		return $aParams;
	}
	
	
	
}



