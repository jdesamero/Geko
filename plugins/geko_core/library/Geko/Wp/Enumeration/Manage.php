<?php

//
class Geko_Wp_Enumeration_Manage extends Geko_Wp_Options_Manage
{
	private static $aInstances = array();
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing
	
	protected $_sEntityIdVarName = 'enum_id';
	
	protected $_sSubject = 'Enumeration';
	protected $_sDescription = 'Enumerations allow for the creation of ad-hoc sets of labels. For use with select boxes or checkbox groups.';
	protected $_sIconId = 'icon-options-general';
	
	protected $_aJsParams = array(
		'row_template' => array(
			'enum-itm' => array(
				'sortable' => array(),
				'toggle_column' => array()
			)
		)
	);
	
	protected $_sType = 'enum';
	protected $_sNestedType = 'enum-itm';
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_enumeration', 'e' )
			->fieldInt( 'enum_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'title', array( 'size' => 256 ) )
			->fieldVarChar( 'slug', array( 'size' => 255, 'unq' ) )
			->fieldLongText( 'value' )
			->fieldLongText( 'description' )
			->fieldLongText( 'params' )
			->fieldInt( 'parent_id', array( 'unsgnd', 'key' ) )
			->fieldInt( 'rank', array( 'unsgnd' ) )
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
	
	// hook method
	public function modifyListingParams( $aParams ) {
		$aParams[ 'is_root' ] = TRUE;
		return $aParams;
	}
	
	//
	public function modifySubEntityParams( $aParams ) {
		$aParams[ 'orderby' ] = 'rank';
		$aParams[ 'order' ] = 'ASC';
		return $aParams;	
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
		<td class="column-slug"><?php $oEntity->echoSlug(); ?></td>
		<td class="column-description"><?php $oEntity->echoDescription(); ?></td>
		<?php
	}
	
	
	
	//
	public function formFields() {
		
		?>
		<h3><?php echo $this->_sSubject; ?> Options</h3>
		<style type="text/css">
			
			.enum-itm-col_value,
			.enum-itm-col_description,
			.enum-itm-col_params {
				display: none;
			}
			
			.enum-itm_title,
			.enum-itm_slug {
				width: 275px;
			}
			
			th.enum-itm-col_del,
			td.enum-itm-col_del {
				width: 30px;
			}
			
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}
			
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="enum_title">Title</label></th>
				<td>
					<input id="enum_title" name="enum_title" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="enum_slug">Slug</label></th>
				<td>
					<input id="enum_slug" name="enum_slug" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<?php $this->customFieldsPre(); ?>
			<tr>
				<th><label for="enum_description">Description</label></th>
				<td>
					<textarea cols="30" rows="5" id="enum_description" name="enum_description" />
				</td>
			</tr>
			<tr>
				<th><label for="enum-itm">Items</label></th>
				<td class="multi_row enum-itm">
					<a href="#" id="enum-itm-tc_value" class="enum-itm_toggle_column">Show Value Column</a> &nbsp; | &nbsp; 
					<a href="#" id="enum-itm-tc_description" class="enum-itm_toggle_column">Show Description Column</a> &nbsp; | &nbsp; 
					<a href="#" id="enum-itm-tc_params" class="enum-itm_toggle_column">Show Params Column</a>
					<table id="enum_items_table">
						<thead>
							<tr>
								<th class="enum-itm-col_del"></th>
								<th class="enum-itm-col_title sort">Title</th>
								<th class="enum-itm-col_slug sort">Slug</th>
								<th class="enum-itm-col_value sort">Value</th>
								<th class="enum-itm-col_description">Description</th>
								<th class="enum-itm-col_params">Params</th>
							</tr>
						</thead>
						<tbody>
							<tr class="row" _row_template="enum-itm">
								<td class="enum-itm-col_del">
									<a href="#" class="del_row">Del</a>
									<input type="hidden" id="enum-itm[][rank]" name="enum-itm[][rank]" class="enum-itm_rank" />
								</td>
								<td class="enum-itm-col_title"><input type="text" id="enum-itm[][title]" name="enum-itm[][title]" class="enum-itm_title" /></td>
								<td class="enum-itm-col_slug"><input type="text" id="enum-itm[][slug]" name="enum-itm[][slug]" class="enum-itm_slug" /></td>
								<td class="enum-itm-col_value"><input type="text" id="enum-itm[][value]" name="enum-itm[][value]" class="enum-itm_value" /></td>
								<td class="enum-itm-col_description"><textarea id="enum-itm[][description]" name="enum-itm[][description]" class="enum-itm_description"></textarea></td>
								<td class="enum-itm-col_params"><input type="text" id="enum-itm[][params]" name="enum-itm[][params]" class="enum-itm_params" /></td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="button" value="Add" class="add_row" class="button-primary" /></p>					
				</td>
			</tr>
			<?php $this->customFieldsMain(); ?>
		</table>
		<?php
		
		$this->parentEntityField();
		
	}
	
	
	
	//// main crud methods
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
				
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'title' ];
		$aValues[ 'slug' ] =  Geko_Wp_Db::generateSlug(
			$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
		);
		
		return $aValues;
		
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
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
				
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'title' ];
		if ( $aValues[ 'slug' ] != $oEntity->getSlug() ) {
			$aValues[ 'slug' ] =  Geko_Wp_Db::generateSlug(
				$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
			);
		}
		
		return $aValues;
	}
	
	
	
	// delete methods
	
	// hook method
	public function postDeleteAction( $aParams, $oEntity ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		// ??? is this supposed to be used for something ???
		// $oPk = $this->getPrimaryTablePrimaryKeyField();
		
		$oDb->delete( $this->_sPrimaryTable, array(
			'parent_id = ?' => $oEntity->getId()
		) );
	}
	
	
	//// sub crud methods
	
	//
	public function modifySubInsertData( $aValues, $aParams ) {
		
		$aValues[ 'slug' ] = ( $aValues[ 'slug' ] ) ? $aValues[ 'slug' ] : $aValues[ 'title' ];
		$aValues[ 'slug' ] = Geko_Wp_Db::generateSlug( $aValues[ 'slug' ], $this->getPrimaryTable(), 'slug' );
		
		return $aValues;
	}
	
	//
	public function modifySubUpdateData( $aValues, $aParams, $oEntity ) {
		
		$sCurSlug = $oEntity->getSlug();
		$aValues[ 'slug' ] = ( $aValues[ 'slug' ] ) ? $aValues[ 'slug' ] : $aValues[ 'title' ];
		
		if ( $sCurSlug != $aValues[ 'slug' ] ) {
			// slug was changed, ensure it's unique
			$aValues[ 'slug' ] = Geko_Wp_Db::generateSlug( $aValues[ 'slug' ], $this->getPrimaryTable(), 'slug' );
		}
		
		return $aValues;
	}
	
	
	
	//// db helpers
	
	//
	public function populate( $aParams ) {
	
		if (
			( is_array( $aParams ) ) &&
			( count( $aParams ) > 0 )
		) {
			
			$oDb = Geko_Wp::get( 'db' );
			
			$aParent = $aParams[ 0 ];
			$aChildren = $aParams[ 1 ];
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'e.enum_id', 'enum_id' )
				->from( '##pfx##geko_enumeration', 'e' )
				->where( 'e.slug = ?', $aParent[ 'slug' ] )
			;
			
			if ( !$oDb->fetchOne( strval( $oQuery ) ) ) {
				
				$oDb->insert( '##pfx##geko_enumeration', $aParent );
				$iLastInsertId = $oDb->lastInsertId();
				
				foreach ( $aChildren as $aChild ) {
					$aChild[ 'parent_id' ] = $iLastInsertId;
					$oDb->insert( '##pfx##geko_enumeration', $aChild );					
				}
				
			}
			
		}
		
		return $this;
	}
	
	
	
	
}

