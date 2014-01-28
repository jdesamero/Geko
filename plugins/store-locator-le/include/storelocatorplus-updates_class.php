<?php
/**
 * Store Locator Plus update manager.
 *
 * Checks remote CSA server for add-on pack updates.
 *
 * @package StoreLocatorPlus\Updates
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 */
class SLPlus_Updates {

    /**
     * The plugin current version
     * @var string
     */
    public $current_version;
    /**
     * The plugin remote update path
     * @var string
     */
    public $update_path;

    /**
     * The global plugin.
     * 
     * @var \SLPlus
     */
    private $plugin;

    /**
     * Plugin Slug (plugin_directory/plugin_file.php)
     * @var string
     */
    public $plugin_slug;
    /**
     * Plugin name (plugin_file)
     * @var string
     */
    public $slug;

    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $current_version
     * @param string $update_path
     * @param string $plugin_slug
     */
    function __construct($current_version, $update_path, $plugin_slug)
    {
        global $slplus_plugin;

        // Set the class public variables
        $this->plugin = $slplus_plugin;
        $this->current_version = $current_version;
        $this->plugin_slug = $plugin_slug;
        list ($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);
        $this->update_path = $update_path . '?slug='.$this->slug;

        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        
        // Define the alternative response for information checking
        add_filter('plugins_api', array($this, 'check_info'), 10, 3);
    }
    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     *
     * @param $transient
     * @return object $ transient
     */
    public function check_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Get the remote version
        $remote_version = $this->getRemote_version();

        // If a newer version is available, add the update
        if (isset($GLOBALS['DebugMyPlugin'])) {
            error_log('slug ' . $this->slug . ' current version ' . $this->current_version . ' remote version ' . $remote_version);
        }
        if (version_compare($this->current_version, $remote_version, '<')) {
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            $obj->url = $this->update_path;
            $obj->package = $this->update_path;
            $transient->response[$this->plugin_slug] = $obj;
        }
        return $transient;
    }
    
    /**
     * Add our self-hosted description to the filter
     *
     * @param mixed $orig original incoming args
     * @param array $action
     * @param object $arg
     * @return bool|object
     */
    public function check_info($orig, $action, $arg)
    {
        // No slug? Not plugin update.
        //
        if (empty($arg->slug)) { return $orig; }
        if (!in_array($arg->slug,$this->plugin->addons)) { return $orig; }

        if (isset($GLOBALS['DebugMyPlugin'])) {
            error_log('check info for action ' . $action . ' arg slug ' . $arg->slug);
        }

        if (!isset($this->plugin->infoFetched[$arg->slug])) {
            $information = $this->getRemote_information($arg->slug);
            $this->plugin->infoFetched[$arg->slug] = true;
            if (isset($GLOBALS['DebugMyPlugin'])) {
                error_log(' plugin info '. print_r($information,true));
            }
            return $information;
        }
        return $orig;
    }
    /**
     * Return the remote version
     * @return string $remote_version
     */
    public function getRemote_version()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'version', 'slug' => $this->slug)));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;
    }
    /**
     * Get information about the remote version
     * @return mixed[] false if cannot get info, unserialized info if we could
     */
    public function getRemote_information($slug=null) {
        if ($slug===null) { $slug = $this->slug; }

        if (isset($GLOBALS['DebugMyPlugin'])) {
            error_log('SLPlus_Updates.getRemote_information()');
        }
        
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'info', 'slug' => $slug)));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            if (isset($GLOBALS['DebugMyPlugin'])) {
                error_log('retrieved remote info for ' . $slug);
            }
            return unserialize($request['body']);
        }
        if (isset($GLOBALS['DebugMyPlugin'])) {
            error_log('remote info retrieval failed for ' . $slug);
        }
        return false;
    }
    /**
     * Get a list of remote packages on this updater URL.
     * @return mixed false if error on remote, unserialized list of products otherwise
     */
    public function getRemote_list()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'list', 'slug' => $this->slug)));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return unserialize($request['body']);
        }
        return false;
    }
    /**
     * Return the status of the plugin licensing
     * @return boolean $remote_license
     */
    public function getRemote_license()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'license', 'slug' => $this->slug)));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;
    }
}
