#!/usr/bin/php
<?php
// SpamBat common file
// License: BSD
// Author: Oleg Khovayko (olegarch)
// Created: Oct, 30, 2022

//------------------------------------------------------------------------------
// EMC account in the work wallet
$AccountName  = ""; // Default account

// Label for sendmany for W0 output TX
$Label = "Pay to SpamBat";

// Number of addresses for addr-ppol
$AddrPoolSize = 20;

// Address pool file
$AddrPoolFname = "./addr_pool.txt";

// Stamp price n EMC; this value will burn
// $StampPrice = 1.00;
$StampPrice = 0.123;

// Number of available stamps (stamp-pool size)
$NumStamps  = 5;

// This file contains ready-to-use stamps
$StampsFname = "./stamps.txt";

//------------------------------------------------------------------------------
// Export here connect variable, like:
// URL for connect to Emercoin wallet
#$emcCONNECT = "http://user:secret_pass@localhost:6662";
include("config-lite.php");

//------------------------------------------------------------------------------
// Performs NVS-request to EMC wallet
// Returns JSON of response result. Exit with err print, if error
// Example:
// $ret = EMC_req('name_show', array('val:emercoin'), "Unable to run name_show");
function EMC_req($cmd, $params, $errtxt) {
  global $emcCONNECT;
  // Prepares the request
  $request = json_encode(array(
    'method' => $cmd,
    'params' => $params,
    'id' => '1'
  ));
  // Prepare and performs the HTTP POST
  $opts = array ('http' => array (
    'method'  => 'POST',
    'header'  => 'Content-type: application/json',
    'content' => $request
  ));
  do {
    $fp = @fopen($emcCONNECT, 'rb', false, stream_context_create($opts));
    if(!$fp)
      break;
    $rc = json_decode(stream_get_contents($fp), true);
    $er = $rc['error'];
    if(!is_null($er)) {
      printf("ERROR: %s\n", $er);
      break;
    }
    return $rc['result'];
  } while(false);
  // Error handler
  printf("%s\nFail request details: cmd=[%s] and params:\n", $errtxt, $cmd);
  print_r($params);
  exit(1);
} // EMC_req

function CheckOpenWallet() {
  $getinfo = EMC_req('getinfo', array(), "Unable connect wallet");
  $err = $getinfo["errors"];
  if(!empty($err)) {
    echo("ERROR: Wallet must be open, error=[$err]\n");
    exit(1);
  }
}

function ValidStamp(&$rawtx) {
    $rc = EMC_req('signrawtransaction', array($rawtx, array(), array(), "ALL"), "Unable to validate raw transaction");
    if(!boolval($rc['complete']))
        return false;
    // Check within mempool
    $mempool = EMC_req('getrawmempool', array(), "Unable to fetch raw mempool");
    if(count($mempool) == 0)
        return true;
    $tx = EMC_req('decoderawtransaction', array($rawtx), "Unable to decode stamp transaction");
    $txid = $tx['txid'];
    foreach($mempool as $mptx)
        if($txid == $mptx)
            return false; // Transaction exists within mempool
    return true;
}

?>
