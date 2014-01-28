<?php
/**
 * A collection of classes that help us provide a consistent plugin experience for WordPress.
 * 
 * This class does most of the heavy lifting for creating a plugin.
* It takes a hash as its one constructor argument, which can have the
* following keys and values:
*
*     * 'basefile' :: Path and filename of main plugin file. Needed so wordpress
*               can tell which plugin is calling some of it's generic hooks.
*
*     * 'css_prefix' :: The prefix to add to CSS classes, use 'csl_theme' to
*               enable generic themes.
*
*     * 'driver_defaults' :: A hash where the keys are the names of
*       support options for a Panhandler driver, and the values are
*       the names of Wordpress settings which will provide the
*       default values for those driver options.  See the method
*       'get_supported_options()' in the Panhandler code for a
*       description of driver options.  The names of the settings
*       should not include the prefix, i.e. write:
*
*           'driver_defaults' => array(
*               'keywords' => 'keywords'
*           )
*
*       instead of
*
*           'driver_defaults' => array(
*               'keywords' => 'csl-mp-ebay-keywords'
*           )
*
*     * 'name' :: The name of the plugin.
*
*     * 'prefix' :: A string used to prefix all of the Wordpress
*       settings for the plugin.
*
*     * 'support_url' :; The URL for the support page at WordPress
*
*     * 'purchase_url' :: The URL for purchasing the plugin
*
*     * 'url' :: The URL for the product page for purchases.
*
*     * 'has_packages' :: defaults to false, if true that means the main product is
*       not licensed but we still need the license class to manage add-ons.
*
**/


// The wpCSL version
//
if (!defined('WPCSL__slplus__VERSION')) { define('WPCSL__slplus__VERSION', '2.4'); }

// WP App Store Affiliate ID
//
if (!defined('WPAS_AFFILIATE_ID')) { define('WPAS_AFFILIATE_ID','3368'); }

/**
 * The base WPCSL class, to which all the other WPCSL objects get attached.
 *
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2013 Charleston Sofware Associates, LLC
 * @package wpCSL
 * @version 2.4
 *
 */
class wpCSL_plugin__slplus {

    //---------------------------------------------
    // Properties
    //---------------------------------------------

    /**
     * The registered admin page hooks for the plugin.
     * 
     * @var string[] $admin_slugs
     */
     private $admin_slugs = array();

    /**
     * Render the display panel on the settings interface? true = yes.
     *
     * @var boolean $display_settings render the display panel on the settings interface?
     */
    private $display_settings = false;

    /**
     * Debug My Plugin stack
     * 
     * named array, key is the panel ID
     * 
     * key is an array that is the params for the DMP function calls.
     * 
     * @var mixed[]
     */
    private $dmpStack = array('main' => array());

    /**
     * The plugin meta data.
     *
     * @var mixed[] $metadata
     */
    public $metadata;
    
    /**
     * The notification object, for display messages to users in the admin panel.
     * 
     * @var wpCSL_notifications__slplus $notifications 
     */
    public  $notifications;

    /**
     * The fully qualified directory name where the plugin is installed.
     *
     * @var string $plugin_path
     */
    public $plugin_path;

    /**
     * The URL that reaches the home directory for the plugin.
     *
     * @var string $plugin_url
     */
    public $plugin_url;

    /**
     * Turn on/off the monetary l10n settings.
     *
     * @var boolean $show_locale
     */
    private $show_locale;

    /**
     * The wpCSL style sheet handle.
     * 
     * @var string $styleHandle
     */
    private $styleHandle = 'wpcsl';

    //---------------------------------------------
    // Methods
    //---------------------------------------------

    /**
     * Run this whenever the class is instantiated.
     *
     * @param mixed[] $params a named array where key is the string of a wpCSL_plugin__slplus property, key is the initial value.
     */
    function __construct($params) {

        // TODO: Move these into the object init as needed.
        //
        require_once('CSL-helper_class.php');
        require_once('CSL-settings_class.php');

        // These settings can be overridden
        //
        $this->broadcast_url                = 'http://www.charlestonsw.com/signage/index.php';
        $this->columns                      = 1;
        $this->css_prefix                   = '';
        $this->current_admin_page           = '';
        $this->display_settings             = false;
        $this->display_settings_collapsed   = false;
        $this->driver_type                  = 'Panhandler';
        $this->has_packages                 = false;
        $this->no_license                   = false;
        $this->prefix                       = '';
        $this->show_locale                  = true;
        $this->shortcode_was_rendered       = false;
        $this->sku                          = '';
        $this->themes_enabled               = false;
        $this->use_obj_defaults             = true;
        $this->uses_money                   = true;
        
        // Set current admin page
        //
        if ( isset($_GET['page']) ) {
            $plugin_page = stripslashes($_GET['page']);
            $plugin_page = plugin_basename($plugin_page);
            $this->current_admin_page = $plugin_page;
        }
                
        // Do the setting override or initial settings.
        //
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }

        // Check to see if we are doing an update
        //
        if (isset($this->version)) {
            if ($this->version != get_option($this->prefix."-installed_base_version")) {
                if (isset($this->on_update)) {
                    call_user_func_array($this->on_update, array($this, get_option($this->prefix."-installed_base_version")));
                }
                update_option($this->prefix.'-installed_base_version', $this->version);

                $destruct_time = get_option($this->prefix."-notice-countdown");

                // We're doing an update, so check to see if they didn't check the check box,
                // and if they didn't... well, show it to them again
                if ($destruct_time) {
                    delete_option($this->prefix."-notice-countdown");
                }
            }
        }

        // Our Admin Page : true if we are on the admin page for this plugin
        // or we are processing the update action sent from this page
        //        
        $this->isOurAdminPage = ($this->current_admin_page == $this->prefix.'-options');

        if (!$this->isOurAdminPage) {
            $this->isOurAdminPage = 
                 isset($_REQUEST['action']) && 
                 ($_REQUEST['action'] === 'update') &&
                 isset($_REQUEST['option_page']) && 
                 (substr($_REQUEST['option_page'], 0, strlen($this->prefix)) === $this->prefix)
                 ;
        }


        // This test allows for direct calling of the options page from an
        // admin page call direct from the sidebar using a class/method
        // operation.
        //
        // To use: pass an array of strings that are valid admin page slugs for
        // this plugin.  You can also pass a single string, we catch that too.
        //
        if ((!$this->isOurAdminPage) && isset($this->admin_slugs)) {
           if (is_array($this->admin_slugs)) {
               foreach ($this->admin_slugs as $admin_slug) {
                $this->isOurAdminPage = ($this->current_admin_page === $admin_slug);
                if ($this->isOurAdminPage) { break; }
               }
           } else {
               $this->isOurAdminPage = ($this->current_admin_page === $this->admin_slugs);
           }
        }

        // Debugging Flag
        $this->debugging = (get_option($this->prefix.'-debugging') == 'on');
        
        // What prefix do we add to the CSS elements?
        if ($this->css_prefix == '') {
            $this->css_prefix = $this->prefix;
        }

        // Store the license option here to prevent
        // multiple DB lookups
        $this->purchased = false;

        // Determine whether or not we need to have a valid license
        // this will disable all license checking/presentation
        //
        if (!isset($this->paypal_button_id)) { $this->paypal_button_id = ''; }
        $this->no_license = ($this->paypal_button_id == '');

        // Make sure we have WP_Http for http posts
        // then instatiate it here in the http_handler property
        // of this class.
        //
        if( !class_exists( 'WP_Http' ) ) {
            include_once( ABSPATH . WPINC. '/class-http.php' );
        }
        if ( class_exists( 'WP_Http' ) ) {
            $this->http_handler = new WP_Http;
        } else if ($this->debugging) {
            print "WordPress HTTP Handler is not available.<br/>\n";
        }

        // Plugin Author URL
        //
        $this->url         = (isset($this->url          ) ? $this->url          : 'http://www.charlestonsw.com/');
        $this->support_url = (isset($this->support_url  ) ? $this->support_url  : $this->url                    );

        // Debugging Flag
        $this->debugging = (get_option($this->prefix.'-debugging') == 'on');

        if ($this->driver_type != 'none') {
            require_once('CSL-products_class.php');
            $this->products_config = array(
                'prefix'            => $this->prefix,
                'css_prefix'        => $this->css_prefix,
                'columns'           => $this->columns,
             );
        }            

        $this->settings_config = array(            
            'http_handler'      => $this->http_handler,
            'broadcast_url'     => $this->broadcast_url,
            'prefix'            => $this->prefix,
            'css_prefix'        => $this->css_prefix,
            'plugin_url'        => $this->plugin_url,
            'name'              => $this->name,
            'url'               => (isset($this->url)?$this->url:null),
            'paypal_button_id'  => $this->paypal_button_id,
            'no_license'        => $this->no_license,
            'sku'               => $this->sku,
            'has_packages'      => $this->has_packages,
            'parent'            => $this            
        );

        /**
         * Cache Object Config (if needed)
         */
        if  ($this->use_obj_defaults || ($this->cache_obj_name != 'none')) {
            require_once('CSL-cache_class.php');
            $this->cache_config = array(
                'prefix' => $this->prefix,
                'path' => (isset($this->cache_path)?$this->cache_path:$this->plugin_path)
            );
        }

        /**
         * Helper Object Config (if needed)
         */
        if  ($this->use_obj_defaults || ($this->helper_obj_name != 'none')) {
            $this->helper_config = array(
            'parent'            => $this
            );
        }
        
        /**
         * License Object Config (if needed)
         */
        if ($this->has_packages || !$this->no_license) {
            require_once('CSL-license_class.php');
            $this->license_config = array(
                'prefix'        => $this->prefix,
                'http_handler'  => $this->http_handler,
                'sku'           => $this->sku,
                'has_packages'  => $this->has_packages,
                'parent'        => $this
            );
        }            

        // Initialize
        $this->create_objects();
        $this->add_refs();
        if (isset($this->driver_name))
            $this->load_driver();
        $this->add_wp_actions();
    }

    /**-------------------------------------
     ** method: ok_to_show
     **
     ** returns true if... 
     **
     ** the plugin has been purchased
     ** the user is an admin
     **
     **/
    function ok_to_show() {
        global $current_user;

        // this instantiation already knows we're licensed
        if ($this->purchased) { 
            return true; // Short circuit, no need to set this again below

        // purchase already recorded
        } else if (get_option($this->prefix.'-purchased') == '1')  { 
            $this->purchased = true;
            return true;

        // user is an admin
        } else if (current_user_can('administrator')) {
            $this->purchased = true;
            return true;

        // purchase not recorded - recheck it on the server
        } else if ($this->no_license || $this->license->check_license_key())      { 
            $this->purchased = true;
            return true;
        }

        // We are not running a licensed copy
        // show the reason via debugging        
        if ($this->debugging) {
            print "Purchased flag: " . get_option($this->prefix.'-purchased') . "<br/>\n";
            if (!isset($current_user)) {
                print "Current user is not set.<br/>\n";
            } else {
                print "Current User ID: " . $current_user->ID . "<br/>\n";
                if ($current_user->ID > 0) {
                    print "Capabilities:<pre>\n";
                    print_r($current_user->wp_capabilities);
                    print "</pre>\n";
                } else {
                    print "You are not logged in.<br/>\n";
                }                    
            }
        }

        return false;                 // And tell our "callers"    
    }

    /**
     * Create a Map Settings Debug My Plugin panel.
     *
     * @return null
     */
    function create_DMPPanels() {
        if (!isset($GLOBALS['DebugMyPlugin'])) { return; }
        if (class_exists('DMPPanelSLPMain') == false) {
            require_once($this->plugin_path.'/WPCSL-generic/classes/class.dmppanels.php');
        }
        $GLOBALS['DebugMyPlugin']->panels['wpcsl.main']        = new DMPPanelWPCSLMain();
        $GLOBALS['DebugMyPlugin']->panels['wpcsl.settings']    = new DMPPanelWPCSLSettings();
    }

    /**-------------------------------------
     ** Method: CSL_ARRAY_FILL_KEYS
     ** Our own version of the php5.2 array_fill_keys
     ** So we can hopefully stay with php5.1 compatability
     **/
    function csl_array_fill_keys($target,$value='') {
        if(is_array($target)) {
            foreach($target as $key => $val) {
                $filledArray[$val] = is_array($value) ? $value[$key] : $value;
            }
        }
        return $filledArray;
    }
    

    /**-------------------------------------
     ** method: create_helper
     **
     ** Instantiates the helper class and attaches it to an instantiation
     ** of this class.
     **
     **/
    function create_helper($class = 'none') {
        switch ($class) {
            case 'none':
                break;

            case 'wpCSL_helper__slplus':
            case 'default':
            default:
                $this->helper = new wpCSL_helper__slplus($this->helper_config);

        }
    }    

    /**
     * Setup the WPCSL Notifications Object.
     *
     * Does not include the class or invoke the object if the class type is 'none'.
     *
     * @param string $class - 'none' to disable notifications
     */
    function create_notifications($class = 'none') {
        if ($class==='none') { return; }
        require_once('CSL-notifications_class.php');
        $this->notifications_config = array(
            'prefix' => $this->prefix,
            'name' => $this->name,
            'url' => 'options-general.php?page='.$this->prefix.'-options',
        );
        $this->notifications = 
            new wpCSL_notifications__slplus($this->notifications_config);
    }
   
    /**-------------------------------------
     ** method: create_products
     **/
    function create_products($class = 'none') {
        switch ($class) {
            case 'none':
                break;

            case 'wpCSL_products__slplus':
            case 'default':
            default:
                $this->products = new wpCSL_products__slplus($this->products_config);

        }
    }
    


    /***********************************************
     ** method: create_settings
     **/
    function create_settings($class = 'none') {
        switch ($class) {
            case 'none':
                break;

            case 'wpCSL_settings__slplus':
            case 'default':
            default:
                $this->settings = new wpCSL_settings__slplus($this->settings_config);

        }
    }


    /**
     * Create the theme object and attach it.
     *
     * @param string $class 'none' to disable themes.
     * @return null
     */
    function create_themes($class = 'none') {
        if ($class === 'none') { return; }
        require_once('CSL-themes_class.php');
        $this->themes_config = array(
            'prefix'        => $this->prefix,
            'plugin_path'   => $this->plugin_path,
            'plugin_url'    => $this->plugin_url,
            'support_url'   => $this->support_url,
            'parent'        => $this
        );
        $this->themes = new wpCSL_themes__slplus($this->themes_config);
    }    

    /**-------------------------------------
     ** method: create_license
     **/
    function create_license($class = 'none') {
        switch ($class) {
            case 'none':
                break;

            case 'wpCSL_license__slplus':
            case 'default':
            default:
                if ($this->has_packages || !$this->no_license) {
                    $this->license = new wpCSL_license__slplus($this->license_config);
                }

        }
    }

    /**-------------------------------------
     ** method: create_cache
     **/
    function create_cache($class = 'none') {
        switch ($class) {
            case 'none':
                break;

            case 'wpCSL_cache__slplus':
            case 'default':
            default:
                $this->cache = new wpCSL_cache__slplus($this->cache_config);

        }
    }

    /**-------------------------------------
     ** method: create_options_page
     **/
    function create_options_page() {
        add_options_page(
            $this->name . ' Options',
            $this->name,
            'administrator',
            $this->prefix . '-options',
            array(
                $this->settings,
                'render_settings_page'
            )
        );
    }

    /**-------------------------------------
     ** method: create_objects
     **/
    function create_objects() {
        
        // use_obj_defaults is set, use the invoke the default 
        // set of wpCSL objects
        //
        if (isset($this->use_obj_defaults) && $this->use_obj_defaults) {
            $this->create_helper('default');
            $this->create_notifications('default');
            $this->create_products('default');
            $this->create_settings('default');
            if ($this->has_packages || !$this->no_license) { $this->create_license('default'); }
            $this->create_cache('default');
            $this->create_themes('default');
            
        // Custom objects are in place
        //
        } else {
            if (isset($this->helper_obj_name))
                $this->create_helper($this->helper_obj_name);
            if (isset($this->notifications_obj_name))
                $this->create_notifications($this->notifications_obj_name);
            if (isset($this->products_obj_name))
                $this->create_products($this->products_obj_name);
            if (isset($this->settings_obj_name))
                $this->create_settings($this->settings_obj_name);
            if (($this->has_packages || !$this->no_license) && isset($this->license_obj_name))
                $this->create_license($this->license_obj_name);
            if (isset($this->cache_obj_name))
                $this->create_cache($this->cache_obj_name);
            if (isset($this->themes_obj_name))
                $this->create_themes($this->themes_obj_name);
        }
    }

    /***********************************************
     ** method: add_refs
     ** What did you say? Refactoring what now? I don't know what that is
     **
     ** This connects the instantiated objects of other classes that are
     ** properties of the main CSL-plugin class to each other.  For example
     ** it ensures each of the other classes can access the notification
     ** object for the main plugin.
     **
     ** settings    <= notifications, license, cache, themes
     ** themes      <= settings, notifications, products
     ** cache       <= settings, notifications
     ** helper      <= notifications
     ** license     <= notifications
     ** products    <= notifications
     **
     **/
    function add_refs() {
        // Notifications doesn't require any other objects yet

        // Settings
        if (isset($this->settings)) {
            if (isset($this->notifications) && !isset($this->settings->notifications))
                $this->settings->notifications = &$this->notifications;
            if (isset($this->license) && !isset($this->settings->license))
                $this->settings->license = &$this->license;
            if (isset($this->cache) && !isset($this->settings->cache))
                $this->settings->cache = &$this->cache;
            if (isset($this->themes) && !isset($this->settings->themes))
                $this->settings->themes = &$this->themes;
        }

        // Cache
        if (isset($this->cache)) {
            if (isset($this->settings) && !isset($this->cache->settings))
                $this->cache->settings = &$this->settings;
            if (isset($this->notifications) && !isset($this->cache->notifications))
                $this->cache->notifications = &$this->notifications;
        }

        // Helper
        if (isset($this->helper)) {
            if (isset($this->helper) && !isset($this->helper->notifications))
                $this->helper->notifications = &$this->notifications;
        }
        
        // License
        if ($this->has_packages || !$this->no_license) { 
            if (isset($this->license)) {
                if (isset($this->notifications) && !isset($this->license->notifications))
                    $this->license->notifications = &$this->notifications;
            }
        }

        // Products
        if (isset($this->products)) {
            if (isset($this->products) && !isset($this->products->notifications))
                $this->products->notifications = &$this->notifications;
        }
        
        // Themes
        if (isset($this->themes)) {
            if (isset($this->themes) && !isset($this->themes->notifications))
                $this->themes->notifications = &$this->notifications;
            if (isset($this->settings) && !isset($this->themes->settings))            
                $this->themes->settings = &$this->settings;
            if (isset($this->products) && !isset($this->themes->products))            
                $this->themes->products = &$this->products;
        }
    }

    /**
     * Setup WordPress action scripts.
     *
     * Note: admin_menu is not called on every admin page load
     * Reference: http://codex.wordpress.org/Plugin_API/Action_Reference
     */
    function add_wp_actions() {
        if ( is_admin() ) {
            add_action('admin_menu'             ,array($this,'create_options_page'      )   );
            add_action('admin_init'             ,array($this,'admin_init'               ),50);
            add_action('admin_enqueue_scripts'  ,array($this,'enqueue_admin_stylesheet' )   );
            add_action('admin_notices'          ,array($this->notifications, 'display'  )   );
            add_action('dmp_addpanel'           ,array($this,'create_DMPPanels'         )   );
        } else {
            if (!$this->themes_enabled && !$this->no_default_css) {
                // non-admin enqueues, actions, and filters
                add_action('wp_head', array($this, 'checks'));
                add_filter('wp_print_scripts', array($this, 'user_header_js'));
                add_filter('wp_print_styles', array($this, 'user_header_css'));
            }
        }

        add_filter('plugin_row_meta', array($this, 'add_meta_links'), 10, 2);

        // Only add shortcodes if there is a driver to use
        if (isset($this->driver)) {
            // Custom shortcodes
            if (isset($this->shortcodes)) {
                if (is_array($this->shortcodes)) {
                    foreach ($this->shortcodes as $shortcode) {
                        $shortcode_lc = strtolower($shortcode);
                        $shortcode_uc = strtoupper($shortcode);
                        add_shortcode($shortcode, array($this, 'shortcode_show_items'));
                        add_shortcode($shortcode_lc, array($this, 'shortcode_show_items'));
                        add_shortcode($shortcode_uc, array($this, 'shortcode_show_items'));
                    }
                } else {
                        $shortcode_lc = strtolower($shortcode);
                        $shortcode_uc = strtoupper($shortcode);
                        add_shortcode($shortcode, array($this, 'shortcode_show_items'));
                        add_shortcode($shortcode_lc, array($this, 'shortcode_show_items'));
                        add_shortcode($shortcode_uc, array($this, 'shortcode_show_items'));
                }
            } 

            // Automatic shortcodes
            // This should cover any basic typos involving dashes or underscores
            add_shortcode($this->prefix.'_show-items', array($this, 'shortcode_show_items'));
            add_shortcode($this->prefix.'_show_items', array($this, 'shortcode_show_items'));
            add_shortcode($this->prefix.'-show-items', array($this, 'shortcode_show_items'));
            add_shortcode($this->prefix.'-show_items', array($this, 'shortcode_show_items'));
            
        // No Driver
        //
        } else {
            if (($this->debugging) && ($this->driver_type != 'none')) {
                print __('DEBUG: No driver found.',WPCSL__slplus__VERSION);
            }
        }
    }

    /**-------------------------------------
     ** method: add_meta_links
     **/
    function add_meta_links($links, $file) {

        if ($file == $this->basefile) {
            if (isset($this->support_url)) {
                $links[] = '<a href="'.$this->support_url.'" title="'.__('Support',WPCSL__slplus__VERSION) . '">'.
                            __('Support',WPCSL__slplus__VERSION) . '</a>';
            }
            if (isset($this->purchase_url)) {
                $links[] = '<a href="'.$this->purchase_url.'" title="'.__('Purchase',WPCSL__slplus__VERSION) . '">'.
                            __('Buy Now',WPCSL__slplus__VERSION) . '</a>';
            }
            $links[] = '<a href="options-general.php?page='.$this->prefix.'-options" title="'.
                            __('Settings',WPCSL__slplus__VERSION) . '">'.__('Settings',WPCSL__slplus__VERSION) . '</a>';
        }
        return $links;
    }

    /**
     * WordPress admin_init hook (runs after admin_menu has run)
     */
    function admin_init() {
        if ($this->display_settings) { $this->add_display_settings(); }
        $this->settings->register();
        $this->checks();
    }

    /**-------------------------------------
     ** method: checks
     **/
    function checks() {
        if (isset($this->cache)) {
            $this->cache->check_cache();
        }

        if (!$this->has_packages && isset($this->license)) {
            $this->license->check_product_key();
        }
    }


    /**-------------------------------------
     ** method: load_driver
     **
     **
     ** This function loads the data driver for this plugin.
     ** The legacy code was very Panhandler centric, so that is
     ** still in place with new hooks to load other "Custom" drivers
     **/
    function load_driver() {

        // Load Panhandler class and drivers if not already loaded
        //
        if ( 
            ($this->driver_type == 'Panhandler') && 
            file_exists($this->plugin_path . 'Panhandler/Panhandler.php')
            ) {
                if (!class_exists('PanhandlerProduct')) {
                    require_once($this->plugin_path . 'Panhandler/Panhandler.php');
                }

                try {
                    require_once($this->plugin_path . 'Panhandler/Drivers/'. 
                                    $this->driver_name .'.php');
                }
                catch (PanhandlerError $e) {
                    $this->notifications->add_notice(1, $e->getMessage());
                }

        // Load Custom class and drivers if not already loaded
        //
        } else {
            if (file_exists($this->plugin_path . 'Custom/Drivers/'. $this->driver_name .'.php')) {
                if (!class_exists($this->driver_name . 'Driver')) {
                    try {
                        require_once($this->plugin_path . 'Custom/Drivers/'. $this->driver_name .'.php');
                    }
                    catch (Exception $e) {
                        $this->notifications->add_notice(1, $e->getMessage());
                    }
                }

            // No Driver Found
            //
            } else {
                if ($this->debugging) {
                    print __('DEBUG: could not locate driver:',WPCSL__slplus__VERSION) . 
                        $this->plugin_path . 'Custom/Drivers/'. $this->driver_name .'.php' .
                        "<br/>\n";                                        
                }
            }                
        }

        // The driver class should now exist, let's load it's definition
        //
        if (class_exists($this->driver_name.'Driver')) {
            try {
                // Add http_handler to driver_args array
                //
                if ( isset($this->driver_args) )  {
                    $this->driver_args = array_merge(
                            array(
                                'http_handler'  => $this->http_handler,
                                'debugging'     => $this->debugging,
                                'prefix'        => $this->prefix,
                                'parent'        => $this
                                ),
                            $this->driver_args
                            );
                } else {
                    $this->driver_args = 
                            array(
                                'http_handler'  => $this->http_handler,
                                'debugging'     => $this->debugging,
                                'prefix'        => $this->prefix,
                                'parent'        => $this
                                );
                }

                // Invoke the driver via reflection classes
                //
                $reflectionDriver = new ReflectionClass($this->driver_name . 'Driver');
                $this->driver = $reflectionDriver->newInstanceArgs(array($this->driver_args));
            }
            catch (Exception $e) {
                $this->notifications->add_notice(1, $e->getMessage());
            }
        }
    }

    /**
     * Add the display settings section to the admin panel.
     *
     * @return none
     */
    function add_display_settings() {      
        $this->settings->add_section(array(
                'name' => __('Display Settings',WPCSL__slplus__VERSION),
                'description' => '',
                'start_collapsed' => $this->display_settings_collapsed
            )
        );
        
        if ($this->themes_enabled) {
            $this->themes->add_admin_settings();
        }

        // If we have an exec function and get locales, show the pulldown.
        //        
        if ($this->show_locale){
            setlocale(LC_MONETARY, get_option($this->prefix.'-locale',get_locale()));

            // Exec function exists.
            // Exec is not disabled.
            // Safe Mode is not on.
            $exec_enabled =
                 function_exists('exec')                                            &&
                 !in_array('exec', array_map('trim',explode(', ', ini_get('disable_functions'))))     &&
                 (strtolower( ini_get( 'safe_mode' ) ) != 'off')
                 ;

            if ($exec_enabled) {

                if (exec('locale -a', $locales)) {
                    $locale_custom = array();
        
                    foreach ($locales as $locale) {
                        $locale_custom[$locale] = $locale;
                    }
        
                    $this->settings->add_item(
                        'Display Settings', 
                        'Locale', 
                        'locale', 
                        'list', 
                        false, 
                        __('Sets the locale for PHP program processing, affects time and currency processing. '.
                            'If you change this, save settings and then select money format.',WPCSL__slplus__VERSION),
                        $locale_custom
                    );
                }
            } else {
                    $this->settings->add_item(
                        'Display Settings', 
                        'Locale', 
                        'locale', 
                        null, 
                        false, 
                        __('Your PHP settings have disabled exec(), your locale list cannot be determined.',WPCSL__slplus__VERSION),
                        '&nbsp;'
                    );
            }
        }            

        // Show money pulldown if we are using Panhandler or have set the uses_money flag
        //
        if  (
            ($this->show_locale) &&
            (($this->driver_type == 'Panhandler') || $this->uses_money) && (function_exists('money_format')) 
            ) {
                $this->settings->add_item(
                    'Display Settings', 
                    'Money Format', 
                    'money_format', 
                    'list', 
                    false, 
                    __('This is based on your current locale, which is set to ',WPCSL__slplus__VERSION).
                        '<code>'. setlocale(LC_MONETARY, 0) .'</code>',
                    array(
                        money_format('%!i', 1234.56)            => '%!i',
                        money_format('%!^i', 1234.56)           => '%!^i',
                        money_format('%!=*(#10.2n', 1234.56)    => '%!=*(#10.2n',
                        money_format('%!=*^-14#8.2i', 1234.56)  => '%!=*^-14#8.2i'
                        )
                    );
        }
        
       if (isset($this->rate_url)){

        	$time = time(); 
            
            //-use this to force the notification for 72 hours checked or not
            //update_option($this->prefix."-notice-countdown", $destruct_time);
            
            $destruct_time = get_option($this->prefix."-notice-countdown", ($time+(3*24*60*60)));
            // have we already expired a timer
            if ($destruct_time === false) {
                return;
            }
            
            if ($destruct_time === true) {
                //if you want something special to happen to people that did not check
                // the check box to turn this off, here's the place to do it...

                return;
            }
            
            $hours_remaining = '';

            $suffix = array('d' => 86400, 'h' => 3600, 'm' => 60,);

            $remainder = abs($destruct_time - $time);

            foreach($suffix as $key => $val) {
                $$key = floor($remainder/$val);
                $remainder -= ($$key*$val);
                $hours_remaining .= ($$key==0) ? '' : $$key . "$key ";
            }

            $hours_remaining .= $remainder . 's ';
            
        	$this->settings->add_item(
        		'Display Settings',
        		'Turn off rate notification', 
        		'thisbox', 
        		'checkbox', 
        		false, 
        		__('This will disable the notification asking you to rate our product.',WPCSL__slplus__VERSION)
        		);
        	
            //if the checkbox is not checked
            if($this->settings->get_item('thisbox')==false){
                //and there is still time left on the timer
                if ($time < $destruct_time){
                    //add our notice
                    $this->notifications->add_notice(
                        9,
                        __("<a href='{$this->rate_url}' target='_blank'>Rating {$this->name}</a> "                  .
                        'a great, and free, way to show your support!  '                                              .
                        "Is something not right? <a href='{$this->forum_url}' target='_blank'>Let me know</a>. "    .
                        '<br/>'                                                                                     .
                        'This message will self destruct in: '.$hours_remaining.' '                                 .
                        'You can turn off this message sooner via the Display Settings on the <a href="'                                    .
                            admin_url().'/options-general.php?page='.$this->prefix.'-options#display_settings">'    .
                        'general settings page.</a> '
                        ,
                        WPCSL__slplus__VERSION)
                    );
                }

            //checkbox was hit, so update to false
            } else {
                update_option($this->prefix."-notice-countdown", false);
            }
            
            //is the timer up?
            if ($time >= $destruct_time) {
                //if the checkbox has been hit, then set to false
                if ($this->settings->get_item('thisbox')==true) {
                    $destruct_time = false;
                }
                //if not then set it to true
                else {
                    $destruct_time = true;
                }
        	}

            update_option($this->prefix."-notice-countdown", $destruct_time);
        }
    }

    /**
     * Add DebugMyPlugin messages.
     *
     * @param string $panel - panel name
     * @param string $type - what type of debugging (msg = simple string, pr = print_r of variable)
     * @param string $header - the header
     * @param string $message - what you want to say
     * @param string $file - file of the call (__FILE__)
     * @param int $line - line number of the call (__LINE__)
     * @param boolean $notime - show time? default true = yes.
     * @return null
     */
    function debugMP($panel='main', $type='msg', $header='wpCSL DMP',$message='',$file=null,$line=null,$notime=false,$clearingStack=false) {

        // Panel not setup yet?  Push onto stack.
        //
        if (
            !isset($GLOBALS['DebugMyPlugin']) ||
            !isset($GLOBALS['DebugMyPlugin']->panels[$panel])
           ) {
            if (!isset($this->dmpStack[$panel])) { $this->dmpStack[$panel] = array(); }
            array_push($this->dmpStack[$panel],array($type,$header,$message,$file,$line,$notime));
            return;
        }

        // Have waiting messages?  Pop off stack.
        //
        if (!$clearingStack && isset($this->dmpStack[$panel]) && is_array($this->dmpStack[$panel])) {
            while ($dmpMessage = array_shift($this->dmpStack[$panel])) {
                $this->debugMP($panel,$dmpMessage[0],$dmpMessage[1],$dmpMessage[2],$dmpMessage[3],$dmpMessage[4],$dmpMessage[5],true);
            }
        }

        // Do normal real-time message output.
        //
        switch (strtolower($type)):
            case 'pr':
                $GLOBALS['DebugMyPlugin']->panels[$panel]->addPR($header,$message,$file,$line,$notime);
                break;
            default:
                $GLOBALS['DebugMyPlugin']->panels[$panel]->addMessage($header,$message,$file,$line,$notime);
        endswitch;
    }

    /**-------------------------------------
     * method: display_objects
     *
     * This method generates the HTML that will be used to display
     * the HTML output for this plugin.
     *
     * Parameters:
     * $objectlist    (named array) - an array of the objects to render
     *
     * Returns:
     * A basic error message string if the render class is missing, otherwise
     * the HTML that was returned from the render_objects_to_HTML method in
     * the driver class.
     *
     **/
    function display_objects($objectlist = NULL) {
        $HTML_to_display = 'Could not figure out how to display the data for this shortcode.';
        if ( is_callable(array($this->driver,'render_objects_to_HTML'), true)) {
            $HTML_to_display = $this->driver->render_objects_to_HTML($objectlist);
        }
        return $HTML_to_display;
    }
    
    /**-------------------------------------
     ** method: render_shortcode
     **
     ** process the shortcode for custom data drivers
     ** should get back an HTML string to replace the shortcode with
     **
     **/
    function render_shortcode($atts) {
        $HTML_to_display = 'Could not figure out how to display this shortcode.';
        if ( is_callable(array($this->driver,'render_shortcode_as_HTML'), true)) {
            $HTML_to_display = $this->driver->render_shortcode_as_HTML($atts);
        }
        return $HTML_to_display;
    }
    
    
    /**-------------------------------------
     * Method: SHORTCODE_SHOW_ITEMS
     *
     * Shows the products in a formatted output on the page wherever the shortcode appears.
     * This is the default output, custom shortcodes and functions can be put in the main
     * calling function.
     *
     */
    function shortcode_show_items($atts, $content = NULL) {
        if ( $this->ok_to_show() ) {
            $this->shortcode_was_rendered = true;
            
            $content = '';

            // Debugging
            //
            if ($this->debugging) {
                if (is_array($atts)) {
                    print __('DEBUG: Shortcode called with attributes:',WPCSL__slplus__VERSION) . "<br/>\n";
                    foreach ($atts as $name=>$value) {
                        print $name.':'.$value."<br/>\n";
                    }
                } else {
                    print __('DEBUG: Shortcode called with no attributes.',WPCSL__slplus__VERSION) . "<br/>\n";
                }
            }            
            
            // Filter out erroneous attributes
            if (is_array($atts)) {
                $atts = array_intersect_key( $atts, 
                            $this->csl_array_fill_keys( $this->driver->get_supported_options(), 
                                                        'temp' ) 
                        );
            }

            // We need some user defaults

            // If there's a custom array set, use that to populate the list
            if (isset($this->driver_defaults) && is_array($this->driver_defaults)) {
                $defaults = $this->apply_driver_defaults($this->driver_defaults);
            } else {
                // Otherwise, grab all of the user defaults from wordpress
                foreach($this->driver->get_supported_options() as $key) {
                    if (get_option($this->prefix .'-'. $key)) {
                        $defaults[$key] = get_option($this->prefix .'-'. $key);
                    }
                }
            }

            // Send them to the driver (if they exist)
            if (isset($defaults)) {
                $this->driver->set_default_option_values($defaults);
            }

            // Render a list of objects to HTML (usually products)
            //
            if (
                ($this->driver_type == 'Panhandler') ||
                ($this->driver_type == 'product')
                ) {
                $content = $this->render_object_list($atts);

            // Custom data driver
            //
            } elseif ($this->driver_type == 'custom') {
                $content = $this->render_shortcode($atts);            
            }

            
        // Not OK TO Show
        } else {
            if ($this->debugging) {
                $content = __('DEBUG: Not OK To Show',WPCSL__slplus__VERSION);
            }
        }
        return $content;
    }
    
    /**-------------------------------------
     ** method: render_object_list
     **
     ** Show products via the shortcode processor.
     **
     ** This is legacy code that came out of shortcode_show_items.
     ** It was separated to continue the generalization of wpCSL.
     **
     ** returns: a string that represents the product info in HTML format
     **
     **/
    function render_object_list($atts) {
        // Fetch the products
        // Check the cache first, then go direct to the source
        //
        if (isset($this->cache) && get_option($this->prefix.'-cache_enable')) {
            if (!($products = $this->cache->load(md5(implode(',',(array)$atts)))) ) {
                $products = $this->driver->get_products($atts);
            }
        } else {
            try {
                $products = $this->driver->get_products($atts);
            }

            // Deal with errors
            // These should probably be posted to the notifications system...
            catch (PanhandlerError $error) {
                return $error->message;
            }
        }

        // If there was an error show that and exit,
        // otherwise save the returned data to the cache if it is enabled
        //
        if (is_a($products, 'PanhandlerError')) return $products->message;
        else {
            if (isset($this->cache) && get_option($this->prefix.'-cache_enable')) {
                $this->cache->save(md5(implode(',', (array)$atts)), $products);
            }
        }

        // If there are products, return the HTML that will display them
        // otherwise return the simple "No products found" message.
        //
        if (count($products) > 0) {

            // Legacy Panhandler Stuff
            //
            if (is_a($products[0], 'PanhandlerProduct')) {
                $content = $this->products->display_products($products);

                // Object Display, yes Panhandler appendages
                // still abound leaving us with a $products var name
                // for now.
                //
            } else {
                $content = $this->display_objects($products);
            }   
            
        // No products, show an error message as the output
        //
        } else {
            $content= __('No products found',WPCSL__slplus__VERSION);
        }

        return $content;            
    }

    /**-------------------------------------
     ** method: user_header_js
     **/
    function user_header_js() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('thickbox');
    }

    /**-------------------------------------
     ** method: user_header_css
     **/
    function user_header_css() {

        $cssPath = '';
        if (isset($this->css_url)) {            
            $cssPath = $this->css_url;
        } else if (isset($this->plugin_url)) {
            if ( file_exists($this->plugin_path.'/css/'.$this->prefix.'.css') ) {
                $cssPath = $this->plugin_url . '/css/'.$this->prefix.'.css';
            }
        }
        
        if ($cssPath != '') {
            wp_enqueue_style(
                    $this->prefix.'css',
                    $cssPath
                    );
        }            
        wp_enqueue_style('thickbox');
    }

    /**-------------------------------------
     ** method: apply_driver_defaults
     **
     ** Populate an array with values from wordpress if they exist, will
     ** propogate through an array structure recursively
     **/
    function apply_driver_defaults(&$defaults) {
        $results = array();
        foreach ($defaults as $key => $value) {
            if (is_array($value)) {
                $results[$key] = $this->apply_driver_defaults($value);
            }
            else {
                if (get_option($this->prefix .'-'.$value)) {
                    $results[$value] = get_option($this->prefix .'-'.$value);
                }
            }
        }

        return $results;
    }

    /**
     * Enqueue the admin stylesheet when needed.
     *
     * @var string $hook
     */
    function enqueue_admin_stylesheet($hook) {
        $this->debugMP('main','msg','wpCSL.enqueue_admin_stylesheet('.$hook.')','',NULL,NULL,true);

        // The CSS file must exists where we expect it and
        // The admin page being rendered must be in "our family" of admin pages
        //
        if (    file_exists($this->plugin_path.'/WPCSL-generic/assets/wpcsl.css') &&
                array_search($hook, apply_filters('wpcsl_admin_slugs',$this->admin_slugs))
           ) {
            wp_register_style($this->styleHandle, $this->plugin_url .'/WPCSL-generic/assets/wpcsl.css');
            wp_enqueue_style($this->styleHandle);

            if (file_exists($this->plugin_path.'/WPCSL-generic/assets/wpcsl-admin-interface.js')) {
                wp_enqueue_script(
                        $this->styleHandle,
                        $this->plugin_url .'/WPCSL-generic/assets/wpcsl-admin-interface.js',
                        'jquery',
                        WPCSL__slplus__VERSION,
                        true
                        );
            }
        }
    }

    /**-----------------------------------
     * method: http_result_is_ok()
     *
     * Determine if the http_request result that came back is valid.
     *
     * params:
     *  $result (required, object) - the http result
     *
     * returns:
     *   (boolean) - true if we got a result, false if we got an error
     */
    function http_result_is_ok($result) {

        // Yes - we can make a very long single logic check
        // on the return, but it gets messy as we extend the
        // test cases. This is marginally less efficient but
        // easy to read and extend.
        //
        if ( is_a($result,'WP_Error') ) { return false; }
        if ( !isset($result['body'])  ) { return false; }
        if ( $result['body'] == ''    ) { return false; }

        return true;
    }
}

