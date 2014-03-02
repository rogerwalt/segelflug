<?
$dbh = new PDO('mysql:host=localhost;dbname=segelflug;charset=utf8', 'segelflug', 'AyurBGRjW9cz2qx3');

switch ($_GET['correct']) {
	case true:
		$sql = 'UPDATE answer_statistics SET correct=correct+1 WHERE id_question = '.intval($_GET['id']).';';
		break;
	case false:
		$sql = 'UPDATE answer_statistics SET wrong=wrong+1 WHERE id_question = '.intval($_GET['id']).';';
		break;
	default:
		throw new Exception('Data malformatted.');
		break;
}

$dbh->exec($sql);

echo json_encode(array('success' => true));