<?
function print_total_questions() {
    $dbh = new PDO('mysql:host=localhost;dbname=segelflug;charset=utf8', 'segelflug', 'AyurBGRjW9cz2qx3');
    $sql = 'SELECT * FROM questions_merged ORDER BY subject asc, id asc';
    echo iterator_count(new IteratorIterator($dbh->query($sql)));
}

function print_numberof_questions_for_subject($subject) {
    $dbh = new PDO('mysql:host=localhost;dbname=segelflug;charset=utf8', 'segelflug', 'AyurBGRjW9cz2qx3');
    $sql = 'SELECT * FROM questions_merged WHERE subject = '.$dbh->quote($subject).' ORDER BY id asc';
    echo iterator_count(new IteratorIterator($dbh->query($sql)));
}

function print_table_for_subject($subject) {
    $dbh = new PDO('mysql:host=localhost;dbname=segelflug;charset=utf8', 'segelflug', 'AyurBGRjW9cz2qx3');
    $sql = 'SELECT * FROM questions_merged WHERE subject = '.$dbh->quote($subject).' ORDER BY id asc';
    $str = '';
    $num_rows = iterator_count(new IteratorIterator($dbh->query($sql)));
    foreach($dbh->query($sql) as $row) {
        $str .= '<tr data-id="'.$row['id'].'"><td>'.$row['question'].'</td><td class="answer" data-correct="'.$row['answer1_correct'].'">'.$row['answer1'].'</td><td class="answer" data-correct="'.$row['answer2_correct'].'">'.$row['answer2'].'</td><td class="answer" data-correct="'.$row['answer3_correct'].'">'.$row['answer3'].'</td><td class="answer" data-correct="'.$row['answer4_correct'].'">'.$row['answer4'].'</td></tr>'."\n";
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
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script>
    var url = 'update_statistics.php';

    $(document).ready(function() {
        $('td.answer').on('click', function(e) {
            $(this).parent('tr').children('td').off('click');

            var correct = $(this).data('correct');
            var questionid = $(this).parent('tr').data('id');

            $(this).parent('tr').children('td.answer').each(function() {
                if ($(this).data('correct')) {
                    $(this).css('background-color', '#9f9');
                    $(this).css('font-weight', 'bold');
                } else {
                    $(this).css('background-color', '#f99');
                }
            });

            if (correct) {
                $(this).css('background-color', '#5f5');
            } else {
                $(this).css('background-color', '#f55');
                $(this).css('font-style', 'italic');
            }

            var object = this;
            $.ajax({
                url:        url,
                dataType:   'json',
                data:       { id: questionid, correct: correct },
                success:    function(data) {
                                $(object).parent('tr').fadeOut(5000, function() { $(this).remove(); });
                            }
            });

            
        });
    });
    </script>
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
        </thead>
        <tbody><? print_table_for_subject('Sprechfunk für Segelflieger') ?></tbody>
    </table>
</body>
</html>
