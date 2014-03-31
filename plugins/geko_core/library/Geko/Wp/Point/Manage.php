<?php

//
class Geko_Wp_Point_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'point_id';
	
	protected $_sSubject = 'Point';
	protected $_sListingTitle = 'Point Id';
	protected $_sDescription = 'Assign points to users when performing particular events.';
	protected $_sIconId = 'icon-users';
	protected $_iEntitiesPerPage = 50;
	protected $_sType = 'point';
	protected $_bHasKeywordSearch = TRUE;
	
	protected $_aSubOptions = array( 'Geko_Wp_Point_Meta' );
	
	protected $_sTabGroupTitle = 'Points';
	protected $_aTabGroup = array(
		'Geko_Wp_Point_Event_Manage',
		'Geko_Wp_Point_Operation'	
	);
	
	protected $_sPointsApprovalEmsg = '';
	
	
	
	//// init
	
	//
	public function add() {
		
		global $wpdb;
		
		parent::add();
		
		
		//// dependencies
		
		Geko_Wp_Enumeration_Manage::getInstance()->affix();
		
		
		//// actions
		
		add_action( 'delete_user', array( $this, 'deleteUser' ) );
		
		
		//// database stuff
		
		$sTableName = 'geko_point';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'p' )
			->fieldBigInt( 'point_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'user_id', array( 'unsgnd', 'notnull', 'key' ) )
			->fieldSmallInt( 'pntevt_id', array( 'unsgnd', 'notnull' ) )
			->fieldBigInt( 'value', array( 'sgnd', 'notnull'  ) )
			->fieldTinyInt( 'approve_status_id', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'transaction_code' )
			->fieldLongText( 'extra' )
			->fieldLongText( 'comments' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
		
	}
	
	
	
	// create table
	public function install() {
		
		parent::install();
		
		Geko_Once::run( sprintf( '%s::enumeration', __CLASS__ ), array( $this, 'installEnumeration' ) );
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	//
	public function installEnumeration() {
		
		Geko_Wp_Enumeration_Manage::getInstance()->install();
		Geko_Wp_Enumeration_Manage::populate( array(
			array( 'title' => 'Point Status', 'slug' => 'geko-point-status', 'description' => 'List of point statuses.' ),
			array(
				array( 'title' => 'Not Reviewed', 'slug' => 'geko-point-status-not-reviewed', 'value' => 0, 'rank' => 0, 'description' => 'The user point has not been reviewed.' ),
				array( 'title' => 'Approved', 'slug' => 'geko-point-status-approved', 'value' => 1, 'rank' => 1, 'description' => 'The user point has been approved.' ),
				array( 'title' => 'Denied', 'slug' => 'geko-point-status-denied', 'value' => 2, 'rank' => 2, 'description' => 'The user point has been denied.' )
			)
		) );
		
		Geko_Wp_Enumeration_Manage::populate( array(
			array( 'title' => 'Point Award Error', 'slug' => 'geko-point-award-error', 'description' => 'List of point award errors.' ),
			array(
				array( 'title' => 'Invalid user', 'slug' => 'geko-point-awerr-invalid-user', 'value' => -1, 'rank' => 0, 'description' => 'Invalid user was specified.' ),
				array( 'title' => 'Invalid event slug', 'slug' => 'geko-point-awerr-invalid-slug', 'value' => -2, 'rank' => 1, 'description' => 'Invalid point event slug was specfied.' ),
				array( 'title' => 'Point is one-time only', 'slug' => 'geko-point-awerr-one-time-only', 'value' => -3, 'rank' => 2, 'description' => 'Trying to award a one-time point again.' ),
				array( 'title' => 'Time limit still in effect', 'slug' => 'geko-point-awerr-time-limit-in-effect', 'value' => -4, 'rank' => 3, 'description' => 'Time limit in effect, must pass before point can be awarded again.' ),
				array( 'title' => 'Max point events exceeded', 'slug' => 'geko-point-awerr-max-events-exceeded', 'value' => -5, 'rank' => 4, 'description' => 'The number of allowable point events have been exceeded.' ),
				array( 'title' => 'Duplicate point already awarded', 'slug' => 'geko-point-awerr-duplicate', 'value' => -6, 'rank' => 5, 'description' => 'Trying to a award a duplicate point.' ),
				array( 'title' => 'Zero (0) points awarded', 'slug' => 'geko-point-awerr-zero-points', 'value' => -7, 'rank' => 6, 'description' => 'Trying to award zero (0) points.' ),
				array( 'title' => 'Unknown error', 'slug' => 'geko-point-awerr-unknown-error', 'value' => -999, 'rank' => 7, 'description' => 'An unknown error occured.' )
			)
		) );
		
	}

	
	
	//// accessors
	
	//
	public function setPointsApprovalEmsg( $sPointsApprovalEmsg ) {
		$this->_sPointsApprovalEmsg = $sPointsApprovalEmsg;
		return $this;
	}
	
	
	
	//// front-end display methods
		
	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-user-email">User Email</th>
		<th scope="col" class="manage-column column-event">Event</th>
		<th scope="col" class="manage-column column-value">Value</th>
		<th scope="col" class="manage-column column-status">Approval Status</th>
		<th scope="col" class="manage-column column-date-created">Date Created</th>
		<th scope="col" class="manage-column column-date-modified">Date Modified</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		?>
		<td class="column-user-email"><a href="<?php Geko_Wp::echoUrl(); ?>/wp-admin/user-edit.php?user_id=<?php $oEntity->echoUserId(); ?>"><?php $oEntity->echoUserEmail(); ?></a></td>
		<td class="column-event"><?php $oEntity->echoEvent(); ?></td>
		<td class="column-value"><?php $oEntity->echoValue(); ?></td>
		<td class="column-status"><?php $oEntity->echoApprovalStatus(); ?></td>
		<td class="date column-date-created"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date-modified"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
		<?php
	}
	
	
	//
	public function formFields() {
		
		$aApprStatus = Geko_Wp_Enumeration_Query::getSet( 'geko-point-status' );
		
		$oEntity = $this->_oCurrentEntity;
		$sAction = $this->_sActionPrefix . '_point_details';
		$sEventSlug = $oEntity->getEventSlug();
		
		?>
		<h3><?php echo $this->_sSubject; ?> Options</h3>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<?php if ( $oEntity ): ?>
				<tr>
					<th>Point Id</th>
					<td><?php $oEntity->echoId(); ?></td>
				</tr>
				<tr>
					<th>User Email</th>
					<td><a href="<?php Geko_Wp::echoUrl(); ?>/wp-admin/user-edit.php?user_id=<?php $oEntity->echoUserId(); ?>"><?php $oEntity->echoUserEmail(); ?></a></td>
				</tr>
				<tr>
					<th>Event</th>
					<td><?php $oEntity->echoEvent(); ?></td>
				</tr>
				<tr>
					<th>Value</th>
					<td><?php $oEntity->echoValue(); ?></td>
				</tr>
			<?php endif; ?>
			<?php do_action( $sAction . '_' . $sEventSlug, $oEntity, $sEventSlug ); ?>
			<tr>
				<th><label for="point_approve_status_id">Approval Status</label></th>
				<td>
					<?php if ( $oEntity->getRequiresApproval() ): ?>
						<select id="point_approve_status_id" name="point_approve_status_id">
							<?php echo $aApprStatus->implode( array( '<option value="##Value##">##Title##</option>', '' ) ); ?>
						<select>
					<?php else:
						$oEntity->echoApprovalStatus();
					endif; ?>
				</td>
			</tr>
			<?php $this->customFieldsMain(); ?>
		</table>
		<?php
		
		$this->parentEntityField();
		
	}
	
	
	
	// hook method
	public function getFilterSelects() {
		
		// point events
		
		$aPointEvents = Geko_Wp_Point_Event_Manage::getInstance()->getPointEvents();
		$aPeFmt = array( '' => 'Show All Point Events' );
		foreach ( $aPointEvents as $oPe ) {
			$aPeFmt[ $oPe->getSlug() ] = $oPe->getTitle();
		}
		
		// approval status
		
		$aApprStatus = Geko_Wp_Enumeration_Query::getSet( 'geko-point-status' );
		$aAsFmt = array( '' => 'Show All Statuses' );
		foreach ( $aApprStatus as $oAs ) {
			$aAsFmt[ $oAs->getId() ] = $oAs->getTitle();
		}
		
		//
		return array(
			'pntevt' => $aPeFmt,
			'appst' => $aAsFmt
		);
	}
	
	
	// hook method
	public function modifyListingParams( $aParams ) {
		
		$aMergeParams = array (
			'kwsearch' => $_GET[ 's' ],
			'pntevt_slug' => $_GET[ 'pntevt' ],
			'orderby' => 'date_modified',
			'order' => 'DESC'
		);
		
		if ( $iAppSt = $_GET[ 'appst' ] ) {
			$aApprStatus = Geko_Wp_Enumeration_Query::getSet( 'geko-point-status' );
			$aMergeParams[ 'approve_status_id' ] = intval( $aApprStatus->getValueFromId( $iAppSt ) );
		}
		
		return array_merge( $aParams, $aMergeParams );
	}
	

	
	//// crud methods
	
	
	// update overrides
	
	//
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
			
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
		
	}
	
	
	
	// delete overrides
	
	
	//// helpers
		
	// award points
	public function awardPoints( $aValues ) {
		
		global $wpdb;
		
		$iUserId = trim( $aValues[ 'user_id' ] );
		$sEmail = trim( $aValues[ 'email' ] );
		$sPointEventSlug = trim( $aValues[ 'point_event_slug' ] );
		$iPointValue = intval( $aValues[ 'point_value' ] );
		$aMetaValues = ( is_array( $aValues[ 'meta' ] ) ) ? $aValues[ 'meta' ] : array() ;
		$sComments = trim( $aValues[ 'comments' ] );
		$bTestingOnly = trim( $aValues[ 'testing_only' ] );
		
		//// do checks
		
		$iErrorCode = NULL;
		$mRes = $this->hasPoints( $aValues, $iErrorCode );
		
		// return error code from hasPoints
		if ( !is_bool( $mRes ) ) return $mRes;
		
		if ( !$iUserId ) $iUserId = email_exists( $sEmail );
		
		$aPointEvents = Geko_Wp_Point_Event_Manage::getInstance()->getPointEvents();
		$oPointEvent = $aPointEvents[ $sPointEventSlug ];
		$iPointEventId = $oPointEvent->getId();
		
		// if point was already awarded, return reason why
		if ( TRUE === $mRes ) return $iErrorCode;
		
		// check the point value
		if ( $oPointEvent->getArbitraryPoints() && !$iPointValue ) {
			// can't award zero points
			return Geko_Wp_Point::getErrorCode( 'zero-points' );
		}
		
		//// calculations

		if ( !$oPointEvent->getArbitraryPoints() ) {
			$iPointValue = intval( $oPointEvent->getValue() );
		}
		
		// make negative if doing a deduction
		if ( $oPointEvent->getDeductPoints() ) {
			$iPointValue = $iPointValue * -1;
		}
		
		$aStatus = Geko_Wp_Enumeration_Query::getSet( 'geko-point-status' );
		
		$iApprovalStatusId = $aStatus->getValueFromSlug( 'geko-point-status-not-reviewed' );
		if ( !$oPointEvent->getRequiresApproval() ) {
			$iApprovalStatusId = $aStatus->getValueFromSlug( 'geko-point-status-approved' );
		}
		
		//// checks are good, award the point
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aInsert = array(
			'user_id' => $iUserId,
			'pntevt_id' => $iPointEventId,
			'value' => $iPointValue,
			'approve_status_id' => $iApprovalStatusId,
			'transaction_code' => $sPointEventSlug,
			'comments' => $sComments,
			'date_created' => $sDateTime,
			'date_modified' => $sDateTime
		);
		
		$aFormat = array( '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s' );
		
		
		// meta data
		
		$aMetaInsert = array();
		
		foreach ( $aMetaValues as $sMetaKey => $mMetaValue ) {
			$aMetaInsert[] = array(
				'mkey_id' => Geko_Wp_Options_MetaKey::getId( $sMetaKey ),
				'meta_value' => $mMetaValue
			);
		}
		
		
		//
		if ( $bTestingOnly ) {
			
			print_r( $aInsert );
			print_r( $aMetaInsert );
			
		} else {
			
			$wpdb->insert( $this->_sPrimaryTable, $aInsert, $aFormat );
			$iPointId = $wpdb->insert_id;
			
			if ( count( $aMetaInsert ) > 0 ) {
				foreach ( $aMetaInsert as $aVals ) {
					$aVals[ 'point_id' ] = $iPointId;
					$wpdb->insert(
						$wpdb->geko_point_meta,
						$aVals,
						array( '%d', '%s', '%d' )
					);
				}
			}
			
			// send notification, if applicable
			if ( $oPointEvent->getRequiresApproval() && $this->_sPointsApprovalEmsg ) {
				$oDeliverMail = new Geko_Wp_EmailMessage_Delivery( $this->_sPointsApprovalEmsg );
				$oDeliverMail
					->setMergeParams( array(
						'pointsApprovalLink' => sprintf( '%s?page=%s&point_id=%d', Geko_Uri::getUrl( 'wp_admin' ), $this->_sInstanceClass, $iPointId )
					) )
					->send()
				;
				
			}
		}
		
		return TRUE;
	}
	
	
	// check if user has points for the given event
	// this is to make sure that duplicate points are not awarded
	// returns a negative error code if any of the provided values are not good
	// will return boolean value (TRUE | FALSE) on an affirmative check
	public function hasPoints( $aValues, &$iErrorCode = NULL ) {
		
		$iUserId = trim( $aValues[ 'user_id' ] );
		$sEmail = trim( $aValues[ 'email' ] );
		$bValidEmail = FALSE;
		$sPointEventSlug = trim( $aValues[ 'point_event_slug' ] );
		$aMetaValues = ( is_array( $aValues[ 'meta' ] ) ) ? $aValues[ 'meta' ] : array() ;
		
		//// do checks
		
		// make sure user is valid
		if ( !$iUserId && !$sEmail ) {
			return Geko_Wp_Point::getErrorCode( 'invalid-user' );
		}
		
		if ( $iUserId && !$sEmail ) {
			if ( $oWpUser = get_userdata( $iUserId ) ) {
				$sEmail = $oWpUser->user_email;
				$bValidEmail = TRUE;
			} else {
				return Geko_Wp_Point::getErrorCode( 'invalid-user' );
			}
		}
		
		if ( $sEmail && !$bValidEmail ) {
			$iUserId = email_exists( $sEmail );
			if ( FALSE === $iUserId ) {
				return Geko_Wp_Point::getErrorCode( 'invalid-user' );
			}
		}
		
		// make sure valid point event slug was provided
		if ( !$sPointEventSlug ) {
			return Geko_Wp_Point::getErrorCode( 'invalid-slug' );
		}
		
		$aPtEvtMng = Geko_Wp_Point_Event_Manage::getInstance();
		$aPointEvents = $aPtEvtMng->getPointEvents();
		if ( !$oPointEvent = $aPointEvents[ $sPointEventSlug ] ) {
			return Geko_Wp_Point::getErrorCode( 'invalid-slug' );
		}
		
		// get user's existing points for the current points event
		$iPointEventId = $oPointEvent->getId();
		$aPoints = new Geko_Wp_Point_Query( array(
			'user_id' => $iUserId,
			'pntevt_id' => $iPointEventId,
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		// check if one-time only point event
		if ( $oPointEvent->getOneTimeOnly() && ( $aPoints->count() > 0 ) ) {
			$iErrorCode = Geko_Wp_Point::getErrorCode( 'one-time-only' );
			return TRUE;					// user already has the one-time point
		}
		
		// if not a one time only point event, check meta values and ensure it's unique
		// if the non-one-time point does not have any meta values,
		// 		no additional checks for uniqueness is performed
		
		$aPointIds = $aPoints->gatherId();
		
		if (
			( !$oPointEvent->getOneTimeOnly() ) && 
			( $sMetaKeys = trim( $oPointEvent->getMetaKeys() ) ) && 
			( count( $aPointIds ) > 0 )
		) {
			
			$oMetaMng = Geko_Wp_Point_Meta::getInstance();
			$aPointMeta = $oMetaMng->getMetaData( array(
				'parent_ids' => $aPoints->gatherId()
			) );
			
			
			// obtain/format valid meta-keys for comparison
			$aMetaKeys = explode( ',', $sMetaKeys );
			
			$aMetaKeysFmt = array();
			foreach ( $aMetaKeys as $sKey ) {
				if ( $sKey = trim( $sKey ) ) {
					$aMetaKeysFmt[] = $sKey;
				}
			}
			
			
			// do comparison
			foreach ( $aPointMeta as $aMeta ) {
				
				$bMatched = TRUE;
				
				foreach ( $aMetaKeysFmt as $sKey ) {
					$mValue = $aMeta[ $sKey ];
					if ( $aMetaValues[ $sKey ] && ( $aMetaValues[ $sKey ] != $mValue ) ) {
						$bMatched = FALSE;
						break;
					}
				}
				
				if ( $bMatched ) {
					$iErrorCode = Geko_Wp_Point::getErrorCode( 'duplicate' );
					return TRUE;			// matching point was found
				}
			}
		}
				
		// check if time limit is in effect
		$iTimeLimit = intval( $oPointEvent->getTimeLimit() );	
		$iMaxTimes = intval( $oPointEvent->getMaxTimes() );
		
		if ( $iTimeLimit ) {
			
			// calculate the beginning of time limit period, based on provided time limit value and units
			// period begins by taking the floor value of beginning of unix epoch divided by time limit units
			// this provides an absolute demarcation of time limit periods
			
			$iTimeLimitUnits = $oPointEvent->getTimeLimitUnits();
			$sUnit = $aPtEvtMng->getTimeLimitCode( $iTimeLimitUnits );
			
			if ( 'sec' == $sUnit ) $iSecs = $iTimeLimit;
			elseif ( 'min' == $sUnit ) $iSecs = $iTimeLimit * 60;
			elseif ( 'hr' == $sUnit ) $iSecs = $iTimeLimit * 60 * 60;
			elseif ( 'day' == $sUnit ) $iSecs = $iTimeLimit * 60 * 60 * 24;
			elseif ( 'wk' == $sUnit ) $iSecs = $iTimeLimit * 60 * 60 * 7;
			else $iSecs = 0;
			
			if ( $iSecs ) {
				$iCurPeriodTs = floor( time() / $iSecs ) * $iSecs;
			} else {
				$iStartYear = date( 'Y', 0 );					// beginning of unix epoch
				$iCurYear = date( 'Y' );
				$iCurMon = date( 'n' );
				if ( 'mon' == $sUnit ) {
					$iNumMons = ( floor( ( ( ( $iCurYear - $iStartYear - 1 ) * 12 ) + $iCurMon ) / $iTimeLimit ) * $iTimeLimit );
					$iPeriodYear = floor( $iNumMons / 12 ) + $iStartYear;
					$iPeriodMon = $iNumMons % 12;				// remainder months
					$iCurPeriodTs = strtotime( $iPeriodYear . '-' . $iPeriodMon );
				} elseif ( 'yr' == $sUnit ) {
					$iPeriodYear = ( floor( ( $iCurYear - $iStartYear ) / $iTimeLimit ) * $iTimeLimit ) + $iStartYear;
					$iCurPeriodTs = strtotime( $iPeriodYear . '-1' );
				}
			}
			
			// count how many point events occured during period
			$iPointCount = 0;
			foreach ( $aPoints as $oPoint ) {
				if ( strtotime( $oPoint->getDateTimeCreated() ) > $iCurPeriodTs ) {
					$iPointCount++;
				}
			}
			
			// check if the number of point events have been exceeded during the period
			if ( $iMaxTimes && ( $iPointCount >= $iMaxTimes ) ) {
				$iErrorCode = Geko_Wp_Point::getErrorCode( 'time-limit-in-effect' );
				return TRUE;
			}
			
		} else {
			// check if the number of point events have been exceeded
			if ( $iMaxTimes && ( $aPoints->count() >= $iMaxTimes ) ) {
				$iErrorCode = Geko_Wp_Point::getErrorCode( 'max-events-exceeded' );
				return TRUE;
			}
		}
				
		// point has not been awarded to user
		return FALSE;
	}
	
	
	//
	public function getPoints( $mUserIdOrEmail ) {
		
		global $wpdb;
		
		if ( preg_match( '/^[0-9]+$/', $mUserIdOrEmail ) ) {
			$iUserId = $mUserIdOrEmail;
			if ( !get_userdata( $iUserId ) ) {
				return FALSE;
			}
		} else {
			$sEmail = $mUserIdOrEmail;
			if ( !$iUserId = email_exists( $sEmail ) ) {
				return FALSE;
			}
		}
		
		$oPointsQuery = new Geko_Sql_Select();
		$oPointsQuery
			->field( 'SUM( CAST( p.value AS SIGNED ) )' )
			->from( $wpdb->geko_point, 'p' )
			->where( 'p.user_id = ?', $iUserId )
			->where( 'p.approve_status_id = 1' )		// hard-code for now
		;
		
		return intval( $wpdb->get_var( strval( $oPointsQuery ) ) );
	}
	
	
	// delete user
	public function deleteUser( $iUserId ) {
		
		global $wpdb;
		
		// clean up point table
		$oSqlDelete = new Geko_Sql_Delete();
		$oSqlDelete
			->from( $this->_sPrimaryTable )
			->where( 'user_id = ?', $iUserId )
		;
		$wpdb->query( strval( $oSqlDelete ) );
		
		// clean-up meta data table
		$wpdb->query("
			DELETE FROM		$wpdb->geko_point_meta
			WHERE			point_id NOT IN (
				SELECT			point_id
				FROM			$wpdb->geko_point
			)
		");
		
	}
	
	
}


