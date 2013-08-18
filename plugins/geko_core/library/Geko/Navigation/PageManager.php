<?php

//
class Geko_Navigation_PageManager
{
	
	//
	protected $_oNavContainer;
	protected $_aNavParams = array();
	protected $_aPageTypes = array();
	protected $_aPrefixedVars = array();
	protected $_aInjectParams = array();
	protected $_aJsOptions = array();
	
	protected $_aPageTypeHash = array();
	protected $_aPageManagerTypeHash = array();
	protected $_aPlugins = array();
	protected $_oDefaultPlugin;
	
	
	
	//
	public function __construct() {
		
		//
		$this->_aPrefixedVars = array(
			'form_id',
				'loading', 'main', 'template', 'list', 'trash',
				'form_action', 'result_field',
			'dialog',
			'legend'
		);
		
		$iMaxWidth = 500;
		$iInnerWidthOffset = 150;
		$iInnerSpanWidth = $iMaxWidth - $iInnerWidthOffset;
		
		//
		$this->_aInjectParams = array(
			
			'nav_prefix' => 'gnp_',
			
				'form_id' => 'form',

					'loading' => 'ld',
					'main' => 'main',
					'template' => 'tpl',
					'list' => 'lst',
					'trash' => 'tsh',

					'form_action' => 'proc.php',
					'result_field' => 'sd',
					
				'dialog' => 'dlg',

				'legend' => 'lg',
			
			'max_width' => $iMaxWidth,
			'outer_width' => $iMaxWidth + 14,
			'inner_span_width' => $iInnerSpanWidth,
			'indent_width' => 20,
			'indent_levels' => 10,
			'dialog_width' => 375,
			'dialog_height' => 550,
			
			'transparent_img' => 'js/img/trans.png',
			'loader_img' => 'js/img/ajax-loader.gif'
			
		);
		
		//
		$this->_aJsOptions = array(
			
			'prefix' => $this->_aInjectParams[ 'nav_prefix' ],
			
			'form_id' => $this->_aInjectParams[ 'form_id' ],
				
				'loading' => $this->_aInjectParams[ 'loading' ],
				'main' => $this->_aInjectParams[ 'main' ],
				'template' => $this->_aInjectParams[ 'template' ],
				'list' => $this->_aInjectParams[ 'list' ],
				'trash' => $this->_aInjectParams[ 'trash' ],
				
				'result_field' => $this->_aInjectParams[ 'result_field' ],
			
			'dialog' => $this->_aInjectParams[ 'dialog' ],
			
			'max_width' => $this->_aInjectParams[ 'max_width' ],
			'inner_span_width' => $this->_aInjectParams[ 'inner_span_width' ],
			'indent_width' => $this->_aInjectParams[ 'indent_width' ],
			'indent_levels' => $this->_aInjectParams[ 'indent_levels' ],
			'dialog_width' => $this->_aInjectParams[ 'dialog_width' ],
			'dialog_height' => $this->_aInjectParams[ 'dialog_height' ],
			
			'remove_drag_template' => FALSE,
			'remove_outdent' => FALSE,
			'remove_indent' => FALSE,
			'remove_options' => FALSE,
			'remove_remove' => FALSE,
			'remove_go_to_link' => FALSE,
			'remove_toggle_visibility' => FALSE,
			'remove_trash' => FALSE,
			'remove_sortable' => FALSE,			
			'remove_default_params' => FALSE
			
		);
		
	}
	
	
	//// accessors
	
	
	//
	public function getPlugin( $mKey = NULL ) {
		
		if ( is_int( $mKey ) ) {
			return $this->_aPlugins[ $mKey ];
		} elseif ( is_string( $mKey ) ) {
			if ( isset( $this->_aPageManagerTypeHash[ $mKey ] ) ) {
				$sKey = $this->_aPageManagerTypeHash[ $mKey ];
				return $this->_aPlugins[ $sKey ];
			} else {
				$sKey = $this->_aPageTypeHash[ $mKey ];
				return $this->_aPlugins[ $sKey ];				
			}
		}
		
		return $this->_aPlugins;
	}
	
	//
	public function getDefaultPlugin() {
		return $this->_oDefaultPlugin;
	}
	
	
	//
	public function setInjectParam( $sKey, $sValue = NULL ) {
		
		if ( is_array( $aParams = $sKey ) ) {
			$this->_aInjectParams = array_merge( $this->_aInjectParams, $aParams );
		} else {
			$this->_aInjectParams[ $sKey ] = $sValue;		
		}
		
		return $this;
	}
	
	//
	public function setJsOption( $sKey, $sValue = NULL ) {
		
		if ( is_array( $aParams = $sKey ) ) {
			$this->_aJsOptions = array_merge( $this->_aJsOptions, $aParams );
		} else {
			$this->_aJsOptions[ $sKey ] = $sValue;		
		}
		
		return $this;
	}
	
	//
	public function setParam( $sKey, $sValue = NULL ) {
		return $this
			->setInjectParam( $sKey, $sValue )
			->setJsOption( $sKey, $sValue )
		;
	}
	
	//
	public function prefixParams( $aParams ) {
		
		$aPrefixed = array();
		
		foreach ( $aParams as $sKey => $mValue ) {
			if ( in_array( $sKey, $this->_aPrefixedVars ) ) {
				// add prefix
				$aPrefixed[ $sKey ] = $this->_aInjectParams[ 'nav_prefix' ] . $mValue;
			} else {
				// no change
				$aPrefixed[ $sKey ] = $mValue;
			}
		}
		
		return $aPrefixed;
	}
	
	//
	public function disablePrefixingForParams() {
		
		$aParamList = func_get_args();
		
		foreach ( $this->_aPrefixedVars as $i => $sKey ) {
			if ( in_array( $sKey, $aParamList ) ) {
				unset( $this->_aPrefixedVars[ $i ] );
			}
		}
		
		return $this;
	}
	
	
	
	//
	public function setNavContainer( $mArg = NULL ) {
		
		// assume that the nav params array is set
		if ( NULL == $mArg ) $mArg = $this->_aNavParams;
		
		if ( is_array( $mArg ) ) {
			
			$this->_aNavParams = $mArg;
			$this->_oNavContainer = new Zend_Navigation( $mArg );
		
		} elseif ( $mArg instanceof Zend_Navigation ) {
			
			$this->_aNavParams = $mArg->toArray();
			$this->_oNavContainer = $mArg;
			
		}
		
		return $this;
	}
	
	//
	public function getNavContainer() {
		return $this->_oNavContainer;
	}
	
	//
	public function getNavParams() {
		return $this->_aNavParams;
	}
	
	//
	public function setPageTypes( $aPageTypes ) {
		$this->_aPageTypes = array_merge( $this->_aPageTypes, $aPageTypes );
		return $this;
	}
	
	//
	public function setPageType( $aPageType ) {
		$this->_aPageTypes[] = $aPageType;
		return $this;
	}
	
	
	
	
	
	
	
	
	
	
	
	//
	public function procSerializedData( $sSerializedData ) {
		
		if ( trim( $sSerializedData ) ) {
			
			$aFlatParams = Zend_Json::decode( $sSerializedData );
			
			if ( is_array( $aFlatParams ) ) {

				foreach ( $aFlatParams as $i => $aParam ) {
					$aParam[ 'type' ] = $this->_aPageTypes[ $aParam[ 'type' ] ][ 'type' ];
					$aFlatParams[ $i ] = $aParam;
				}
				
				$oNested = new Geko_IndentedList_AssociativeArray(
					$aFlatParams, 'pages', 'indent'
				);
				
				$this->_aNavParams = $oNested->getTree();
			}
			
		}
		
		return $this;
	}
	
	
	
	
	
	//// setup
	
	//
	public function init() {
		
		$aClientSubscribers = array();
		$sTypeCssClasses = '';
		
		foreach ( $this->_aPageTypes as $iType => $aPageType ) {
			
			$sPageClass = $aPageType[ 'type' ];
			
			$this->_aPageTypeHash[ $sPageClass ] = $iType;
			$sTypeCssClasses .= 'type-' . $iType . ' ';
			
			
			//// determine a page manager class, if it exists
			$sPageManagerClass = self::resolvePageManagerClass( $sPageClass, $aPageType[ 'type_manager' ] );
			
			// remember the corresponding "page manager plugin" class, in case needed by sub-class
			if ( $sPageManagerClass ) $this->_aPageTypes[ $iType ][ 'type_manager' ] = $sPageManagerClass;
			
			//// instantiate plugins
			if (
				$sPageManagerClass &&
				Geko_Class::isSubclassOf( $sPageManagerClass, 'Geko_Navigation_PageManager_PluginAbstract' )
			) {
				$this->_aPlugins[ $iType ] = new $sPageManagerClass( $iType );
				$this->_aPageManagerTypeHash[ $sPageManagerClass ] = $iType;
				
				if ( $aPageType[ 'default' ] ) {
					$this->_oDefaultPlugin = $this->_aPlugins[ $iType ];
				}
				
				//
				if ( !isset( $this->_aPageTypes[ $iType ][ 'type_name' ] ) ) {
					$this->_aPageTypes[ $iType ][ 'type_name' ] = Geko_String::coalesce(
						call_user_func( array( $sPageManagerClass, 'getDescription' ) ),
						$sPageClass
					);
				}
				
			}
			
		}
		
		//
		if ( FALSE == is_object( $this->_oDefaultPlugin ) ) {
			if ( count( $this->_aPlugins ) > 0 ) {
				$this->_oDefaultPlugin = reset( $this->_aPlugins );
			} else {
				$this->_oDefaultPlugin = new Geko_Navigation_PageManager_Default();
			}
		}
		
		return $this;
	}	
	
	
	// static helper that resolves the corresponding page manager class
	public static function resolvePageManagerClass( $sPageClass, $sPageManagerClass = '' ) {
		return Geko_Class::existsCoalesce(
			( $sPageManagerClass ) ? $sPageManagerClass : '',
			str_replace( '_Page_', '_PageManager_', $sPageClass ),
			$sPageClass . 'Manager'
		);
	}
	
	
	
	//// output methods

	
	// injection
	
	//
	protected function outputPluginJs( $sCallback ) {
		$sCallback = 'get' . ucfirst( $sCallback );
		foreach ($this->_aPlugins as $iType => $oPlugin) {
			if ( $sOutput = $oPlugin->$sCallback() ) {
				echo $this->outputInjectType( $iType, $sOutput ) . "\n";
			}
		}
	}
	
	
	//
	protected function outputInjectType( $iType, $sOutput ) {
		return str_replace(
			array( '##type##', '##nvpfx_type##' ),
			array( $iType, $this->_aInjectParams[ 'nav_prefix' ] . $iType . '_' ),
			$sOutput
		);
	}
	
	//
	protected function _outputInject( $sCallback ) {
		
		ob_start();
		$this->$sCallback();
		$sOutput = ob_get_contents();
		ob_end_clean();
		
		$aParams = $this->prefixParams( $this->_aInjectParams );
		
		// do injections
		echo str_replace(
			array_map(
				create_function(
					'$sValue',
					'return "##" . $sValue . "##";'
				),
				array_keys( $aParams )
			),
			array_values( $aParams ),
			$sOutput
		);
		
	}
	
	
	// style
	
	
	//
	public function _outputInjectStyle() {
		?>
		
		.demo ul { list-style-type: none; margin: 0; padding: 0; margin-bottom: 10px; }
		.demo li { margin: 2px; padding: 2px; width: ##max_width##px; font-size: 12px; line-height: 1.5em; height: 18px; }
		.demo li a { text-decoration: none; }
		.demo li a:hover { text-decoration: underline; }
		
		###form_id## .##template## span.item_title, ###form_id## .##list## span.item_title, ###form_id## .##trash## span.item_title {
			font-weight: bold;
			display: inline-block;
			width: ##inner_span_width##px;
			height: 18px;
			overflow: hidden;
		}
		
		###form_id## .##template## div.item_icon, ###form_id## .##list## div.item_icon, ###form_id## .##trash## div.item_icon { width: 16px; height: 16px; margin-right: 6px; float: left; }
		###form_id## .##template## div.item_ops, ###form_id## .##list## div.item_ops, ###form_id## .##trash## div.item_ops { float: right; cursor: pointer; }
		###form_id## .##template##, ###form_id## .##list## li { cursor: move; }
		
		###form_id## .##nav_prefix##drag_handle { margin: 0 0 8px 6px; padding: 2px; width: ##max_width##px; height: 5px; background-color: #85b3c8; cursor: move; border: solid 1px #21759B; }
		
		###legend## div.icon { width: 20px; height: 20px; margin: 2px 10px 2px 0; float: left; }
		
		.##list## { border: solid 1px #21759B; width: ##outer_width##px; min-height: 6px; }
		
		/* Begin Modal Stuff */
		###dialog## { display: none; }
		###dialog## label, ###dialog## input { display: block; }
		input.text { margin-bottom: 12px; width: 95%; padding: .4em; }
		fieldset { padding: 0; border: 0; margin-top: 25px; }
		.ui-button { outline: 0; margin:0; padding: .4em 1em .5em; text-decoration:none;  !important; cursor:pointer; position: relative; text-align: center; }
		.ui-dialog .ui-state-highlight, .ui-dialog .ui-state-error { padding: .3em;  }
				
		<?php
		$this->outputPluginJs( 'style' );
		
	}
	
	
	
	//// javascript
	
	//
	public function _outputInjectJs() {
		
		$oIterator = new RecursiveIteratorIterator(
			$this->_oNavContainer,
			RecursiveIteratorIterator::SELF_FIRST
		);
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		// create a structure that contains nested nav data
		
		$aNav = array();
		foreach ($oIterator as $oPage) {
			
			$sPageClass = get_class( $oPage );
			
			if ( Geko_Class::isSubclassOf( $sPageClass, 'Geko_Navigation_PageInterface' ) ) {
				$aNavOptions = array_merge( $oPage->getOrigOptions(), $oPage->getImplicitOptions() );
			} else {
				$aNavOptions = $oPage->toArray();
				unset( $aNavOptions[ 'pages' ] );
			}
			
			if ( array_key_exists( $aNavOptions[ 'type' ], $this->_aPageTypeHash ) ) {
				$aNavOptions[ 'type' ] = $this->_aPageTypeHash[ $aNavOptions[ 'type' ] ];
			} else {
				$aNavOptions[ 'type' ] = $this->_oDefaultPlugin->getIndex();
			}
			
			$aNavOptions[ 'indent' ] = intval( $oIterator->getDepth() );
			
			$aNav[] = $aNavOptions;
		}
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		// create a structure that contains management data
		
		$aManage = array();
		foreach ( $this->_aPlugins as $iType => $oPlugin ) {
			$aManage[ $iType ] = array_merge(
				array(
					'default_params' => $oPlugin->getDefaultParams(),
					'type' => $iType,
					'pfx_type' => $this->_aInjectParams[ 'nav_prefix' ] . $iType . '_',
					'disable_params' => FALSE
				),
				$oPlugin->getManagementData()
			);
		}
		
		// prepare parameters to pass to jQuery plugin
		
		$aParams = array(
			'nav_data' => $aNav,
			'mgmt_data' => $aManage,
			'default_idx' => intval( $this->_oDefaultPlugin->getIndex() ),
			'opts' => $this->prefixParams( $this->_aJsOptions )
		);
		
		?>
		
		jQuery( document ).ready( function( $ ) {
			
			//
			$.gekoNavigationPageManager.init(
				<?php echo Zend_Json::encode( $aParams, FALSE, array( 'enableJsonExprFinder' => TRUE ) ); ?>
			);
			
		} );
		
		<?php
	}
	
	
	
	// html
	
	//
	public function _outputInjectDragSortHtml() {
		?>
		<div class="demo">
			
			<div class="##loading##"><img src="##loader_img##" /></div>
			
			<div class="##main##">
				
				<div class="##nav_prefix##tmpl_outer">
					<div class="##nav_prefix##drag_handle"></div>
					<ul class="##nav_prefix##template">
						<li class="ui-state-highlight ##template##">
							<div class="item_icon"></div><span class="item_title"><a href="#"></a></span>
							<div class="item_ops">
								<a href="#" class="outdent" title="Outdent"><img src="##transparent_img##" /></a>
								<a href="#" class="indent" title="Indent"><img src="##transparent_img##" /></a>
								<a href="#" class="options" title="Options"><img src="##transparent_img##" /></a>
								<a href="#" class="remove" title="Remove"><img src="##transparent_img##" /></a>
								<a href="#" class="link" target="_blank" title="Go to Link"><img src="##transparent_img##" /></a>
								<a href="#" class="visibility" title="Toggle Visibility"><img class="vbl" src="##transparent_img##" /></a>
							</div>
						</li>
					</ul>
					<p>(Drag to make a copy)</p>
					<br /><br />
				</div>
				
				<ul class="##list##"></ul>
				<br /><br />
				
				<ul class="##trash##"></ul>
				
				<input type="hidden" id="##result_field##" name="##result_field##" value="" />
				
				<input type="button" id="test" name="test" value="Test" />
				<input type="submit" value="Save" />
				
			</div>
			
		</div><!-- End demo -->		
		<?php
	}
	
	//
	public function _outputInjectOptionsFormHtml() {
		?>
		<div id="##dialog##" title="Navigation Item Options">
			<form>
				<fieldset>
					
					<label for="##nav_prefix##type">Type</label>
					<select name="##nav_prefix##type" id="##nav_prefix##type" class="text ui-widget-content ui-corner-all">
						<?php foreach ( $this->_aPageTypes as $iType => $aType ): ?>
							<option value="<?php echo $iType; ?>"><?php echo $aType[ 'type_name' ]; ?></option>
						<?php endforeach; ?>
					</select>
	
					<?php foreach ( $this->_aPlugins as $iType => $oPlugin ):
						if ( $sOutput = $oPlugin->getHtml() ): ?>
							<div class="opt-group opt-<?php echo $iType; ?>">
								<?php echo $this->outputInjectType( $iType, $sOutput ); ?>
							</div><?php
						endif;
					endforeach; ?>
					
					<div class="opt-common">
						<label for="##nav_prefix##label">Label</label>
						<input type="text" name="##nav_prefix##label" id="##nav_prefix##label" class="text ui-widget-content ui-corner-all" />
					</div>
					<div class="opt-common">
						<label for="##nav_prefix##title">Title</label>
						<input type="text" name="##nav_prefix##title" id="##nav_prefix##title" class="text ui-widget-content ui-corner-all" />
					</div>
					<div class="opt-common">
						<label for="##nav_prefix##target">Target</label>
						<input type="text" name="##nav_prefix##target" id="##nav_prefix##target" class="text ui-widget-content ui-corner-all" />
					</div>
					<div class="opt-common">
						<label for="##nav_prefix##css_class">Class</label>
						<input type="text" name="##nav_prefix##css_class" id="##nav_prefix##css_class" class="text ui-widget-content ui-corner-all" />
					</div>
					<div class="opt-common">
						<label for="##nav_prefix##inactive">Force Inactive</label>
						<input type="checkbox" name="##nav_prefix##inactive" id="##nav_prefix##inactive" class="text ui-widget-content ui-corner-all" />
					</div>
					<div class="opt-common">
						<label for="##nav_prefix##hide">Hide</label>
						<input type="checkbox" name="##nav_prefix##hide" id="##nav_prefix##hide" class="text ui-widget-content ui-corner-all" />
					</div>
					
				</fieldset>
			</form>
		</div>		
		<?php
	}
	
	//
	public function _outputInjectFormTagHtml() {
		?><form id="##form_id##" method="post" action="##form_action##"><?php
	}
	
	//
	public function _outputInjectHtml() {
		$this->_outputInjectFormTagHtml(); ?>
			<?php $this->_outputInjectDragSortHtml(); ?>
		</form>
		<?php $this->_outputInjectOptionsFormHtml();
	}
	
	
	//
	public function _outputInjectLegendHtml() {
		?>
		<table id="##legend##">
			<?php foreach( $this->_aPageTypes as $iType => $aType ): ?>
				<tr>
					<td><div class="icon type-<?php echo $iType; ?>"></div></td>
					<td><?php echo $aType[ 'type_name' ]; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		// check if there is a matching _outputInject* method
		$sOutputInjectMethod = str_replace( 'output', '_outputInject', $sMethod );
		
		if ( method_exists( $this, $sOutputInjectMethod ) ) {
			// delegate
			return $this->_outputInject( $sOutputInjectMethod );
		} else {
			throw new Exception( 'Invalid method ' . __CLASS__ . '::' . $sMethod . '() called.' );
		}
		
	}
	
	
}


