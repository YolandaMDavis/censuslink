<?php

// $test = new Socrata();

// print_r($test->getIncomeByCounty());

class Socrata {
  // The base URL for this Socrata API, ex: http://data.medicare.gov/api or http://www.socrata.com/api
  private $root_url = "http://api.census.gov/data/2010/acs5?key=9b64236d2e9459864b2ed1bfe20abfee4df43261";

  // App Token
  private $app_token = "";

  // Basic constructor
  public function __construct($app_token = "") {
    $this->app_token = $app_token;
    return true;
  }

  // Convenience function for GET calls
  public function get($params) {

    // The full URL for this resource is the root + the path
    $full_url = $this->root_url . $params;

    // Build up the headers we'll need to pass
    $headers = array(
      'Accept: application/json',
      'Content-type: application/json',
      "X-App-Token: " . $this->app_token
    );

    // Time for some cURL magic...
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $full_url);
    curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($handle);
    $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    if($code != "200") {
      echo "Error \"$code\" from server: $response";
      die();
    }

    return json_decode($response, true);
  }
  
  public function getIncomeByCounty($county = 121, $state = 13) {

    $income_map = array(
      "income" => array(
          "_001E" => array("title" => "Total", "quantity" => ""),
          "results" => array(
            "_002E" => array("title" => "Less than $10,000", "quantity" => ""),
            "_003E" => array("title" => "$10,000 to $14,999", "quantity" => ""),
            "_004E" => array("title" => "$15,000 to $19,999", "quantity" => ""),
            "_005E" => array("title" => "$20,000 to $24,999", "quantity" => ""),
            "_006E" => array("title" => "$25,000 to $29,999", "quantity" => ""),
            "_007E" => array("title" => "$30,000 to $34,999", "quantity" => ""),
            "_008E" => array("title" => "$35,000 to $39,999", "quantity" => ""),
            "_009E" => array("title" => "$40,000 to $44,999", "quantity" => ""),
            "_010E" => array("title" => "$45,000 to $49,999", "quantity" => ""),
            "_011E" => array("title" => "$50,000 to $59,999", "quantity" => ""),
            "_012E" => array("title" => "$60,000 to $74,999", "quantity" => ""),
            "_013E" => array("title" => "$75,000 to $99,999", "quantity" => ""),
            "_014E" => array("title" => "$100,000 to $124,999", "quantity" => ""),
            "_015E" => array("title" => "$125,000 to $149,999", "quantity" => ""),
            "_016E" => array("title" => "$150,000 to $199,999", "quantity" => ""),
            "_017E" => array("title" => "$200,000 or more", "quantity" => ""),
          )
        )
    );


    // construct the query string
    $qstring = "&get=B19001_001E,B19001" . implode(',B19001', array_keys($income_map['income']['results'])) . "&for=county:$county&in=state:$state";

    // issue get
    $results = $this->get($qstring);

    for($i = 0; $i < count($results[1]); $i++) {
      if($results[0][$i] == 'B19001_001E') {
        $income_map['income']['_001E']['quantity'] = $results[1][$i];
      } else {
        $income_map['income']['results'][substr($results[0][$i], 6)]['quantity'] = $results[1][$i];
      }
    }

    return $income_map;
  }


 // Convenience function for Posts
  public function post($path, $json_filter) {

    // The full URL for this resource is the root + the path
    $full_url = $this->root_url . $path;


    // Build up the headers we'll need to pass
    $headers = array(
      'Accept: application/json',
      'Content-type: application/json',
      "X-App-Token: " . $this->app_token
    );

    // Time for some cURL magic...
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $full_url);
    curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json_filter);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

    // Set up request, and auth, if configured
    if($this->user_name != "" && $this->password != "") {
      curl_setopt($handle, CURLOPT_USERPWD, $this->user_name . ":" . $this->password);
    }

    $response = curl_exec($handle);
    $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    if($code != "200") {
      echo "Error \"$code\" from server: $response";
      die();
    }

    return json_decode($response, true);
  }
}


// Convenience functions
function array_get($needle, $haystack) {
  return (in_array($needle, array_keys($haystack)) ? $haystack[$needle] : NULL);
}

function pre_dump($var) {
  echo "<pre>" . print_r($var) . "</pre>";
}
?>
