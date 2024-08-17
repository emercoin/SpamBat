#!/usr/bin/php
<?php
// SpamBat common file
// License: BSD
// Author: Oleg Khovayko (olegarch)
// Created: Oct, 30, 2022

include "SpamBat_inc.php";

CheckOpenWallet();

$unspent_list = array();
// Label is "SpamBat demo v01"
// Do not decrease length, otherwise tx-size-small error in 0.8.5
// After 0.8.6, we can decrease this tail label
$dummy_vout   = array('data' => '5370616d4261742064656d6f20763031'); // Hex of the label

// There is used full wallet, maybe will be need to add filter for addresses from pool
$rc = EMC_req('listunspent', array(), "Unable to listunspent");
foreach($rc as $utxo)
    $unspent_list[$utxo['txid']][$utxo['vout']] = $utxo['amount'];

$stamps_tmp = fopen($StampsFname . ".out", "w");

$stamps_copied  = 0;
$stamps_deleted = 0;
$stamps_created = 0;

if(file_exists($StampsFname)) {
    // Read/filter/copy infile if exists only
    $stamps_fh  = fopen($StampsFname, "r");
    // Read stamps, check status for each, and copy valid to output
    while (($line = fgets($stamps_fh)) !== false) {
        if(empty($line) || $line[0] < '0')
            continue; // skip empty lines or comments/garbage
                      // Line format: timestamp,transaction_stamp[,amount]
        $time_txstamp = explode(",", trim($line));
        if(!ValidStamp($time_txstamp[1])) {
            $stamps_deleted++;
            continue; // skip invalid or paid stamps
        }
        $rc = EMC_req('decoderawtransaction', array($time_txstamp[1]), "Unable to decoderawtransaction");
        foreach($rc['vin'] as $in)
            $unspent_list[$in['txid']][$in['vout']] = 0; // Disallow these VINs for stamp generation
                                                         // Write walid stamp or comment
        fwrite($stamps_tmp, $line);
        $stamps_copied++;
    } // while
    fclose($stamps_fh);
} // // Read/filter/copy

// Generate stamps from remaining unspent
foreach($unspent_list as $txid => $vout) {
    foreach($vout as $n => $amo) {
        if($amo < $StampPrice)
            continue; // Skip not sufficient or blocked UTXOs
        $new_vin = array(array('txid' => $txid, 'vout' => $n));
        $stamp = EMC_req('createrawtransaction', array($new_vin, $dummy_vout), "Unable to createrawtransaction");
        $rc = EMC_req('signrawtransactionwithwallet', array($stamp), "Unable to sign raw transaction, seems like wallet is locked");
        if($rc['complete']) {
            $hex = $rc['hex'];
            // echo "Created stamp for $$amo\n";
            fprintf($stamps_tmp, "0,$hex,$amo\n");
            $stamps_created++;
        } else
            echo "Incomplete TX for input:  $txid:$n => $amo\n";
     } // foreach vout
} // foreach unspent_list

fclose($stamps_tmp);

echo "$argv[0]: Stamps: Deleted=$stamps_deleted Copied=$stamps_copied Created=$stamps_created\n";
?>
