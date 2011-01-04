<?
session_start();
include 'class/ShortURL.php';

$url = new ShortURL();
$log = new Log('shorting.log');

$newname = $_POST['shortName'];
$newURL  = $_POST['url'];

$log->message('entering '.$newname.' as '.$newURL);

$_SESSION['msg'] = '';
if($newname =='' || $newURL==''){
	$_SESSION['msg'] .= ShortURL::STATE_FIELD_MISSING;
}

$ret = $url->shortThisUrl($newURL, $newname);

if(is_bool($ret) && !$ret){
	$_SESSION['msg'] .= ShortURL::STATE_ERROR;
}
elseif($ret === ShortURL::STATE_ALREADY_EXIST){
	$_SESSION['msg'] .= $ret;
}
else{
	$URI = $_SERVER['REQUEST_URI'];
	$folders = explode('/', $URI);
	if(count($folders) > 2){
		$folder = '/'.$folders[1].'/';
	}else
		$folder = '/';
	
	$log->message('folder : '.$folder);
	
	$_SESSION['msg'] .= 'Raccourci cr&eacute;&eacute; : <a href="http://'.$_SERVER['SERVER_NAME'].$folder.rawurlencode($newname).'">http://'.$_SERVER['SERVER_NAME'].$folder.rawurlencode($newname).'</a>';
	
	$log->message('that makes the link : http://'.$_SERVER['SERVER_NAME'].$folder.rawurlencode($newname).'"');
}

header('Location: '.$folder);