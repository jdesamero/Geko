<?php

//
class GekoX_Test_Wp_Full_Manage extends Geko_Wp_Options_Manage
{
	private static $aTypes;
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'test_id';
	
	protected $_sSubject = 'Test Entity';
	protected $_sListingTitle = 'Subject';	
	protected $_sDescription = 'This is for testing only.';
	protected $_sIconId = 'icon-options-general';
	protected $_sType = 'test';
	
	protected $_iEntitiesPerPage = 10;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		Geko_Wp_Enumeration_Manage::getInstance()->add();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_test_entity', 't' )
			->fieldBigInt( 'test_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'title' )
			->fieldVarChar( 'slug', array( 'size' => 256, 'unq' ) )
			->fieldFloat( 'percent', array( 'size' => '7,4' ) )
			->fieldLongText( 'description' )
			->fieldLongText( 'notes' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
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
	
	
	
	
	
	//// front-end display methods
	
	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-slug">Slug</th>
		<th scope="col" class="manage-column column-percent">Percent</th>
		<th scope="col" class="manage-column column-date">Date Created</th>
		<th scope="col" class="manage-column column-date">Date Modified</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		?>
		<td class="column-slug"><?php $oEntity->echoSlug(); ?></td>
		<td class="column-percent"><?php $oEntity->echoPercent(); ?></td>
		<td class="date column-date"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
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
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="test_title">Title</label></th>
				<td>
					<input id="test_title" name="test_title" type="text" class="regular-text" value="" /><br />
					<span class="description">Subject of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<tr>
				<th><label for="test_slug">Slug</label></th>
				<td>
					<input id="test_slug" name="test_slug" type="text" class="regular-text" value="" /><br />
					<span class="description">Slug of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<tr>
				<th><label for="test_percent">Percent</label></th>
				<td>
					<input id="test_percent" name="test_percent" type="text" class="regular-text" value="" /><br />
					<span class="description">Percentage of the entity.</span>
				</td>
			</tr>
			<?php $this->customFieldsPre(); ?>
			<tr>
				<th><label for="test_description">Description</label></th>
				<td>
					<textarea cols="30" rows="5" id="test_description" name="test_description" /><br />
					<span class="description">Description of the test entity.</span>
				</td>
			</tr>
			<tr>
				<th><label for="test_notes">Notes</label></th>
				<td>
					<textarea cols="30" rows="5" id="test_notes" name="test_notes" /><br />
					<span class="description">Notes for the test entity.</span>
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
	public function getInsertData( $aParams ) {
		
		list( $aValues, $aFormat ) = parent::getInsertData( $aParams );
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'title' ];
		$aValues[ 'slug' ] =  Geko_Wp_Db::generateSlug(
			$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
		);
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return array( $aValues, $aFormat );
		
	}
	
	//
	public function getInsertContinue( $aInsertData, $aParams ) {
		
		$bContinue = parent::getInsertContinue( $aInsertData, $aParams );
		
		list( $aValues, $aFormat ) = $aInsertData;
		
		//// do checks
		
		$sTitle = $aValues[ 'title' ];
		
		// check title
		if ( $bContinue && !$sTitle ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty title was given
		}
		
		return $bContinue;
		
	}
	
	
	
	// update overrides
	
	//
	public function getUpdateData( $aParams, $oEntity ) {
		
		list( $aValues, $aWhere, $aFormat, $aWhereFmt ) = parent::getUpdateData( $aParams, $oEntity );
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'title' ];
		if ( $aValues[ 'slug' ] != $oEntity->getSlug() ) {
			$aValues[ 'slug' ] = Geko_Wp_Db::generateSlug(
				$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
			);
		}
			
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return array( $aValues, $aWhere, $aFormat, $aWhereFmt );
	}
	
	
	
	// delete overrides
	
	
	
	
}



