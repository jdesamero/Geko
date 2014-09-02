<?php

//
class Geko_Wp_NavigationManagement_PluginAdmin extends Geko_Wp_Plugin_Admin
{
	protected $_sMenuTitle = 'Navigation';

	protected $oPageManager;
	protected $oNavContainer;
	protected $aPageTypes = array();
	
	protected $iAddIndex;
	
	
	//
	public function initPageManager() {
		
		static $bCalled = FALSE;
		
		if ( !$bCalled ) {
			
			$this->setPrefixFormElems( FALSE );
					
			if ( isset( $_GET[ 'grp' ] ) ) {
				
				$iGrpId = intval( $_GET[ 'grp' ] );
				
				$aNavParams = Zend_Json::decode( $this->getOption( sprintf( 'gp_%d', $iGrpId ) ) );
				$aNavParams = apply_filters(
					'admin_geko_wp_nav_load_group',
					$aNavParams,
					__CLASS__
				);
				
			} else {
				$aNavParams = array();
			}
			
			$this->oNavContainer = $oNavContainer = new Zend_Navigation(
				Geko_Navigation_Renderer::filterNavParams( $aNavParams )
			);
			
			// assign page manager
			if ( has_filter( 'admin_geko_wp_nav_get_page_manager' ) ) {
				$this->oPageManager = $oPageManager = apply_filters( 'admin_geko_wp_nav_get_page_manager' );
			} else {
				// default
				$this->oPageManager = $oPageManager = new Geko_Navigation_PageManager();
			}
			
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
							$aBreakFormat[ 'type' ] = $aElem[0];
						} elseif ( count( $aElem ) == 2 ) {
							$aBreakFormat[ $aElem[0] ] = $aElem[1];
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
				if ( !$aCheck[ $aPageType[ 'type' ] ] ) {
					$this->aPageTypes[] = $aPageType;
					$aCheck[ $aPageType[ 'type' ] ] = TRUE;
				}
			}
			
			////
			
			$oPageManager
				->setPageTypes( $this->aPageTypes )
				->init()
			;
			
			$oPageManager = apply_filters( 'admin_geko_wp_nav_configure_page_manager', $oPageManager );
			
			$bCalled = TRUE;
		}
		
	}
	
	
		
	//
	public function getRedirect() {
		
		$oUrl = new Geko_Uri( parent::getUrl() );
		
		if ( isset( $_POST[ 'nav_group_idx' ] ) ) {
			$oUrl->setVar( 'grp', $_POST[ 'nav_group_idx' ] );
		}
		
		if ( 'delete' == strtolower( $_POST[ 'ops' ] ) ) {
			$oUrl->unsetVar( 'grp' );		
		}

		if ( 'add' == strtolower( $_POST[ 'ops' ] ) ) {
			$oUrl->setVar( 'grp', $this->iAddIndex );		
		}
		
		$oUrl = apply_filters( 'admin_geko_wp_nav_redirect', $oUrl );
		
		return strval( $oUrl );
	}
	
	
	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();
		
		$this->initPageManager();
		
		wp_enqueue_style( 'geko-jquery-ui-wp' );
		
		wp_enqueue_script( 'geko-jquery-geko_util' );
		wp_enqueue_script( 'geko-jquery-syger' );
		
		wp_enqueue_script( 'geko-jquery-ui-draggable' );
		wp_enqueue_script( 'geko-jquery-ui-sortable' );
		wp_enqueue_script( 'geko-jquery-ui-resizable' );
		wp_enqueue_script( 'geko-jquery-ui-dialog' );

		wp_enqueue_script( 'geko-jquery-fx-core' );
		wp_enqueue_script( 'geko-jquery-fx-highlight' );

		wp_enqueue_script( 'geko-jquery-bgiframe' );
		
		$aPlugins = $this->oPageManager->getPlugin();
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
		
		$this->oPageManager
			->disablePrefixingForParams( 'form_action', 'result_field' )
			->setNavContainer( $this->oNavContainer )
			->setParam( 'result_field', 'serialized_data' )
		;
	}
	
	//
	public function outputAjaxJs() {
		$this->outputInit();
		$this->oPageManager->outputJs();
	}
	
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		$this->outputInit();
		
		$this->oPageManager->setInjectParam( array(
			'form_action' => sprintf( '%s/proc.php', $this->getPluginUrl() ),
			'transparent_img' => sprintf( '%s/styles/img/trans.png', $this->getPluginUrl() ),
			'loader_img' => sprintf( '%s/styles/img/ajax-loader.gif', $this->getPluginUrl() )
		) );
		
		?>
			<link type="text/css" href="<?php echo $this->getPluginUrl(); ?>/styles/icons.css" rel="stylesheet" />
			<style type="text/css">
				
				<?php $this->oPageManager->outputStyle(); ?>
				
				.wrap a { text-decoration: none; }
				.wrap a:hover { text-decoration: underline; }
				.wrap .settings { width: 100%; }
				.wrap .settings td { vertical-align: top; }
				#gnp_lg td { vertical-align: middle; }
				
				.wrap div.note { font-style: italic; font-size: 11px; }
				.wrap .page_types { width: 300px; height: 250px; }
				
			</style>
		<?php

		if ( isset( $_GET[ 'grp' ] ) ) {
			
			$iGrpId = intval( $_GET[ 'grp' ] );
			
			$sAjaxUrl = sprintf( '%s/ajax.js.php?grp=%d', $this->getPluginUrl(), $iGrpId );
			$sAjaxUrl = apply_filters( 'admin_geko_wp_nav_ajax_url', $sAjaxUrl );
			
			$aJsonParams = array(
				'ajax_url' => $sAjaxUrl
			);
			
			?>
			<script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
					
					$( '#ops_delete' ).click( function () {
						return confirm( 'Are you sure?' );
					} );
					
					$.getScript(
						oParams.ajax_url,
						function () {
							$( '#gnp_form .gnp_ld' ).hide();
							$( '#gnp_form .gnp_main' ).css( 'visibility', 'visible' );
						}
					);
					
				} );
				
			</script>
			<?php
		}
		
		?>
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				$( '#nav_gs_toggle' ).click( function () {
					
					var eToggle = $( this );
					
					$( '#nav_form_div' ).toggle( 500 );
					
					if ( eToggle.hasClass( 'exp' ) ) {
						eToggle.addClass( 'ctd' ).removeClass( 'exp' );
					} else {
						eToggle.addClass( 'exp' ).removeClass( 'ctd' );					
					}
					
				} );
			
			} );
			
		</script>
		<?php
		
		return $this;
	}
	
	
	//
	protected function outputInnerForm() {
		
		$aNavGroups = Zend_Json::decode( $this->getOption( 'groups' ) );
		
		if ( isset( $_GET[ 'grp' ] ) ) {
			$sCurGroupName = $aNavGroups[ $_GET[ 'grp' ] ][ 'label' ];
			$sCurGroupCode = $aNavGroups[ $_GET[ 'grp' ] ][ 'code' ];
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
					
					<?php if ( isset( $_GET[ 'grp' ] ) ): ?>
						<h3>Edit Navigation Group</h3>
					<?php else: ?>
						<h3>Add Navigation Group</h3>
					<?php endif; ?>
					
					<form method="post" action="<?php echo $this->getPluginUrl(); ?>/proc.php">
						
						Group Label: <input type="text" id="nav_group_label" name="nav_group_label" value="<?php echo $sCurGroupName; ?>" /><br />
						Group Code: <input type="text" id="nav_group_code" name="nav_group_code" value="<?php echo $sCurGroupCode; ?>" /><br />
						
						<br />
						<br />
						
						<?php if ( isset( $_GET[ 'grp' ] ) ): ?>
							<input type="hidden" id="nav_group_idx" name="nav_group_idx" value="<?php echo intval( $_GET[ 'grp' ] ); ?>" />
							<input type="submit" id="ops_edit" name="ops" value="Edit" /> &nbsp;  
							<input type="submit" id="ops_delete" name="ops" value="Delete" /> &nbsp;  
							<a href="<?php echo $this->getUrl(); ?>">Add New</a> 
						<?php else: ?>
							<input type="submit" id="ops_add" name="ops" value="Add" /> 
						<?php endif; ?>
						
					</form>
					
					<br />
					
					<?php if ( isset( $_GET[ 'grp' ] ) ): ?>
					
						<h3>Legend</h3>
						<?php $this->oPageManager->outputLegendHtml(); ?>
						<br />
						
						<?php do_action( 'admin_geko_wp_nav_left' ); ?>
						
					<?php endif; ?>
					
					<h3>General Settings <a href="#" id="nav_gs_toggle" class="exp"><img src="<?php echo $this->getPluginUrl(); ?>/styles/img/trans.png" /></a></h3>
					
					<div id="nav_form_div">
						<?php
							$this->setPrefixFormElems( TRUE );
							parent::outputInnerForm();
							$this->setPrefixFormElems( FALSE );
						?>
					</div>
					
				</td>
				<td>
					<?php if ( isset( $_GET[ 'grp' ] ) ): ?>
						<h3>Editing: <?php echo $sCurGroupName; ?></h3>
						<?php $this->oPageManager->outputFormTagHtml(); ?>
							<?php do_action( 'admin_geko_wp_nav_hidden_fields' ); ?>
							<input type="hidden" id="nav_group_idx" name="nav_group_idx" value="<?php echo intval( $_GET[ 'grp' ] ); ?>" />
							<?php $this->oPageManager->outputDragSortHtml(); ?>
						</form>
						<?php $this->oPageManager->outputOptionsFormHtml(); ?>
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
	public function procSave() {
		
		$this->setMenuPlacement( self::MANAGEMENT );
		
		$iNavGrpIdx = $_POST[ 'nav_group_idx' ];
		$sNavGrpIdxPfx = sprintf( 'gp_%d', $iNavGrpIdx );
		
		if ( isset( $_POST[ 'nav_group_label' ] ) ) {
			
			if (
				!is_array( $aNavGroups = Zend_Json::decode( $this->getOption( 'groups' ) ) )
			) {
				$aNavGroups = array();
			}
			
			$aParams = array(
				'label' => $_POST[ 'nav_group_label' ],
				'code' => $_POST[ 'nav_group_code' ]
			);
			
			if ( 'edit' == strtolower( $_POST[ 'ops' ] ) ) {
				
				// edit a group entry
				$aNavGroups[ $iNavGrpIdx ] = $aParams;
			
			} elseif ( 'add' == strtolower( $_POST[ 'ops' ] ) ) {
				
				// create a group entry
				$aNavGroups[] = $aParams;
				
				// create a default group entry
				end( $aNavGroups );
				$iIndex = key( $aNavGroups );
				
				$aDefaultEntry = array(array(
					'type' => 'Geko_Navigation_Page_Uri',
					'uri' => '/index.php'
				));
				
				$aDefaultEntry = apply_filters(
					'admin_geko_wp_nav_new_group',
					$aDefaultEntry
				);
				
				$this->updateOption(
					sprintf( 'gp_%d', $iIndex ),
					Zend_Json::encode( $aDefaultEntry )
				);
				
				// so that page re-directs to the newly added nav group
				$this->iAddIndex = $iIndex;
				
			} elseif ( 'delete' == strtolower( $_POST[ 'ops' ] ) ) {
				
				unset( $aNavGroups[ $iNavGrpIdx ] );
				$this->deleteOption( $sNavGrpIdxPfx );
				unset( $iNavGrpIdx );
				
			}
			
			$this->updateOption( 'groups', Zend_Json::encode( $aNavGroups ) );
						
		} elseif ( isset( $_POST[ 'serialized_data' ] ) ) {
			
			$this->initPageManager();
			$this->oPageManager
				->procSerializedData( stripslashes( $_POST[ 'serialized_data' ] ) )
			;
			
			$aNavParams = Zend_Json::decode( $this->getOption( $sNavGrpIdxPfx ) );
			$aNavParams = apply_filters(
				'admin_geko_wp_nav_save_group',
				$this->oPageManager->getNavParams(),
				$aNavParams
			);
			
			$this->updateOption( $sNavGrpIdxPfx, Zend_Json::encode( $aNavParams ) );
			
		}
		
		// die();
	}
	
}




