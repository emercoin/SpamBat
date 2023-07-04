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

$txid = EMC_req('sendrawtransaction', array($stamp_tx, true), "Unable to broadcast stamp transaction");
echo "Burn coins: Sent TX=$txid\n";

?>
