<?php
/**
 * Store Locator Plus Activation handler.
 *
 * Mostly handles data structure changes.
 * Update the plugin version in config.php on every structure change.
 *
 * @package StoreLocatorPlus\Activation
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 */
class SLPlus_Activate {

    //----------------------------------
    // Properties
    //----------------------------------

    public  $db_version_on_start = '';


    //----------------------------------
    // Methods
    //----------------------------------

    /**
     * Initialize the object.
     *
     * @param mixed[] $params
     */
    function __construct($params = null) {
        // Do the setting override or initial settings.
        //
        if ($params != null) {
            foreach ($params as $name => $sl_value) {
                $this->$name = $sl_value;
            }
        }
    } 

    /**
     * Update the data structures on new db versions.
     *
     * @global object $wpdb
     * @param type $sql
     * @param type $table_name
     * @return string
     */
    function dbupdater($sql,$table_name) {
        global $wpdb;

        // New installation
        //
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            return 'new';

        // Installation upgrade
        //
        } else {        
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            return 'updated';    
        }   
    }

    /*************************************
     * Update main table
     *
     * As of version 3.5, use sl_option_value to store serialized options
     * related to a single location.
     *
     * Update the plugin version in config.php on every structure change.
     *
     */
    function install_main_table() {
        global $wpdb;

        $charset_collate = '';
        if ( ! empty($wpdb->charset) )
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) )
            $charset_collate .= " COLLATE $wpdb->collate";	
        $table_name = $wpdb->prefix . "store_locator";
        $sql = "CREATE TABLE $table_name (
                sl_id mediumint(8) unsigned NOT NULL auto_increment,
                sl_store varchar(255) NULL,
                sl_address varchar(255) NULL,
                sl_address2 varchar(255) NULL,
                sl_city varchar(255) NULL,
                sl_state varchar(255) NULL,
                sl_zip varchar(255) NULL,
                sl_country varchar(255) NULL,
                sl_latitude varchar(255) NULL,
                sl_longitude varchar(255) NULL,
                sl_tags mediumtext NULL,
                sl_description text NULL,
                sl_email varchar(255) NULL,
                sl_url varchar(255) NULL,
                sl_hours varchar(255) NULL,
                sl_phone varchar(255) NULL,
                sl_fax varchar(255) NULL,
                sl_image varchar(255) NULL,
                sl_private varchar(1) NULL,
                sl_neat_title varchar(255) NULL,
                sl_linked_postid int NULL,
                sl_pages_url varchar(255) NULL,
                sl_pages_on varchar(1) NULL,
                sl_option_value longtext NULL,
                sl_lastupdated  timestamp NOT NULL default current_timestamp,			
                PRIMARY KEY  (sl_id),
                KEY (sl_store(255)),
                KEY (sl_longitude(255)),
                KEY (sl_latitude(255))
                ) 
                $charset_collate
                ";

        // If we updated an existing DB, do some mods to the data
        //
        if ($this->dbupdater($sql,$table_name) === 'updated') {
            // We are upgrading from something less than 2.0
            //
            if (floatval($this->db_version_on_start) < 2.0) {
                dbDelta("UPDATE $table_name SET sl_lastupdated=current_timestamp " . 
                    "WHERE sl_lastupdated < '2011-06-01'"
                    );
            }   
            if (floatval($this->db_version_on_start) < 2.2) {
                dbDelta("ALTER $table_name MODIFY sl_description text ");
            }
        }         

        //set up google maps v3
        $old_option = get_option('sl_map_type');
        $new_option = 'roadmap';
        switch ($old_option) {
            case 'G_NORMAL_MAP':
                $new_option = 'roadmap';
                break;
            case 'G_SATELLITE_MAP':
                $new_option = 'satellite';
                break;
            case 'G_HYBRID_MAP':
                $new_option = 'hybrid';
                break;
            case 'G_PHYSICAL_MAP':
                $new_option = 'terrain';
                break;
            default:
                $new_option = 'roadmap';
                break;
        }
        update_option('sl_map_type', $new_option);
    }

    /*************************************
     * Install reporting tables
     *
     * Update the plugin version in config.php on every structure change.
     */
    function install_reporting_tables() {
        global $wpdb;

        $charset_collate = '';
        if ( ! empty($wpdb->charset) )
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) )
            $charset_collate .= " COLLATE $wpdb->collate";

        // Reporting: Queries
        //
        $table_name = $wpdb->prefix . "slp_rep_query";
        $sql = "CREATE TABLE $table_name (
                slp_repq_id    bigint(20) unsigned NOT NULL auto_increment,
                slp_repq_time  timestamp NOT NULL default current_timestamp,
                slp_repq_query varchar(255) NOT NULL,
                slp_repq_tags  varchar(255),
                slp_repq_address varchar(255),
                slp_repq_radius varchar(5),
                PRIMARY KEY  (slp_repq_id),
                INDEX (slp_repq_time)
                )
                $charset_collate						
                ";
        $this->dbupdater($sql,$table_name);	



        // Reporting: Query Results
        //
        $table_name = $wpdb->prefix . "slp_rep_query_results";
        $sql = "CREATE TABLE $table_name (
                slp_repqr_id    bigint(20) unsigned NOT NULL auto_increment,
                slp_repq_id     bigint(20) unsigned NOT NULL,
                sl_id           mediumint(8) unsigned NOT NULL,
                PRIMARY KEY  (slp_repqr_id),
                INDEX (slp_repq_id)
                )
                $charset_collate						
                ";

        // Install or Update the slp_rep_query_results table
        //
        $this->dbupdater($sql,$table_name);     
    }

    /*************************************
     * Add roles and caps
     */
    function add_splus_roles_and_caps() {
        $role = get_role('administrator');
        if (is_object($role) && !$role->has_cap('manage_slp')) {
            $role->add_cap('manage_slp');
        }
    }


    /************************************************************
     * Copy a file, or recursively copy a folder and its contents
     */
    function copyr($source, $dest) {

        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        if (!is_dir($dest)) {
            mkdir($dest, 0755);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->copyr("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();
        return true;
    }

    /**
     * Workaround the dbDelta() glitch, drop dupe indexes.
     *
     * Only looks for the first "infraction".
     *
     * @global object $wpdb
     */
    function drop_duplicate_indexes() {
        $this->drop_index('sl_store_2');
        $this->drop_index('sl_latitude_2');
        $this->drop_index('sl_longitude_2');
    }

    /**
     * Drop an index only if it exists.
     *
     * @global object $wpdb
     * @param string $idxName name of index to drop
     */
    function drop_index($idxName) {
        global $wpdb;
        if ($wpdb->get_var('SELECT count(*) FROM information_schema.statistics '.
                "WHERE table_name='".$this->plugin->database['table']."' " .
                    "AND index_name='{$idxName}'" ) > 0) {
            $wpdb->query("DROP INDEX {$idxName} ON " . $this->plugin->database['table']);
        }
    }

    /**
     * Delete all files in a directory, non-recursive.
     * 
     * @param string $dirname
     * @param string $filepattern
     * @return null
     */
    function emptydir($dirname=null, $filepattern='*') {
       if ($dirname === null) { return; }
       array_map('unlink', glob($dirname.$filepattern));
    }

    /*************************************
     * Copy language and image files to wp-content/uploads/sl-uploads for safekeeping.
     */
    function save_important_files() {
        $allOK = true;

        // Make the upload director(ies)
        //
        if (!is_dir(ABSPATH . "wp-content/uploads")) {
            mkdir(ABSPATH . "wp-content/uploads", 0755);
        }
        if (!is_dir(SLPLUS_UPLOADDIR)) {
            mkdir(SLPLUS_UPLOADDIR, 0755);
        }
        if (!is_dir(SLPLUS_UPLOADDIR . "languages")) {
            mkdir(SLPLUS_UPLOADDIR . "languages", 0755);
        }
        if (!is_dir(SLPLUS_UPLOADDIR . "saved-icons")) {
            mkdir(SLPLUS_UPLOADDIR . "saved-icons", 0755);
        }

        // Copy core language files to languages save location
        //
        if (is_dir(SLPLUS_COREDIR. "languages") && is_dir(SLPLUS_UPLOADDIR . "languages")) {
            $allOK = $allOK && $this->copyr(SLPLUS_COREDIR . "languages", SLPLUS_UPLOADDIR . "languages");
        }

        // Copy ./images/icons to custom-icons save loation
        //
        if (is_dir(SLPLUS_PLUGINDIR . "/images/icons") && is_dir(SLPLUS_UPLOADDIR . "saved-icons")) {
            $allOK = $allOK && $this->copyr(SLPLUS_PLUGINDIR . "/images/icons", SLPLUS_UPLOADDIR . "saved-icons");
        }

        // Copy core images to images save location
        //
        if (is_dir(SLPLUS_COREDIR . "images/icons") && is_dir(SLPLUS_UPLOADDIR . "saved-icons")) {
            $allOK = $allOK && $this->copyr(SLPLUS_COREDIR . "images/icons", SLPLUS_UPLOADDIR . "saved-icons");
        }

        return $allOK;
    }

    /*************************************
     * Updates the plugin
     */
    static function update($slplus_plugin=null, $old_version=null) {

        // Called As Namespace
        //
        if ($slplus_plugin!=null) {
            $updater = new SLPlus_Activate(array(
                'plugin' => $slplus_plugin,
                'old_version' => $old_version,
            ));

        // Called as object method
        } else {
            $updater = $this;
        }

        // Set our starting version
        //
        $updater->db_version_on_start = get_option( SLPLUS_PREFIX."-db_version" );

        // New Installation
        //
        if ($updater->db_version_on_start == '') {
            add_option(SLPLUS_PREFIX."-db_version", $updater->plugin->version);
            add_option(SLPLUS_PREFIX.'_'.'disable_find_image','1');   // Disable the image find locations on new installs

        // Updating previous install
        //
        } else {
            // Save Image and Lanuages Files
            $filesSaved = $updater->save_important_files();

            // Core Icons Moved
            // 3.8.6
            //
            if (is_dir(SLPLUS_COREDIR.'images/icons/')) {

                // Change home and end icon if it was in core/images/icons
                //
                update_option('sl_map_home_icon', $updater->iconMapper(get_option('sl_map_home_icon')));
                update_option('sl_map_end_icon' , $updater->iconMapper(get_option('sl_map_end_icon') ));

                // If the icons were saved in the save dir
                // clean out core icons and remove the directory.
                //
                if ($filesSaved) {
                    $updater->emptydir(SLPLUS_COREDIR.'images/icons/');
                    rmdir(SLPLUS_COREDIR.'images/icons/');
                }
            }

            // Admin Pages might be blank, set to 10
            // 3.8.18
            //
            $tmpVar = get_option('sl_admin_locations_per_page');
            if (empty($tmpVar)) {
                update_option('sl_admin_locations_per_page','10');
            }

            // Update incorrect google map domain
            //
            if (get_option('sl_google_map_domain','maps.google.com') === 'maps.googleapis.com') {
                update_option('sl_google_map_domain','maps.google.com');
            }

            // Set DB Version
            //
            update_option(SLPLUS_PREFIX."-db_version", $updater->plugin->version);
        }
        update_option(SLPLUS_PREFIX.'-theme_lastupdated','2006-10-05');

        // Update Tables, Setup Roles
        //
        $updater->install_main_table();
        //$updater->drop_duplicate_indexes();
        $updater->install_reporting_tables();
        $updater->add_splus_roles_and_caps();
        /* $updater->get_addonpack_metadata(); */
    }

    /**
     * Fetch the add-on pack meta data from the server.
     * 
     * @return null
     */
    function get_addonpack_metadata() {
        require_once(SLPLUS_PLUGINDIR . '/include/storelocatorplus-updates_class.php');
        $this->Updates = new SLPlus_Updates(
                $this->plugin->version,
                $this->plugin->updater_url,
                SLPLUS_BASENAME
                );
        $result = $this->Updates->getRemote_list();
        update_option('slp_addonpack_meta',$result['body']);
        return;
    }

    /**
     * Updates specific to 3.8.6
     *
     * @return string icon file
     */
    function iconMapper($iconFile) {
        $newIcon = $iconFile;

        // Azure Bulb Name Change (default destination marker)
        //
        $newIcon =
            str_replace(
                '/store-locator-le/core/images/icons/a_marker_azure.png',
                '/store-locator-le/images/icons/bulb_azure.png',
                $iconFile
            );
        if ($newIcon != $iconFile) { return $newIcon; }

        // Box Yellow Home (default home marker)
        //
        $newIcon =
            str_replace(
                '/store-locator-le/core/images/icons/sign_yellow_home.png',
                '/store-locator-le/images/icons/box_yellow_home.png',
                $iconFile
            );
        if ($newIcon != $iconFile) { return $newIcon; }

        // General core/images/icons replaced with images/icons
        $newIcon =
            str_replace(
                '/store-locator-le/core/images/icons/',
                '/store-locator-le/images/icons/',
                $iconFile
            );
        if ($newIcon != $iconFile) { return $newIcon; }

        return $newIcon;
    }
}