<?php
header('Access-Control-Allow-Origin: *'); 
header('Content-Type: application/json');
define("PATH","/var/www/html/db");  

function get_block($hash) {
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
	}
       else { 
           return array();
       }
}

function Hex2String($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

if (!isset($_GET['block'])) $_GET['block']=800;
if (!isset($_GET['count'])) $_GET['count']=100;

$a=array();
$a['jsonrpc']="2.0";
$a['id']=1;
$a['password']="MYPASSWORD";
$a['method']="getTransactions";
$a['params']=array();
$b=array();
$b['firstBlockIndex']=(int)$_GET['block'];
$b['blockCount']=(int)$_GET['count'];
$a['params']=$b;
$u = json_encode($a);
$u=str_replace("[]","{}",$u);
unlink("z.log");
$v = "curl -d '" . $u . "' http://localhost:8070/json_rpc > z.log";
exec($v);
$myfile = fopen("z.log", "r") or die("Unable to open file!");
$data=fread($myfile,filesize("z.log"));
fclose($myfile);
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
        $block=get_block($block_array['BLOCK_HASH'] . ".h");
        array_push($output,$block);
 }
   }
}
echo json_encode($output);
?>


