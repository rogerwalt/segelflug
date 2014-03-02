<?
function print_total_questions() {
	$dbh = new PDO('mysql:host=localhost;dbname=segelflug;charset=utf8', 'segelflug', 'AyurBGRjW9cz2qx3');
	$sql = 'SELECT * FROM questions_merged ORDER BY subject asc, id asc';
	echo iterator_count(new IteratorIterator($dbh->query($sql)));
}

function print_numberof_questions_for_subject($subject) {
	$dbh = new PDO('mysql:host=localhost;dbname=segelflug;charset=utf8', 'segelflug', 'AyurBGRjW9cz2qx3');
	$sql = 'SELECT * FROM questions_merged WHERE subject = '.$dbh->quote($subject).' ORDER BY subject asc, id asc';
	echo iterator_count(new IteratorIterator($dbh->query($sql)));
}

function print_table_for_subject($subject) {
	$dbh = new PDO('mysql:host=localhost;dbname=segelflug;charset=utf8', 'segelflug', 'AyurBGRjW9cz2qx3');
	$sql = 'SELECT * FROM questions_merged WHERE subject = '.$dbh->quote($subject).' ORDER BY subject asc, id asc';
	$str = '';
	$num_rows = iterator_count(new IteratorIterator($dbh->query($sql)));
	foreach($dbh->query($sql) as $row) {
		$str .= '<tr><td>'.$row['question'].'</td><td>'.$row['answer1'].'</td><td>'.$row['answer2'].'</td><td>'.$row['answer3'].'</td><td>'.$row['answer4'].'</td><td>';
		if ($row['answer1_correct']) $str .= '1';
		if ($row['answer2_correct']) $str .= '2';
		if ($row['answer3_correct']) $str .= '3';
		if ($row['answer4_correct']) $str .= '4';
		$str .= '</td></tr>'."\n";
	}
	echo $str;
}
?>
<html>
	<head>
		<meta charset="utf-8">
		<style>
			table {
				border: 1px solid black;
				border-collapse: collapse;
			}
			table thead th {
				background-color: #9f9;
				padding: 5px;
				border: 1px solid black;
			}
			table tbody td {
				padding: 5px;
				border: 1px solid black;
			}
		</style>
		<title>Segelflug Prüfungsfragen</title>
	</head>
	<body>
		<h1>Segelflug Theoriefragen</h1>
		Total <? print_total_questions() ?> Fragen.

		<h2>10 Luftrecht</h2>
		<? print_numberof_questions_for_subject('Luftrecht') ?> Fragen.
		<table style="border: solid black 1px;">
			<thead>
				<th>Frage</th>
				<th>Antwort 1</th>
				<th>Antwort 2</th>
				<th>Antwort 3</th>
				<th>Antwort 4</th>
				<th>Richtige Antwort</th>
			</thead>
			<tbody><? print_table_for_subject('Luftrecht') ?></tbody>
		</table>

		<h2>20 Allgemeine Luftfahrzeugkenntnis</h2>
		<? print_numberof_questions_for_subject('Allgemeine Luftfahrzeugkenntnis') ?> Fragen.
		<table style="border: solid black 1px;">
			<thead>
				<th>Frage</th>
				<th>Antwort 1</th>
				<th>Antwort 2</th>
				<th>Antwort 3</th>
				<th>Antwort 4</th>
				<th>Richtige Antwort</th>
			</thead>
			<tbody><? print_table_for_subject('Allgemeine Luftfahrzeugkenntnis') ?></tbody>
		</table>

		<h2>30 Flugleistungen und Flugplanung</h2>
		<? print_numberof_questions_for_subject('Flugleistungen und Flugplanung') ?> Fragen.
		<table style="border: solid black 1px;">
			<thead>
				<th>Frage</th>
				<th>Antwort 1</th>
				<th>Antwort 2</th>
				<th>Antwort 3</th>
				<th>Antwort 4</th>
				<th>Richtige Antwort</th>
			</thead>
			<tbody><? print_table_for_subject('Flugleistungen und Flugplanung') ?></tbody>
		</table>

		<h2>40 Menschliches Leistungsvermögen</h2>
		<? print_numberof_questions_for_subject('Menschliches Leistungsvermögen') ?> Fragen.
		<table style="border: solid black 1px;">
			<thead>
				<th>Frage</th>
				<th>Antwort 1</th>
				<th>Antwort 2</th>
				<th>Antwort 3</th>
				<th>Antwort 4</th>
				<th>Richtige Antwort</th>
			</thead>
			<tbody><? print_table_for_subject('Menschliches Leistungsvermögen') ?></tbody>
		</table>

		<h2>50 Meteorologie</h2>
		<? print_numberof_questions_for_subject('Meteorologie') ?> Fragen.
		<table style="border: solid black 1px;">
			<thead>
				<th>Frage</th>
				<th>Antwort 1</th>
				<th>Antwort 2</th>
				<th>Antwort 3</th>
				<th>Antwort 4</th>
				<th>Richtige Antwort</th>
			</thead>
			<tbody><? print_table_for_subject('Meteorologie') ?></tbody>
		</table>

		<h2>60 Navigation</h2>
		<? print_numberof_questions_for_subject('Navigation') ?> Fragen.
		<table style="border: solid black 1px;">
			<thead>
				<th>Frage</th>
				<th>Antwort 1</th>
				<th>Antwort 2</th>
				<th>Antwort 3</th>
				<th>Antwort 4</th>
				<th>Richtige Antwort</th>
			</thead>
			<tbody><? print_table_for_subject('Navigation') ?></tbody>
		</table>

		<h2>70 Betriebsverfahren</h2>
		<? print_numberof_questions_for_subject('Betriebsverfahren') ?> Fragen.
		<table style="border: solid black 1px;">
			<thead>
				<th>Frage</th>
				<th>Antwort 1</th>
				<th>Antwort 2</th>
				<th>Antwort 3</th>
				<th>Antwort 4</th>
				<th>Richtige Antwort</th>
			</thead>
			<tbody><? print_table_for_subject('Betriebsverfahren') ?></tbody>
		</table>

		<h2>80 Grundlagen des Fluges</h2>
		<? print_numberof_questions_for_subject('Grundlagen des Fluges') ?> Fragen.
		<table style="border: solid black 1px;">
			<thead>
				<th>Frage</th>
				<th>Antwort 1</th>
				<th>Antwort 2</th>
				<th>Antwort 3</th>
				<th>Antwort 4</th>
				<th>Richtige Antwort</th>
			</thead>
			<tbody><? print_table_for_subject('Grundlagen des Fluges') ?></tbody>
		</table>

		<h2>90 Sprechfunk für Segelflieger</h2>
		<? print_numberof_questions_for_subject('Sprechfunk für Segelflieger') ?> Fragen.
		<table style="border: solid black 1px;">
			<thead>
				<th>Frage</th>
				<th>Antwort 1</th>
				<th>Antwort 2</th>
				<th>Antwort 3</th>
				<th>Antwort 4</th>
				<th>Richtige Antwort</th>
			</thead>
			<tbody><? print_table_for_subject('Sprechfunk für Segelflieger') ?></tbody>
		</table>
	</body>
</html>
