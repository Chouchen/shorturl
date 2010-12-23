<?
session_start();
include 'class/ShortURL.php';

$url = new ShortURL();

$name = $_GET['name'];

$_SESSION['msg'] = '';

$ret = $url->findThisUrl($name);

if($ret == null){
	header("Location: 404.html");
}else{
	header("Location: ".$ret[0]);
}
