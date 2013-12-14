<?php

class Gloc_User_Meta extends Geko_Wp_User_Meta
{

	//
	public function getTitle() {
		return 'Member Properties';
	}
	
	
	/*
	//
	public function affix() {
		Geko_Wp_Enumeration_Manage::getInstance()->init();
		return $this;
	}
	
	
	//
	public function affixAdmin() {
		return $this;
	}
	*/

	// $aCategories = Geko_Wp_Enumeration_Query::getSet( 'user-categories' );
	
	//
	public function formFields() {
		
		$sRole = '';
		
		if ( $iUserId = $this->resolveUserId() ) {
			$oUser = new Gloc_User( $iUserId );
			$sRole = $oUser->getRoleSlug();
		}
		
		?>
		
		<!-- agent fields -->
		<?php if ( 'subscriber' == $sRole ): ?>
			<p>
				<label class="main">Phone</label>
				<input id="phone" name="phone" type="text" value="" />
			</p>
		<?php endif; ?>
		
		<?php
		
	}
	
}


