<?php

// listing
class Geko_Wp_Form_Section_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'fmsec_id';
	
	protected $_sSubject = 'Form Section';
	protected $_sDescription = 'Sections of the form.';
	protected $_sType = 'fmsec';
	
	protected $_bSubMainFields = TRUE;
	protected $_bExtraForms = TRUE;
	protected $_bHasDisplayMode = FALSE;
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_section', 'fs' )
			->fieldBigInt( 'fmsec_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'form_id', array( 'unsgnd', 'notnull', 'key' ) )
			->fieldLongText( 'title' )
			->fieldVarChar( 'slug', array( 'size' => 256, 'key' ) )
			->fieldLongText( 'description' )
			->fieldSmallInt( 'rank', array( 'unsgnd', 'notnull' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
		
	}
	
	
	
	//
	public function getStoredSubOptionParams( $oMainMng, $oMainEnt ) {
		
		$aParams = array_merge(
			parent::getStoredSubOptionParams( $oMainMng, $oMainEnt ),
			array (
				'orderby' => 'rank',
				'order' => 'ASC'
			)
		);
		
		return $aParams;
	}
	
	//
	public function getDefaultSubOptions( $aRet ) {
		$aRet[ $this->_sType ] = array( array(
			'title' => '(Untitled)',
			'description' => 'This is a new form'		
		) );
		return $aRet;
	}

	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	//// front-end display methods
	
	//
	public function extraForms( $oEntity ) {
		?>
		<div class="dialog" id="edit_form_section">
			<form>
				<div id="edit_form_section_lang" class="geko-wpadmin-tabs">
					<ul>
						<script id="section-dialog-tab-tmpl" type="text/x-jquery-tmpl">
							<li><a href="" class="ui-tab"><\/a><\/li>
						</script>
					</ul>
					<script id="section-dialog-content-tmpl" type="text/x-jquery-tmpl">
						<div class="ui-tabs-panel-single">
							<fieldset class="ui-helper-reset">
								<div class="default">
									<label for="section_title">Title<\/label>
									<input type="text" name="section_title" id="section_title" value="" \/>
								<\/div>
								<div class="default">
									<label for="section_slug">Code<\/label>
									<input type="text" name="section_slug" id="section_slug" value="" \/>
								<\/div>
								<div class="default">
									<label for="section_description">Description<\/label>
									<textarea name="section_description" id="section_description"><\/textarea>
								<\/div>
							<\/fieldset>
						<\/div>
					</script>
				</div>
			</form>
		</div>
		<?php
	}
	
	//
	public function formFields( $oEntity, $sSection ) {
		
		if ( 'pre' == $sSection ):
			
			?>
			<tr>
				<th colspan="2"><label>Form Editor</label></th>
			</tr>
			<tr>
				<td colspan="2">
					<div class="loading">Loading... <span class="loading"></span></div>
					<div id="form_editor" class="geko-wpadmin-tabs">
						<ul>
							<li class="ui-not-sortable add_menu"><a>+</a></li>
							<script id="section-tmpl" type="text/x-jquery-tmpl">
								<li><a href="" class="ui-tab"><\/a><\/li>
							</script>
						</ul>
						<script id="section-content-tmpl" type="text/x-jquery-tmpl">
							<div id="">
								<div class="ui-tabs-panel-header">
									<div class="icons">
										<a href="#" class="geko-form-add-item"><span class="geko-form-icon"><\/span><\/a>
										<span class="spacer"><\/span>
										<a href="#" class="geko-form-edit-section" title="Edit Section"><span class="geko-form-icon geko-form-icon-edit"><\/span><\/a>
										<a href="#" class="geko-form-remove-section" title="Remove Section"><span class="geko-form-icon geko-form-icon-remove"><\/span><\/a>
									<\/div>
									<br clear="all" />
									<div class="description"><\/div>
								<\/div>
								<div class="ui-tabs-panel-body">
									<ul class="geko-form-items">
										<li class="geko-form-item">
											<a href="#" class="geko-form-item-options"><span class="geko-form-icon"><\/span><\/a>
											<a href="#" class="geko-form-remove-item" title="Remove Item"><span class="geko-form-icon geko-form-icon-remove"><\/span><\/a>
											<a href="#" class="geko-form-move-item" title="Move Item to Other Section"><span class="geko-form-icon geko-form-icon-submit"><\/span><\/a>
											<span class="label"><\/span>
											<div class="fix"><\/div>
											<div class="geko-form-values-main">
												<ul class="geko-form-values">
													<li class="geko-form-value">
														<a href="#" class="geko-form-item-options"><span class="geko-form-icon geko-form-icon-plain_text"><\/span><\/a>
														<span class="label"><\/span>
														<div class="icons_container">
															<div class="icons">
																<a href="#" class="geko-form-add-item"><span class="geko-form-icon"><\/span><\/a>
																<span class="spacer"><\/span>
															<\/div>
														<\/div>
														<a href="#" class="geko-form-expand-widgets"><span class="geko-form-icon"><\/span><\/a>
														<a href="#" class="geko-form-expand-items"><span class="geko-form-icon"><\/span><\/a>
														<div class="fix"><\/div>
														<div class="geko-form-sub-items"><\/div>
													<\/li>
												<\/ul>
												<div class="fix"><\/div>
											<\/div>
											<a href="#" class="geko-form-expand-choices" title="See Choices for this Question"><span class="geko-form-icon"><\/span><\/a>
										<\/li>
									<\/ul>
								<\/div>
								<div class="ui-tabs-panel-footer">&nbsp;<\/div>
								<input type="hidden" class="fmsec_id" name="fmsec_id[]" value="" />
							<\/div>
						</script>
					</div>
					<input type="hidden" id="fmsec" name="fmsec" />
					<input type="hidden" id="fmitm" name="fmitm" />
					<input type="hidden" id="fmitmval" name="fmitmval" />
					<input type="hidden" id="fmitmmv" name="fmitmmv" />
					<!-- <input type="button" id="form_editor_test" value="Test" /> -->
				</td>
			</tr>
			<?php
			
		endif;
		
	}

	
	
}


