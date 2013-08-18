<?php

//
class Geko_Wp_NavigationManagement_PageManager_Role
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{
	//
	protected $aRolesNorm = array();
	protected $aRoleTypeGroups = array();
	
	
	
	//
	public function init() {
		
		$aRolesNorm = array();
		$aRoleTypeGroups = array();
		
		$aRoles = new Geko_Wp_Role_Query();
		
		foreach ( $aRoles as $oRole ) {
			
			$aRolesNorm[ $oRole->getId() ] = array(
				'title' => $oRole->getTheTitle(),
				'link' => $oRole->getUrl(),
				'skip_id' => FALSE
			);
			
			$aRoleTypeGroups[ $oRole->getType() ][] = $oRole->getId();
		}
		
		$aRoleTypes = Geko_Wp_Role_Types::getInstance();
		
		// sneak in the all pseudo type
		foreach ( $aRoleTypes as $oRoleType ) {
			
			$sType = $oRoleType->getName();
			
			$oAllRole = Geko_Wp_Role::createPseudoAll( $sType );
			
			$aRolesNorm[ $oAllRole->getId() ] = array(
				'title' => $oAllRole->getTheTitle(),
				'link' => $oAllRole->getUrl(),
				'skip_id' => TRUE
			);
			
			$aItems = is_array( $aRoleTypeGroups[ $sType ] ) ? $aRoleTypeGroups[ $sType ] : array();
			array_unshift( $aItems, $oAllRole->getId() );
			$aRoleTypeGroups[ $sType ] = $aItems;
		}
		
		$this->setRolesNorm( $aRolesNorm );
		$this->setRoleTypeGroups( $aRoleTypeGroups );
	}
	
	//
	public function setRolesNorm( $aRolesNorm ) {
		$this->aRolesNorm = $aRolesNorm;
		return $this;
	}

	//
	public function setRoleTypeGroups( $aRoleTypeGroups ) {
		$this->aRoleTypeGroups = $aRoleTypeGroups;
		return $this;
	}
	
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams['role_id'] = key( $this->aRolesNorm );
		
		return $aParams;
	}
	
	
	//
	public function getManagementData() {
		
		$aData = parent::getManagementData();
		$aData = array_merge( $aData, array(
			'role_params' => $this->aRolesNorm,
			'role_groups' => $this->aRoleTypeGroups
		) );
		
		return $aData;
	}
	
	
	
	
	
	
	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: #d8fafa; border: dotted 1px #00b3b6; }
		<?php
	}
	
	
	//
	public function outputHtml() {
		?>
		<label for="##nvpfx_type##role_id">Role Title</label>
		<select name="##nvpfx_type##role_id" id="##nvpfx_type##role_id" class="text ui-widget-content ui-corner-all"></select>
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Role';
    }
    
}

