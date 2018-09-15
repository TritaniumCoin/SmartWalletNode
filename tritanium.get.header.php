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
	
$fp = fopen (PATH . '/ledger.ctrl', 'w+');	
$ch = curl_init(MASTER_NODE . "api.tritanium.ledger.php");
curl_setopt($ch, CURLOPT_TIMEOUT, 500);
curl_setopt($ch, CURLOPT_FILE, $fp); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "EXEC=LEDGER");	
curl_exec($ch); 
curl_close($ch);
fclose($fp);

$csvAsArray = array_map('str_getcsv', file(PATH . "/ledger.ctrl"));

$new_files = fopen (PATH . '/newfiles.ctrl', 'w+');		
foreach ($csvAsArray as $hash) {
$fp=PATH . "/" . $hash[1] . ".h";
if (!file_exists($fp)) {
fwrite($new_files,$hash[0] . "," . $hash[1] . "\r\n");

echo "Header: " . $hash[1] . "\r\n";

$fp = fopen (PATH . '/' . $hash[1] . '.h', 'w+');	
$ch = curl_init(MASTER_NODE . "api.tritanium.file.php?file=" . urlencode($hash[1]));
curl_setopt($ch, CURLOPT_TIMEOUT, 500);
curl_setopt($ch, CURLOPT_FILE, $fp); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, true);
//curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);	
curl_exec($ch); 
curl_close($ch);
fclose($fp);			
}
}
fclose($new_files);

foreach ($csvAsArray as $hash) {
$fp=PATH . "/" . $hash[1] . ".b";
if (!file_exists($fp)) {			
$fp = fopen (PATH . '/' . $hash[1] . '.b', 'w+');	
echo "Block: " . $hash[1] . "\r\n";
$ch = curl_init(MASTER_NODE . "api.tritanium.file.php?file=" . urlencode($hash[1]));
curl_setopt($ch, CURLOPT_TIMEOUT, 50);
curl_setopt($ch, CURLOPT_FILE, $fp); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, true);
//curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);	
curl_exec($ch); 
curl_close($ch);
fclose($fp);			
}
}

?>
