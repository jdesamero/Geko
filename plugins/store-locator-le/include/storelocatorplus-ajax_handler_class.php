<?php

/**
 * Store Locator Plus Ajax Handler
 *
 * Manage the AJAX calls that come in from our admin and frontend UI.
 * Currently only holds new AJAX calls, all calls need to go in here.
 *
 * @package StoreLocatorPlus\AjaxHandler
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 */
class SLPlus_AjaxHandler {

    //-------------------------------------
    // Properties
    //-------------------------------------
    
    /**
     * The plugin object.
     * 
     * @var SLPlus $plugin 
     */
    public $plugin;


    /**
     * The database query string.
     *
     * @var string $dbQuery
     */
    private $dbQuery;

    //----------------------------------
    // Methods
    //----------------------------------
    
    /*************************************
     * The Constructor
     */
    function __construct($params=null) {
    }

    /**
     * Set the plugin property to point to the primary plugin object.
     *
     * Returns false if we can't get to the main plugin object.
     *
     * @global wpCSL_plugin__slplus $slplus_plugin
     * @return boolean true if plugin property is valid
     */
    function setPlugin() {
        if (!isset($this->plugin) || ($this->plugin == null)) {
            global $slplus_plugin;
            $this->plugin = $slplus_plugin;
        }
        return (isset($this->plugin) && ($this->plugin != null));
    }

    /**
     * Format the result data into a named array.
     *
     * We will later use this to build our JSONP response.
     *
     * @param mixed[] $data the data from the SLP database
     * @return mixed[]
     */
    function slp_add_marker($row = null) {
        if ($row == null) {
            return '';
        }
        $marker = array(
              'name'        => esc_attr($row['sl_store']),
              'address'     => esc_attr($row['sl_address']),
              'address2'    => esc_attr($row['sl_address2']),
              'city'        => esc_attr($row['sl_city']),
              'state'       => esc_attr($row['sl_state']),
              'zip'         => esc_attr($row['sl_zip']),
              'country'     => esc_attr($row['sl_country']),
              'lat'         => $row['sl_latitude'],
              'lng'         => $row['sl_longitude'],
              'description' => html_entity_decode($row['sl_description']),
              'url'         => esc_attr($row['sl_url']),
              'sl_pages_url'=> esc_attr($row['sl_pages_url']),
              'email'       => esc_attr($row['sl_email']),
              'hours'       => esc_attr($row['sl_hours']),
              'phone'       => esc_attr($row['sl_phone']),
              'fax'         => esc_attr($row['sl_fax']),
              'image'       => esc_attr($row['sl_image']),
              'distance'    => $row['sl_distance'],
              'tags'        => ((get_option(SLPLUS_PREFIX.'_show_tags',0) ==1)? esc_attr($row['sl_tags']) : ''),
              'option_value'=> esc_js($row['sl_option_value']),
              'attributes'  => maybe_unserialize($row['sl_option_value']),
              'id'          => $row['sl_id'],
          );
          $this->plugin->currentLocation->set_PropertiesViaArray($row);
          $marker = apply_filters('slp_results_marker_data',$marker);
          return $marker;
    }

    /**
     * Handle AJAX request for OnLoad action.
     *
     */
    function csl_ajax_onload() {
        $this->setPlugin();

        // Get Locations
        //
        $response = array();
        $locations = $this->execute_LocationQuery('sl_num_initial_displayed');
        foreach ($locations as $row){
            $response[] = $this->slp_add_marker($row);
        }

        // Output the JSON and Exit
        //
        $this->renderJSON_Response(
                array(
                        'count'         => count($response) ,
                        'type'          => 'load',
                        'response'      => $response
                    )
                );
    }

    /**
     * Handle AJAX request for Search calls.
     *
     * @global type $wpdb
     */
    function csl_ajax_search() {
        global $wpdb;
        $this->setPlugin();

        // Reporting
        // Insert the query into the query DB
        //
        if (get_option(SLPLUS_PREFIX.'-reporting_enabled','0') === '1') {
            $qry = sprintf(
                    "INSERT INTO {$this->plugin->db->prefix}slp_rep_query ".
                               "(slp_repq_query,slp_repq_tags,slp_repq_address,slp_repq_radius) ".
                        "values ('%s','%s','%s','%s')",
                        mysql_real_escape_string($_SERVER['QUERY_STRING']),
                        mysql_real_escape_string($_POST['tags']),
                        mysql_real_escape_string($_POST['address']),
                        mysql_real_escape_string($_POST['radius'])
                    );
            $wpdb->query($qry);
            $slp_QueryID = mysql_insert_id();
        }

        // Get Locations
        //
        $response = array();
        $locations = $this->execute_LocationQuery(SLPLUS_PREFIX.'_maxreturned');
        foreach ($locations as $row){
            $thisLocation = $this->slp_add_marker($row);
            if (!empty($thisLocation)) {
                $response[] = $thisLocation;

                // Reporting
                // Insert the results into the reporting table
                //
                if (get_option(SLPLUS_PREFIX.'-reporting_enabled','0') === '1') {
                    $wpdb->query(
                        sprintf(
                            "INSERT INTO {$this->plugin->db->prefix}slp_rep_query_results
                                (slp_repq_id,sl_id) values (%d,%d)",
                                $slp_QueryID,
                                $row['sl_id']
                            )
                        );
                }
            }
        }

        // Output the JSON and Exit
        //
        $this->renderJSON_Response(
                array(  
                        'count'         => count($response),
                        'option'        => $_POST['address'],
                        'type'          => 'search',
                        'response'      => $response
                    )
                );
     }

    /**
     * Run a database query to fetch the locations the user asked for.
     *
     * @param string $maxReturned how many results to max out at
     * @return object a MySQL result object
     */
    function execute_LocationQuery($optName_HowMany='') {
        //........
        // SLP options that tweak the query
        //........

        // Distance Unit (KM or MI) Modifier
        // Since miles is default, if kilometers is selected, divide by 1.609344 in order to convert the kilometer value selection back in miles
        //
        $multiplier=(get_option('sl_distance_unit')=="km")? 6371 : 3959;

        // Return How Many?
        //
        if (empty($optName_HowMany)) { $optName_HowMany = SLPLUS_PREFIX.'_maxreturned'; }
        $maxReturned = trim(get_option($optName_HowMany,'25'));
        if (!is_numeric($maxReturned)) { $maxReturned = '25'; }


        //........
        // Post options that tweak the query
        //........

        // Add all the location filters together for SQL statement.
        // FILTER: slp_location_filters_for_AJAX
        //
        $filterClause = '';
        $locationFilters = array();
        foreach (apply_filters('slp_location_filters_for_AJAX',$locationFilters) as $filter) {
            $filterClause .= $filter;
        }

        // Set the query
        // FILTER: slp_mysql_search_query
        //
        $this->dbQuery = apply_filters('slp_mysql_search_query',
            "SELECT *,".
            "( $multiplier * acos( cos( radians('%s') ) * cos( radians( sl_latitude ) ) * cos( radians( sl_longitude ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( sl_latitude ) ) ) ) AS sl_distance ".
            "FROM {$this->plugin->db->prefix}store_locator ".
            "WHERE sl_longitude<>'' and sl_latitude<>'' ".
            $filterClause . ' ' .
            "HAVING (sl_distance < %d) ".
            'ORDER BY sl_distance ASC '.
            'LIMIT %d'
            );

        // Run the query
        //
        // First convert our placeholder dbQuery into a string with the vars inserted.
        // Then turn off errors so they don't munge our JSONP.
        //
        global $wpdb;
        $this->dbQuery =
            $wpdb->prepare(
                $this->dbQuery,
                $_POST['lat'],
                $_POST['lng'],
                $_POST['lat'],
                $_POST['radius'],
                $maxReturned
                );
        $wpdb->hide_errors();
        $result = $wpdb->get_results($this->dbQuery, ARRAY_A);

        // Problems?  Oh crap.  Die.
        //
        if ($result === null) {
            die(json_encode(array(
                'success'       => false, 
                'response'      => 'Invalid query: ' . $wpdb->last_error,
                'dbQuery'       => $this->dbQuery
            )));
        }

        // Return the results
        //
        return $result;
    }

    /**
     * Output a JSON response based on the incoming data and die.
     *
     * Used for AJAX processing in WordPress where a remote listener expects JSON data.
     *
     * @param mixed[] $data named array of keys and values to turn into JSON data
     * @return null dies on execution
     */
    function renderJSON_Response($data) {

        // What do you mean we didn't get an array?
        //
        if (!is_array($data)) {
            $data = array(
                'success'       => false,
                'count'         => 0,
                'message'       => __('renderJSON_Response did not get an array()','csa-slplus')
            );
        }

        // Add our SLP Version and DB Query to the output
        //
        $data = array_merge(
                    array(
                        'success'       => true,
                        'slp_version'   => $this->plugin->version,
                        'dbQuery'       => $this->dbQuery
                    ),
                    $data
                );

        // Tell them what is coming...
        //
        header( "Content-Type: application/json" );

        // Go forth and spew data
        //
        echo json_encode($data);

        // Then die.
        //
        die();
    }
}
