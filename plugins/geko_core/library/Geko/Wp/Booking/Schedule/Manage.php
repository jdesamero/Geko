<?php

//
class Geko_Wp_Booking_Schedule_Manage extends Geko_Wp_Options_Manage
{
	const TYPE_PUBLIC = 0;
	const TYPE_PRIVATE = 1;
	const TYPE_OPEN = 2;
	
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'bksch_id';
	
	protected $_sParentEntityClass = 'Geko_Wp_Booking';
	
	protected $_sSubject = 'Booking Schedule';
	protected $_sListingTitle = 'Subject';
	protected $_sDescription = 'Schedules associated with a product.';
	protected $_sIconId = 'icon-users';
	protected $_sType = 'bksch';
	
	protected $_aSubOptions = array( 'Geko_Wp_Booking_Schedule_Time_Manage' );
	
	protected $_iEntitiesPerPage = 10;
	
	private $bHasItems = FALSE;
	private $iBookedEvents = 0;
	
	
	
	//// init
	
	
	//
	public function add() {
		
		global $wpdb;
		
		parent::add();
		
		$sTableName = 'geko_bkng_schedule';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'bs' )
			->fieldBigInt( 'bksch_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'bksch_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'name' )
			->fieldVarChar( 'slug', array( 'size' => 256 ) )
			->fieldLongText( 'description' )
			->fieldDateTime( 'date_start' )
			->fieldDateTime( 'date_end' )
			->fieldTinyInt( 'booking_type', array( 'unsgnd' ) )
			->fieldFloat( 'unit', array( 'unsgnd', 'size' => '5,2' ) )
			->fieldFloat( 'cost', array( 'unsgnd', 'size' => '10,2' ) )
			->fieldSmallInt( 'slots' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		
		return $this;
	}
	
	
	
	
	// create table
	public function install() {
		
		parent::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	
	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {			
			wp_enqueue_style( 'geko-jquery-ui-wp' );
			// wp_enqueue_script( 'geko_wp_booking_schedule_manage' );
			wp_enqueue_script( 'geko-jquery-ui-datepicker' );
		}
		
		return $this;
	}
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {
			
			$bDisable = FALSE;
			
			if ( $this->_oCurrentEntity ) {
				
				global $wpdb;
				
				$oEntity = $this->_oCurrentEntity;
				
				$oBkTrMg = Geko_Wp_Booking_Transaction_Manage::getInstance()->init();
				$oStQuery = $oBkTrMg->getSlotsTakenQuery();
				
				$oQuery = new Geko_Sql_Select();
				$oQuery
					->field( 'COUNT(*)', 'num' )
					->from( $wpdb->geko_bkng_item, 'bsi' )
					->joinLeft( $oStQuery, 'bst' )
						->on( 'bst.bkitm_id = bsi.bkitm_id' )
					->where( 'bsi.bksch_id = ?', $oEntity->getId() )
					->where( '( bst.slots_taken IS NOT NULL ) AND ( bst.slots_taken > 0 )' )
				;
				
				$this->iBookedEvents = intval( $wpdb->get_var( $oQuery ) );
				$this->bHasItems = Geko_Wp_Booking_Item_Manage::getInstance()->scheduleHasItems( $oEntity->getId() );
				
				$bDisable = ( $this->iBookedEvents ) ? TRUE : FALSE;
				
			}
			
			$aJsonParams = array(
				'file' => array(
					'cal_icon' => Geko_Uri::getUrl( 'geko_styles' ) . '/themes/base/images/calendar.gif'
				),
				'date' => array(
					'year' => date( 'Y' ),
					'mon' => date( 'n' ),
					'day' => date( 'j' )
				),
				'disable' => $bDisable,
				'has_items' => $this->bHasItems
			);
			
			?><script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
					var editForm = $( '#editform' );
					var today = new Date( oParams.date.year, oParams.date.mon - 1, oParams.date.day );
					
					$( '#bksch_date_start, #bksch_date_end' ).datepicker( {
						showOn: 'button',
						buttonImage: oParams.file.cal_icon,
						buttonImageOnly: true,
						minDate: today
					} );
					
					$( '#activate-btn' ).click( function() {
						$( 'input[name="activate"]' ).val( 1 );
					} );
					
					$( 'input[name="submit"]' ).click( function() {
						if ( oParams.has_items ) {
							$( 'input[name="activate"]' ).val( 1 );
						}
					} );
					
					$( '#deactivate-btn' ).click( function() {
						$( 'input[name="deactivate"]' ).val( 1 );
					} );
					
					if ( oParams.disable ) {
						
						// disable all form widgets
						editForm.find( 'input, textarea, select' ).attr( 'disabled', 'disabled' );
						$( '#bksch_date_start' ).datepicker( 'disable' );
						
						// enable these ones
						editForm.find( '#bksch_date_end, #bksch_description, #_wpnonce, #parent_id, input[name="_wp_http_referer"], input[name="action"], input[name="submit"], input[name="has_items"], input[name="extend"], input[name="bksch_id"]' ).removeAttr( 'disabled' );
						
						// min date is either the current date, or the end date
						// whichever is greater of the two
						var endMinDate = $( '#bksch_date_end' ).datepicker( 'getDate' );
						if ( endMinDate.getTime() > today.getTime() ) {
							$( '#bksch_date_end' ).datepicker( 'option', 'minDate', endMinDate );
						}
						
					}
					
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
				'bksch_name' => $oEntity->getName(),
				'bksch_slug' => $oEntity->getSlug(),
				'bksch_description' => $oEntity->getDescription(),
				'bksch_date_start' => $oEntity->getDateStart( 'm/d/Y' ),
				'bksch_date_end' => $oEntity->getDateEnd( 'm/d/Y' ),
				'bksch_booking_type' => $oEntity->getBookingType(),
				'bksch_unit' => $oEntity->getUnit(),
				'bksch_cost' => $oEntity->getCost(),
				'bksch_slots' => $oEntity->getSlots()
			);
			
			$aRet = apply_filters( 'admin_geko_bkschs_getstoredopts', $aRet, $oEntity );
			$aRet = apply_filters( 'admin_geko_bkschs_getstoredopts' . $oEntity->getSlug(), $aRet, $oEntity );
			
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
		
		if ( $iParentId = $this->_iCurrentParentEntityId ) {
			$aParams[ 'parent_id' ] = $iParentId;
		}
		
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
			
			<form id="geko-bksch-filter" method="get" action="">
				
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
							<tr id="bksch-<?php $oEntity->echoId(); ?>" class='alternate author-self status-publish iedit' valign="top">
								<th scope="row" class="check-column"><input type="checkbox" name="bksch[]" value="<?php $oEntity->echoId(); ?>" /></th>
								<td class="bksch-title column-title">
									<strong><a class="row-title" href="<?php echo $sEditLink; ?>" title="<?php echo htmlspecialchars( $oEntity->getTitle() ); ?>"><?php echo htmlspecialchars( $oEntity->getTitle() ); ?></a></strong><br />
									<div class="row-actions">
										<span class="edit"><a href="<?php echo $sEditLink; ?>">Edit</a></span>
										<!-- TO DO: implement delete restrictions -->
										<?php if ( TRUE ): ?>
											<span class="delete"> | <a class="delete:the-list:bksch-<?php $oEntity->echoId(); ?> submitdelete" href="<?php echo $sDeleteLink; ?>">Delete</a></span>
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
		
		$sAction = $sOp . 'bksch';
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
				
				<input type="hidden" name="action" value="<?php echo $sAction; ?>" />				
				<input type="hidden" name="deactivate" value="0" />
				<input type="hidden" name="activate" value="0" />
				<input type="hidden" name="extend" value="<?php echo ( $this->iBookedEvents ) ? 1 : 0; ?>" />
				
				<?php echo Geko_String::sw( '<input type="hidden" name="%s$1" value="%d$0">', $iBkngId, $this->_sEntityIdVarName ); ?>
				
				<?php
					$this->outputForm();
					do_action( 'admin_geko_bksch_extra_fields', $oEntity, 'extra' );
					do_action( 'admin_geko_bksch_extra_fields_' . $this->_sSlug, $oEntity, 'extra', $this->_sSlug );
				?>
				
				<p class="submit">
					<input type="submit" class="button-primary" name="submit" value="<?php echo $sSubmit; ?>" />
					<?php if ( $this->bHasItems ): ?>
						<input type="submit" class="button-primary" id="deactivate-btn" value="De-activate" />
					<?php else: ?>
						<input type="submit" class="button-primary" id="activate-btn" value="Activate" />
					<?php endif; ?>
				</p>
			
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
			p.note {
				font-style: italic;
			}
		</style>
		<?php if ( $this->iBookedEvents ): ?>
			<p class="note">There are <?php echo $this->iBookedEvents; ?> slot(s) currently booked for this schedule.</p>
		<?php endif; ?>
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
				<th><label for="bksch_name">Schedule Name</label></th>
				<td>
					<input id="bksch_name" name="bksch_name" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="bksch_slug">Schedule Slug</label></th>
				<td>
					<input id="bksch_slug" name="bksch_slug" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="bksch_description">Schedule Description</label></th>
				<td>
					<textarea cols="30" rows="5" id="bksch_description" name="bksch_description" />
				</td>
			</tr>
			<?php
				do_action( 'admin_geko_bksch_main_fields', $oEntity, 'pre' );
				do_action( 'admin_geko_bksch_main_fields_' . $this->_sSlug, $oEntity, 'pre', $this->_sSlug );
			?>
			<tr>
				<th><label for="bksch_date_start">Date Start</label></th>
				<td>
					<input id="bksch_date_start" name="bksch_date_start" type="text" class="regular-text short" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="bksch_date_end">Date End</label></th>
				<td>
					<input id="bksch_date_end" name="bksch_date_end" type="text" class="regular-text short" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="bksch_booking_type">Booking Type</label></th>
				<td>
					<select id="bksch_booking_type" name="bksch_booking_type">
						<option value="<?php echo self::TYPE_PUBLIC; ?>">Public</option>
						<option value="<?php echo self::TYPE_PRIVATE; ?>">Private</option>
						<option value="<?php echo self::TYPE_OPEN; ?>">Open</option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="bksch_unit">Unit</label></th>
				<td>
					<input id="bksch_unit" name="bksch_unit" type="text" class="regular-text short" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="bksch_cost">Cost</label></th>
				<td>
					<input id="bksch_cost" name="bksch_cost" type="text" class="regular-text short" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="bksch_slots">Slots</label></th>
				<td>
					<input id="bksch_slots" name="bksch_slots" type="text" class="regular-text short" value="" />
				</td>
			</tr>
			<?php
				$this->customFields();
				do_action( 'admin_geko_bksch_main_fields', $oEntity, 'main' );
				do_action( 'admin_geko_bksch_main_fields_' . $this->_sSlug, $oEntity, 'main', $this->_sSlug );
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
		$iBookingId = intval( $_POST[ 'parent_id' ] );
		$sName = stripslashes( $_POST[ 'bksch_name' ] );
		$sSlug = ( $_POST[ 'bksch_slug' ] ) ? $_POST[ 'bksch_slug' ] : $sName;
		$sDescription = stripslashes( $_POST[ 'bksch_description' ] );
		$sDateStart = Geko_Db_Mysql::getTimestamp( strtotime( $_POST[ 'bksch_date_start' ] ) );
		$sDateEnd = Geko_Db_Mysql::getTimestamp( strtotime( $_POST[ 'bksch_date_end' ] ) );
		$iBookingType = intval( $_POST[ 'bksch_booking_type' ] );
		$fUnit = floatval( $_POST[ 'bksch_unit' ] );
		$fCost = floatval( $_POST[ 'bksch_cost' ] );
		$iSlots = intval( $_POST[ 'bksch_slots' ] );
		
		//// do checks
		
		// check title
		if ( $bContinue && !$sName ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty product name was given
		}
		
		//// do operation !!!
		
		if ( $bContinue ) {
			$sSlug = Geko_Wp_Db::generateSlug( $sSlug, 'geko_bkng_schedule', 'slug' );
			
			$sDateTime = Geko_Db_Mysql::getTimestamp();
			$aInsertValues = array(
				'bkng_id' => $iBookingId,
				'name' => $sName,
				'slug' => $sSlug,
				'description' => $sDescription,
				'date_start' => $sDateStart,
				'date_end' => $sDateEnd,
				'booking_type' => $iBookingType,
				'unit' => $fUnit,
				'cost' => $fCost,
				'slots' => $iSlots,
				'date_created' => $sDateTime,
				'date_modified' => $sDateTime
			);
			
			$aInsertFormat = array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%f', '%d', '%s', '%s' );
			
			// update the database first
			$wpdb->insert(
				$wpdb->geko_bkng_schedule,
				$aInsertValues,
				$aInsertFormat
			);
			
			$aParams[ 'entity_id' ] = $wpdb->insert_id;
			
			// rewrite the referer url
			$oUrl = new Geko_Uri( $aParams[ 'referer' ] );
			$oUrl
				->setVar( $this->_sEntityIdVarName, $aParams[ 'entity_id' ] )
				->setVar( 'page', $this->_sInstanceClass )
			;
			
			$aParams[ 'referer' ] = strval( $oUrl );
			
			$sEntityClass = $this->_sEntityClass;			
			$oInsertedBkng = new $sEntityClass( $aParams[ 'entity_id' ] );
			
			do_action( 'admin_geko_bkschs_add', $oInsertedBkng );
			do_action( 'admin_geko_bkschs_add_' . $this->_sSlug, $oInsertedBkng );				
			
			$this->triggerNotifyMsg( 'm101' );										// success!!!
		}
		
		return $aParams;
	}
	
	
	//
	public function doEditAction( $aParams ) {
		
		// if we're simply extending the event, then re-route to a different action
		if ( $_POST[ 'extend' ] ) {
			return $this->doExtendAction( $aParams );
		}
		
		// -------------------------------------------------------------------------------------- //
		
		global $wpdb;
		
		$bContinue = TRUE;
		$iBookingId = intval( $_POST[ 'parent_id' ] );
		$sName = stripslashes( $_POST[ 'bksch_name' ] );
		$sSlug = ( $_POST[ 'bksch_slug' ] ) ? $_POST[ 'bksch_slug' ] : $sName;
		$sDescription = stripslashes( $_POST[ 'bksch_description' ] );
		$sDateStart = Geko_Db_Mysql::getTimestamp( strtotime( $_POST[ 'bksch_date_start' ] ) );
		$sDateEnd = Geko_Db_Mysql::getTimestamp( strtotime( $_POST[ 'bksch_date_end' ] ) );
		$iBookingType = intval( $_POST[ 'bksch_booking_type' ] );
		$fUnit = floatval( $_POST[ 'bksch_unit' ] );
		$fCost = floatval( $_POST[ 'bksch_cost' ] );
		$iSlots = intval( $_POST[ 'bksch_slots' ] );
		
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
			$this->triggerErrorMsg( 'm202' );										// bad bksch id given
		}
		
		//// do operation !!!
		
		if ( $bContinue ) {
			
			$sCurSlug = $oEntity->getSlug();
						
			//
			if ( $sCurSlug != $sSlug ) {
				// slug was changed, ensure it's unique
				$sSlug = Geko_Wp_Db::generateSlug( $sSlug, 'geko_bkng_schedule', 'slug' );
			}
			
			$sDateTime = Geko_Db_Mysql::getTimestamp();
			$aUpdateValues = array(
				'bkng_id' => $iBookingId,
				'name' => $sName,
				'slug' => $sSlug,
				'description' => $sDescription,
				'date_start' => $sDateStart,
				'date_end' => $sDateEnd,
				'booking_type' => $iBookingType,
				'unit' => $fUnit,
				'cost' => $fCost,
				'slots' => $iSlots,
				'date_modified' => $sDateTime
			);
			
			$aUpdateFormat = array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%f', '%d', '%s' );
			
			
			// update the database first
			$wpdb->update(
				$wpdb->geko_bkng_schedule,
				$aUpdateValues,
				array( 'bksch_id' => $aParams[ 'entity_id' ] ),
				$aUpdateFormat,
				array( '%d' )
			);
			
			$sEntityClass = $this->_sEntityClass;			
			$oUpdatedBkng = new $sEntityClass( $aParams[ 'entity_id' ] );
			
			do_action( 'admin_geko_bkschs_edit', $oEntity, $oUpdatedBkng );
			do_action( 'admin_geko_bkschs_edit_' . $this->_sSlug, $oEntity, $oUpdatedBkng );
			
			$this->triggerNotifyMsg( 'm102' );										// success!!!
			
		}
		
		return $aParams;
	}
	

	//
	public function doExtendAction( $aParams ) {
		
		global $wpdb;
		
		$bContinue = TRUE;
		$sDescription = stripslashes( $_POST[ 'bksch_description' ] );
		$sDateEnd = Geko_Db_Mysql::getTimestamp( strtotime( $_POST[ 'bksch_date_end' ] ) );
		
		//// do checks
		
		// check the enity id given
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );										// bad bksch id given
		}
		
		//// do operation !!!
		
		if ( $bContinue ) {
			
			$sDateTime = Geko_Db_Mysql::getTimestamp();
			$aUpdateValues = array(
				'description' => $sDescription,
				'date_end' => $sDateEnd,
				'date_modified' => $sDateTime
			);
			
			$aUpdateFormat = array( '%s', '%s', '%s' );
			
			// update the database first
			$wpdb->update(
				$wpdb->geko_bkng_schedule,
				$aUpdateValues,
				array( 'bksch_id' => $aParams[ 'entity_id' ] ),
				$aUpdateFormat,
				array( '%d' )
			);
			
			$sEntityClass = $this->_sEntityClass;			
			$oUpdatedBkng = new $sEntityClass( $aParams[ 'entity_id' ] );
			
			do_action( 'admin_geko_bkschs_extend', $oEntity, $oUpdatedBkng );
			do_action( 'admin_geko_bkschs_extend_' . $this->_sSlug, $oEntity, $oUpdatedBkng );
			
			$this->triggerNotifyMsg( 'm102' );										// success!!!
			
		}		
		
		return $aParams;
	}
	
	
	//
	public function doDelAction( $aParams ) {
		
		global $wpdb;
		
		// check the bksch id given
		$bContinue = TRUE;
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );										// bad bksch id given
		}
		
		// TO DO: ensure bksch has no member objects
		
		//// do operation !!!

		if ( $bContinue ) {
			
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->geko_bkng_schedule WHERE bksch_id = %d", $aParams[ 'entity_id' ] ) );
			
			do_action( 'admin_geko_bkschs_delete', $oEntity );
			do_action( 'admin_geko_bkschs_delete' . $this->_sSlug, $oEntity );
			
			$this->triggerNotifyMsg( 'm103' );										// success!!!
		}
		
		return $aParams;
	}
	
	
		
	
}


