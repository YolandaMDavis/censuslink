<?php

require_once "censuslink.php";

$api_key = isset( $_REQUEST['key'] ) ? $_REQUEST['key'] : "9b64236d2e9459864b2ed1bfe20abfee4df43261";

$censusLink = new CensusLink($api_key);

$output = array();

if( isset( $_REQUEST['action'] ) ) {

  $actions = explode(',', $_REQUEST['action']);

  foreach( $actions as $action ) {
    $output['censusLink'] = call_user_func( array( $censusLink, $action ) );
  }

}

$output['censusLink']['keys'] = $censusLink->included;

$output = isset( $_REQUEST['callback'] ) ? "{$_REQUEST['callback']}(" . json_encode( $output ) . ")" : json_encode( $output );

echo $output;