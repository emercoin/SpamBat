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
// ANTTN:
// sendrawtransaction incorrectly works in emercoin core 0.8.5, and this code can work correctly
// in 0.8.6 or higher.
// Contact support@emercoin.com for details
$txid = EMC_req('sendrawtransaction', array($stamp_tx, 0), "Unable to broadcast stamp transaction");
echo "Burn coins: Sent TX=$txid\n";

?>
