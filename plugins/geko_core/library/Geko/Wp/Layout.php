<?php

//
class Geko_Wp_Layout extends Geko_Layout
{
	
	protected $_aUnprefixedActions = array(
		'get_header', 'wp_head', 'get_sidebar', 'wp_footer', 'get_footer'
	);
	protected $_aUnprefixedFilters = array();
	
	protected $_sRenderer = 'Geko_Wp_Layout_Renderer';
	protected $_aPrefixes = array( 'Gloc_', 'Geko_Wp_', 'Geko_' );
	
	protected $_aLinks = array();
	
	
	
	
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		$oTrans = Geko_Wp_Language_Translate::getInstance();
		
		$this->_aMapMethods = array_merge( $this->_aMapMethods, array(
			
			'is' => array( 'Geko_Wp', 'is' ),
			
			'listCats' => 'wp_list_cats',
			'listArchives' => 'wp_get_archives',
			'listAuthors' => 'wp_list_authors',
			'listBookmarks' => 'wp_list_bookmarks',
			'tagCloud' => 'wp_tag_cloud',
			
			'_t' => array( $oTrans, 'getValue' ),
			'_e' => array( $oTrans, 'echoValue' )
			
		) );
		
	}
	
	
	//// helpers
	
	
	//
	public function escapeHtml( $sValue ) {
		return wp_specialchars( $sValue, 1 );
	}
	
	
	
	// translate labels
	public function _getLabel() {

		$aArgs = func_get_args();
		
		$sCurLang = $aArgs[ 1 ];
		$sLabel = call_user_func_array( array( parent, '_getLabel' ), $aArgs );
		
		return $this->_t( $sLabel, NULL, $sCurLang );
	}
	
	//
	public function _getLabels() {
		
		$aLabels = parent::_getLabels();
		$aRet = array();
		
		foreach ( $aLabels as $iIdx => $sValue ) {
			$aRet[ $iIdx ] = $this->_t( $sValue );
		}
		
		return $aRet;
	}
	
	//
	public function getScriptUrls( $aOther = NULL ) {
		return Geko_Wp::getScriptUrls( $aOther );
	}
	
	
	
	
	//// link methods
	
	//
	public function addLink( $sKey, $aLink ) {
		$this->_aLinks[ $sKey ] = $aLink;
		return $this;
	}
	
	//
	public function echoLinks() {
		
		$aArgs = func_get_args();
		
		if ( count( $aArgs ) == 0 ) {
			$aLinkKeys = array_keys( $this->_aLinks );		
		} elseif ( is_array( $aArgs[ 0 ] ) ) {
			$aLinkKeys = $aArgs[ 0 ];
		} else {
			$aLinkKeys = $aArgs;
		}
		
		$oA = new Geko_Html_Element_A();
		
		if ( count( $aLinkKeys ) > 0 ): ?>
			<p><?php foreach ( $aLinkKeys as $i => $sKey ) {
				$aLink = $this->_aLinks[ $sKey ];
				if ( 0 != $i ) echo ' | ';
				$oA
					->reset()
					->_setAtts( $aLink )
					->append( $aLink[ 'title' ] )
				;
				echo strval( $oA );
			} ?></p>
		<?php endif;
		
	}
	
	
	
	
	//
	public function getBodyClassCb() {
		
		$aArgs = func_get_args();
		
		// do this to prevent noisy logs
		if ( 0 == count( $aArgs ) ) {
			$aArgs[] = '';
		}
		
		return call_user_func_array( array( 'Geko_Wp', 'getBodyClass' ), $aArgs );
	}
	
	
	
	
	//// render tags
	
	//
	public function getEnqueueScriptCb() {
		return 'wp_enqueue_script';
	}
	
	//
	public function getEnqueueStyleCb() {
		return 'wp_enqueue_style';
	}
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( 0 === strpos( strtolower( $sMethod ), 'do' ) ) {
			
			$sAction = Geko_Inflector::underscore(
				substr_replace( $sMethod, '', 0, 2 )
			);
			
			if ( !in_array( $sAction, $this->_aUnprefixedActions ) ) {
				$sAction = sprintf( 'theme_%s', $sAction );
			}
			
			parent::__call( $sMethod, $aArgs );
			
			do_action_ref_array( $sAction, $aArgs );
			
			return NULL;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'apply' ) ) {

			$sFilter = Geko_Inflector::underscore(
				substr_replace( $sMethod, '', 0, 5 )
			);
			
			if ( !in_array( $sFilter, $this->_aUnprefixedFilters ) ) {
				$sFilter = sprintf( 'theme_%s', $sFilter );
			}
			
			$mRes = parent::__call( $sMethod, $aArgs );
			$aArgs[ 0 ] = $mRes;
			
			return apply_filters_ref_array( $sFilter, $aArgs );
			
		}
		
		return parent::__call( $sMethod, $aArgs );
	}
	
	
	
}

