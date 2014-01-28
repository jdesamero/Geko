<?php

/**
 * The base plugin class for Store Locator Plus.
 *
 * "gloms onto" the WPCSL base class, extending it for our needs.
 *
 * @package StoreLocatorPlus
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 *
 */
class SLPlus extends wpCSL_plugin__slplus {

    /**
     * An array of the add-on slugs that are active.
     * 
     * @var string[] active add-on slugs 
     */
    public $addons = array();

    /**
     * The current location.
     * 
     * @var SLPlus_Location $currentLocation
     */
    public $currentLocation;

    /**
     * The global $wpdb object for WordPress.
     *
     * @var wpdb $db
     */
    public $db;

    /**
     * Array of slugs + booleans for plugins we've already fetched info for.
     * 
     * @var array[] named array, key = slug, value = true
     */
    public $infoFetched = array();

    /**
     * The options that the user has set for Store Locator Plus.
     *
     * Key is the name of a supported option, value is the default value.
     *
     * Anything stored in here also gets passed to the csl.js via the slplus.options object.
     * Reference the settings in the csl.js via slplus.options.<key>
     *
     * These elements are LOADED EVERY TIME the plugin starts.
     *
     * @var mixed[] $options
     */
    public  $options                = array(
        'initial_radius'        => '10000',
        'slplus_version'        => SLPLUS_VERSION
        );

    /**
     * The settings that impact how the plugin renders.
     * 
     * These elements are ONLY WHEN wpCSL.helper.loadPluginData() is called.
     *
     * @var mixed[] $data
     */
    public $data;

    /**
     * Full path to this plugin directory.
     *
     * @var string $dir
     */
    private $dir;

    /**
     * Sets the values of the $data array.
     *
     * Drives the wpCSL loadPluginData method.
     *
     * This has a method to tell it HOW to load the data.
     *   via a simple get_option() call or via the wpCSL.settings.getitem() call.
     *
     * wpCSL getitem() looks for variations in the option names based on an option "root" name.
     *
     * @var mixed $dataElements
     */
    public $dataElements;

    /**
     * Set to true if the plugin data was already loaded.
     *
     * @var boolean $pluginDataLoaded
     */
    public $pluginDataLoaded = false;

    /**
     * What slug do we go by?
     *
     * @var string $slug
     */
    public $slug;

    /**
     * Full URL to this plugin directory.
     *
     * @var string $url
     */
    public $url;

    /**
     * Initialize a new SLPlus Object
     *
     * @param mixed[] $params - a named array of the plugin options for wpCSL.
     */
    public function __construct($params) {
        global $wpdb;
        $this->db = $wpdb;
        $this->url  = plugins_url('',__FILE__);
        $this->dir  = plugin_dir_path(__FILE__);
        $this->slug = plugin_basename(__FILE__);

        parent::__construct($params);
        $this->currentLocation = new SLPlus_Location(array('plugin'=>$this));
        $this->themes->css_dir = SLPLUS_PLUGINDIR . 'css/';
        $this->initOptions();
        $this->initData();
        do_action('slp_invocation_complete');
        $this->debugMP('slp.main','msg','Store Locator Plus invocation complete.');
    }

    /**
     * Initialize the options properties from the WordPress database.
     */
    function initOptions() {
        $dbOptions = get_option(SLPLUS_PREFIX.'-options');
        if (is_array($dbOptions)) {
            $this->options = array_merge($this->options,$dbOptions);
        }
    }

    /**
     * Set the plugin data property.
     *
     * Plugin data elements, helps make data lookups more efficient
     *
     * 'data' is where actual values are stored
     * 'dataElements' is used to fetch/initialize values whenever helper->loadPluginData() is called
     *
     * FILTER: slp_attribute_values
     * This filter only fires at the very start of SLP, it may not run add-on pack stuff.
     *
     * FILTER: wpcsl_loadplugindata__slplus
     * This filter fires much later, only when loadplugindata() methods are called in wpcsl.
     *
     * The slp_attribute_values fitler takes an array of arrays.
     *
     * The outter array is a list of instructions for setting the data property of this class.
     *
     * The inner array has 3 elements:
     *     first element is the name of the data property, the 'blah' in $this->data['blah'].
     *     second element is the method to employ to set the element, 'get_option' or 'get_item'.
     *     third element is the parameters to send along to the get_option or get_item method.
     *
     * If the second element is 'get_option' the third element can be:
     *     null - in this case $this->data['blah'] is set to get_option('blah')
     *     array('moreblah') - in this case $this->data['blah'] = get_option('moreblah')
     *     array('moreblah','default') - $this->data['blah'] = get_option('moreblah','default')
     *
     * get_option('moreblah','default') returns the value 'default' if the option 'moreblah' does not exist in the WP options table.
     */
    function initData() {
        $this->data = array();
        $this->dataElements =
            apply_filters('slp_attribute_values',
                array(
                      array(
                        'sl_admin_locations_per_page',
                        'get_option',
                        array('sl_admin_locations_per_page','25')
                      ),
                      array(
                        'sl_map_end_icon'                   ,
                        'get_option'                ,
                        array('sl_map_end_icon'         ,SLPLUS_ICONURL.'bulb_azure.png'    )
                      ),
                      array('sl_map_home_icon'              ,
                          'get_option'              ,
                          array('sl_map_home_icon'      ,SLPLUS_ICONURL.'box_yellow_home.png'  )
                      ),
                      array('sl_map_height'         ,
                          'get_option'              ,
                          array('sl_map_height'         ,'480'                                  )
                      ),
                      array('sl_map_height_units'   ,
                          'get_option'              ,
                          array('sl_map_height_units'   ,'px'                                   )
                      ),
                      array('sl_map_width'          ,
                          'get_option'              ,
                          array('sl_map_width'          ,'100'                                  )
                      ),
                      array('sl_map_width_units'    ,
                          'get_option'              ,
                          array('sl_map_width_units'    ,'%'                                    )
                      ),
                      array('theme'                 ,
                          'get_item'                ,
                          array('theme'                 ,'default'                              )
                      ),
                )
           );
    }

    /**
     * Set valid options from the incoming REQUEST
     *
     * @param mixed $val - the value of a form var
     * @param string $key - the key for that form var
     */
    function set_ValidOptions($val,$key) {
        if (array_key_exists($key, $this->options)) {
            $this->options[$key] = $val;
            $this->debugMP('slp.main','msg',"SLP.set_ValidOptions $key was set to $val.");
        }
     }

    /**
     * Register an add-on pack.
     * 
     * @param string $slug
     */
    public function register_addon($slug) {
        $slugparts = explode('/', $slug);
        $this->addons[] = str_replace('.php','',$slugparts[count($slugparts)-1]);
    }

    /**
     * Build a query string of the add-on packages.
     *
     * @return string
     */
    public function create_addon_query() {
        return http_build_query($this->addons,'addon_');
    }
}
