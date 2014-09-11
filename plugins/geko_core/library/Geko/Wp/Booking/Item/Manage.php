<?php

//
class Geko_Wp_Booking_Item_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'bkitm_id';
	
	protected $_sParentEntityClass = 'Geko_Wp_Booking';
	
	protected $_sSubject = 'Booking Items';
	protected $_sListingTitle = 'Subject';
	protected $_sDescription = 'A table that holds all the scheduled items.';
	protected $_sIconId = 'icon-users';
	protected $_sType = 'bkitm';
	
	protected $_iEntitiesPerPage = 10;
	
	//// init
	
	
	
	
	
	//
	public function add() {
		
		parent::add();
		
		
		//// action stuff
		
		add_action( 'admin_geko_bkschs_add', array( $this, 'activateSchedule' ), 11 );
		add_action( 'admin_geko_bkschs_edit', array( $this, 'activateSchedule' ), 11, 2 );
		add_action( 'admin_geko_bkschs_edit', array( $this, 'deactivateSchedule' ), 11, 2 );
		add_action( 'admin_geko_bkschs_extend', array( $this, 'activateSchedule' ), 11, 2 );
		add_action( 'admin_geko_bkschs_delete', array( $this, 'deactivateSchedule' ), 11 );
		
		
		//// database stuff
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_bkng_item', 'bsi' )
			->fieldBigInt( 'bkitm_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'bksch_id', array( 'unsgnd', 'key' ) )
			->fieldDateTime( 'date_item' )
			->fieldVarChar( 'time_start', array( 'size' => 16 ) )
			->fieldVarChar( 'time_end', array( 'size' => 16 ) )
			->fieldBigInt( 'user_id', array( 'unsgnd', 'key' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
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
					->where( 'bsi.bkitm_id = ?', $oEntity->getId() )
					->where( '( bst.slots_taken IS NOT NULL ) AND ( bst.slots_taken > 0 )' )
				;
				
				$this->iBookedEvents = intval( $wpdb->get_var( $oQuery ) );
				
				$bDisable = ( $this->iBookedEvents ) ? TRUE : FALSE;

			}
			
			$aJsonParams = array(
				'file' => array(
					'cal_icon' => sprintf( '%s/themes/base/images/calendar.gif', Geko_Uri::getUrl( 'geko_styles' ) )
				),
				'date' => array(
					'year' => date( 'Y' ),
					'mon' => date( 'n' ),
					'day' => date( 'j' )
				),
				'disable' => $bDisable
			);
			
			?><script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
					var editForm = $( '#editform' );
					var today = new Date( oParams.date.year, oParams.date.mon - 1, oParams.date.day );
					
					$( '#bkitm_date_item' ).datepicker( {
						showOn: 'button',
						buttonImage: oParams.file.cal_icon,
						buttonImageOnly: true,
						minDate: today
					} );
					
					if ( oParams.disable ) {
						
						// disable all form widgets
						editForm.find( '#bkitm_bksch_id, #bkitm_date_item, #bkitm_time_start, #bkitm_time_end, input.button-primary' ).attr( 'disabled', 'disabled' );
						
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
				'bkitm_bksch_id' => $oEntity->getBkschId(),
				'bkitm_date_item' => $oEntity->getDateItem( 'm/d/Y' ),
				'bkitm_time_start' => $oEntity->getTimeStart(),
				'bkitm_time_end' => $oEntity->getTimeEnd()
			);
			
			$aRet = apply_filters( 'admin_geko_bkitm_getstoredopts', $aRet, $oEntity );
			$aRet = apply_filters( sprintf( 'admin_geko_bkitm_getstoredopts%s', $oEntity->getSlug() ), $aRet, $oEntity );
			
		}
		
		return $aRet;
	}
	
	
	//// front-end display methods
	
	//
	public function listingPage() {
		
		$aParams = array(
			'paged' => $this->getPageNum(),
			'posts_per_page' => $this->_iEntitiesPerPage,
			'orderby' => 'datetime_start',
			'order' => 'ASC'
		);
		
		if ( $iParentId = $this->_iCurrentParentEntityId ) {
			$aParams[ 'bkng_id' ] = $iParentId;
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
			
			<form id="geko-bkitm-filter" method="get" action="">
				
				<div class="tablenav">
					<?php echo Geko_String::sw( '<div class="tablenav-pages">%s</div>', $sPaginateLinks ); ?>
					<br class="clear"/>
				</div>
				
				<table class="widefat fixed" cellspacing="0">
				
					<thead>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-schedule-name">Schedule Name</th>
							<th scope="col" class="manage-column column-date-item">Date</th>
							<th scope="col" class="manage-column column-start-time">Start Time</th>
							<th scope="col" class="manage-column column-end-time">End Time</th>
						</tr>
					</thead>
					
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-schedule-name">Schedule Name</th>
							<th scope="col" class="manage-column column-date-item">Date</th>
							<th scope="col" class="manage-column column-start-time">Start Time</th>
							<th scope="col" class="manage-column column-end-time">End Time</th>
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
							$sDeleteLink = wp_nonce_url( $sDeleteLink, sprintf( '%s%s', $this->_sInstanceClass, $this->_sDelAction ) );
							$sDeleteLink .= sprintf( '&_wp_http_referer=%s', urlencode( $sThisUrl ) );
						}
						
						?><tbody>
							<tr id="bkitm-<?php $oEntity->echoId(); ?>" class='alternate author-self status-publish iedit' valign="top">
								<th scope="row" class="check-column"><input type="checkbox" name="bkitm[]" value="<?php $oEntity->echoId(); ?>" /></th>
								<td class="bkitm-title column-title">
									<strong><a class="row-title" href="<?php echo $sEditLink; ?>" title="<?php echo htmlspecialchars( $oEntity->getScheduleName() ); ?>"><?php echo htmlspecialchars( $oEntity->getScheduleName() ); ?></a></strong><br />
									<div class="row-actions">
										<span class="edit"><a href="<?php echo $sEditLink; ?>">Edit</a></span>
										<!-- TO DO: implement delete restrictions -->
										<?php if ( TRUE ): ?>
											<span class="delete"> | <a class="delete:the-list:bkitm-<?php $oEntity->echoId(); ?> submitdelete" href="<?php echo $sDeleteLink; ?>">Delete</a></span>
										<?php endif; ?>
									</div>
								</td>
								<td class="column-title"><?php $oEntity->echoDateItem( 'F j, Y' ); ?></td>
								<td class="column-title"><?php $oEntity->echoTimeStart(); ?></td>
								<td class="column-title"><?php $oEntity->echoTimeEnd(); ?></td>
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
	public function detailsPage( $oEntity = NULL ) {
		
		// default to "add" mode
		$iBkitmId = 0;
		$sOp = 'add';
		$sSubmit = 'Add';
		
		// edit mode
		if ( $oEntity ) {
			$iBkitmId = $oEntity->getId();
			$sOp = 'edit';
			$sSubmit = 'Update';
		}
		
		$sAction = sprintf( '%sbkitm', $sOp );
		$sNonceField = sprintf( '%s%s', $this->_sInstanceClass, $sAction );
		$sSubmit .= sprintf( ' %s', $this->_sSubject );
		
		
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
				
				<?php echo Geko_String::sw( '<input type="hidden" name="%s$1" value="%d$0" />', $iBkitmId, $this->_sEntityIdVarName ); ?>
				
				<?php
					$this->outputForm();
					do_action( 'admin_geko_bkitm_extra_fields', $oEntity, 'extra' );
					do_action( sprintf( 'admin_geko_bkitm_extra_fields_%s', $this->_sSlug ), $oEntity, 'extra', $this->_sSlug );
				?>
				
				<p class="submit">
					<input type="submit" class="button-primary" name="submit" value="<?php echo $sSubmit; ?>" />
				</p>
			
			</form>
			
		</div>
		<?php
		
	}
	
	
	//
	public function formFields() {
		
		$oEntity = $this->_oCurrentEntity;
		
		$oTmMg = Geko_Wp_Booking_Schedule_Time_Manage::getInstance();
		$aTimes = $oTmMg->getTimes();
		
		$aParams = array(
			'showposts' -1,
			'posts_per_page' => -1,
			'parent_id' => $this->_iCurrentParentEntityId
		);
		
		$aSchedules = new Geko_Wp_Booking_Schedule_Query( $aParams, FALSE );
		
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
			<p class="note">There are <?php echo $this->iBookedEvents; ?> slot(s) currently booked for this event.</p>
		<?php endif; ?>
		<table class="form-table">
			<tr>
				<th><label for="bkitm_bksch_id">Schedule</label></th>
				<td><select id="bkitm_bksch_id" name="bkitm_bksch_id">
					<?php echo $aSchedules->implode( '<option value="##Id##">##Title##</option>' ); ?>
				</select></td>
			</tr>
			<tr>
				<th><label for="bkitm_date_item">Event Date</label></th>
				<td>
					<input id="bkitm_date_item" name="bkitm_date_item" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="bkitm_time_start">Start Time</label></th>
				<td><select id="bkitm_time_start" name="bkitm_time_start">
					<?php foreach ( $aTimes as $sTime ): ?>
						<option value="<?php echo $sTime; ?>"><?php echo $sTime; ?></option>
					<?php endforeach; ?>
				</select></td>
			</tr>
			<tr>
				<th><label for="bkitm_time_end">End Time</label></th>
				<td><select id="bkitm_time_end" name="bkitm_time_end">
					<?php foreach ( $aTimes as $sTime ): ?>
						<option value="<?php echo $sTime; ?>"><?php echo $sTime; ?></option>
					<?php endforeach; ?>
				</select></td>
			</tr>
			<?php
				do_action( 'admin_geko_bkitm_main_fields', $oEntity, 'pre' );
				do_action( sprintf( 'admin_geko_bkitm_main_fields_%s', $this->_sSlug ), $oEntity, 'pre', $this->_sSlug );
				do_action( 'admin_geko_bkitm_main_fields', $oEntity, 'main' );
				do_action( sprintf( 'admin_geko_bkitm_main_fields_%s', $this->_sSlug ), $oEntity, 'main', $this->_sSlug );
			?>
		</table>
		<?php
		
		if ( $this->_iCurrentParentEntityId ):
			?><input type="hidden" id="parent_id" name="parent_id" value="<?php echo $this->_iCurrentParentEntityId; ?>" /><?php
		endif;
		
	}
	
	
	
	
	//// crud methods
	
	//
	public function doAddAction( $aParams ) {
		
		global $wpdb;
		$oDb = Geko_Wp::get( 'db' );
		
		$bContinue = TRUE;
		$iBkschId = intval( $_POST[ 'bkitm_bksch_id' ] );		
		$sDateItem = $oDb->getTimestamp( strtotime( $_POST[ 'bkitm_date_item' ] ) );
		$sStartTime = $_POST[ 'bkitm_time_start' ];
		$sEndTime = $_POST[ 'bkitm_time_end' ];
		
		//// do checks
		
		/* // check title
		if ( $bContinue && !$sName ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty product name was given
		} */
		
		//// do operation !!!
		
		if ( $bContinue ) {
			
			$aInsertValues = array(
				'bksch_id' => $iBkschId,
				'date_item' => $sDateItem,
				'time_start' => $sStartTime,
				'time_end' => $sEndTime
			);
			
			$aInsertFormat = array( '%d', '%s', '%s', '%s' );
			
			// update the database first
			$wpdb->insert(
				$wpdb->geko_bkng_item,
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
			$oInsertedBkitm = new $sEntityClass( $aParams[ 'entity_id' ] );
			
			do_action( 'admin_geko_bkitm_add', $oInsertedBkitm );
			do_action( sprintf( 'admin_geko_bkitm_add_%s', $this->_sSlug ), $oInsertedBkitm );				
			
			$this->triggerNotifyMsg( 'm101' );										// success!!!
		}
		
		return $aParams;
	}
	
	//
	public function doEditAction( $aParams ) {
		
		global $wpdb;
		$oDb = Geko_Wp::get( 'db' );
		
		$bContinue = TRUE;
		$iBkschId = intval( $_POST[ 'bkitm_bksch_id' ] );		
		$sDateItem = $oDb->getTimestamp( strtotime( $_POST[ 'bkitm_date_item' ] ) );
		$sStartTime = $_POST[ 'bkitm_time_start' ];
		$sEndTime = $_POST[ 'bkitm_time_end' ];
		
		//// do checks
		
		/* // check title
		if ( $bContinue && !$sName ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty product name was given
		} */
		
		// check the enity id given
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );										// bad bksch id given
		}
		
		//// do operation !!!
		
		if ( $bContinue ) {
			
			$sDateTime = $oDb->getTimestamp();
			$aUpdateValues = array(
				'bksch_id' => $iBkschId,
				'date_item' => $sDateItem,
				'time_start' => $sStartTime,
				'time_end' => $sEndTime
			);
			
			$aUpdateFormat = array( '%d', '%s', '%s', '%s' );
			
			
			// update the database first
			$wpdb->update(
				$wpdb->geko_bkng_item,
				$aUpdateValues,
				array( 'bkitm_id' => $aParams[ 'entity_id' ] ),
				$aUpdateFormat,
				array( '%d' )
			);
			
			$sEntityClass = $this->_sEntityClass;			
			$oUpdatedBkitm = new $sEntityClass( $aParams[ 'entity_id' ] );
			
			do_action( 'admin_geko_bkitm_edit', $oEntity, $oUpdatedBkitm );
			do_action( sprintf( 'admin_geko_bkitm_edit_%s', $this->_sSlug ), $oEntity, $oUpdatedBkitm );
			
			$this->triggerNotifyMsg( 'm102' );										// success!!!
			
		}
		
		return $aParams;
	}
	
	//
	public function doDelAction( $aParams ) {

		global $wpdb;
		
		// check the bkitm id given
		$bContinue = TRUE;
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );										// bad bksch id given
		}
		
		// TO DO: ensure bkitm has no member objects
		
		//// do operation !!!

		if ( $bContinue ) {
			
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->geko_bkng_item WHERE bkitm_id = %d", $aParams[ 'entity_id' ] ) );
			
			do_action( 'admin_geko_bkitm_delete', $oEntity );
			do_action( sprintf( 'admin_geko_bkitm_delete%s', $this->_sSlug ), $oEntity );
			
			$this->triggerNotifyMsg( 'm103' );										// success!!!
		}
		
		return $aParams;
		
	}
	
	
	
	
	//// schedule hooks
	
	//
	public function activateSchedule( $oBksch, $oUpdatedBksch ) {
		
		if ( $_REQUEST[ 'activate' ] || $_REQUEST[ 'extend' ] ) {
			
			global $wpdb;
			global $user_ID;
			
			$oDb = Geko_Wp::get( 'db' );
			
			if ( $oUpdatedBksch ) $oBksch = $oUpdatedBksch;
			
			// force cleanup of old schedule anyway
			if ( $_REQUEST[ 'extend' ] ) {
				// get existing and create comparison hash so there are no duplicates
				$aItemsFmt = array();
				$aItems = $wpdb->get_results( $wpdb->prepare(
					"SELECT date_item, time_start, time_end FROM $wpdb->geko_bkng_item WHERE bksch_id = %d",
					$oBksch->getId()
				), ARRAY_A );
				foreach ( $aItems as $aItem ) {
					$aItemsFmt[ sprintf( '%s-%s-%s', $aItem[ 'date_item' ], $aItem[ 'time_start' ], $aItem[ 'time_end' ] ) ] = TRUE;
				}
			} else {
				// clean-up existing
				$this->deleteItems( $oBksch );
			}
			
			$iStartDate = strtotime( $oBksch->getDateStart() );
			$iEndDate = strtotime( $oBksch->getDateEnd() );
			
			// compile schedule times
			$aTimesFmt = array();
			
			$oTimesMgmt = Geko_Wp_Booking_Schedule_Time_Manage::getInstance();
			$aTimes = $oTimesMgmt->getStoredOptions( array(), $oBksch );
			$aTimes = $aTimes[ 'times' ];
			
			foreach ( $aTimes as $aTime ) {
				$aTimesFmt[ $aTime[ 'weekday_id' ] ][] = array(
					'start' => $aTime[ 'start' ],
					'end' => $aTime[ 'end' ]
				);
			}
			
			$sProductName = $oBksch->getBookingName();
			$sScheduleName = $oBksch->getName();
			$fUnits = floatval( $oBksch->getUnit() );
			$fCost = floatval( $oBksch->getCost() );
			
			while( $iStartDate <= $iEndDate ) {
				
				$iWeekdayId = date( 'w', $iStartDate );
				$sDate = date( 'Y-m-d ', $iStartDate );
				// $sDateItem = $oDb->getTimestamp( $iStartDate );
				$sPrevDateItem = $sDateItem;
				$sDateItem = sprintf( '%s 00:00:00', $sDate );
				
				// daylight savings time check
				if ( $sPrevDateItem == $sDateItem ) {
					$iStartDate += ( 60 * 60 * 24 );			// advance by a day
					continue;
				}
				
				if ( $aTms = $aTimesFmt[ $iWeekdayId ] ) {
					foreach ( $aTms as $aTm ) {
						
						$iStartTime = strtotime( sprintf( '%s%s', $sDate, $aTm[ 'start' ] ) );
						$iEndTime = strtotime( sprintf( '%s%s', $sDate, $aTm[ 'end' ] ) );
						
						// roll over to next day
						if ( ( '12:00 AM' == $aTm[ 'start' ] ) || ( '12:30 AM' == $aTm[ 'start' ] ) ) {
							$iStartTime += 60 * 60 * 24;
						}

						if ( ( '12:00 AM' == $aTm[ 'end' ] ) || ( '12:30 AM' == $aTm[ 'end' ] ) ) {
							$iEndTime += 60 * 60 * 24;
						}
						
						while ( $iStartTime < $iEndTime ) {
							
							$iStartTime2 = $iStartTime + ( 60 * 60 * $fUnits );
							
							$sTimeStart = date( 'g:i A', $iStartTime );
							$sTimeEnd = date( 'g:i A', $iStartTime2 );
							
							if (
								( !$_REQUEST[ 'extend' ] ) || 
								(
									( $_REQUEST[ 'extend' ] ) &&
									( !$aItemsFmt[ sprintf( '%s-%s-%s', $sDateItem, $sTimeStart, $sTimeEnd ) ] )
								)
							) {
								
								$aInsertValues = array(
									'bksch_id' => $oBksch->getId(),
									'date_item' => $sDateItem,
									'time_start' => $sTimeStart,
									'time_end' => $sTimeEnd
								);
								
								$aInsertFormat = array( '%d', '%s', '%s', '%s' );
								
								//
								$wpdb->insert(
									$wpdb->geko_bkng_item,
									$aInsertValues,
									$aInsertFormat
								);
								
								//// record transaction
								
								if ( $oBksch->isPrivate() ) {
									
									$iItemId = $wpdb->get_var( 'SELECT LAST_INSERT_ID()' );
									
									$sDetails = sprintf(
										'Private Booking Purchase: %s, %s; %s : %s - %s; %s hr(s)',
										$sProductName,
										$sScheduleName,
										date( 'M d, Y', $iStartDate ),
										$sTimeStart,
										$sTimeEnd,
										$fUnits
									);
									
									$oTrnsMng = Geko_Wp_Booking_Transaction_Manage::getInstance();
									$oTrnsMng->recordPrivateTransaction( array(
										// 'is_test' => ???,			// how to pass this???
										'bkitm_id' => $iItemId,
										'details' => $sDetails,
										'units' => $fUnits,
										'amount' => $fCost,
										'user_id' => $user_ID
									) );
								
								}
								
							}
							
							$iStartTime = $iStartTime2;
						}
					}
				}
				
				$iStartDate += ( 60 * 60 * 24 );			// advance by a day
			}
		
		}
		
	}
	
	//
	public function deactivateSchedule( $oBksch, $oUpdatedBksch ) {
		if ( $_REQUEST[ 'deactivate' ] || ( 'deletebksch' == $_REQUEST[ 'action' ] ) ) {
			if ( $oUpdatedBksch ) $oBksch = $oUpdatedBksch;
			$this->deleteItems( $oBksch );
		}
	}
	
	
	//// helpers
	
	//
	public function deleteItems( $oBksch ) {
		global $wpdb;
		$iBkschId = $oBksch->getId();
		if ( $oBksch->isPrivate() ) {
			$oTrnsMng = Geko_Wp_Booking_Transaction_Manage::getInstance();
			$oTrnsMng->deletePrivateTransactions( $iBkschId );
		}
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->geko_bkng_item WHERE bksch_id = %d", $iBkschId ) );
	}
	
	//
	public function scheduleHasItems( $iBkschId ) {
		global $wpdb;
		return (
			$wpdb->get_var( $wpdb->prepare( "SELECT bksch_id FROM $wpdb->geko_bkng_item WHERE bksch_id = %d", $iBkschId ) )
		) ? TRUE : FALSE;
	}
	
	
	//
	public function getItem( $iEntityId, $iUserId = NULL, $aExtraParams = array() ) {
		
		$aParams = array( 'bkitm_id' => $iEntityId );
		
		if ( $iUserId ) $aParams[ 'user_id' ] = $iUserId;
		
		$aParams = array_merge( $aParams, $aExtraParams );
		
		$oEntity = new Geko_Wp_Booking_Item_Query( $aParams, FALSE );
		
		return $oEntity->getOne();
	}
	
	// DEPRACATED ???
	public function bookItem( $oUser, $oEntity = NULL ) {
		
		global $wpdb;
		
		$oEntity = ( $oEntity ) ? $oEntity : $this->_oCurrentEntity;
		
		$wpdb->update(
			$wpdb->geko_bkng_item,
			array( 'user_id' => $oUser->getId() ),
			array( 'bkitm_id' => $oEntity->getId() ),
			array( '%d' ),
			array( '%d' )
		);
		
		// re-query entity
		return $this->getItem( $oEntity->getId() );
	}
	
	// DEPRACATED ???
	// $oUser is currently not used
	public function cancelItem( $oUser, $oEntity = NULL ) {
		
		global $wpdb;
		
		$oEntity = ( $oEntity ) ? $oEntity : $this->_oCurrentEntity;
		
		$wpdb->query( sprintf( "
			UPDATE				$wpdb->geko_bkng_item
			SET					user_id = NULL
			WHERE				bkitm_id = %d
		", $oEntity->getId() ) );
		
		// re-query entity
		return $this->getItem( $oEntity->getId() );
	}
	
}



