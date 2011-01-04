<?
session_start();
include 'class/ShortURL.php';

$url = new ShortURL();

$ret = $url->extractEverything();

echo '<ul>';
foreach($ret as $unRet){
	echo '<li> '.$unRet['attributes']['id'].' => <a href="'.$unRet['childs']['url'].'">'.$unRet['childs']['url'].'</a> => '.$unRet['childs']['hit'].' hits';	
}

echo '</ul>';