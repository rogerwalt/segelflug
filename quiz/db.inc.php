<?
try {
	$dbh = new PDO('mysql:host=localhost;dbname=segelflug;charset=utf8', 'segelflug', 'AyurBGRjW9cz2qx3');
}
catch (PDOException $e) {
	die('unable to connect to database ' . $e->getMessage());
}