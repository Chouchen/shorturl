<?
session_start();
include 'class/ShortURL.php';

$url = new ShortURL();

?>
<form action="short.php" method="post">
<label for="url">URL : </label><input type="text" name="url" />
<label for="shortName">Raccourci : </label><input type="text" name="shortName" />
<input type="submit" value="Envoyer">
</form>

<?
if(isset($_SESSION['msg'])){
	echo $_SESSION['msg'];
	unset($_SESSION['msg']);
}