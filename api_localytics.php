<?php
/*
 * PHP library for Localytics data API
 * Requires PHP 5.2 with JSON
 */

// YOU MUST SET EACH METRIC AS TRUE BEFORE YOU INCLUDE THIS FILE TO OUTPUT IT, OTHERWISE IT WILL SKIP THAT METRIC FOR PERFORMANCE PURPOSES //
			// OUTPUT: users_today; -- unique users for today -- //
			$run_users_today = true;
			// OUTPUT: users_today; -- unique users for today -- //			
			$run_users_yesterday = true;
			// OUTPUT: users_today; -- unique users for today -- //			
			$run_average_users_last_30 = true;
			// OUTPUT: users_today; -- unique users for today -- //			
			$run_users_last_30 = true;
			// OUTPUT: users_today; -- unique users for today -- //			
			$run_average_session_length_yesterday = true;
			$run_seconds_per_user_yesterday = true;
			$run_average_session_length_today = true;
			$run_seconds_per_user_today = true;
			$run_register_today = true;
			$run_register_yesterday = true;
			$run_register_last_30 = true;
			$run_sessions_today = true;
			$run_messages_today = true;
			$run_messages_yesterday = true;
			
			

date_default_timezone_set('America/Los_Angeles');

// -- API FUNCTIONS -- //   

function call_localytics_api($method, $url, $data)
    {
        $curl = curl_init();

        $url = sprintf("%s?%s", $url, http_build_query($data));
        $api_key = "ENTER YOUR API KEY HERE";
        $api_secret = "ENTER YOUR API SECRET HERE";

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":" . $api_secret);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // Disable the SSL verificaiton process
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/vnd.localytics.v1+hal+json"));
            
        // Confirm cURL gave a result, if not, write the error
    
        $response = curl_exec($curl);
    
        if ($response === FALSE) {
            die("Curl Failed: " . curl_error($curl));
        } else {
            return $response;
        }
    }



// VARIALBES //

$today = date('Y-m-d');
$yesterday = date('Y-m-d',strtotime("-1 days"));
$last_30_start = date('Y-m-d', strtotime("-31 days"));
$last_30_end = date('Y-m-d', strtotime("-1 days"));
$api_querystring = "https://api.localytics.com/v1/query";
$app_id = "ENTER YOUR APP ID HERE";

// VARIALBES //

// METRIC: Users Today (DAU)

if ($run_users_today) {

    $metric = "users"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"day":["in","' . urlencode($today) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    // print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $users_today = $result->users;         
    }
}    

// METRIC: Users Yesterday (DAU)

if ($run_users_yesterday) {

    $metric = "users"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"day":["in","' . urlencode($yesterday) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    // print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $users_yesterday = $result->users;         
    }      

}

// METRIC: Users Average Last 30 Days (DAU)

if ($run_average_users_last_30) {

    $metric = "users"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"day":["between","' . urlencode($last_30_start) . '","' . urlencode($last_30_end) . '"]}');  

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    // print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $users[] = $result->users;                    

    }      

    $total = array_sum($users);
    $average_users_last_30 = number_format($total / 30);

}

//METRIC: Users Last 30 Days (MAU)

if ($run_users_last_30) {

    $metric = "users"; 
    $days = 30;
    $data = array(app_id => $app_id, metrics => $metric, days => $days);

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    // print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $users_last_30 = $result->users;         
    }      

}


// METRIC: Average Session Length Yesterday

if ($run_average_session_length_yesterday) {

    $metric = "average_session_length"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"day":["in","' . urlencode($yesterday) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    //print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $average_session_length_yesterday = $result->average_session_length; // IN SECONDS
    } 

    //$average_session_length_yesterday = gmdate("i\m s\s", $average_session_length_yesterday%86400);

}

// METRIC: Average Time in App Yesterday  

if ($run_seconds_per_user_yesterday) {

    $metric = "sessions"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"day":["in","' . urlencode($yesterday) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    //print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $sessions_yesterday = $result->sessions;               
    } 
    $total_seconds_yesterday = $sessions_yesterday * $average_session_length_yesterday;
    $seconds_per_user_yesterday = $total_seconds_yesterday / $users_yesterday;
    
    // $average_time_in_app_yesterday = gmdate("g\h i\m", $seconds_per_user_yesterday%86400);
}

// METRIC: Average Session Length Today

if ($run_average_session_length_today) {

    $metric = "average_session_length"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"day":["in","' . urlencode($today) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    //print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $average_session_length_today = $result->average_session_length; // IN SECONDS
    } 

    //$average_session_length_today = gmdate("i\m s\s", $average_session_length_today%86400);    
}

// METRIC: Average Time in App Today

if ($run_seconds_per_user_today) {

    $metric = "sessions"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"day":["in","' . urlencode($today) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    //print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $sessions_today = $result->sessions;               
    } 
    $total_seconds_today = $sessions_today * $average_session_length_today;
    $seconds_per_user_today = $total_seconds_today / $users_today;

    //$average_time_in_app_today = date('g\h i\m',$seconds_per_user_today);
    // $average_time_in_app_today = gmdate("g\h i\m", $seconds_per_user_today%86400); 

}

// EVENT: Register Today

if ($run_register_today) {

    $metric = "occurrences"; 
    $dimensions = "event_name";
    $event = "Register";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"event_name":"' . $event . '","day":["in","' . urlencode($today) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    // print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $register_today = number_format($result->occurrences); 
    }  
}

// EVENT: Register Yesterday

if ($run_register_yesterday) {

    $metric = "occurrences"; 
    $dimensions = "event_name";
    $event = "Register";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"event_name":"' . $event . '","day":["in","' . urlencode($yesterday) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    // print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $register_yesterday = number_format($result->occurrences); 
    }  
}

// EVENT: Register Last 30 Days

if ($run_register_last_30) {

    $metric = "occurrences"; 
    $dimensions = "event_name";
    $event = "Register";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"event_name":"' . $event . '","day":["between","' . urlencode($last_30_start) . '","' . urlencode($last_30_end) . '"]}');  

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    //print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $register_last_30 = number_format($result->occurrences);
    }   
}       
        
// EVENT: Sessions Today

if ($run_sessions_today) {

    $metric = "sessions"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"day":["in","' . urlencode($today) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    //print_r($json);
    $json = $json->results;
    foreach ($json as $result) {
        $sessions_today = number_format($result->sessions);               
    } 
}    

// EVENT: # of Messages Today

if ($run_messages_today) {

    $metric = "occurrences"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"event_name":["in_groups",{"Send_Total":["Send_Public","Send_Private","Send_Direct"]}],"day":["in","' . urlencode($today) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    // print_r($json);
    // die();
    $json = $json->results;
    foreach ($json as $result) {
        $messages_today = number_format($result->occurrences); 
    }  
}

// EVENT: # of Messages Yesteraday

if ($run_messages_yesterday) {

    $metric = "occurrences"; 
    $dimensions = "day";
    $data = array(app_id => $app_id, metrics => $metric, dimensions => $dimensions, conditions => '{"event_name":["in_groups",{"Send_Total":["Send_Public","Send_Private","Send_Direct"]}],"day":["in","' . urlencode($yesterday) . '"]}');

    $response = call_localytics_api('GET', $api_querystring, $data);
    $json = json_decode($response);

    // print_r($json);
    // die();
    $json = $json->results;
    foreach ($json as $result) {
        $messages_yesterday = number_format($result->occurrences); 
    }  
}

?>
