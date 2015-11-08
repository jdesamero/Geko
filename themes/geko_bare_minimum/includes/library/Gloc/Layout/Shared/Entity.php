<?php

//
class Gloc_Layout_Shared_Entity extends Gloc_Layout
{
	
	protected $_sMode = 'listing';
	
	protected $_sEntityClass;
	protected $_sQueryClass;
	protected $_sManageClass;
	protected $_sServiceClass;
	
	protected $_oManage;
	protected $_oService;
	
	protected $_oUser;
	protected $_bPassUserId = FALSE;
	
	protected $_oEntity = NULL;
	protected $_aEntities = NULL;
	
	
	
	//
	public function start() {
		
		parent::start();
		
		
		//// resolve related classes
		
		// resolve query class
		
		$this->_sQueryClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Query', $this->_sQueryClass
		);
				
		// resolve management class
		
		$this->_sManageClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Manage', $this->_sManageClass
		);
		
		if ( $this->_sManageClass ) {
			$this->_oManage = Geko_Singleton_Abstract::getInstance( $this->_sManageClass );
		}
		
		// resolve service class
		
		$this->_sServiceClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Service', $this->_sServiceClass
		);
		
		if ( $this->_sServiceClass  ) {
			
			$oService = Geko_Singleton_Abstract::getInstance( $this->_sServiceClass )
				->initActions();
			
			// valid modes are: listing, details, edit, add
			
			$sMode = trim( $_GET[ 'mode' ] );
			
			if ( $sMode ) {
				// get actions for modes
			}
			
			$this->_oService = $oService;
			
		}
		
		
		
		//// setup other objects
		
		$oMainLayout = Gloc_Layout_Main::getInstance();
		
		$this->_oUser = $oUser = $oMainLayout->getUser();
		
		
		
		//// entity stuff
		
		$oManage = $this->_oManage;
		$sQueryClass = $this->_sQueryClass;
		
		$sVarName = $oManage->getEntityIdVarName();
		
		$bSingleEntity = FALSE;
		
		$aParams = array();
		
		if ( $this->_bPassUserId ) {
			$iUserId = ( $oUser ) ? $oUser->getId() : 0;
			$aParams[ 'user_id' ] = $iUserId;
		}
		
		if ( isset( $_GET[ $sVarName ] ) ) {

			$aParams = array_merge( $aParams, array(
				$sVarName => $_GET[ $sVarName ],
				'posts_per_page' => 1,
				'showpost' => 1
			) );
			
			$aParams = $this->modifyParams( $aParams, 'details' );
			
			$aEntities = new $sQueryClass( $aParams, FALSE );
			
			if ( $aEntities->getTotalRows() == 1 ) {
				$this->_oEntity = $aEntities->getOne();
				$bSingleEntity = TRUE;
				$sMode = ( $sMode ) ? $sMode : 'details';
			}
			
		}
		
		if ( !$bSingleEntity && ( 'add' != $sMode ) ) {

			$aParams = array_merge( $aParams, array(
				'showposts' => -1,
				'posts_per_page' => -1
			) );
			
			$aParams = $this->modifyParams( $aParams, 'listing' );
			
			$this->_aEntities = new $sQueryClass( $aParams, FALSE );
			
		}
		
		$this->_sMode = $sMode;
		
	}
	
	
	//
	public function echoEnqueue() {
		$oManage = $this->_oManage;
		$oManage->layoutEnqueue();
	}
	
	
	//
	public function echoHeadLate() {
				
		$oUrl = new Geko_Uri();
		$sCurPage = strval( $oUrl );
		
		$oManage = $this->_oManage;
		$oService = $this->_oService;
				
		$sSubject = $oManage->getSubject();
		$sEntityVarName = $oManage->getEntityIdVarName();
		$sEditPage = sprintf(
			'%s%s?mode=edit&%s=',
			Geko_Wp::getUrl(), $oUrl->getPath(), $sEntityVarName
		);
		$sListingPage = Geko_Wp::getUrl() . $oUrl->getPath();
		
		//// create json params
		
		// script
		// TO DO: add hooks
		$aScript = $this->getScriptUrls( array(
			'editpage' => $sEditPage,
			'listingpage' => $sListingPage		
		) );
		
		
		// form
		// TO DO: add hooks
		$aForm = array(
			'mode' => $this->_sMode,
			'delete_confirm_msg' => sprintf( 'Are you sure you want to delete this %s?', $sSubject ),
			'entity_var_name' => $sEntityVarName
		);
		
		// labels
		// TO DO: add hooks
		$aLabels = $this->_getLabels();
		
		// status
		// TO DO: add hooks
		$aService = $oService->getJsonParams();
		
		//// assemble json params
		
		$aJsonParams = array(
			'script' => $aScript,
			'form' => $aForm,
			'labels' => $aLabels,
			'service' => $aService
		);
		
		?>
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				
				$.oGekoLayoutParams = oParams;
				
				var process = oParams.script.process;
				var editpage = oParams.script.editpage;
				var listingpage = oParams.script.listingpage;
				
				var labels = oParams.labels;
				
				var mode = oParams.form.mode;
				var delete_confirm_msg = oParams.form.delete_confirm_msg;
				var entity_var_name = oParams.form.entity_var_name;
				
				var initForm = function( form, action ) {
					
					var service_name = oParams.service.name;
					var oAction = oParams.service.actions[ action ];
					
					if ( oAction ) {
						
						var status = oAction.status;
						var success_msg = oAction.success_msg;
						var error_msg = oAction.error_msg;
						
						form.gekoAjaxForm( {
							status: status,
							process_script: process,
							action: '&_service=' + service_name + '&_action=' + action,
							validate: function( form, errors ) {
								// add hooks here
								return errors;
							},
							process: function( form, res, status ) {
								if ( status.success == parseInt( res.status ) ) {
									if ( 'add' == action ) {
										success_msg += ' You will be redirected to the edit page.';
										form.successLoading( success_msg );
										setTimeout( function() {
											window.location = editpage + parseInt( res.insert_id );
										}, 2000 );
									} else if ( 'edit' == action ) {
										if ( res.update_values ) {
											$.each( res.update_values, function( k, v ) {
												var elem = form.find( '#' + k );
												var type = elem.prop( 'tagName' ).toLowerCase();
												if ( 'span' == type ) {
													elem.html( v );
												}
											} );
										}
										form.success( success_msg );
									} else if ( 'delete' == action ) {
										success_msg += ' You will be redirected to the listing page.';
										form.successLoading( success_msg );
										setTimeout( function() {
											window.location = listingpage;
										}, 2000 );
										}
								} else {
									form.error( error_msg );
								}
							}
						} );
					
					}
					
				}
				
				if ( ( 'add' == mode ) || ( 'edit' == mode ) ) {
					var detailForm = $( '#detailform' );
					initForm( detailForm, mode );
				}
				
				var deleteForm = $( '#deleteform' );
				initForm( deleteForm, 'delete' );
				
				// delete buttons
				
				$( '.delete' ).click( function() {
					if ( confirm( delete_confirm_msg ) ) {
						var uri = $( this ).uri();
						var query = uri.search( true );
						deleteForm.find( '#' + entity_var_name ).val( query[ entity_var_name ] );
						deleteForm.submit();
					}
					return false;
				} );
				
			} );
			
		</script>
		<?php
		
		$oManage->layoutHeadLate();
		
	}
	
	
	
	//
	public function echoContent() {		

		$oPage = $this->newPage();
		
		$oManage = $this->_oManage;
		$oEntity = $this->_oEntity;
		$aEntities = $this->_aEntities;
				
		$sSubject = $oManage->getSubject();
		$sSubjectPlural = $oManage->getSubjectPlural();
		
		//// page title
		
		$sTitle = '';
		if ( $oEntity ) {
			if ( 'edit' == $this->_sMode ) {
				$sTitle = 'Edit ' . $sSubject;		
			} else {
				$sTitle = $sSubject . ' Details';			
			}
		} elseif ( is_object( $aEntities ) ) {
			$sTitle = $sSubjectPlural;
		} else {
			$sTitle = 'Add ' . $sSubject;
		}
		
		//// links
		
		$oUrl = new Geko_Uri();
		$sVarName = $oManage->getEntityIdVarName();
		
		// edit
		$oUrl->setVar( 'mode', 'edit' );
		$this->addLink( 'edit', array(
			'title' => 'Edit ' . $sSubject,
			'href' => strval( $oUrl )
		) );
		
		// delete
		$oUrl->setVar( 'mode', 'delete' );
		$this->addLink( 'delete', array(
			'title' => 'Delete ' . $sSubject,
			'href' => strval( $oUrl ),
			'class' => 'delete'
		) );
		
		// details
		$oUrl->unsetVar( 'mode' );
		$this->addLink( 'details', array(
			'title' => $sSubject . ' Details',
			'href' => strval( $oUrl )
		) );
		
		// add
		$oUrl->setVar( 'mode', 'add' );
		$oUrl->unsetVar( $sVarName );
		$this->addLink( 'add', array(
			'title' => 'Add ' . $sSubject,
			'href' => strval( $oUrl )
		) );
		
		// listing
		$oUrl->unsetVars();
		$this->addLink( 'listing', array(
			'title' => 'Back to ' . $sSubject . ' Listings',
			'href' => strval( $oUrl )
		) );
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			
			<h1><?php echo $sTitle; ?></h1>
			
			<div class="entry-content">
				
				<?php $oPage->echoTheContent(); ?>
				
				<p>&nbsp;<p>
				
				<form id="deleteform">
					<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
					<div class="error"></div>
					<div class="success"></div>
					<input type="hidden" id="<?php echo $sVarName; ?>" name="<?php echo $sVarName; ?>" value="" />
				</form>
				
				<?php if ( $oEntity ): ?>
										
					<?php
					
					if ( 'edit' == $this->_sMode ):
						
						// edit form
						$oEntity->renderDetailForm();						
						$this->echoLinks( 'listing', 'add', 'details', 'delete' );
						
					else:
						
						// read-only details
						$oEntity->renderDetail();
						$this->echoLinks( 'listing', 'add', 'edit', 'delete' );
						
					endif;
				
				elseif ( $aEntities ):
					
					if ( $aEntities->getTotalRows() > 0 ):
						
						// listing mode
						$aEntities->renderListing();
						
					else:
						
						// no matching results
						?>
						<p>You currently have no <?php echo $sSubjectPlural; ?>.</p>
						<?php
						
					endif;
					
					$this->echoLinks( 'add' );
					
				else:
					
					// add mode
					$oManage->renderDetailForm();
					$this->echoLinks( 'listing' );
					
				endif; ?>
				
				<?php $this->doLinkPages(); ?>
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
				
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
	
	//// hooks
	
	//
	public function modifyParams( $aParams ) {
		return $aParams;
	}
	
	
	
}

