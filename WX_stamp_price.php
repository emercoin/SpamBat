#!/usr/bin/php
<?php
// SpamBat common file
// License: BSD
// Author: Oleg Khovayko (olegarch)
// Created: Oct, 30, 2022

include "SpamBat_inc.php";

// Get argv parameter - how many UTXO to generate
$stamp_tx = $argv[1];
if($stamp_tx == 0) {
    echo "Usage:\n\t$argv[0] Stamp_TX\n";
    exit(1);
}

$burn_coins = 0;
do {
    if(!ValidStamp($stamp_tx))
        break;
    $tx = EMC_req('decoderawtransaction', array($stamp_tx), "Unable to decode stamp transaction");
    foreach($tx['vout'] as $vout)
        if($vout['scriptPubKey']['type'] != 'nulldata')
            $burn_coins -= $vout['value'];
    // Stamp transaction burns it's own VINs, so ccalculate sum of amounts
    foreach($tx['vin'] as $vin) {
        $prev_tx = EMC_req('getrawtransaction', array($vin['txid'], 1), "Unable to fetch prevTX=" . $vin['txid']);
        $burn_coins += $prev_tx['vout'][$vin['vout']]['value'];
    }
} while(false);
print "$burn_coins\n";
?>
