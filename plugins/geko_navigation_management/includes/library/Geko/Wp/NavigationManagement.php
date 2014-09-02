<?php

//
class Geko_Wp_NavigationManagement
	extends Geko_Wp_Plugin
	implements Geko_Integration_Service_ActionInterface
{
	
	protected $_sPrefix = 'geko_nav';
	
	
	protected $aNavContainers = array();
	
	protected $aGroups = NULL;
	protected $aCodeHash = array();
	
	protected $aActivePlugins = array();
	
	
	//// initialize
	
	//
	public function start() {
		
		parent::start();
		
		//
		if ( !is_array(
			$aRegisteredPlugins = Zend_Json::decode( $this->getOption( 'plugins' ) )
		) ) {
			$aRegisteredPlugins = array();
		}
		
		foreach ( $this->aActivePlugins as $sClass => $b ) {
			if ( !$aRegisteredPlugins[ $sClass ] ) {
				if (
					is_subclass_of( $sClass, 'Geko_Singleton_Abstract' ) && 
					method_exists( $sClass, 'pluginHookActivate' )
				) {
					$oPlugin = Geko_Singleton_Abstract::getInstance( $sClass );
					$oPlugin->pluginHookActivate();
				}
				$aRegisteredPlugins[ $sClass ] = TRUE;
			}
		}
		
		foreach ( $aRegisteredPlugins as $sClass => $b ) {
			if ( !$this->aActivePlugins[ $sClass ] ) {
				if (
					is_subclass_of( $sClass, 'Geko_Singleton_Abstract' ) && 
					method_exists( $sClass, 'pluginHookDeactivate' )
				) {
					$oPlugin = Geko_Singleton_Abstract::getInstance( $sClass );
					$oPlugin->pluginHookDeactivate();
				}
				unset( $aRegisteredPlugins[ $sClass ] );
			}
		}
		
		$this->updateOption( 'plugins', Zend_Json::encode( $aRegisteredPlugins ) );
			
					
		return $this;
	}
	
	//
	protected function initGroups() {
		
		static $bCalled = FALSE;
		
		if ( !$bCalled ) {
			
			$this->aGroups = Zend_Json::decode( $this->getOption( 'groups' ) );
			
			foreach ( $this->aGroups as $i => $aGroup ) {
				$this->aCodeHash[ $aGroup[ 'code' ] ] = sprintf( 'gp_%d', $i );
			}
			
			$bCalled = TRUE;
		}	
	}
	
	//
	public function activatePlugin( $sClass ) {
		$this->aActivePlugins[ $sClass ] = TRUE;
	}
	
	
	
	
	
	
	//// accessors
	
	//
	public function getGroups() {
		$this->initGroups();
		return $this->aGroups;
	}
	
	//
	public function getCodeHash() {
		$this->initGroups();
		return $this->aCodeHash;
	}	
	
	
	
	//// methods
	
	//
	public function loadNavParams( $sKey ) {
		
		if ( is_array( $aNavParams = Zend_Json::decode( $this->getOption( $sKey ) ) ) ) {
		
			$aNavParams = apply_filters(
				'admin_geko_wp_nav_load_group',
				$aNavParams,
				__CLASS__
			);
			
			return $aNavParams;
		}
		
		return NULL;
	}
	
	//
	public function getNavParams( $sKey ) {
		
		$this->initGroups();
		
		if ( is_array( $this->aGroups ) ) {

			if ( isset( $this->aCodeHash[ $sKey ] ) ) {
				$sKey = $this->aCodeHash[ $sKey ];
			}
			
			if ( is_array( $aNavParams = $this->loadNavParams( $sKey ) ) ) {
				return $aNavParams;
			} else {
				return array();
			}
			
		} else {
			return array();
		}
	}
	
	//
	public function getNavContainer( $sKey ) {
		
		$this->initGroups();
		
		if ( is_array( $this->aGroups ) ) {
			
			// check if key is in the code hash and re-assign
			if ( isset( $this->aCodeHash[ $sKey ] ) ) {
				$sKey = $this->aCodeHash[ $sKey ];
			}
			
			// serialize nav data
			if ( !isset( $this->aNavContainers[ $sKey ] ) ) {
				
				if ( is_array( $aNavParams = $this->loadNavParams( $sKey ) ) ) {
					$oContainer = new Zend_Navigation(
						Geko_Navigation_Renderer::filterNavParams( $aNavParams )
					);
				} else {
					$oContainer = NULL;
				}
				$this->aNavContainers[ $sKey ] = $oContainer;
			}
			
		}
		
		return $this->aNavContainers[ $sKey ];
	}
	
	//
	public function render( $sKey, $aParams = array() ) {
		if ( $oNavContainer = $this->getNavContainer( $sKey ) ) {			
			echo Geko_Navigation_Renderer::menu( $oNavContainer, $aParams );
		}
	}

	// alias of $this->render()
	public function renderMenu( $sKey, $aParams = array() ) {
		$this->render( $sKey, $aParams );
	}
	
	//
	public function renderBreadcrumb( $sKey, $aParams = array() ) {
		if ( $oNavContainer = $this->getNavContainer( $sKey ) ) {			
			echo Geko_Navigation_Renderer::breadcrumbs( $oNavContainer, $aParams );
		}
	}
	
	//
	public function renderClassChain( $sKey, $aParams = array() ) {
		if ( $oNavContainer = $this->getNavContainer( $sKey ) ) {			
			echo Geko_Navigation_Renderer::classChain( $oNavContainer, $aParams );
		}
	}
	
	//
	public function findActiveDepth( $sKey, $aParams = array() ) {
		if ( $oNavContainer = $this->getNavContainer( $sKey ) ) {			
			return Geko_Navigation_Renderer::getActiveDepth( $oNavContainer, $aParams );
		}
		return NULL;
	}
	
	//
	public function findActiveParent( $sKey, $aParams = array() ) {
		if ( $oNavContainer = $this->getNavContainer( $sKey ) ) {			
			return Geko_Navigation_Renderer::getActiveParent( $oNavContainer, $aParams );
		}
		return NULL;
	}	
	
	
}


