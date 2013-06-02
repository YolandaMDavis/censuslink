<?php

$censusLink = new CensusLink();

$output = array();

if( isset( $_REQUEST['action'] ) ) {

  $actions = explode(',', $_REQUEST['action']);

  foreach( $actions as $action ) {
    $output[] = call_user_func( array( $censusLink, $action ) );
  }

}

$output = isset( $_REQUEST['callback'] ) ? "{$_REQUEST['callback']}(" . json_encode( $output ) . ")" : json_encode( $output );

echo $output;


/*
 * Start of the CensusLink class
 *
 */

class CensusLink {
  // The base URL for this Socrata API, ex: http://data.medicare.gov/api or http://www.socrata.com/api
  private $root_url = "http://api.census.gov/data/2010/acs5?key=";

  // App Token
  private $app_token = "";

  private $county = "";
  private $state = "";

  //@todo remove the app token once the api has been shown off
  // Basic constructor
  public function __construct( $app_token = "9b64236d2e9459864b2ed1bfe20abfee4df43261" ) {
    
    // Check that the token given was not just an empty string
    if( $app_token != '' ) {

      $this->county = isset($_REQUEST['county']) ? $_REQUEST['county'] : "121";
      $this->state = isset($_REQUEST['state']) ? $_REQUEST['state'] : "13";

      $this->app_token = isset( $_REQUEST['key'] ) ? $_REQUEST['key'] : $app_token;
      return true;

    } else {

      // Present an error if it is empty
      echo "The census API key given is not valid.";
      return false;

    }
    
  }

  // Convenience function for GET calls
  public function get( $params ) {

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
    curl_setopt( $handle, CURLOPT_URL, $full_url );
    curl_setopt( $handle, CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );

    $response = curl_exec( $handle );
    $code = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
    if( $code != "200" ) {
      echo "Error \"$code\" from server: $response";
      die();
    }

    return json_decode( $response, true );
  }
  
  public function getIncomeByCounty() {

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
    $qstring = "&get=B19001_001E,B19001" . implode( ',B19001', array_keys( $income_map['income']['results'] ) ) . "&for=county:{$this->county}&in=state:{$this->state}";

    // Issue get
    $results = $this->get( $qstring );

    // Loop throught the so-called JSON response to figure out where things go
    for( $i = 0; $i < count( $results[1] ); $i++ ) {

      // One result is the totals so we need to make a special case due to placement in map
      if( $results[0][$i] == 'B19001_001E' ) {

        $income_map['income']['_001E']['quantity'] = $results[1][$i];

      } else {

        if( !empty( $income_map['income']['results'][substr( $results[0][$i], 6 )] ) ) {
          // @todo check that the element exists before putting into the map
          $income_map['income']['results'][substr( $results[0][$i], 6 )]['quantity'] = $results[1][$i];
        }
      
      }
    }

    return $income_map;
  }

  // Function for getting the education levels
  public function getEducationByCounty() {

    // Create an API map to be given as json after results are used
    $education_map = array(
      "education" => array(
          "_001E" => array("title" => "Total", "quantity" => ""),
          "results" => array(
            "_002E" => array("title" => "Less than high school graduate", "quantity" => ""),
            "_003E" => array("title" => "High school graduate", "quantity" => ""),
            "_004E" => array("title" => "Some college or associate's degree", "quantity" => ""),
            "_005E" => array("title" => "Bachelor's degree", "quantity" => ""),
            "_006E" => array("title" => "Graduate or professional degree", "quantity" => "")
          )
        )
    );

    // Construct the query string
    $qstring = "&get=B07409_001E,B07409" . implode( ',B07409', array_keys( $education_map['education']['results'] ) ) . "&for=county:{$this->county}&in=state:{$this->state}";

    // Issue get
    $results = $this->get( $qstring );

    // Loop throught the so-called JSON response to figure out where things go
    for( $i = 0; $i < count( $results[1] ); $i++ ) {

      // One result is the totals so we need to make a special case due to placement in map
      if( substr( $results[0][$i], 6 ) == '_001E' ) {

        $education_map['education']['_001E']['quantity'] = $results[1][$i];

      } else {

        if( !empty( $education_map['education']['results'][substr( $results[0][$i], 6 )] ) ) {
          // @todo check that the element exists before putting into the map
          $education_map['education']['results'][substr( $results[0][$i], 6 )]['quantity'] = $results[1][$i];
        }
      
      }
    }

    return $education_map;
  }

  // Function for getting the ethnicity by county
  public function getEthnicityByCounty() {

    if (!isset($this->county) || !isset($this->state)) {
      echo "Error: county param or state param not set properly";
      die();
    }


    $ethnicity_map = array(
      "ethnicity" => array(
          "_001E" => array("title" => "Total", "quantity" => ""),
          "results" => array(
            "_002E" => array("title" => "White", "quantity" => ""),
            "_003E" => array("title" => "Black or African American", "quantity" => ""),
            "_004E" => array("title" => "American Indian and Alaska Native", "quantity" => ""),
            "_005E" => array("title" => "Asian", "quantity" => ""),
            "_006E" => array("title" => "Native Hawaiian and Other Pacific Islander", "quantity" => ""),
            "_005E" => array("title" => "Other race", "quantity" => ""),
          )
        )
    );

    // Construct the query string
    $qstring = "&get=B02001_001E,B02001" . implode( ',B02001', array_keys( $ethnicity_map['ethnicity']['results'] ) ) . "&for=county:{$this->county}&in=state:{$this->state}";

    // Issue get
    $results = $this->get( $qstring );

    // Loop throught the so-called JSON response to figure out where things go
    for( $i = 0; $i < count( $results[1] ); $i++ ) {

      // One result is the totals so we need to make a special case due to placement in map
      if( substr( $results[0][$i], 6 ) == '_001E' ) {

        $ethnicity_map['ethnicity']['_001E']['quantity'] = $results[1][$i];

      } else {

        if( !empty( $ethnicity_map['ethnicity']['results'][substr( $results[0][$i], 6 )] ) ) {
          // @todo check that the element exists before putting into the map
          $ethnicity_map['ethnicity']['results'][substr( $results[0][$i], 6 )]['quantity'] = $results[1][$i];
        }
      
      }
    }

    return $ethnicity_map;
  }

  public function getCountyList() {

    if (!isset($this->state)) {
      echo "Error: state param not set properly";
      die();
    }

    // Construct the query string
    $qstring = "&get=NAME&for=county:*&in=state:{$this->state}";

    // Issue get
    $counties = array('counties' => $this->get( $qstring ) );

    for($i = 0; $i < count($counties['counties']); $i++) {
      unset($counties['counties'][$i][1]);
    }

    return $counties;

  }

  public function getStateIdList() {

    // Construct the query string
    $qstring = "&get=NAME&for=state:*";

    // Issue get
    return array('states' => $this->get( $qstring ) );

  }

}