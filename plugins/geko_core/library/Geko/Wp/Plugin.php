<?php

//
class Geko_Wp_Plugin extends Geko_Wp_Initialize
{
	protected static $sThemeName;
	
	
	protected $_sPrefix = '';
	protected $_sPluginName = '';
	
	protected $_sPluginAdminClass = '';
	protected $_sQueryHooksClass = '';
	
	protected $_oPluginAdmin = NULL;
	
	
	protected $_bAddTemplatePages = FALSE;
	
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		//
		$this->_sPluginAdminClass = Geko_Class::resolveRelatedClass(
			$this, '', '_PluginAdmin', $this->_sPluginAdminClass
		);
		
		//
		$this->_sQueryHooksClass = Geko_Class::resolveRelatedClass(
			$this, '', '_QueryHooks', $this->_sQueryHooksClass
		);
				
	}
	
	
	//
	public function init() {
		
		static $bCalled = FALSE;
		
		if ( !$bCalled ) {
			
			self::$sThemeName = get_option( 'current_theme' );
			add_action( 'save_post', array( __CLASS__, 'savePostdata' ) );
			
		}
		
		if ( $this->_sPluginAdminClass ) {
			$this->_oPluginAdmin = Geko_Singleton_Abstract::getInstance( $this->_sPluginAdminClass );
			$this->_oPluginAdmin->setShowUpdateMsg( FALSE )->init();
		}
		
		if ( $this->_sQueryHooksClass ) {
			call_user_func( array( $this->_sQueryHooksClass, 'register' ) );			
		}
		
		//
		if ( $this->_bAddTemplatePages ) {
			add_filter( 'template_redirect', array( $this, 'templateRedirect' ) );		
			add_filter( 'admin_page_template_select_pq', array( $this, 'addTemplatePagesPq' ) );		
		}
		
		return $this;
	}
	
	
	
	//
	public static function savePostdata( $iPostId ) {
		if ( isset( $_POST['page_template'] ) ) {
			update_post_meta( $iPostId, '_wp_page_template', $_POST['page_template'] );
		}
		return TRUE;
	}
	
	
	
	//
	public function addTemplatePagesPq( $oDoc ) {
		
		global $post;
		
		$oSelPq = $oDoc->find( 'select' );
		
		$sPluginName = $this->getPluginName();
		
		$aTemplates = $this->getTemplates();
		$sTemplate = get_post_meta( $post->ID, '_wp_page_template', TRUE );
		
		foreach ( $aTemplates as $sName => $sFile ) {
			
			$sFile = '/plugins/' . $sPluginName . '/' . $sFile;
			
			$sChecked = ( $sFile == $sTemplate ) ? ' selected="selected"' : '';
			
			$oSelPq->append( sprintf( '<option value="%s"%s>%s</option>', $sFile, $sChecked, $sName ) );
			
		}
		
		return $oDoc;
	}
	
	//
	public function getPluginName() {
		
		if ( '' == $this->_sPluginName ) {
			
			$oReflect = new ReflectionClass( $this->_sInstanceClass );
			
			$sPluginPath = ABSPATH . PLUGINDIR . '/';
			
			// windows server, fix path separator
			if ( FALSE !== strpos( strtolower( $_SERVER[ 'OS' ] ), 'windows' ) ) {
				$sPluginPath = str_replace( '/', '\\', $sPluginPath );
			}
			
			$sPluginName = str_replace( $sPluginPath, '', $oReflect->getFileName() );
			
			if ( FALSE !== strpos( $sPluginName, DIRECTORY_SEPARATOR ) ) {
				$sPluginName = substr( $sPluginName, 0, strpos( $sPluginName, DIRECTORY_SEPARATOR ) );
			}
			
			$this->_sPluginName = $sPluginName;
		}
		
		return $this->_sPluginName;
	}
	
	//
	public function prefixPage( $sPage ) {
		return $this->_sPrefix . '_' . $sPage;
	}
	
	//
	public function getPluginDir() {
		return PLUGINDIR . '/' . $this->getPluginName();
	}
	
	//
	public function getPluginUrl() {
		return get_bloginfo( 'url' ) . '/' . $this->getPluginDir();
	}	
	
	//
	public function getSecurePluginUrl() {
		return str_replace( 'http://', 'https://', $this->getPluginUrl() );
	}
	
	//
	public function getPrefix() {
		return $this->_sPrefix;
	}
	
	
	//
	public function getOption( $sKey ) {
		
		if ( $this->_oPluginAdmin ) {
			return $this->_oPluginAdmin->getOption( $sKey );
		}
		
		return get_option( $sKey );
	}
	
	//
	public function updateOption( $sKey, $mValue ) {
		
		if ( $this->_oPluginAdmin ) {
			return $this->_oPluginAdmin->updateOption( $sKey, $mValue );
		}
		
		return update_option( $sKey, $mValue );
	}
	
	//
	public function deleteOption( $sKey ) {
		
		if ( $this->_oPluginAdmin ) {
			return $this->_oPluginAdmin->deleteOption( $sKey );
		}
		
		return delete_option( $sKey );
	}
	
	
	
	
	
	////

	//
	public function templateRedirect() {
		
		global $post;
		
		$sPluginName = $this->getPluginName();
		$sTemplate = get_post_meta( $post->ID, '_wp_page_template', TRUE );
		
		if ( $sTemplate ) {
			
			if ( $this->isCompatibleWithCurrentTheme() ) {
				
				if ( is_page() ) {
					
					if ( FALSE !== strpos( $sTemplate, '/plugins/' . $sPluginName ) ) {
						include( ABSPATH . '/wp-content' . $sTemplate );
						exit;
					}
					
				} else {
					// call hook
					$this->templateRedirectHook();
				}
				
			} else {
				
				if ( FALSE !== strpos( $sTemplate, '/plugins/' . $sPluginName ) ) {
					
					if ( FALSE == function_exists('get_plugins') ) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					
					$sPluginDir = str_replace( PLUGINDIR, '', $this->getPluginDir() );
					$aPluginInfo = get_plugins( $sPluginDir );
					$aPluginInfo = current( $aPluginInfo );
					
					echo '<span style="color: red">Warning!!! The "' . $aPluginInfo['Name'] . '" plugin is not compatible with this theme (' . self::$sThemeName . '). Deactivate this plugin to get rid of this error message.</span><br />';
				}
				
			}
			
		}
	}
	
	
	
	//
	public function getTemplates() {
		$oTmpl = Geko_Wp_Template::getInstance();
		return $oTmpl->getTemplateValues( array(
			'prefix' => $this->getPrefix() . '-page-template',
			'directory' => ABSPATH . PLUGINDIR . '/' . $this->getPluginName(),
			'attribute_name' => 'Template Name'
		) );
	}
	
	//
	public function isCompatibleWithCurrentTheme() {
		return class_exists( 'Geko_Wp_Layout' ) && class_exists( 'Geko_Wp_Template' );
	}
	
	
	//// hook methods
	
	//
	protected function templateRedirectHook() { }
	
	
}



