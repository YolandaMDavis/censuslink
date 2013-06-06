<?php
require_once("censuslink.php");

$api_key = isset( $_REQUEST['key'] ) ? $_REQUEST['key'] : "";

$censusLink = new CensusLink($api_key);

$censusLink->state = isset( $_REQUEST['state'] ) ? $_REQUEST['state'] : null;
$censusLink->county = isset( $_REQUEST['county'] ) ? $_REQUEST['county'] : null;

$output = array();

if( isset( $_REQUEST['action'] ) ) {

  $actions = explode(',', $_REQUEST['action']);

  foreach( $actions as $action ) {
    $output['censusLink'] = call_user_func( array( $censusLink, $action ) );
  }

}

$output = isset( $_REQUEST['callback'] ) ? "{$_REQUEST['callback']}(" . json_encode( $output ) . ")" : json_encode( $output );

echo $output;