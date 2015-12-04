<?php

//
class Geko_Wp_Point_Event_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'pntevt_id';
	
	protected $_sSubject = 'Point Event';
	protected $_sDescription = 'Events in which points can be assigned to.';
	protected $_sIconId = 'icon-users';
	protected $_sType = 'pntevt';
	
	protected $_sShortCode = 'pointevent';
	
	private $aPointEvents = NULL;
	
	private $aTimeLimitUnits = array(
		0 => array( 'sec', 'Second(s)' ),
		1 => array( 'min', 'Minute(s)' ),
		2 => array( 'hr', 'Hour(s)' ),
		3 => array( 'day', 'Day(s)' ),
		4 => array( 'wk', 'Week(s)' ),
		5 => array( 'mon', 'Month(s)' ),
		6 => array( 'yr', 'Year(s)' )
	);
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_point_event', 'e' )
			->fieldBigInt( 'pntevt_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'name', array( 'size' => 256 ) )
			->fieldVarChar( 'slug', array( 'size' => 255, 'unq' ) )
			->fieldBigInt( 'value', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'description' )
			->fieldLongText( 'meta_keys' )
			->fieldBool( 'requires_approval' )
			->fieldBool( 'one_time_only' )
			->fieldBigInt( 'max_times', array( 'unsgnd', 'notnull' ) )
			->fieldBool( 'arbitrary_points' )
			->fieldBool( 'deduct_points' )
			->fieldInt( 'time_limit' )
			->fieldInt( 'time_limit_units' )
			->fieldInt( 'ip_limit' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
		
	}
	
	
	
	
	//// implement actions
	
	//
	public function getPointEvents() {
		
		if ( NULL === $this->aPointEvents ) {
			$aPointEvents = new $this->_sQueryClass( array(
				'showposts' => -1,
				'posts_per_page' => -1
			), FALSE );
			foreach ( $aPointEvents as $oPointEvent ) {
				$this->aPointEvents[ $oPointEvent->getSlug() ] = $oPointEvent;
			}
		}
		
		return $this->aPointEvents;
	}
	
	//
	public function applyShortCode( $aAtts ) {
		
		$aPointEvents = $this->getPointEvents();
		
		if ( $oPointEvent = $aPointEvents[ $aAtts[ 'slug' ] ] ) {
			return $oPointEvent->getValue();
		}
		
		return 0;
	}
	
	//
	public function getTimeLimitCode( $iCodeId ) {
		return $this->aTimeLimitUnits[ $iCodeId ][ 0 ];
	}
	
	
	
	//// front-end display methods
	
	//
	public function listingPage() {
		?>
		<style type="text/css">
			
			th.column-slug,
			td.column-slug {
				width: auto !important;
			}

			th.column-description,
			td.column-description {
				width: 30%;
			}
			
		</style>		
		<?php
		parent::listingPage();
	}
	
	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-slug">Slug</th>
		<th scope="col" class="manage-column column-value">Value</th>
		<th scope="col" class="manage-column column-description">Description</th>
		<th scope="col" class="manage-column column-date-created">Date Created</th>
		<th scope="col" class="manage-column column-date-modified">Date Modified</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		?>
		<td class="column-slug"><?php $oEntity->echoSlug(); ?></td>
		<td class="column-value"><?php $oEntity->echoTheValue(); ?></td>
		<td class="column-description"><?php $oEntity->echoTheExcerpt( 100 ); ?></td>
		<td class="date column-date-created"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date-modified"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
		<?php
	}
	
	
	//
	public function formFields() {
		
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
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="pntevt_name">Name</label></th>
				<td>
					<input id="pntevt_name" name="pntevt_name" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="pntevt_slug">Slug</label></th>
				<td>
					<input id="pntevt_slug" name="pntevt_slug" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="pntevt_value">Value</label></th>
				<td>
					<input id="pntevt_value" name="pntevt_value" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<?php $this->customFieldsPre(); ?>
			<tr>
				<th><label for="pntevt_description">Description</label></th>
				<td>
					<textarea cols="30" rows="5" id="pntevt_description" name="pntevt_description" />
				</td>
			</tr>
			<tr>
				<th><label for="pntevt_meta_keys">Meta Keys</label></th>
				<td>
					<textarea cols="30" rows="5" id="pntevt_meta_keys" name="pntevt_meta_keys" />
				</td>
			</tr>
			<tr>
				<th><label for="pntevt_requires_approval">Requires Approval</label></th>
				<td>
					<input id="pntevt_requires_approval" name="pntevt_requires_approval" type="checkbox" value="1" />
				</td>
			</tr>
			<tr>
				<th><label for="pntevt_one_time_only">One-Time Only</label></th>
				<td>
					<input id="pntevt_one_time_only" name="pntevt_one_time_only" type="checkbox" value="1" />
				</td>
			</tr>
			<tr>
				<th><label for="pntevt_max_times">Max Times</label></th>
				<td>
					<input id="pntevt_max_times" name="pntevt_max_times" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="pntevt_time_limit">Time Limit</label></th>
				<td>
					<input id="pntevt_time_limit" name="pntevt_time_limit" type="text" class="regular-text short" value="" />
					<select id="pntevt_time_limit_units" name="pntevt_time_limit_units">
						<?php foreach ( $this->aTimeLimitUnits as $i => $aUnits ): ?>
							<option value="<?php echo $i; ?>"><?php echo $aUnits[ 1 ]; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="pntevt_ip_limit">Daily IP Limit</label></th>
				<td><input id="pntevt_ip_limit" name="pntevt_ip_limit" type="text" class="regular-text short" value="" /></td>
			</tr>
			<tr>
				<th><label for="pntevt_arbitrary_points">Arbitrary Points</label></th>
				<td>
					<input id="pntevt_arbitrary_points" name="pntevt_arbitrary_points" type="checkbox" value="1" />
				</td>
			</tr>
			<tr>
				<th><label for="pntevt_deduct_points">Deduct Points</label></th>
				<td>
					<input id="pntevt_deduct_points" name="pntevt_deduct_points" type="checkbox" value="1" />
				</td>
			</tr>
			<?php $this->customFieldsMain(); ?>
		</table>
		<?php
		
		$this->parentEntityField();
		
	}
	
	
	
	
	
	//// crud methods
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'name' ];
		$aValues[ 'slug' ] =  Geko_Wp_Db::generateSlug(
			$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
		);
		
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		if ( !isset( $aValues[ 'requires_approval' ] ) ) $aValues[ 'requires_approval' ] = 0;
		if ( !isset( $aValues[ 'one_time_only' ] ) ) $aValues[ 'one_time_only' ] = 0;
		
		return $aValues;
		
	}
	
	//
	public function getInsertContinue( $aInsertData, $aParams ) {
		
		$bContinue = parent::getInsertContinue( $aInsertData, $aParams );
		
		list( $aValues, $aFormat ) = $aInsertData;
		
		//// do checks
		
		$sTitle = $aValues[ 'name' ];
		
		// check title
		if ( $bContinue && !$sTitle ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty title was given
		}
		
		return $bContinue;
		
	}
	
	
	
	// update overrides
	
	//
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'name' ];
		if ( $aValues[ 'slug' ] != $oEntity->getSlug() ) {
			$aValues[ 'slug' ] = Geko_Wp_Db::generateSlug(
				$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
			);
		}
		
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		if ( !isset( $aValues[ 'requires_approval' ] ) ) $aValues[ 'requires_approval' ] = 0;
		if ( !isset( $aValues[ 'one_time_only' ] ) ) $aValues[ 'one_time_only' ] = 0;
		
		return $aValues;
		
	}
	
	
	
	// delete overrides
	
	
	
}

