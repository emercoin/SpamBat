#!/usr/bin/php
<?php
// SpamBat common file
// License: BSD
// Author: Oleg Khovayko (olegarch)
// Created: Oct, 30, 2022

include "SpamBat_inc.php";

CheckOpenWallet();

// Get argv parameter - how many UTXO to generate
$num_utxo = intval($argv[1]);
if($num_utxo == 0) {
    echo "Usage:\n\t$argv[0] Num_STAMP_UTXO_to_generate\n";
    exit(1);
}

// We cannot generate Num UTXOs more than addresses within addr pool.
// You need to increase config param $AddrPoolSize,
// and regenerate addr_pool with W1_generate_addr_pool.php
if($num_utxo > $AddrPoolSize) {
    echo "Requested too many STAMP_UTXOs=$num_utxo, reduced to AddrPoolSize=$AddrPoolSize\n";
    $num_utxo = $AddrPoolSize;
}

// Need load all addresses for shuffle, for randomize addresses usage
$addr_list = array();
$addr_fh  = fopen($AddrPoolFname, "r");
while (($line = fgets($addr_fh)) !== false) {
    if($line[0] >= '0') {
        array_push($addr_list, trim($line));
    }
} // while
fclose($addr_fh);
shuffle($addr_list);

$listsz = count($addr_list);
if($listsz < $num_utxo) {
    echo "Current file $AddrPoolFname contains only $listsz addresses; Will be generated $listsz STAMP_UTXOs\n";
    $num_utxo = $listsz;
}

$wallet_balance = EMC_req('getbalance', array(), "Unable to fetch wallet balance");
$will_spend = 0.0001 + $num_utxo * ($StampPrice + 0.00001); // Conservative approx value

echo "wallet_balance=$wallet_balance expect_to_pay=$will_spend\n";
if($will_spend > $wallet_balance) {
  echo "Insufficient balance for generate $num_utxo STAMP_UTXOs, exit\n";
  exit(1);
}

// Generate parameter for sendmany
$pay_list = array();
for($i = 0; $i < $num_utxo; $i++)
    $pay_list[$addr_list[$i]] = $StampPrice;

$rc = EMC_req('sendmany', array("", $pay_list, 1, $Label), "Unable to pay with sendmany");
echo "$argv[0]: $num_utxo STAMP_UTXOs generated; TxId=$rc\n";
?>
