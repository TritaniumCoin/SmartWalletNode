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

 
class TritaniumRpcWallet {
	
	protected $password;					
	
	function __construct($password) {
		$this->password=$password;
	}

	function String2Hex($s){
		$hex='';
		for ($i=0; $i < strlen($s); $i++){
			$hex .= dechex(ord($s[$i]));
		}
		return $hex;
	}

	private function set_post($cmd) {
		
			$post=array();
			$post['jsonrpc']="2.0";
			$post['id']=1;
			$post['password']=$this->password;
			$post['method']=$cmd;
			$post['params']=array();			
			return $post;
			
	}	
 
	private function Hex2String($hex){
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}
	
	private function callApi($u) {
		if (file_exists("api.log")) unlink("api.log");
		$v = "curl -d '" . $u . "' http://localhost:8070/json_rpc > api.log";
		exec($v);
		$myfile = fopen("api.log", "r") or die("Unable to open file!");
		$json=fread($myfile,filesize("api.log"));
		$results=json_decode($json,true);
		return $results;
	}

	public function postTraceIO($timestamp, $hash, $url="http://tracebilityblockchain.io/data/", $fee=1) {

		$payload=array();
		$payload['TIME_STAMP']=$timestamp;
		$payload['BLOCK_HASH']=$hash;
		$payload['BLOCK_URL']=$url;
		$str=json_encode($payload);
		$output=$this->String2Hex($str);
		$extra="88888888888888888888" . $output;

		$post=array();
		$post['jsonrpc']="2.0";
		$post['id']=1;
		$post['password']=$this->password;
		$post['method']="sendTransaction";
		$post['params']=array();
		
		$params=array();
		$transfers=array();
		$tx=array();
		$tx['address']=MINER_ADDRESS;
		$tx_amount=$fee*1000000;
		$tx['amount']=$tx_amount;
		array_push($transfers,$tx);

		$params['transfers']=$transfers;
		$params['fee']=100;
		$params['anonymity']=7;
		$params['extra']=$extra;
		$params['changeAddress']=MY_ADDRESS;

		$post['params']=$params;
		$u = json_encode($post);
		
		$results=$this->callApi($u);
		return $results;
		
	}
	
	public function getTraceBlock($hash) {
			$fullPath=PATH . "/" . $hash;
			if ($fd = fopen ($fullPath, "r")) {
				$json="";
				while(!feof($fd)) {
					$buffer = fread($fd, 2048);
					$json.=$buffer;
				}
				fclose ($fd);
                $json=base64_decode($json);
                $j=json_decode($json,true);
                return $j;
			} else { 
				return array();
			}
	}

	public function getTraceBlocks($firstBlockIndex,$blockCount) {
			$data=$this->getTransactions($firstBlockIndex,$blockCount);
			$output=array();
			$array=json_decode($data,true);
			$ax=$array['result']['items'];
			foreach ($ax as $aa) {
				foreach($aa['transactions'] as $ab) {
					$xtra=$ab['extra'];
					if (strpos($xtra,"88888888888888888888")) {
						$x=substr($xtra,86,999);
						$y=Hex2String($x);
						$block_array=json_decode($y,true); 
						$block=$this->getTraceBlock($block_array['BLOCK_HASH'] . ".h");
						array_push($output,$block);
					}
				}
			}
			return $output;
	}

	public function getTraceLedger($firstBlockIndex=0,$blockCount=99999999) {
			$array=$this->getTransactions($firstBlockIndex,$blockCount);
			$output=array();
			$ax=$array['result']['items'];
			foreach ($ax as $aa) {
				foreach($aa['transactions'] as $ab) {
					$xtra=$ab['extra'];
					if (strpos($xtra,"88888888888888888888")) {
						$x=substr($xtra,86,999);
						$y=$this->Hex2String($x);
						$block_array=json_decode($y,true); 
                                                if (!isset($block_array["BLOCK_URL"])) {
						      $block_array['BLOCK_URL']="http://traceabilityblockchain.io/data/";
                                                }
                                               array_push($output,$block_array);
					}
				}
			}
			return $output;
	}

	public function getOwnerBlocks($owner,$firstBlockIndex,$blockCount) {
			$data=$this->getTransactions($firstBlockIndex,$blockCount);
			$output=array();
			$array=json_decode($data,true);
			$ax=$array['result']['items'];
			foreach ($ax as $aa) {
				foreach($aa['transactions'] as $ab) {
					$xtra=$ab['extra'];
					if (strpos($xtra,"88888888888888888888")) {
						$x=substr($xtra,86,999);
						$y=Hex2String($x);
						$block_array=json_decode($y,true); 
						$block=$this->getTraceBlock($block_array['BLOCK_HASH'] . ".h");
						$f=0;
						foreach($block['TRANSACTION_LIST'] as $tl) {
								if ($tl['OWNER']==$owner||$tl['CREATOR']==$owner) $f=1;
						}
						if ($f==0) {
								$block['TRANSACTION_LIST']=array();
						}
						array_push($output,$block);								
					}
				}
			}
			return $output;
	}
	
	public function sendTransaction($address, $coins=0) {

		$post=$this->set_post("sendTransaction");
		
		$params=array();
		$transfers=array();
		$tx=array();
		$tx['address']=$address;
		$tx_amount=$coins*1000000;
		$tx['amount']=$tx_amount;
		array_push($transfers,$tx);

		$params['transfers']=$c;
		$params['fee']=100;
		$params['anonymity']=7;
		$params['changeAddress']=MY_ADDRESS;

		$post['params']=$params;
		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}	
	
	public function resetWallet($viewSecretKey) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"reset","params":{"viewSecretKey":"xxxxx..."}}' http://localhost:8070/json_rpc
		
		$post=$this->set_post("reset");	
		$params=array();
		$params['viewSecretKey']=$viewSecretKey;
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;		
	}
	public function save() {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"save","params":{}}' http://localhost:8070/json_rpc

		$post=$this->set_post("save");	
		$params=array();
		$post['params']=$params;
		$u = json_encode($post);
		$u=str_replace("[]","{}",$u);		
		$results=$this->callApi($u);
		return $results;
	}
	public function getViewKey() {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getViewKey","params":{}}' http://localhost:8070/json_rpc		
		
		$post=$this->set_post("getViewKey");			
		$params=array();
		$post['params']=$params;
		$u = json_encode($post);
		$u=str_replace("[]","{}",$u);	
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getSpendKeys($address) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getSpendKeys","params":{"address":"TRTLxxxx..."}}' http://localhost:8070/json_rpc	

		$post=$this->set_post("getSpendKeys");	
		$params=array();
		$params['address']=$address;
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getMnemonicSeed($address) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getMnemonicSeed","params":{"address":"TRTLxxxx..."}}' http://localhost:8070/json_rpc

		$post=$this->set_post("getMnemonicSeed");	
		$params=array();
		$params['address']=$address;
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getStatus() {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getStatus","params":{}}' http://localhost:8070/json_rpc

		$post=$this->set_post("getStatus");	
		$params=array();
		$post['params']=$params;
		$u = json_encode($post);
		$u=str_replace("[]","{}",$u);	
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getAddresses() {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getAddresses","params":{}}' http://localhost:8070/json_rpc

		$post=$this->set_post("getAddresses");	
		$params=array();
		$post['params']=$params;
		$u = json_encode($post);
		$u=str_replace("[]","{}",$u);	
		$results=$this->callApi($u);
		return $results;
		
	}
	public function createAddress() {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"createAddress","params":{}}' http://localhost:8070/json_rpc

		$post=$this->set_post("createAddress");	
		$params=array();
		$post['params']=$params;
		$u = json_encode($post);
		$u=str_replace("[]","{}",$u);	
		$results=$this->callApi($u);
		return $results;
		
	}
	public function deleteAddress($address) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"deleteAddress","params":{"address":"TRTLxxxx..."}}' http://localhost:8070/json_rpc

		$post=$this->set_post("deleteAddress");	
		$params=array();
		$params['address']=$address;
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getBalance($address) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getBalance","params":{"address":"TRTLxxxx..."}}' http://localhost:8070/json_rpc

		$post=$this->set_post("getBalance");	
		$params=array();
		$params['address']=$address;
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getBlockHashes($firstBlockIndex,$blockCount) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getBlockHashes","params":{"firstBlockIndex":0,"blockCount":3}}' http://localhost:8070/json_rpc

		$post=$this->set_post("getBlockHashes");	
		$params=array();
		$params['firstBlockIndex']=$firstBlockIndex;
		$params['blockCount']=$blockCount;		
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getTransactionHashes() {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getTransactionHashes","params":{"firstBlockIndex":400000,"blockCount":100000}}' http://localhost:8070/json_rpc

		$post=$this->set_post("getTransactionHashes");	
		$params=array();
		$params['firstBlockIndex']=$firstBlockIndex;
		$params['blockCount']=$blockCount;		
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getTransactions($firstBlockIndex=0,$blockCount=9999999) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getTransactions","params":{"firstBlockIndex":400000,"blockCount":100000}}' http://localhost:8070/json_rpc

		$post=$this->set_post("getTransactions");	
		$params=array();
		$params['firstBlockIndex']=(int)$firstBlockIndex;
		$params['blockCount']=(int)$blockCount;		
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getUnconfirmedTransactionHashes() {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getUnconfirmedTransactionHashes","params":{}}' http://localhost:8070/json_rpc

		$post=$this->set_post("getUnconfirmedTransactionHashes");	
		$params=array();
		$post['params']=$params;
		$u = json_encode($post);
		$u=str_replace("[]","{}",$u);	
		$results=$this->callApi($u);
		return $results;
		
		
	}
	public function getTransaction($transactionHash) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getTransaction","params":{"transactionHash":"55a23..."}}' http://localhost:8070/json_rpc

		$post=$this->set_post("getTransaction");	
		$params=array();
		$params['transactionHash']=$transactionHash;
		$params['blockCount']=$blockCount;		
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}	
	public function createDelayedTransaction() {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"createDelayedTransaction","params":{"transfers":[{"address":"TRTLxxxx...","amount":5000}],"fee":10,"anonymity":7,"changeAddress":"TRTLyyyy..."}}' http://localhost:8070/json_rpc		

		$post=$this->set_post("createDelayedTransaction");	
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}	
	public function getDelayedTransactionHashes() {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getDelayedTransactionHashes","params":{}}' http://localhost:8070/json_rpc		

		$post=$this->set_post("getDelayedTransactionHashes");	
		$params=array();
		$post['params']=$params;
		$u = json_encode($post);
		$u=str_replace("[]","{}",$u);	
		$results=$this->callApi($u);
		return $results;
		
	}	
	public function deleteDelayedTransaction($transactionHash) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"deleteDelayedTransaction","params":{"transactionHash":"b3e37..."}}' http://localhost:8070/json_rpc		

		$post=$this->set_post("deleteDelayedTransaction");	
		$params=array();
		$params['transactionHash']=$transactionHash;
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}	
	public function sendDelayedTransaction($transactionHash) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"sendDelayedTransaction","params":{"transactionHash":"c37cd..."}}' http://localhost:8070/json_rpc		

		$post=$this->set_post("sendDelayedTransaction");	
		$params=array();
		$params['transactionHash']=$transactionHash;
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}	
	public function sendFusionTransaction() {
		
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"sendFusionTransaction","params":{"threshold":1000000,"anonymity":7,"addresses":["TRTLxxxx...","TRTLyyyy..."],"destinationAddress":"TRTLzzzz..."}}' http://localhost:8070/json_rpc

		$post=$this->set_post("sendFusionTransaction");	
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}	
	public function estimateFusion() {
		
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"estimateFusion","params":{"threshold":1000000,"addresses":["TRTLxxxx...","TRTLyyyy..."]}}' http://localhost:8070/json_rpc		

		$post=$this->set_post("estimateFusion");	
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}
	public function createIntegratedAddress($paymentId, $address) {
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"createIntegratedAddress","params":{"paymentId":"7FE73BD90EF05DEA0B5C15FC78696619C50DD5F2BA628F2FD16A2E3445B1922F", "address":"TRTLxxxx..."}}' http://localhost:8070/json_rpc		

		$post=$this->set_post("createIntegratedAddress",$this->password);	
		$params=array();
		$params['paymentId']=$paymentId;		
		$params['address']=$address;
		$post['params']=$params;		
		$u = json_encode($post);
		$results=$this->callApi($u);
		return $results;
		
	}
	public function getFeeInfo() {
		
		//		curl -d '{"jsonrpc":"2.0","id":1,"password":"passw0rd","method":"getFeeInfo","params":{}}' http://localhost:8070/json_rpc		

		$post=$this->set_post("getFeeInfo");	
		$params=array();
		$post['params']=$params;
		$u = json_encode($post);
		$u=str_replace("[]","{}",$u);	
		$results=$this->callApi($u);
		return $results;
	}	
}

?>
