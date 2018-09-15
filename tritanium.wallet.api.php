<?php
/*  
 * Blockchain Utilities Class 
 * https://github.com/TritaniumCoin/SmartWalletNode
 *
 * Copyright 2016-2018, Tritanium Labs PTE Ltd.
 * UEN: 201821323R
 * #14-02 Collyer Quay, The Arcade, 11 Collyer Quay,  Singapore 049317
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */
 
require('tritanium.blockchain.wallet.php');

define("MY_ADDRESS",   "Tri1KEH6zQ8BxHviABB3goXg5QdiyJ5Z7dHU8vBLLBRmE77ou3mJXsdV1KGFr4JNR4jeuiqdwBcHgaPjRf8JH8JX2i2asrzK55";
define("MINER_ADDRESS","Tri1WfbAJ2viEkdaVxQmAgKwqtaUBx1mgj1ddwXUywPTJrxUESuciMUg8mtFwEoyBB5w4khcJKuKXDCdjjhKtXu62sN2Y7vy67";
define("PATH","/var/www/html/db");  
define("RPC_PASSWORD","passw0rd");
define("API_KEY","passw0rd");

if (!isset($_REQUEST['action'])) die('{{"Error":"Invalid Parameters"}');
if (!isset($_REQUEST['key'])) die('{"Error":"Invalid Parameters"}');
if ($_REQUEST['key']!=API_KEY) die('{"Error":"Invalid Parameters"}');
$X=New TritaniumRpcWallet(RPC_PASSWORD);

$result=array();
$result['Error']="Invalid Parameters";

switch ($_REQUEST['action']) {
		case "getFeeInfo":
			$result=$X->getFeeInfo();		
			break;
		case "createIntegratedAddress":
			if (!isset($_REQUEST['paymentId'])) die('{"Error":"Invalid Parameters"}');
			if (!isset($_REQUEST['address'])) die('{"Error":"Invalid Parameters"}');			
			$result=$X->createIntegratedAddress($_REQUEST['paymentId'], $_REQUEST['address']);
			break;
		case "postTraceIO":
			if (!isset($_REQUEST['timestamp'])) die('{"Error":"Invalid Parameters"}');
			if (!isset($_REQUEST['hash'])) die('{"Error":"Invalid Parameters"}');			
			if (!isset($_REQUEST['fee'])) $_REQUEST['fee']=1;				
			$result=$X->postTraceIO($_REQUEST['timestamp'], $_REQUEST['hash'], $_REQUEST['fee']);
			break;
		case "sendTransaction":
			if (!isset($_REQUEST['address'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->sendTransaction($_REQUEST['address'], $_REQUEST['coins']);
			break;
		case "resetWallet":
			if (!isset($_REQUEST['viewSecretKey'])) die('{"Error":"Invalid Parameters"}');					
			$result=$X->resetWallet($_REQUEST['viewSecretKey']);
			break;
		case "save":
			$result=$X->save();
			break;
		case "getViewKey":
			$result=$X->getViewKey();
			break;
		case "getSpendKeys":
			if (!isset($_REQUEST['address'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->getSpendKeys($_REQUEST['address']);
			break;
		case "getMnemonicSeed":
			if (!isset($_REQUEST['address'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->getMnemonicSeed($_REQUEST['address']);
			break;
		case "getStatus":
			$result=$X->getStatus();
			break;
		case "getAddresses":
			$result=$X->getAddresses();
			break;
		case "createAddress":
			$result=$X->createAddress();
			break;
		case "deleteAddress":
			if (!isset($_REQUEST['address'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->deleteAddress($_REQUEST['address']);
			break;
		case "getBalance":
			if (!isset($_REQUEST['address'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->getBalance($_REQUEST['address']);
			break;
		case "getBlockHashes":
			if (!isset($_REQUEST['firstBlockIndex'])) die('{"Error":"Invalid Parameters"}');			
			if (!isset($_REQUEST['blockCount'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->getBlockHashes($_REQUEST['firstBlockIndex'],$_REQUEST['blockCount']);
			break;
		case "getTraceBlocks":
			if (!isset($_REQUEST['firstBlockIndex'])) die('{"Error":"Invalid Parameters"}');			
			if (!isset($_REQUEST['blockCount'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->getTraceBlocks($_REQUEST['firstBlockIndex'],$_REQUEST['blockCount']);
			break;
		case "getTransactionHashes":
			$result=$X->getTransactionHashes();
			break;
		case "getTransactions":
			$result=$X->getTransactions();
			break;
		case "getUnconfirmedTransactionHashes":
			$result=$X->getUnconfirmedTransactionHashes();
			break;
		case "getTransaction":
			if (!isset($_REQUEST['transactionHash'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->getTransaction($_REQUEST['transactionHash']);
			break;
		case "createDelayedTransaction":
			$result=$X->createDelayedTransaction()
			break;
		case "deleteDelayedTransaction":
			if (!isset($_REQUEST['transactionHash'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->deleteDelayedTransaction($_REQUEST['transactionHash']);
			break;
		case "sendDelayedTransaction":
			if (!isset($_REQUEST['transactionHash'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->sendDelayedTransaction($_REQUEST['transactionHash']);
			break;
		case "sendFusionTransaction":
			$result=$X->sendFusionTransaction()
			break;
		case "estimateFusion":
			$result=$X->estimateFusion()
			break;
		case "createIntegratedAddress":
			if (!isset($_REQUEST['paymentId'])) die('{"Error":"Invalid Parameters"}');				
			if (!isset($_REQUEST['address'])) die('{"Error":"Invalid Parameters"}');				
			$result=$X->createIntegratedAddress($_REQUEST['paymentId'], $_REQUEST['address']);
			break;
		case "getFeeInfo":
			$result=$X->getFeeInfo()
			break;
}
echo json_encode($result);

?>