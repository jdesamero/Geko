<?php
/*
 * "geko_navigation_management/includes/library/Geko/Wp/NavigationManagement/PluginAdmin.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_PluginAdmin extends Geko_Wp_Plugin_Admin
{
	protected $_sMenuTitle = 'Navigation';

	protected $_oPageManager;
	protected $_oNavContainer;
	protected $_aPageTypes = array();
	
	protected $_iAddIndex;
	
	
	
	
	//
	public function initPageManager() {
		
		static $bCalled = FALSE;
		
		if ( !$bCalled ) {
			
			$this->setPrefixFormElems( FALSE );
					
			if ( isset( $_GET[ 'grp' ] ) ) {
				
				$iGrpId = intval( $_GET[ 'grp' ] );
				
				$aNavParams = Geko_Json::decode( $this->getOption( sprintf( 'gp_%d', $iGrpId ) ) );
				$aNavParams = apply_filters(
					'admin_geko_wp_nav_load_group',
					$aNavParams,
					__CLASS__
				);
				
			} else {
				$aNavParams = array();
			}
			
			$this->_oNavContainer = $oNavContainer = new Zend_Navigation(
				Geko_Navigation_Renderer::filterNavParams( $aNavParams )
			);
			
			
			// assign page manager
			$oPageManager = NULL;
			
			if ( has_filter( 'admin_geko_wp_nav_get_page_manager' ) ) {
				
				$oPageManager = apply_filters( 'admin_geko_wp_nav_get_page_manager' );
			
			} else {
				
				// default
				$oPageManager = new Geko_Navigation_PageManager();
			}
			
			$this->_oPageManager = $oPageManager;
			
			
			
			// assign page types
			if ( has_filter( 'admin_geko_wp_nav_get_page_types' ) ) {
				
				$aPageTypes = apply_filters( 'admin_geko_wp_nav_get_page_types' );
				
			} else {
				
				// default
				$aPageTypes = array(
					array( 'type' => 'Geko_Navigation_Page_Uri' ),
					array( 'type' => 'Geko_Wp_NavigationManagement_Page_Home' ),
					array( 'type' => 'Geko_Wp_NavigationManagement_Page_Page' ),
					array( 'type' => 'Geko_Wp_NavigationManagement_Page_Category' ),
					array( 'type' => 'Geko_Wp_NavigationManagement_Page_CustomType' ),
					array( 'type' => 'Geko_Wp_NavigationManagement_Page_Author' ),
					array( 'type' => 'Geko_Wp_NavigationManagement_Page_Post' )
				);
			}
			
			
			// merge page types from config
			//var_dump( @class_exists( 'Geko_Navigation_Page_Foo' ) );
			$aConfigPageTypes = explode( "\n", $this->getOption( 'page_types' ) );
			$aMergePageTypes = array();
			
			foreach ( $aConfigPageTypes as $sConfigPageType ) {
				
				$aBreak = explode( '|', $sConfigPageType );
				$aBreakFormat = array();
				
				foreach ( $aBreak as $i => $sElem ) {
					$sElem = trim( $sElem );
					if ( $sElem ) {
						$aElem = explode( ':', $sElem );
						if ( count( $aElem ) == 1 ) {
							$aBreakFormat[ 'type' ] = $aElem[ 0 ];
						} elseif ( count( $aElem ) == 2 ) {
							$aBreakFormat[ $aElem[ 0 ] ] = $aElem[ 1 ];
						}						
					}
				}
				
				if ( Geko_Class::isSubclassOf( $aBreakFormat[ 'type' ], 'Zend_Navigation_Page' ) ) {
					$aMergePageTypes[] = $aBreakFormat;
				}	
			}
			
			
			$aPageTypes = array_merge( $aPageTypes, $aMergePageTypes );
			
			// remove duplicate types
			$aCheck = array();
			foreach ( $aPageTypes as $aPageType ) {
				
				$aCheck[ $aPageType[ 'type' ] ] = $aPageType;
			}
			
			$this->_aPageTypes = array_values( $aCheck );
			
			
			// page manager
			
			$oPageManager
				->setPageTypes( $this->_aPageTypes )
				->init()
			;
			
			$oPageManager = apply_filters( 'admin_geko_wp_nav_configure_page_manager', $oPageManager );
			
			
			$bCalled = TRUE;
		}
		
	}
	
	
	
	//// accessors
	
	//
	public function getPageManager() {
		return $this->_oPageManager;
	}
	
	//
	public function getRedirect( $sOp, $iNavGrpIdx ) {
		
		$oUrl = new Geko_Uri( parent::getUrl() );
		
		if ( is_int( $iNavGrpIdx ) ) {
			$oUrl->setVar( 'grp', $iNavGrpIdx );
		}
		
		if ( 'delete' == $sOp ) {
			$oUrl->unsetVar( 'grp' );		
		}
		
		if ( 'add' == $sOp ) {
			$oUrl->setVar( 'grp', $this->_iAddIndex );		
		}
		
		$oUrl = apply_filters( 'admin_geko_wp_nav_redirect', $oUrl );
		
		return strval( $oUrl );
	}
	
	
	
	
	
	
	
	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();
		
		$this->initPageManager();
		
		wp_enqueue_style( 'geko_wp_navigationmanagement' );
		
		$aPlugins = $this->_oPageManager->getPlugin();
		foreach ( $aPlugins as $oPlugin ) {
			wp_enqueue_script( strtolower( get_class( $oPlugin ) ) );
		}
		
		return $this;
	}
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		$this->initPageManager();
		$this->setMenuPlacement( self::MANAGEMENT );
		
		return $this;
	}
	
	
	
	
	
	//
	protected function outputInit() {
		
		$this->initPageManager();
		
		$this->_oPageManager
			->disablePrefixingForParams( 'form_action', 'result_field' )
			->setNavContainer( $this->_oNavContainer )
			->setParam( 'result_field', 'serialized_data' )
		;
	}
	
	//
	public function getNavData() {
		
		$this->outputInit();
		
		return $this->_oPageManager->getNavData();
	}
	
	
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		$this->outputInit();
		
		$this->_oPageManager->setInjectParam( array(
			'transparent_img' => sprintf( '%s/styles/img/trans.png', $this->getPluginUrl() ),
			'loader_img' => sprintf( '%s/styles/img/ajax-loader.gif', $this->getPluginUrl() )
		) );
		
		
		
		$aJsonParams = array( 'script' => Geko_Wp::getScriptUrls() );
		
		if ( isset( $_GET[ 'grp' ] ) ) {

			$iGrpId = intval( $_GET[ 'grp' ] );
			
			$aJsonGetParams = array( 'grp' => $iGrpId );
			$aJsonGetParams = apply_filters( 'admin_geko_wp_nav_get_params', $aJsonGetParams );
			
			$aJsonParams[ 'get_params' ] = $aJsonGetParams;
		}
		
		
		?>			
		<style type="text/css">
			
			<?php $this->_oPageManager->outputStyle(); ?>
						
		</style>
		
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Geko_Json::encode( $aJsonParams ); ?>;
				var oGetParams = oParams.get_params;
				
				
				// only do this if a group is selected
				if ( oGetParams ) {
					
					oGetParams[ '_service' ] = 'Geko_Wp_NavigationManagement_Service';
					oGetParams[ '_action' ] = 'load_data';
					
					$.gekoNavigationPageManager.load( oParams );
				}	
				
				
				
				//// active all the time
				
				$( '#nav_gs_toggle' ).on( 'click', function() {
					
					var eToggle = $( this );
					
					$( '#nav_form_div' ).toggle( 500 );
					
					if ( eToggle.hasClass( 'exp' ) ) {
						eToggle.addClass( 'ctd' ).removeClass( 'exp' );
					} else {
						eToggle.addClass( 'exp' ).removeClass( 'ctd' );					
					}
					
					return false;
				} );				
				
				
				//// nav group form
				
				var eNavGrpForm = $( '#geko_manage_nav_group' );
				
				eNavGrpForm.submit( function( e ) {
					
					return false;
					
				} );
				
				var cPerformOp = function( sOp ) {
					
					$.post(
						oParams.script.process,
						'%s&_service=Geko_Wp_NavigationManagement_Service&_action=nav_group&ops=%s'.printf( eNavGrpForm.serialize(), sOp ),
						function( res ) {
							window.location = res.redirect;
						},
						'json'
					);
					
				};
				
				
				// crud operations
				
				if ( oGetParams ) {
					
					eNavGrpForm.find( '#ops_delete' ).click( function() {
						
						if ( confirm( 'Are you sure?' ) ) {
							cPerformOp( 'Delete' );
						}
						
						return false;
					} );

					eNavGrpForm.find( '#ops_edit' ).click( function() {
						cPerformOp( 'Edit' );
						return false;
					} );
					
				} else {
					
					eNavGrpForm.find( '#ops_add' ).click( function() {
						cPerformOp( 'Add' );
						return false;
					} );					
				}
				
			} );
			
		</script>
		
		<?php
		
		return $this;
	}
	
	
	//
	protected function outputInnerForm() {
		
		$aNavGroups = Geko_Json::decode( $this->getOption( 'groups' ) );
		
		$bHasGroup = FALSE;
		$iGrpId = NULL;
		
		if ( isset( $_GET[ 'grp' ] ) ) {
			
			$bHasGroup = TRUE;
			$iGrpId = intval( $_GET[ 'grp' ] );
			
			$sCurGroupName = $aNavGroups[ $iGrpId ][ 'label' ];
			$sCurGroupCode = $aNavGroups[ $iGrpId ][ 'code' ];
		
		} else {
			
			$sCurGroupName = '';
			$sCurGroupCode = '';
		}
		
		?>
		
		<table class="settings">
			<tr>
				<td>

					<h3>Navigation Groups</h3>
					
					<?php if ( is_array( $aNavGroups ) ): ?>
						
						<ul>
							<?php foreach( $aNavGroups as $i => $aGroup ): ?>
								<li><a href="<?php echo $this->getUrl(); ?>&grp=<?php echo $i; ?>"><?php echo $aGroup[ 'label' ]; ?></a></li>
							<?php endforeach; ?>
						</ul>
						
					<?php else: ?>
						
						(You have no navigation groups created.)
						
					<?php endif; ?>
					
					<br />
					
					<?php if ( $bHasGroup ): ?>
						<h3>Edit Navigation Group</h3>
					<?php else: ?>
						<h3>Add Navigation Group</h3>
					<?php endif; ?>
					
					<form id="geko_manage_nav_group">
						
						Group Label: <input type="text" id="nav_group_label" name="nav_group_label" value="<?php echo $sCurGroupName; ?>" /><br />
						Group Code: <input type="text" id="nav_group_code" name="nav_group_code" value="<?php echo $sCurGroupCode; ?>" /><br />
						
						<br />
						<br />
						
						<?php if ( $bHasGroup ): ?>
							<input type="hidden" id="nav_group_idx" name="nav_group_idx" value="<?php echo $iGrpId; ?>" />
							<input type="submit" id="ops_edit" name="ops" value="Edit" /> &nbsp;  
							<input type="submit" id="ops_delete" name="ops" value="Delete" /> &nbsp;  
							<a href="<?php echo $this->getUrl(); ?>">Add New</a> 
						<?php else: ?>
							<input type="submit" id="ops_add" name="ops" value="Add" /> 
						<?php endif; ?>
						
					</form>
					
					<br />
					
					<?php if ( $bHasGroup ): ?>
					
						<h3>Legend</h3>
						<?php $this->_oPageManager->outputLegendHtml(); ?>
						<br />
						
						<?php do_action( 'admin_geko_wp_nav_left' ); ?>
						
					<?php endif; ?>
					
					<h3>General Settings <a href="#" id="nav_gs_toggle" class="exp"></a></h3>
					
					<div id="nav_form_div">
						<?php
							$this->setPrefixFormElems( TRUE );
							parent::outputInnerForm();
							$this->setPrefixFormElems( FALSE );
						?>
					</div>
					
				</td>
				<td>
					<?php if ( $bHasGroup ): ?>
						
						<h3>Editing: <?php echo $sCurGroupName; ?></h3>
						
						<?php $this->_oPageManager->outputFormTagHtml(); ?>
							<?php do_action( 'admin_geko_wp_nav_hidden_fields' ); ?>
							<input type="hidden" id="nav_group_idx" name="nav_group_idx" value="<?php echo $iGrpId; ?>" />
							<?php $this->_oPageManager->outputDragSortHtml(); ?>
						</form>
						
						<?php $this->_oPageManager->outputOptionsFormHtml(); ?>
					
					<?php else: ?>
						Please select a navigation group.
					<?php endif; ?>
				</td>
			</tr>
		</table>
		
		<?php
	}
	
	
	//
	protected function formFields() {
		?>
		<label for="page_types">Additional Page Types:</label><br />
		<textarea id="page_types" class="page_types"></textarea><br />
		<div class="note">(Do not touch this unless you know exactly what you're doing!)</div>
		<?php
	}
	
	
	
	
	
	//
	public function saveNavGroup( $sOp, $iNavGrpIdx, $sLabel, $sCode ) {
		
		$this->setMenuPlacement( self::MANAGEMENT );
		
		$sNavGrpIdxPfx = sprintf( 'gp_%d', $iNavGrpIdx );
		
		$aNavGroups = Geko_Json::decode( $this->getOption( 'groups' ) );
		
		if ( !is_array( $aNavGroups ) ) {
			$aNavGroups = array();
		}
		
		$aParams = array(
			'label' => $sLabel,
			'code' => $sCode
		);
		
		if ( 'edit' == $sOp ) {
			
			// edit a group entry
			$aNavGroups[ $iNavGrpIdx ] = $aParams;
		
		} elseif ( 'add' == $sOp ) {
			
			// create a group entry
			$aNavGroups[] = $aParams;
			
			// create a default group entry
			end( $aNavGroups );
			$iIndex = key( $aNavGroups );
			
			$aDefaultEntry = array( array(
				'type' => 'Geko_Navigation_Page_Uri',
				'uri' => '/index.php'
			) );
			
			$aDefaultEntry = apply_filters(
				'admin_geko_wp_nav_new_group',
				$aDefaultEntry
			);
			
			$this->updateOption(
				sprintf( 'gp_%d', $iIndex ),
				Geko_Json::encode( $aDefaultEntry )
			);
			
			// so that page re-directs to the newly added nav group
			$this->_iAddIndex = $iIndex;
			
		} elseif ( 'delete' == $sOp ) {
			
			unset( $aNavGroups[ $iNavGrpIdx ] );
			$this->deleteOption( $sNavGrpIdxPfx );
			unset( $iNavGrpIdx );
			
		}
		
		$this->updateOption( 'groups', Geko_Json::encode( $aNavGroups ) );
		
		
		// die();
	}
	
	
	//
	public function saveNavData( $iNavGrpIdx, $sSerializedData ) {

		$this->setMenuPlacement( self::MANAGEMENT );
		
		$sNavGrpIdxPfx = sprintf( 'gp_%d', $iNavGrpIdx );
		
		
		$this->initPageManager();
		$this->_oPageManager
			->procSerializedData( $sSerializedData )
		;
		
		$aNavParams = Geko_Json::decode( $this->getOption( $sNavGrpIdxPfx ) );
		$aNavParams = apply_filters(
			'admin_geko_wp_nav_save_group',
			$this->_oPageManager->getNavParams(),
			$aNavParams
		);
		
		$this->updateOption( $sNavGrpIdxPfx, Geko_Json::encode( $aNavParams ) );
		
	}
	
	
	
}


