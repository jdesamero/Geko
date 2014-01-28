<?php
/***********************************************************************
* Class: csl_mobile_listener
*
* The slplus service creation object
*
* This handles the creation of the mobile listener service
*
************************************************************************/

if (! class_exists('csl_mobile_listener')) {
    class csl_mobile_listener {
        
            /*************************************
             * The Constructor
             */
            function __construct($params) {
                foreach ($params as $name => $sl_value) {            
                    $this->$name = $sl_value;
                }

                $this->DoHeaders();
                $this->CheckErrors();
                $this->PerformSearch();
            }

            function GetLocations() {
                import_request_variables("gp");
                global $slplus_plugin;

                //set the callback
                if (!isset($_REQUEST['callback'])) {
                    $callback = '';
                }
                else {
                    $callback = $_REQUEST['callback'];
                }

                if (!isset($_REQUEST['max'])) {
                    $max = get_option(SLPLUS_PREFIX.'_maxreturned');
                }
                else {
                    $max = $_REQUEST['max'];
                }
                
                //set a latitude
                if (!isset($_REQUEST['lat'])) {
                    $lat = '';
                }
                else {
                    $lat = $_REQUEST['lat'];
                }

                //set a longitude
                if (!isset($_REQUEST['lng'])) {
                    $lng = '';
                }
                else {
                    $lng = $_REQUEST['lng'];
                }

                //set a radius
                if (!isset($_REQUEST['radius'])) {
                    $radius = 40000;
                }
                else {
                    $radius = $_REQUEST['radius'];
                }

                //set tags
                if (!isset($_REQUEST['tags'])) {
                    $tags = '';
                }
                else {
                    $tags = $_REQUEST['tags'];
                }

                //set a name
                if (!isset($_REQUEST['name'])) {
                    $name = '';
                }
                else {
                    $name = $_REQUEST['name'];
                }

                //create a params object
                $params = array(
                    'center_lat' => $lat,
                    'center_lng' => $lng,
                    'radius' => $radius,
                    'tags' => $tags,
                    'name' => $name,
                    'callback' => $callback,
                    'max' => $max,
                    'apiKey' => ''
                );
                $response = new csl_mobile_listener($params);
            }

            function CheckErrors() {
                if ($this->callback == '') {
                    die (0);
                }

                if ($this->center_lat == '') {
                    $this->Respond(false, 'no latitude passed');
                }

                if ($this->center_lng == '') {
                    $this->Respond(false, 'no longitude passed');
                }
            }

            function Respond($status, $complete) {
                die(''.$this->callback.'('.json_encode(array('success' => $status, 'response' => $complete)).');');
            }

            function DoHeaders() {
                header('Content-Type: application/json; charset=' . get_option('blog_charset'), true);
            }

            function PerformSearch() {
                global $wpdb;
	            $username=DB_USER;
	            $password=DB_PASSWORD;
	            $database=DB_NAME;
	            $host=DB_HOST;
	            $dbPrefix = $wpdb->prefix;

	            //-----------------
	            // Set the active MySQL database
	            //
	            $connection=mysql_connect ($host, $username, $password);
	            if (!$connection) { die(json_encode( array('success' => false, 'response' => 'Not connected : ' . mysql_error()))); }
	            $db_selected = mysql_select_db($database, $connection);
	            mysql_query("SET NAMES utf8");
	            if (!$db_selected) {
		            $this->Respond( 'Can\'t use db : ' . mysql_error());
	            }

	            // If tags are passed filter to just those tags
	            //
	            $tag_filter = ''; 
	            if (
		            (get_option(SLPLUS_PREFIX.'_show_tag_search') ==1) &&
		            isset($this->tags) && ($this->tags != '')
	            ){
		            $posted_tag = preg_replace('/^\s+(.*?)/','$1',$this->tags);
		            $posted_tag = preg_replace('/(.*?)\s+$/','$1',$posted_tag);
		            $tag_filter = " AND ( sl_tags LIKE '%%". $posted_tag ."%%') ";
	            }

	            $this->name_filter = '';
	            if(isset($this->name) && ($this->name != ''))
	            {
		            $posted_name = preg_replace('/^\s+(.*?)/','$1',$this->name);
		            $posted_name = preg_replace('/(.*?)\s+$/','$1',$posted_name);
		            $name_filter = " AND (sl_store LIKE '%%".$posted_name."%%')";
	            }

                // Radian multiplier to get linear distance
                $multiplier=(get_option('sl_distance_unit')=="km")? 6371 : 3959;

	            $option[SLPLUS_PREFIX.'_maxreturned']=(trim(get_option(SLPLUS_PREFIX.'_maxreturned'))!="")? 
                get_option(SLPLUS_PREFIX.'_maxreturned') : 
                '25';
	
	            $max = $option[SLPLUS_PREFIX.'_maxreturned'];

                if ($this->max < $max) {
                    $max = $this->max;
                }

                //for ($rad = $this->radius; $rad < 40000; $rad += 100) {
		            //Select all the rows in the markers table
		            $query = sprintf(
			            "SELECT *,".
			            "( $multiplier * acos( cos( radians('%s') ) * cos( radians( sl_latitude ) ) * cos( radians( sl_longitude ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( sl_latitude ) ) ) ) AS sl_distance ".
			            "FROM ${dbPrefix}store_locator ".
			            "WHERE sl_longitude<>'' %s %s ".
			            "HAVING (sl_distance < '%s') ".
			            'ORDER BY sl_distance ASC '.
			            'LIMIT %s',
			            mysql_real_escape_string($this->center_lat),
			            mysql_real_escape_string($this->center_lng),
			            mysql_real_escape_string($this->center_lat),
			            $tag_filter,
			            $name_filter,
			            mysql_real_escape_string($this->radius),
			            mysql_real_escape_string($max)
		            );
		
		            $result = mysql_query($query);
		            if (!$result) {
			            $this->Respond( false, 'Invalid query: ' . mysql_error() . '- '.$query);
		            }

		            // Show Tags
		            //
		            $slplus_show_tags = (get_option(SLPLUS_PREFIX.'_show_tags') ==1);

		            // Reporting
		            // Insert the query into the query DB
		            // 
		            if (get_option(SLPLUS_PREFIX.'-reporting_enabled') === 'on') {
			            $qry = sprintf(                                              
					            "INSERT INTO ${dbPrefix}slp_rep_query ". 
							               "(slp_repq_query,slp_repq_tags,slp_repq_address,slp_repq_radius) ". 
						            "values ('%s','%s','%s','%s')",
						            mysql_real_escape_string($_SERVER['QUERY_STRING']),
						            mysql_real_escape_string($this->tags),
						            mysql_real_escape_string($_POST['address']),
						            mysql_real_escape_string($this->radius)
					            );
			            $wpdb->query($qry);
			            $slp_QueryID = mysql_insert_id();
		            }
		
		            // Start the response string
		            $response = array();
		
		            // Iterate through the rows, printing XML nodes for each
		            while ($row = @mysql_fetch_assoc($result)){
			            // ADD to array of markers
			
			            $marker = array(
				            //'test' => stuff
                            'id' => esc_attr($row['sl_id']),
				            'name' => esc_attr($row['sl_store']),
				            'address' => esc_attr($row['sl_address']),
				            'address2' => esc_attr($row['sl_address2']),
				            'city' => esc_attr($row['sl_city']),
				            'state' => esc_attr($row['sl_state']),
				            'zip' => esc_attr($row['sl_zip']),
				            'lat' => $row['sl_latitude'],
				            'lng' => $row['sl_longitude'],
				            'description' => html_entity_decode($row['sl_description']),
				            'url' => esc_attr($row['sl_url']),
				            'sl_pages_url' => esc_attr($row['sl_pages_url']),
				            'email' => esc_attr($row['sl_email']),
				            'hours' => esc_attr($row['sl_hours']),
				            'phone' => esc_attr($row['sl_phone']),
				            'fax' => esc_attr($row['sl_fax']),
                            'units' => get_option('sl_distance_unit'),
				            'image' => esc_attr($row['sl_image']),
				            'distance' => $row['sl_distance'],
				            'tags' => ($slplus_show_tags) ? esc_attr($row['sl_tags']) : ''
			            );
			            $response[] = $marker;
			
			            // Reporting
			            // Insert the results into the reporting table
			            //
			            if (get_option(SLPLUS_PREFIX.'-reporting_enabled') === "on") {
				            $wpdb->query(
					            sprintf(
						            "INSERT INTO ${dbPrefix}slp_rep_query_results 
							            (slp_repq_id,sl_id) values (%d,%d)",
							            $slp_QueryID,
							            $row['sl_id']  
						            )
					            );           
			            }
		            }
		
		            //if (count($response) > 1) {
		            //	break;
		            //}
	            //}

	            $this->Respond(true, $response);
            }
    }
}