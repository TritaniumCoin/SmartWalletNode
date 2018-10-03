<?php
header('Access-Control-Allow-Origin: *'); 
header('Content-Type: application/json');

function String2Hex($s){
    $hex='';
    for ($i=0; $i < strlen($s); $i++){
        $hex .= dechex(ord($s[$i]));
    }
    return $hex;
}
 
 
function Hex2String($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

if (!isset($_GET['ts'])) die();
if (!isset($_GET['hash'])) die();
if (!isset($_GET['password'])) die();

$timestamp=$_GET['ts'];
$hash=$_GET['hash'];
$password=$_GET['password'];

$payload=array();
$payload['TIME_STAMP']=$timestamp;
$payload['BLOCK_HASH']=$hash;
$str=json_encode($payload);
$output=String2Hex($str);
$extra="88888888888888888888" . $output;
$a=array();
$a['jsonrpc']="2.0";
$a['id']=1;
$a['password']=$password;
$a['method']="sendTransaction";

$a['params']=array();
$b=array();
$c=array();
$c0=array();
$c0['address']="Tri1WfbAJ2viEkdaVxQmAgKwqtaUBx1mgj1ddwXUywPTJrxUESuciMUg8mtFwEoyBB5w4khcJKuKXDCdjjhKtXu62sN2Y7vy67";
$c0['amount']=1000000;
array_push($c,$c0);

$b['transfers']=$c;
$b['fee']=100;
$b['anonymity']=7;
$b['extra']=$extra;
$v['changeAddress']="YOUR_ADDRESS_HERE";

$a['params']=$b;
$u = json_encode($a);
//$u=str_replace("[]","{}",$u);
unlink("b.log");
$v = "curl -d '" . $u . "' http://localhost:8070/json_rpc > b.log";
exec($v);
$myfile = fopen("b.log", "r") or die("Unable to open file!");
echo fread($myfile,filesize("b.log"));
fclose($myfile);
?>
