<?php

/*
 * Blockchain Node Communication
 * https://github.com/TritaniumCoin/SmartWalletNode
 *
 * Copyright 2016-2018, Tritanium Labs PTE Ltd.
 * UEN: 201821323R
 * #14-02 Collyer Quay, The Arcade, Singapore 049317
 *
 * Blockchain API interface to transfer the current block  
 * ledger to another node.
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */
 

require('tritanium.blockchain.wallet.php');
define("PATH","/var/www/html/db");  
define("MASTER_NODE","http://traceabilityblockchain.io/data/"); 
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
ini_set('memory_limit',-1);
ini_set('max_execution_time', 30000); 
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *'); 
set_time_limit(0);
	
$fp = fopen (PATH . '/tritanium.ctrl', 'w+');		
$ch = curl_init(MASTER_NODE . "api.tritanium.ctrl.php");
curl_setopt($ch, CURLOPT_TIMEOUT, 500);
curl_setopt($ch, CURLOPT_FILE, $fp); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "EXEC=CTRL");	
curl_exec($ch); 
curl_close($ch);
fclose($fp);

$X=New TritaniumRpcWallet("Loipol229!");
$array=$X->getTraceLedger(1,99999);

foreach ($array as $hash) {
	$fp=PATH . "/" . $hash['BLOCK_HASH'] . ".h";
	if (!file_exists($fp)) {
		echo "Header: " . $hash['BLOCK_HASH'] . ".h\r\n";
		$fp = fopen (PATH . '/' . $hash['BLOCK_HASH'] . '.h' , 'w+');	
		$ch = curl_init($hash['BLOCK_URL'] . "api.tritanium.header.php?file=" . urlencode($hash['BLOCK_HASH']));
		curl_setopt($ch, CURLOPT_TIMEOUT, 500);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_exec($ch); 
		curl_close($ch);
		fclose($fp);			
	}
}

foreach ($array as $hash) {
	$fp=PATH . "/" . $hash['BLOCK_HASH'] . ".b";
	if (!file_exists($fp)) {			
		$fp = fopen (PATH . '/' . $hash['BLOCK_HASH'] . '.b', 'w+');
		echo "Block: " . $hash['BLOCK_HASH'] . "h\r\n";
		$ch = curl_init($hash['BLOCK_URL'] . "api.tritanium.block.php?file=" . urlencode($hash['BLOCK_HASH']));
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_exec($ch); 
		curl_close($ch);
		fclose($fp);			
	}
}

?>

