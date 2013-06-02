<?php

$censusLink = new CensusLink();

if(isset($_REQUEST['action'])) {
  if($_REQUEST['action'] == 'getIncomeByCounty') {
    $censusLink->getIncomeByCounty($_REQUEST['county'], $_REQUEST['state']);
  }
}

class CensusLink {
  // The base URL for this Socrata API, ex: http://data.medicare.gov/api or http://www.socrata.com/api
  private $root_url = "http://api.census.gov/data/2010/acs5?key=";

  // App Token
  private $app_token = "";

  // Basic constructor
  public function __construct($app_token = "9b64236d2e9459864b2ed1bfe20abfee4df43261") {
    
    // Check that the token given was not just an empty string
    if($app_token != '') {

      $this->app_token = isset( $_REQUEST['key'] ) ? $_REQUEST['key'] : $app_token;
      return true;

    } else {

      // Present an error if it is empty
      echo "The census API key given is not valid.";
      return false;

    }
    
  }

  // Convenience function for GET calls
  public function get($params) {

    // The full URL for this resource is the root + the path
    $full_url = $this->root_url . $this->app_token . $params;

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

    // Create an API map to be given as json after results are used
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


    // Construct the query string
    $qstring = "&get=B19001_001E,B19001" . implode(',B19001', array_keys($income_map['income']['results'])) . "&for=county:$county&in=state:$state";

    // Issue get
    $results = $this->get($qstring);

    // Loop throught the so-called JSON response to figure out where things go
    for($i = 0; $i < count($results[1]); $i++) {

      // One result is the totals so we need to make a special case due to placement in map
      if($results[0][$i] == 'B19001_001E') {

        $income_map['income']['_001E']['quantity'] = $results[1][$i];

      } else {

        $income_map['income']['results'][substr($results[0][$i], 6)]['quantity'] = $results[1][$i];
      
      }
    }

    $json = isset($_REQUEST['callback']) ? "{$_REQUEST['callback']}(" . json_encode($income_map) . ")" : json_encode($income_map);

    echo $json;
  }
}

?>
