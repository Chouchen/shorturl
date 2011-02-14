<?
/**
 * 
 * Main file : style incorporated since it's a unique page
 * A form and a bookmarklet
 */
session_start();
include 'class/ShortURL.php';

$url = new ShortURL();

?>
<!doctype html>
<html>
<head>
<link href='http://fonts.googleapis.com/css?family=Geo' rel='stylesheet' type='text/css'>
<style>
html {margin:0;padding:0;border:0;font-size:100.01%;}
body, div, span, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, code, del, dfn, em, img, q, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, dialog, figure, footer, header, hgroup, nav, section {margin:0;padding:0;border:0;font-weight:inherit;font-style:inherit;font-size:100%;font-family:inherit;vertical-align:baseline; font-family:'Geo', Arial, sans-serif;}
body {line-height:1.5;background:white;}
h1 {font-size:3em;line-height:1;margin-bottom:0.5em;font-weight:normal;color:#FFF;}
hr {visibility:hidden;clear:both;float:none;width:100%;height:1px;border:none;}
label {font-weight:bold; width:98px;margin-top:11px;}
input[type=text], input[type=password], input.text, input.title, textarea {background-color:#fff;border:1px solid #bbb;margin:0.5em 0;}
input[type=text]:focus, input[type=password]:focus, input.text:focus, input.title:focus, textarea:focus {border-color:#666;}
input.text, input.title {width:300px;padding:5px;}
input.title {font-size:1.5em;}
body{background:#1E1E1A; color:#FFF;}
.container{background:#0C0C0A;border-radius:5px;}
input[type=text], input[type=search], input[type=email], input[type=password]{width:300px;height:23px;}
input[type=text], input[type=search], input[type=email], input[type=password], textarea{
	background:#EEE;
	float:left;
	margin: 0.7em 0.5em;
	border:1px solid #CCC;
	padding: 2px 7px;
	font-family:Georgia, serif;
}
input[type=text]:hover, input[type=search]:hover, input[type=email]:hover, input[type=password]:hover, textarea:hover{
	border:1px solid #000;
}
h1 { font-family: 'Geo', arial, serif; }
h1 a, label, a {color:#FF9900;}
label{float:left;}
input[type=submit] {
  display:block;
  float:left;
  margin-left:105px;
  padding:5px 10px 5px 7px;   /* Links */
  
  border:1px solid #dedede;
  border-top:1px solid #eee;
  border-left:1px solid #eee;

  background-color:#f5f5f5;
  font-family:"Geo", Tahoma, Arial, Verdana, sans-serif;
  font-size:100%;
  line-height:130%;
  text-decoration:none;
  font-weight:bold;
  color:#565656;
  cursor:pointer;
}
button, input[type=submit] {
  width:auto;
  overflow:visible;
  padding:4px 10px 3px 7px;   /* IE6 */
}
button[type], input[type=submit] {
  padding:4px 10px 4px 7px;   /* Firefox */
  line-height:17px;           /* Safari */
}
*:first-child+html button[type] {
  padding:4px 10px 3px 7px;   /* IE7 */
}
button img, a.button img, input[type=submit] img{
  margin:0 3px -3px 0 !important;
  padding:0;
  border:none;
  width:16px;
  height:16px;
  float:none;
}


/* Button colors
-------------------------------------------------------------- */

/* Standard */
button:hover, a.button:hover, input[type=submit]:hover{
  background-color:#dff4ff;
  border:1px solid #c2e1ef;
  color:#336699;
}
.bookmarklet{ text-decoration: none; background: #FF9900; color: white; padding: 8px 20px; -moz-border-radius: 10px; -webkit-border-radius: 10px; border-radius: 10px; border: 1px solid #fff; margin-left:105px;}
#footer{clear:both; margin: 200px 0 0 30px;}
.myLink{padding-left:14px; background:url('http://shikiryu.com/favicon12.png') left center no-repeat;}
</style>
</head>
<body>
<h1>Yet Another URL Shortener</h1>
<form action="short.php" method="post">
<label for="url">URL : </label><input type="text" name="url" /><hr class="space" />
<label for="shortName">Shortcut : </label><input type="text" name="shortName" /><hr class="space" />
<input type="submit" value="Save">
</form>
<br/><br/><?
if(isset($_SESSION['msg'])){
	echo $_SESSION['msg'];
	unset($_SESSION['msg']);
}
?><br/><br/><br/>
<a href="javascript:var%20saisie=prompt('Shortcut%20name:','');if(saisie==null){alert('Shortcut%20cannot%20be%20empty')}else{var%20d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,t=d.title,f='http://nu.x10.bz/add.php',l=d.location,e=encodeURIComponent,p='?v=1&u='+e(l.href)%20+'&t='+e(t)%20+'&s='+saisie,u=f+p;var%20newScript%20=%20document.createElement('script');newScript.type='text/javascript';newScript.src=u;document.body.appendChild(newScript);}void(0)" class="bookmarklet">Short this!</a> <span style="font-size: 14px;">&lt; drag to your bookmarks bar</span> 
<?
if(isset($_SESSION['msg'])){
	echo $_SESSION['msg'];
	unset($_SESSION['msg']);
}
?>
<div id="footer">Powered by <a href="http://shikiryu.com/" class="myLink">Shikiryu</a></div>
</body>
</html>