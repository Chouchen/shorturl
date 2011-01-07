<? 
header("Content-type: text/javascript");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé

$s			= $_GET['s'];
$url		= $_GET['u'];
$title		= $_GET['t'];
$version	= $_GET['v'];

include 'class/ShortURL.php';

$short = new ShortURL();

if($s=='' || $url==''){
	echo 'alert("'.ShortURL::STATE_FIELD_MISSING.'");';
	exit;
}

$ret = $short->shortThisUrl($url, $s);

if(is_bool($ret) && !$ret){
	echo 'alert("'.ShortURL::STATE_ERROR.'");';
	exit;
}
elseif($ret === ShortURL::STATE_ALREADY_EXIST){
	echo 'alert("'.$ret.'");';
	exit;
}
else{
	echo 'alert("Raccourci cr&eacute;&eacute; : <a href="http://'.$_SERVER['SERVER_NAME'].$folder.rawurlencode($newname).'">http://'.$_SERVER['SERVER_NAME'].$folder.rawurlencode($newname).'</a>");';
	exit;
}