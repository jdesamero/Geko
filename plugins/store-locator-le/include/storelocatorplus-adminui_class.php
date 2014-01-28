<?php
/**
 * Store Locator Plus basic admin user interface.
 *
 * @package StoreLocatorPlus\AdminUI
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 */
class SLPlus_AdminUI {

    //-------------------------------------
    // Properties
    //-------------------------------------
    public $addingLocation = false;
    public $currentLocation = array();
    public $parent = null;

    /**
     * The id string to show for this location.
     * 
     * @var string $idString 
     */
    private $idString;

    /**
     * The SLPlus object.
     * 
     * @var SLPlus $plugin
     */
    private $plugin;

    public $styleHandle = 'csl_slplus_admin_css';
    private $geocodeIssuesRendered = false;

    //----------------------------------
    // Methods
    //----------------------------------

    /**
     * Invoke the AdminUI class.
     *
     */
    function __construct() {

        // Register our admin styleseheet
        //
        if (file_exists(SLPLUS_PLUGINDIR.'css/admin.css')) {
            wp_register_style($this->styleHandle, SLPLUS_PLUGINURL .'/css/admin.css');
        }
    }

    /**
     * Set a currentLocation field value.
     *
     * @param string $name name of the currenLocation field to set
     * @param string $value what to set that field to
     */
    function set_CurrentLocationVal($name,$value='') {
        $this->currentLocation[$name] = $value;
        return null;
    }

    /**
     * Set the parent property to point to the primary plugin object.
     *
     * Returns false if we can't get to the main plugin object.
     *
     * @global wpCSL_plugin__slplus $slplus_plugin
     * @return type boolean true if plugin property is valid
     */
    function setParent() {
        if (!isset($this->parent) || ($this->parent == null)) {
            global $slplus_plugin;
            $this->parent = $slplus_plugin;
            $this->plugin = $slplus_plugin;
        }

        return (isset($this->parent) && ($this->parent != null));
    }


    /**
     * Creates a store page if needed.
     * 
     * @param array[] $locationData - the location fields and new values
     * @return int - the page ID
     */
    function getorcreate_PageID($locationData) {

        // If linked_postid is set and valid (an int as string) then return that.
        if (isset($locationData['sl_linked_postid']) && ctype_digit($locationData['sl_linked_postid'])) { return $locationData['sl_linked_postid']; }

        // We have a location record ID, let's pull data and see...
        //
        // TODO: swap this with the location class
        //
        global $wpdb;
        if (isset($locationData['sl_id']) && ctype_digit($locationData['sl_id'])) {
            $this->currentLocation = 
                $wpdb->get_row(
                    $wpdb->prepare(
                            $this->plugin->database['query']['selectall'] .
                            $this->plugin->database['query']['whereslid'],
                            $locationData['sl_id']
                    ),
                    ARRAY_A
                );
        }

        // No Page, create one
        //
        if (!ctype_digit($this->get_CurrentLocationVal('sl_linked_postid')) || ($this->get_CurrentLocationVal('sl_linked_postid') <= 0)) {

            // Create a blank draft page for this location to store meta
            //
            $slpNewListing = array(
                'ID'            => '',
                'post_type'     => 'store_page',
                'post_status'   => 'draft',
                'post_title'    => $this->get_CurrentLocationVal('sl_store'),
                'post_content'  => ''
                );
            $slpNewListing = apply_filters('slp_location_page_attributes',$slpNewListing);

            // Set the in-memory location property
            //
            $this->set_CurrentLocationVal('sl_linked_postid',wp_insert_post($slpNewListing));
        }

        return $this->get_CurrentLocationVal('sl_linked_postid');
    }


    /**
     * Add an address into the SLP locations database.
     *
     * Returns 'added' or 'duplicate'
     * 
     * @global object $wpdb
     * @param array[] $locationData
     * @param boolean $skipdupes
     * @param boolean $skipGeocode
     * @return string 'duplicate' or 'added'
     *
     */
    function add_this_addy($locationData,$skipdupes=false,$storename='',$skipGeocode=false) {
        global $wpdb;

        // Dupe check?
        //
        if ($skipdupes) {
            $wpdb->query(
                $wpdb->prepare(
                    'SELECT 1 ' . $this->plugin->database['query']['fromslp'] .
                        'WHERE ' .
                            'sl_store   = %s AND '.
                            'sl_address = %s AND '.
                            'sl_address2= %s AND '.
                            'sl_city    = %s AND '.
                            'sl_state   = %s AND '.
                            'sl_zip     = %s AND '.
                            'sl_country = %s     '.
                          'LIMIT 1',
                    $this->ValOrBlank($locationData['sl_store'])    ,
                    $this->ValOrBlank($locationData['sl_address'])  ,
                    $this->ValOrBlank($locationData['sl_address2']) ,
                    $this->ValOrBlank($locationData['sl_city'])     ,
                    $this->ValOrBlank($locationData['sl_state'])    ,
                    $this->ValOrBlank($locationData['sl_zip'])      ,
                    $this->ValOrBlank($locationData['sl_country'])
                )
            );
            if ($wpdb->num_rows == 1) {
                return 'duplicate';
            }
        }

        // Make sure all locations have a related page
        //
        $locationData['sl_linked_postid'] = $this->getorcreate_PageID($locationData);

        // Insert the new location into the database
        //
        $wpdb->insert($this->plugin->database['table_ns'],$locationData);

        // Fire slp_location_added hook
        //
        do_action('slp_location_added',mysql_insert_id());

        if (!$skipGeocode) {
            $this->do_geocoding(
                    $this->ValOrBlank($locationData['sl_address'])  .','.
                    $this->ValOrBlank($locationData['sl_address2']) .','.
                    $this->ValOrBlank($locationData['sl_city'])     .','.
                    $this->ValOrBlank($locationData['sl_state'])    .','.
                    $this->ValOrBlank($locationData['sl_zip'])      .','.
                    $this->ValOrBlank($locationData['sl_country'])
                    );
        }
        return 'added';
    }

    /**
     * Setup some of the general settings interface elements.
     */
    function build_basic_admin_settings() {
        if (!$this->setParent()) { return; }

        //-------------------------
        // Navbar Section
        //-------------------------
        $this->parent->settings->add_section(
            array(
                'name'          => 'Navigation',
                'div_id'        => 'slplus_navbar_wrapper',
                'description'   => $this->parent->AdminUI->create_Navbar(),
                'innerdiv'      => false,
                'is_topmenu'    => true,
                'auto'          => false,
                'headerbar'     => false
            )
        );

        //-------------------------
        // How to Use Section
        //-------------------------
         $this->parent->settings->add_section(
            array(
                'name' => 'How to Use',
                'description' => $this->parent->helper->get_string_from_phpexec(SLPLUS_PLUGINDIR.'/how_to_use.txt'),
                'start_collapsed' => false
            )
        );

        //-------------------------
        // Google Communication
        //-------------------------
         $this->parent->settings->add_section(
            array(
                'name'        => 'Google Communication',
                'description' => 'These settings affect how the plugin communicates with Google to create your map.'.
                                    '<br/><br/>'
            )
        );

         $this->parent->settings->add_item(
            'Google Communication',
            __('Google API Key','csa-slplus'),
            'api_key',
            'text',
            false,
            'Your Google Maps V3 API Key.  NOT REQUIRED. Used for searches only. You will need to ' .
            '<a href="http://code.google.com/apis/console/" target="newinfo">'.
            'go to Google</a> to get your Google Maps API Key.'
        );


         $this->parent->settings->add_item(
            'Google Communication',
            __('Geocode Retries','csa-slplus'),
            'goecode_retries',
            'list',
            false,
            sprintf(__('How many times should we try to set the latitude/longitude for a new address. ' .
                'Higher numbers mean slower bulk uploads ('.
                '<a href="%s">plus version</a>'.
                '), lower numbers makes it more likely the location will not be set during bulk uploads.',
                 'csa-slplus'),
                 'http://www.charlestonsw.com/product/store-locator-plus/'
                 ),                        
            array (
                  'None' => 0,
                  '1' => '1',
                  '2' => '2',
                  '3' => '3',
                  '4' => '4',
                  '5' => '5',
                  '6' => '6',
                  '7' => '7',
                  '8' => '8',
                  '9' => '9',
                  '10' => '10',
                )
        );

         $this->parent->settings->add_item(
            'Google Communication',
            'Turn Off SLP Maps',
            'no_google_js',
            'checkbox',
            false,
            __('Check this box if your Theme or another plugin is providing Google Maps and generating warning messages.  THIS MAY BREAK THIS PLUGIN.', 'csa-slplus')
        );
    }

    /**
     *
     * @param type $a
     * @return type
     */
    function slp_escape($a) {
        $a=preg_replace("/'/"     , '&#39;'   , $a);
        $a=preg_replace('/"/'     , '&quot;'  , $a);
        $a=preg_replace('/>/'     , '&gt;'    , $a);
        $a=preg_replace('/</'     , '&lt;'    , $a);
        $a=preg_replace('/,/'     , '&#44;'   , $a);
        $a=preg_replace('/ & /'   , ' &amp; ' , $a);
        return $a;
    }

    /**
     * GeoCode a given location and update it in the database.
     *
     * Google Server-Side API geocoding is documented here:
     * https://developers.google.com/maps/documentation/geocoding/index
     *
     * Required Google Geocoding API Params:
     * address
     * sensor=true|false
     *
     * Optional Google Goecoding API Params:
     * bounds
     * language
     * region
     * components
     * 
     * @global type $wpdb
     * @global type $slplus_plugin
     * @param type $address
     * @param type $sl_id
     */
    function do_geocoding($address,$sl_id='',$extendedInfo = false) {
        global $wpdb, $slplus_plugin;

        $language = '&language='.$slplus_plugin->helper->getData('map_language','get_item',null,'en');

        $delay = 0;
        $request_url =
            'http://maps.googleapis.com/maps/api/geocode/json'.
            '?sensor=false' .
            $language .
            '&address=' . urlencode($address)
            ;

        // Loop through for X retries
        //
        $iterations = get_option(SLPLUS_PREFIX.'-goecode_retries');
        if ($iterations <= 0) { $iterations = 1; }
        $initial_iterations = $iterations;
        while($iterations){
            $iterations--;

            // Iterate through the rows, geocoding each address
            $errorMessage = '';

            // Use HTTP Handler (WP_HTTP) first...
            //
            if (isset($slplus_plugin->http_handler)) {
                $result = $slplus_plugin->http_handler->request(
                                $request_url,
                                array('timeout' => 3)
                                );
                if ($slplus_plugin->http_result_is_ok($result) ) {
                    $raw_json = $result['body'];
                }

            // Then Curl...
            //
            } elseif (extension_loaded("curl") && function_exists("curl_init")) {
                    $cURL = curl_init();
                    curl_setopt($cURL, CURLOPT_URL, $request_url);
                    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
                    $raw_json = curl_exec($cURL);
                    curl_close($cURL);

            // Lastly file_get_contents
            //
            } else {
                 $raw_json = file_get_contents($request_url);
            }

            // If raw_json exists, parse it
            //
            if (isset($raw_json)) {
                $json = json_decode($raw_json);
                $status = $json->{'status'};

            // no raw json
            //
            } else {
                $json = '';
                $status = '';
            }

            // Geocode completed successfully
            //
            if (strcmp($status, "OK") == 0) {
                $iterations = 0;      // Break out of retry loop if we are OK
                $delay = 0;

                // successful geocode
                $geocode_pending = false;
                $lat = $json->results[0]->geometry->location->lat;
                $lng = $json->results[0]->geometry->location->lng;
                // Update newly inserted address
                //
                if ($sl_id=='') {
                    $query = sprintf("UPDATE " . $wpdb->prefix ."store_locator " .
                           "SET sl_latitude = '%s', sl_longitude = '%s' " .
                           "WHERE sl_id = LAST_INSERT_ID()".
                           " LIMIT 1;",
                           mysql_real_escape_string($lat),
                           mysql_real_escape_string($lng)
                           );
                // Update an existing address
                //
                } else {
                    $query = sprintf("UPDATE " . $wpdb->prefix ."store_locator SET sl_latitude = '%s', sl_longitude = '%s' WHERE sl_id = $sl_id LIMIT 1;", mysql_real_escape_string($lat), mysql_real_escape_string($lng));
                }

                // Run insert/update
                //
                $update_result = $wpdb->query($query);
                if ($update_result == 0) {
                    $theDBError = htmlspecialchars(mysql_error($wpdb->dbh),ENT_QUOTES);
                    $errorMessage .= (($sl_id!='')?'Location #'.$sl_id.' : ' : '');
                    $errorMessage .= __("Could not set the latitude and/or longitude  ", 'csa-slplus');
                    if ($theDBError != '') {
                        $errorMessage .= sprintf(
                                                __("Error: %s.", 'csa-slplus'),
                                                $theDBError
                                                );
                    } elseif ($update_result === 0) {
                        $errorMessage .=  sprintf(__(", The latitude %s and longitude %s did not change.", 'csa-slplus'),$lat,$lng);
                    } else {
                        $errorMessage .=  __("No error logged.", 'csa-slplus');
                        $errorMessage .= "<br/>\n" . __('Query: ', 'csa-slplus');
                        $errorMessage .= print_r($wpdb->last_query,true);
                        $errorMessage .= "<br/>\n" . "Results: " . gettype($update_result) . ' '. $update_result;
                    }

                }

            // Geocoding done too quickly
            //
            } else if (strcmp($status, "OVER_QUERY_LIMIT") == 0) {

              // No iterations left, tell user of failure
              //
              if(!$iterations){
                $errorMessage .= sprintf(__("Address %s <font color=red>failed to geocode</font>. ", 'csa-slplus'),$address);
                $errorMessage .= sprintf(__("URL %s.", 'csa-slplus'),$request_url)."\n<br>";
                $errorMessage .= sprintf(__("Received status %s.", 'csa-slplus'),$status)."\n<br>";
                $errorMessage .= sprintf(
                        __("Total attempts %d, waited up to %4.2 seconds between request.", 'csa-slplus'),
                        $initial_iterations,
                        $delay/100000
                        ).
                        "\n<br>";
              }
              $delay += 100000;

            // Invalid address
            //
            } else if (strcmp($status, 'ZERO_RESULTS') == 0) {
                $iterations = 0;
                $errorMessage .= sprintf(__("Address %s <font color=red>failed to geocode</font>. ", 'csa-slplus'),$address);
                $errorMessage .= sprintf(__("URL %s.", 'csa-slplus'),$request_url)."\n<br>";
                $errorMessage .= sprintf(__("Unknown Address! Received status %s.", 'csa-slplus'),$status)."\n<br>";

            // Could Not Geocode
            //
            } else {
                $geocode_pending = false;
                echo sprintf(__("Address %s <font color=red>failed to geocode</font>. ", 'csa-slplus'),$address);
                if ($status != '') {
                    $errorMessage .= sprintf(__("URL %s.", 'csa-slplus'),$request_url)."\n<br>";
                    $errorMessage .= sprintf(__("Received data %s.", 'csa-slplus'),'<pre>'.print_r($json,true).'</pre>')."\n";
                } else {
                    $errorMessage .= sprintf(__("Request sent to %s.", 'csa-slplus'),$request_url)."\n<br>";
                    $errorMessage .= sprintf(__("Received status %s.", 'csa-slplus'),$status)."\n<br>";
                }
            }

            // Show Error Messages
            //
            if ($errorMessage != '') {
                if (!$this->geocodeIssuesRendered) {
                    print
                        '<div class="geocode_error">' .
                       '<strong>'.
                       sprintf(
                           __('Read <a href="%s">this</a> if you are having geocoding issues.','csa-slplus'),
                           'http://www.charlestonsw.com/support/documentation/store-locator-plus/troubleshooting/geocoding-errors/'
                           ).
                       "</strong><br/>\n" .
                       '</div>'
                       ;
                    $this->geocodeIssuesRendered = true;
                }

                if ($extendedInfo) {
                    $slplus_plugin->notifications->add_notice(4,$errorMessage);
                } else {
                    print '<div class="geocode_error">' .
                            $errorMessage .
                            '</div>';
                }
            } elseif ($extendedInfo) {
                $slplus_plugin->notifications->add_notice(
                         9,
                         sprintf(
                                 __('Google thinks %s is at <a href="%s" target="_blank">lat: %s long %s</a>','csa-slplus'),
                                 $address, 
                                 sprintf('http://%s/?q=%s,%s',
                                         $slplus_plugin->helper->getData('mapdomain','get_option',array('sl_google_map_domain','maps.google.com')),
                                         $lat,
                                         $lng),
                                 $lat, $lng
                                 )
                         );
            }

            usleep($delay);
        }
    }

    /**
     * Initialize variables for the map settings.
     * 
     * @global type $sl_google_map_country
     * @global type $sl_location_table_view
     * @global type $sl_zoom_level
     * @global type $sl_zoom_tweak
     * @global type $sl_use_name_search
     * @global type $sl_website_label
     * @global type $sl_distance_unit
     */
    function initialize_variables() {
        global $sl_google_map_country, $sl_location_table_view,
            $sl_zoom_level, $sl_zoom_tweak, $sl_use_name_search,
            $sl_website_label, $sl_distance_unit;

        $sl_distance_unit=get_option('sl_distance_unit');
        if (empty($sl_distance_unit)) {
            $sl_distance_unit="miles";
            add_option('sl_distance_unit', $sl_distance_unit);
            }
        $sl_website_label=get_option('sl_website_label');
        if (empty($sl_website_label)) {
            $sl_website_label="Website";
            add_option('sl_website_label', $sl_website_label);
            }
        $sl_map_type=get_option('sl_map_type');
        if (isset($sl_map_type)) {
            $sl_map_type='roadmap';
            add_option('sl_map_type', $sl_map_type);
            }
        $sl_remove_credits=get_option('sl_remove_credits');
        if (empty($sl_remove_credits)) {
            $sl_remove_credits="0";
            add_option('sl_remove_credits', $sl_remove_credits);
            }
        $sl_use_name_search=get_option('sl_use_name_search');
        if (empty($sl_use_name_search)) {
            $sl_use_name_search="0";
            add_option('sl_use_name_search', $sl_use_name_search);
            }

        $sl_zoom_level=get_option('sl_zoom_level','4');
        add_option('sl_zoom_level', $sl_zoom_level);

        $sl_zoom_tweak=get_option('sl_zoom_tweak','1');
        add_option('sl_zoom_tweak', $sl_zoom_tweak);

        $sl_location_table_view=get_option('sl_location_table_view');
        if (empty($sl_location_table_view)) {
            $sl_location_table_view="Normal";
            add_option('sl_location_table_view', $sl_location_table_view);
            }
        $sl_google_map_country=get_option('sl_google_map_country');
        if (empty($sl_google_map_country)) {
            $sl_google_map_country="United States";
            add_option('sl_google_map_country', $sl_google_map_country);
        }
    }


    /**
     * Display the manage locations pagination
     *
     * @param type $totalLocations
     * @param int $num_per_page
     * @param int $start
     */
    function manage_locations_pagination($totalLocations = 0, $num_per_page = 10, $start = 0) {

        // Variable Init
        $pos=0;
        $prev = min(max(0,$start-$num_per_page),$totalLocations);
        $next = min(max(0,$start+$num_per_page),$totalLocations);
        $num_per_page = max(1,$num_per_page);
        $qry = isset($_GET['q'])?$_GET['q']:'';
        $cleared=preg_replace('/q=$qry/', '', $_SERVER['REQUEST_URI']);

        $extra_text=(trim($qry)!='')    ?
            __("for your search of", 'csa-slplus').
                " <strong>\"$qry\"</strong>&nbsp;|&nbsp;<a href='$cleared'>".
                __("Clear&nbsp;Results", 'csa-slplus')."</a>" :
            "" ;

        // URL Regex Replace
        //
        if (preg_match('#&start='.$start.'#',$_SERVER['QUERY_STRING'])) {
            $prev_page=str_replace("&start=$start","&start=$prev",$_SERVER['REQUEST_URI']);
            $next_page=str_replace("&start=$start","&start=$next",$_SERVER['REQUEST_URI']);
        } else {
            $prev_page=$_SERVER['REQUEST_URI']."&start=$prev";
            $next_page=$_SERVER['REQUEST_URI']."&start=$next";
        }

        // Pages String
        //
        $pagesString = '';
        if ($totalLocations>$num_per_page) {
            if ((($start/$num_per_page)+1)-5<1) {
                $beginning_link=1;
            } else {
                $beginning_link=(($start/$num_per_page)+1)-5;
            }
            if ((($start/$num_per_page)+1)+5>(($totalLocations/$num_per_page)+1)) {
                $end_link=(($totalLocations/$num_per_page)+1);
            } else {
                $end_link=(($start/$num_per_page)+1)+5;
            }
            $pos=($beginning_link-1)*$num_per_page;
            for ($k=$beginning_link; $k<$end_link; $k++) {
                if (preg_match('#&start='.$start.'#',$_SERVER['QUERY_STRING'])) {
                    $curr_page=str_replace("&start=$start","&start=$pos",$_SERVER['QUERY_STRING']);
                }
                else {
                    $curr_page=$_SERVER['QUERY_STRING']."&start=$pos";
                }
                if (($start-($k-1)*$num_per_page)<0 || ($start-($k-1)*$num_per_page)>=$num_per_page) {
                    $pagesString .= "<a class='page-button' href=\"{$_SERVER['SCRIPT_NAME']}?$curr_page\" >";
                } else {
                    $pagesString .= "<a class='page-button thispage' href='#'>";
                }


                $pagesString .= "$k</a>";
                $pos=$pos+$num_per_page;
            }
        }

        $prevpages = 
            "<a class='prev-page page-button" .
                ((($start-$num_per_page)>=0) ? '' : ' disabled' ) .
                "' href='".
                ((($start-$num_per_page)>=0) ? $prev_page : '#' ).
                "'>‹</a>"
            ;
        $nextpages = 
            "<a class='next-page page-button" .
                ((($start+$num_per_page)<$totalLocations) ? '' : ' disabled') .
                "' href='".
                ((($start+$num_per_page)<$totalLocations) ? $next_page : '#').
                "'>›</a>"
            ;

        $pagesString =
            $prevpages .
            $pagesString .
            $nextpages
            ;

        print
            '<div id="slp_pagination" class="tablenav top">'              .
                '<div id="slp_pagination_pages" class="tablenav-pages">'    .
                    '<span class="displaying-num">'                         .
                            $totalLocations                                 .
                            ' '.__('locations','csa-slplus')               .
                        '</span>'                                           .
                        '<span class="pagination-links">'                   .
                        $pagesString                                        .
                        '</span>'                                           .
                    '</div>'                                                .
                    $extra_text                                             .
                '</div>'
            ;
    }

    /**
     * Render the manage locations table header
     *
     * @param array $slpManageColumns - the manage locations columns pre-filter
     */
    function manage_locations_table_header($slpManageColumns,$slpCleanURL,$opt,$dir) {
        $tableHeaderString =
            '<thead>'                                                                                                           .
                '<tr >'                                                                                                         .
                    "<th id='top_of_checkbox_column'>"                                                                          .
                        "<input type='checkbox' onclick='jQuery(\".slp_checkbox\").attr(\"checked\",true);' class='button'>"    .
                    '</th>'                                                                                                     .
                    "<th id='top_of_actions_column'>"                                                                           .
                        __('Actions', 'csa-slplus')                                                                             .
                    '</th>'
                ;
        foreach ($slpManageColumns as $slpField => $slpLabel) {
            $tableHeaderString .= $this->slpCreateColumnHeader($slpCleanURL,$slpField,$slpLabel,$opt,$dir);
        }
        $tableHeaderString .= '<th>Lat</th><th>Lon</th></tr></thead>';
        return $tableHeaderString;
    }

    /**
     * Enqueue the admin stylesheet when needed.
     */
    function enqueue_admin_stylesheet() {
        wp_enqueue_style($this->styleHandle);
    }

    /**
     * Setup the stylesheet only when needed.
     */
    function set_style_as_needed() {
        $slugPrefix = 'store-locator-plus_page_';

        // Add Locations
        //
        add_action(
               'admin_print_styles-' . $slugPrefix . 'slp_add_locations',
                array($this,'enqueue_admin_stylesheet')
                );

        // General Settings
        //
       add_action(
               'admin_print_styles-'  . $slugPrefix . 'slp_general_settings',
                array($this,'enqueue_admin_stylesheet')
                );
       add_action(
               'admin_print_styles-'  . 'settings_page_csl-slplus-options',
                array($this,'enqueue_admin_stylesheet')
                );


        // Manage Locations
        //
        add_action(
               'admin_print_styles-' . $slugPrefix . 'slp_manage_locations',
                array($this,'enqueue_admin_stylesheet')
                );

        // Map Settings
        //
        add_action(
               'admin_print_styles-' . $slugPrefix . 'slp_map_settings',
                array($this,'enqueue_admin_stylesheet')
                );
    }

    /**
     * Check if a URL starts with http://
     *
     * @param type $url
     * @return type
     */
    function url_test($url) {
        return (strtolower(substr($url,0,7))=="http://");
    }

    /**
     * Return the value of the data element or blank if not set.
     *
     * @param mixed $dataElement - the variable to test
     * @param mixed $setTo - the value to set the variable to if not set
     * @return mixed - the data element value or value of $setTo
     */
    function ValOrBlank($dataElement,$setTo='') {
        return isset($dataElement) ? $dataElement : $setTo;
    }

    /**
     * Create the column headers for sorting the table.
     *
     * @param type $theURL
     * @param type $fldID
     * @param type $fldLabel
     * @param type $opt
     * @param type $dir
     * @return type
     */
    function slpCreateColumnHeader($theURL,$fldID='sl_store',$fldLabel='ID',$opt='sl_store',$dir='ASC') {
        if ($opt == $fldID) {
            $curDIR = (($dir=='ASC')?'DESC':'ASC');
        } else {
            $curDIR = $dir;
        }
        return "<th class='manage-column sortable'><a href='$theURL&o=$fldID&sortorder=$curDIR'>" .
                "<span>$fldLabel</span>".
                "<span class='sorting-indicator'></span>".
                "</a></th>";
    }

    /**
     * Draw the add locations page.
     *
     */
     function renderPage_AddLocations() {
            $this->initialize_variables();

            print "<div class='wrap'>
                        <div id='icon-add-locations' class='icon32'><br/></div>
                        <h2>Store Locator Plus - ".
                        __('Add Locations', 'csa-slplus').
                        "</h2>" .
                        $this->parent->AdminUI->create_Navbar()
                  ;

            //Inserting addresses by manual input
            //
            $locationData = array();
            if ( isset($_POST['store-']) && $_POST['store-']) {
                foreach ($_POST as $key=>$sl_value) {
                    if (preg_match('#\-$#', $key)) {
                        $fieldName='sl_'.preg_replace('#\-$#','',$key);
                        $locationData[$fieldName]=$this->slp_escape(stripslashes($sl_value));
                    }
                }
                $resultOfAdd = $this->plugin->AdminUI->add_this_addy($locationData);
                print "<div class='updated fade'>".
                        $_POST['store-'] ." " .
                        __("Added Successfully",'csa-slplus') . '.</div>';

            /** Bulk Upload
             **/
            } elseif ( 
                isset($_FILES['csvfile']['name']) &&
                ($_FILES['csvfile']['name']!='')  &&
                ($_FILES['csvfile']['size'] > 0)
               ) {
                do_action('slp_addlocations_with_csv');
            }

            $this->addingLocation = true;
            print 
                '<div id="location_table_wrapper">'.
                    "<table id='manage_locations_table' class='slplus wp-list-table widefat fixed posts' cellspacing=0>" .
                        '<tr><td class="slp_locationinfoform_cell">' .
                            $this->plugin->AdminUI->createString_LocationInfoForm(array(),'', true) .
                        '</td></tr>' .
                    '</table>' .
                '</div>'
                ;
     }

     /**
      * Return the value of the field specified for the current location.
      * @param string $fldname - a location field
      * @return string - value of the field
      */
     function get_CurrentLocationVal($fldname=null) {
         if ($fldname === null      ) { return ''; }
         if ($this->addingLocation  ) {
             return apply_filters('slp_addlocation_fieldvalue','',$fldname);
         }
         return isset($this->currentLocation[$fldname])?$this->currentLocation[$fldname]:'';
     }

    /**
     * Create the add/edit form field.
     *
     * Leave fldLabel blank to eliminate the leading <label>
     *
     * inType can be 'input' (default) or 'textarea'
     *
     * @param string $fldName name of the field, base name only
     * @param string $fldLabel label to show ahead of the input
     * @param string $fldValue
     * @param string $inputclass class for input field
     * @param boolean $noBR skip the <br/> after input
     * @param string $inType type of input field (default:input)
     * @return string the form HTML output
     */
    function create_InputElement($fldName,$fldLabel,$fldValue, $inputClass='', $noBR = false, $inType='input') {
        $matches = array();
        $matchStr = '/(.+)\[(.*)\]/';
        if (preg_match($matchStr,$fldName,$matches)) {
            $fldName = $matches[1];
            $subFldName = '['.$matches[2].']';
        } else {
            $subFldName='';
        }
        return
            (empty($fldLabel)?'':"<label  for='{$fldName}-{$this->plugin->currentLocation->id}{$subFldName}'>{$fldLabel}</label>").
            "<{$inType} "                                                                .
                "id='edit-{$fldName}{$subFldName}' "                                     .
                "name='{$fldName}-{$this->plugin->currentLocation->id}{$subFldName}' "   .
                (($inType==='input')?
                        "value='{$fldValue}' "  :
                        "rows='5' cols='17'  "
                 )                                                          .
                (empty($inputClass)?'':"class='{$inputClass}' ")            .
            '>'                                                             .
            (($inType==='textarea')?$fldValue       :'')                    .
            (($inType==='textarea')?'</textarea>'   :'')                    .
            ($noBR?'':'<br/>')
            ;
    }

    /**
     * Add the left column to the add/edit locations form.
     *
     * @param string $HTML the html of the base form.
     * @return string HTML of the form inputs
     */
    function filter_EditLocationLeft_Address($HTML) {
        $theHTML =
            $this->plugin->helper->create_SubheadingLabel(__('Address','csa-slplus')).
            $this->create_InputElement(
                'store',
                __('Name of Location', 'csa-slplus'),
                $this->plugin->currentLocation->store
                ).
            $this->create_InputElement(
                'address',
                __('Street - Line 1', 'csa-slplus'),
                $this->plugin->currentLocation->address
                ).
            $this->create_InputElement(
                'address2',
                __('Street - Line 2', 'csa-slplus'),
                $this->plugin->currentLocation->address2
                ).
            $this->create_InputElement(
                'city',
                __('City, State, ZIP', 'csa-slplus'),
                $this->plugin->currentLocation->city,
                'mediumfield',
                true
                ).
            $this->create_InputElement(
                'state',
                '',
                $this->plugin->currentLocation->state,
                'shortfield',
                true
                ).
            $this->create_InputElement(
                'zip',
                '',
                $this->plugin->currentLocation->zip,
                'shortfield'
                ).
            $this->create_InputElement(
                'country',
                __('Country','csa-slplus'),
                $this->plugin->currentLocation->country
                );

            // Edit Location Only
            //
            if ($this->plugin->AdminUI->addingLocation === false) {
                $theHTML .=
                    $this->plugin->AdminUI->create_InputElement(
                            'latitude',
                            __('Latitude (N/S)', 'csa-slp-pro'),
                            $this->plugin->currentLocation->latitude
                            ).
                    $this->plugin->AdminUI->create_InputElement(
                            'longitude',
                            __('Longitude (E/W)', 'csa-slp-pro'),
                            $this->plugin->currentLocation->longitude
                            )
                        ;
            }

        return $theHTML.$HTML;
    }

    /**
     * Put the add/cancel button on the add/edit locations form.
     * 
     * This is rendered AFTER other HTML stuff.
     *
     * @param string $HTML the html of the base form.
     * @return string HTML of the form inputs
     */
    function filter_EditLocationLeft_Submit($HTML) {
        $edCancelURL = isset($_GET['edit']) ?
            preg_replace('/&edit='.$_GET['edit'].'/', '',$_SERVER['REQUEST_URI']) :
            $_SERVER['REQUEST_URI']
            ;
        $alTitle =
            ($this->addingLocation?
                __('Add Location','csa-slplus'):
                sprintf("%s #%d",__('Update Location', 'csa-slplus'),$this->plugin->currentLocation->id)
            );
        return
            $HTML .
            ($this->addingLocation? '' : "<span class='slp-edit-location-id'>Location # $this->idString</span>") .
            "<div id='slp_form_buttons'>" .
            "<input type='submit' value='".($this->addingLocation?__('Add','csa-slplus'):__('Update', 'csa-slplus')).
                "' alt='$alTitle' title='$alTitle' class='button-primary'>".
            "<input type='button' class='button' "                                                  .
                "value='".__('Cancel', 'csa-slplus')."' "                                           .
                "onclick='location.href=\"".$edCancelURL."\"'>"                                     .
            "<input type='hidden' name='option_value-{$this->plugin->currentLocation->id}' "        .
                "value='".($this->addingLocation?'':$this->plugin->currentLocation->option_value)   .
                "' />"                                                                              .
            "</div>"
            ;
    }

    /**
     * Add the right column to the add/edit locations form.
     *
     * @param string $HTML the html of the base form.
     * @return string HTML of the form inputs
     */
    function filter_EditLocationRight_Address($HTML) {
        return
            $this->plugin->helper->create_SubheadingLabel(__('Additional Information','csa-slplus')).
            $this->create_InputElement(
                    'description',
                    __('Description', 'csa-slplus'),
                    $this->plugin->currentLocation->description,
                    '',
                    false,
                    'textarea'
                    ).
            $this->create_InputElement(
                    'url',
                    get_option('sl_website_label','Website'),
                    $this->plugin->currentLocation->url
                    ).
            $this->create_InputElement(
                    'email',
                    __('Email', 'csa-slplus'),
                    $this->plugin->currentLocation->email
                    ).
            $this->create_InputElement(
                    'hours',
                    $this->plugin->settings->get_item('label_hours','Hours','_'),
                    $this->plugin->currentLocation->hours,
                    '',
                    false,
                    'textarea'
                    ).
            $this->create_InputElement(
                    'phone',
                    $this->plugin->settings->get_item('label_phone','Phone','_'),
                    $this->plugin->currentLocation->phone
                    ).
            $this->create_InputElement(
                    'fax',
                    $this->plugin->settings->get_item('label_fax','Fax','_'),
                    $this->plugin->currentLocation->fax
                    ).
            $HTML
            ;
    }

    /**
     * Render the General Settings admin page.
     *
     */
    function renderPage_GeneralSettings() {
        if (!$this->setParent()) { return; }
        $this->plugin->settings->render_settings_page();
    }


    /**
     * Render the Manage Locations admin page.
     */
    function renderPage_ManageLocations() {
        require_once(SLPLUS_PLUGINDIR . '/include/slp-adminui_managelocations_class.php');
        $this->parent->AdminUI->ManageLocations = new SLPlus_AdminUI_ManageLocations();
        $this->parent->AdminUI->ManageLocations->render_adminpage();
    }

    /**
     * Render the Map Settings admin page.
     */
    function renderPage_MapSettings() {
        require_once(SLPLUS_PLUGINDIR . '/include/slp-adminui_mapsettings_class.php');
        $this->parent->AdminUI->MapSettings = new SLPlus_AdminUI_MapSettings();
        $this->parent->AdminUI->MapSettings->render_adminpage();
    }

     /**
      * Returns the string that is the Location Info Form guts.
      *
      * Invoke SLP Filter: slp_edit_location_data before rending form.
      *
      * TODO: rip out local sl_value calls and use plugin.currentLocation object instead.
      *
      * @param mixed $sl_value - the data values for this location in array format
      * @param int $locID - the ID number for this location
      * @param bool $addform - true if rendering add locations form
      */
     function createString_LocationInfoForm($sl_value, $locID, $addform=false) {
        if (!$this->setParent()) { return; }
        $this->addingLocation = $addform;

        // TODO: currentLocation can be replaced with the plugin.currentLocation object
        // make sure to transfer the filter down to the plugin setup first.
        //
        $this->currentLocation = apply_filters('slp_edit_location_data',$sl_value);
        $this->plugin->currentLocation->set_PropertiesViaArray($this->currentLocation);
        $this->idString =
                $this->plugin->currentLocation->id .
                (!empty($this->plugin->currentLocation->linked_postid)?
                 ' - '. $this->plugin->currentLocation->linked_postid :
                 ''
                 );
        if (
                is_numeric($this->plugin->currentLocation->latitude) &&
                is_numeric($this->plugin->currentLocation->longitude)
           ) {
            $this->idString .= __(' at ').$this->plugin->currentLocation->latitude.','.$this->plugin->currentLocation->longitude;
        }

        // Hook in our filters that generate the form.
        //
        add_filter('slp_edit_location_left_column'  ,array($this,'filter_EditLocationLeft_Address')   , 5);
        add_filter('slp_edit_location_left_column'  ,array($this,'filter_EditLocationLeft_Submit')    ,99);
        add_filter('slp_edit_location_right_column' ,array($this,'filter_EditLocationRight_Address')  , 5);

        // Create the form.
        //
        // FILTER: slp_add_location_form_footer
        // FILTER: slp_edit_location_left_column
        // FILTER: slp_edit_location_right_column
        //
        $content  =
           "<form id='manualAddForm' name='manualAddForm' method='post' enctype='multipart/form-data'>" .
           "<input type='hidden' name='locationID' "                                                    .
                "id='locationID' value='{$this->plugin->currentLocation->id}' />"                       .
           "<input type='hidden' name='linked_postid-{$this->plugin->currentLocation->id}' "            .
                "id='linked_postid-{$this->plugin->currentLocation->id}' value='"                       .
                $this->plugin->currentLocation->linked_postid                                           .
                "' />"                                                                                  .
           "<a name='a{$this->plugin->currentLocation->id}'></a>"                                       .
           "<table cellpadding='0' class='slp_locationinfoform_table'>"                                 .
           "<tr>"                                                                                       .

           // Left Cell
           "<td id='slp_manual_update_table_left_cell' valign='top'>"                                   .
                "<div id='slp_edit_left_column' class='add_location_form'>"                             .
                    apply_filters('slp_edit_location_left_column','')                                   .
                '</div>'                                                                                .
           '</td>'                                                                                      .

           // Right Cell
           "<td id='slp_manual_update_table_right_cell' valign='top'>"                                  .
                "<div id='slp_edit_right_column' class='add_location_form'>"                            .
                    apply_filters('slp_edit_location_right_column','')                                  .
                '</div>'                                                                                .
           '</td>'                                                                                      .
           '</tr></table>'                                                                              .
            ($this->addingLocation?apply_filters('slp_add_location_form_footer', ''):'')                .
            '</form>'
            ;

           // FILTER: slp_locationinfoform
           //
          return apply_filters('slp_locationinfoform',$content);
     }

    /**
     * Render the admin page navbar (tabs)
     *
     * @global type $submenu - the WordPress Submenu array
     * @return type
     */
    function create_Navbar() {
        if (!$this->setParent()) { return; }

        global $submenu;
        if (!isset($submenu[$this->plugin->prefix]) || !is_array($submenu[$this->plugin->prefix])) {
            echo apply_filters('slp_navbar','');
        } else {
            $content =
                '<div id="slplus_navbar">' .
                    '<div class="about-wrap"><h2 class="nav-tab-wrapper">';

            // Loop through all SLP sidebar menu items on admin page
            //
            foreach ($submenu[$this->plugin->prefix] as $slp_menu_item) {

                // Create top menu item
                //
                $selectedTab = ((isset($_REQUEST['page']) && ($_REQUEST['page'] === $slp_menu_item[2])) ? ' nav-tab-active' : '' );
                $content .= apply_filters(
                        'slp_navbar_item_tweak',
                        '<a class="nav-tab'.$selectedTab.'" href="'.menu_page_url( $slp_menu_item[2], false ).'">'.
                            $slp_menu_item[0].
                        '</a>'
                        );
            }
            $content .= apply_filters('slp_navbar_item','');
            $content .='</h2></div></div>';
            return apply_filters('slp_navbar',$content);
        }
    }

    /**
     * Return the icon selector HTML for the icon images in saved markers and default icon directories.
     *
     * @param type $inputFieldID
     * @param type $inputImageID
     * @return string
     */
     function CreateIconSelector($inputFieldID = null, $inputImageID = null) {
        if (!$this->setParent()) { return 'could not set parent'; }
        if (($inputFieldID == null) || ($inputImageID == null)) { return ''; }


        $htmlStr = '';
        $files=array();
        $fqURL=array();


        // If we already got a list of icons and URLS, just use those
        //
        if (
            isset($this->plugin->data['iconselector_files']) &&
            isset($this->plugin->data['iconselector_urls'] ) 
           ) {
            $files = $this->plugin->data['iconselector_files'];
            $fqURL = $this->plugin->data['iconselector_urls'];

        // If not, build the icon info but remember it for later
        // this helps cut down looping directory info twice (time consuming)
        // for things like home and end icon processing.
        //
        } else {

            // Load the file list from our directories
            //
            // using the same array for all allows us to collapse files by
            // same name, last directory in is highest precedence.
            $iconAssets = apply_filters('slp_icon_directories',
                    array(
                            array('dir'=>SLPLUS_UPLOADDIR.'saved-icons/',
                                  'url'=>SLPLUS_UPLOADURL.'saved-icons/'
                                 ),
                            array('dir'=>SLPLUS_ICONDIR,
                                  'url'=>SLPLUS_ICONURL
                                 )
                        )
                    );
            $fqURLIndex = 0;
            foreach ($iconAssets as $icon) {
                if (is_dir($icon['dir'])) {
                    if ($iconDir=opendir($icon['dir'])) {
                        $fqURL[] = $icon['url'];
                        while ($filename = readdir($iconDir)) {
                            if (strpos($filename,'.')===0) { continue; }
                            $files[$filename] = $fqURLIndex;
                        };
                        closedir($iconDir);
                        $fqURLIndex++;
                    } else {
                        $this->parent->notifications->add_notice(
                                9,
                                sprintf(
                                        __('Could not read icon directory %s','csa-slplus'),
                                        $directory
                                        )
                                );
                         $this->parent->notifications->display();
                    }
               }
            }
            ksort($files);
            $this->plugin->data['iconselector_files'] = $files;
            $this->plugin->data['iconselector_urls']  = $fqURL;
        }

        // Build our icon array now that we have a full file list.
        //
        foreach ($files as $filename => $fqURLIndex) {
            if (
                (preg_match('/\.(png|gif|jpg)/i', $filename) > 0) &&
                (preg_match('/shadow\.(png|gif|jpg)/i', $filename) <= 0)
                ) {
                $htmlStr .=
                    "<div class='slp_icon_selector_box'>".
                        "<img class='slp_icon_selector'
                             src='".$fqURL[$fqURLIndex].$filename."'
                             onclick='".
                                "document.getElementById(\"".$inputFieldID."\").value=this.src;".
                                "document.getElementById(\"".$inputImageID."\").src=this.src;".
                             "'>".
                     "</div>"
                     ;
            }
        }

        // Wrap it in a div
        //
        if ($htmlStr != '') {
            $htmlStr = '<div id="'.$inputFieldID.'_icon_row" class="slp_icon_row">'.$htmlStr.'</div>';

        }


        return $htmlStr;
     }

}
