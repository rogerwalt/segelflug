<?
/*
// reset answer statistics
UPDATE answer_statistics SET correct = 0, wrong = 0;
*/
include('db.inc.php');

function print_total_questions($dbh) {
    $sql = 'SELECT * FROM questions_merged ORDER BY subject asc, id asc';
    echo $dbh->query($sql)->rowCount();
}

function print_numberof_questions_for_subject($dbh, $subject) {
    $sql = 'SELECT * FROM questions_merged WHERE subject = '.$dbh->quote($subject).' ORDER BY id asc';
    echo $dbh->query($sql)->rowCount();
}

function print_table_for_subject($dbh, $subject) {
    //$sql = 'SELECT * FROM questions_merged WHERE subject = '.$dbh->quote($subject).' ORDER BY id asc';
    /*
    -- g1
SELECT id_question, 1-correct/(correct+wrong) AS g1 FROM answer_statistics

-- g2
	-- questions in subject
	SELECT count(*) AS QiS FROM questions_merged WHERE subject = 'Luftrecht'
	-- or
	SELECT count(*) AS QiS FROM answer_statistics WHERE id_question IN (SELECT id FROM questions_merged WHERE subject = 'Luftrecht')
	
	-- how many times was this question answered
	SELECT correct+wrong FROM answer_statistics
	-- how many times were all questions in this subject answered
	SELECT sum(correct)+sum(wrong) FROM answer_statistics WHERE id_question IN (SELECT id FROM questions_merged WHERE subject = 'Luftrecht')

	-- g2 total
	SELECT -2/3*count(*)*(correct+wrong)/(sum(correct)+sum(wrong))+4/3 FROM answer_statistics WHERE id_question IN (SELECT id FROM questions_merged WHERE subject = 'Luftrecht')
    */
    $sql = 
'SELECT
    *,
    (weight_answered_less_often+weight_answered_wrong+weight_answered_correctly)*RAND() AS order_column
    FROM (SELECT
        id_question,
        0.5 AS weight_answered_less_often,
        0 AS weight_answered_wrong,
        0 AS weight_answered_correctly
        FROM answer_statistics
        WHERE 0.6*(correct+wrong < (SELECT (sum(correct)+sum(wrong))/count(*) FROM answer_statistics))
    UNION
        SELECT id_question,
        0 AS weight_answered_less_often,
        0.3 AS weight_answered_wrong,
        0 AS weight_answered_correctly
        FROM answer_statistics
        WHERE wrong>correct
    UNION
        SELECT id_question,
        0 AS weight_answered_less_often,
        0 AS weight_answered_wrong,
        0.2 AS weight_answered_correctly
        FROM answer_statistics
        WHERE correct>wrong
    ) AS weights
    JOIN questions_merged ON weights.id_question = questions_merged.id
    WHERE subject = '.$dbh->quote($subject).'
    ORDER BY order_column DESC
    LIMIT 10';
    $str = '';
    try{
        foreach($dbh->query($sql) as $row) {
            $str .= '<tr data-id="'.$row['id'].'"><td>'.$row['question'].'</td><td class="answer" data-correct="'.$row['answer1_correct'].'">'.$row['answer1'].'</td><td class="answer" data-correct="'.$row['answer2_correct'].'">'.$row['answer2'].'</td><td class="answer" data-correct="'.$row['answer3_correct'].'">'.$row['answer3'].'</td><td class="answer" data-correct="'.$row['answer4_correct'].'">'.$row['answer4'].'</td></tr>'."\n";
        }
    } catch (PDOException $ex) {
        echo "A PDO error occured: ".$ex->getMessage();
        die();
    } catch (Exception $ex) {
        echo "An error occured: ".$ex->getMessage();
        die();
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
    Total <? print_total_questions($dbh) ?> Fragen.

    <h2>10 Luftrecht</h2>
    <? print_numberof_questions_for_subject($dbh, 'Luftrecht') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Luftrecht') ?></tbody>
    </table>

    <h2>20 Allgemeine Luftfahrzeugkenntnis</h2>
    <? print_numberof_questions_for_subject($dbh, 'Allgemeine Luftfahrzeugkenntnis') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Allgemeine Luftfahrzeugkenntnis') ?></tbody>
    </table>

    <h2>30 Flugleistungen und Flugplanung</h2>
    <? print_numberof_questions_for_subject($dbh, 'Flugleistungen und Flugplanung') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Flugleistungen und Flugplanung') ?></tbody>
    </table>

    <h2>40 Menschliches Leistungsvermögen</h2>
    <? print_numberof_questions_for_subject($dbh, 'Menschliches Leistungsvermögen') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Menschliches Leistungsvermögen') ?></tbody>
    </table>

    <h2>50 Meteorologie</h2>
    <? print_numberof_questions_for_subject($dbh, 'Meteorologie') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Meteorologie') ?></tbody>
    </table>

    <h2>60 Navigation</h2>
    <? print_numberof_questions_for_subject($dbh, 'Navigation') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Navigation') ?></tbody>
    </table>

    <h2>70 Betriebsverfahren</h2>
    <? print_numberof_questions_for_subject($dbh, 'Betriebsverfahren') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Betriebsverfahren') ?></tbody>
    </table>

    <h2>80 Grundlagen des Fluges</h2>
    <? print_numberof_questions_for_subject($dbh, 'Grundlagen des Fluges') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Grundlagen des Fluges') ?></tbody>
    </table>

    <h2>90 Sprechfunk für Segelflieger</h2>
    <? print_numberof_questions_for_subject($dbh, 'Sprechfunk für Segelflieger') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
        </thead>
        <tbody><? print_table_for_subject($dbh,     'Sprechfunk für Segelflieger') ?></tbody>
    </table>
</body>
</html>
