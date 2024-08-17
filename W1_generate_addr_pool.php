#!/usr/bin/php
<?php
// SpamBat common file
// License: BSD
// Author: Oleg Khovayko (olegarch)
// Created: Oct, 30, 2022
include "SpamBat_inc.php";

function fetchAddresses($errtxt) {
    global $AccountName;
    $addr_list = EMC_req('getaddressesbylabel', array($AccountName), $errtxt);
    return empty($addr_list)? array() : array_keys($addr_list);
}

CheckOpenWallet();

$addr_list = fetchAddresses("");
$listsz = count($addr_list);
$need_more = $AddrPoolSize - $listsz;
echo "Wallet contains $listsz addresses for label [$AccountName]\n";
if($need_more > 0) {
    echo "Will generate extra $need_more to reach $AddrPoolSize\n";
    while($need_more != 0) {
        $rc = EMC_req('getnewaddress', array($AccountName), "Unable getnewaddress");
        $need_more--;
    }
    $addr_list = fetchAddresses("Unable to get address list");
}
file_put_contents($AddrPoolFname, implode("\n", $addr_list) . "\n");
?>
