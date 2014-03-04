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
    Original weighting query:
SET @subject = 'Luftrecht';

SET @num_questions_in_subject = (SELECT count(*) FROM questions_merged WHERE subject = @subject);

SET @num_questions_answered_in_subject = (SELECT sum(correct)+sum(wrong) FROM answer_statistics WHERE id_question IN (SELECT id FROM questions_merged WHERE subject = 'Luftrecht'));

SELECT id_question, g_tilde*rand() AS weight FROM (
  SELECT id_question, g1, g2, IFNULL(g1/g2,0), g1+g2+IFNULL(g1/g2,0) AS g_tilde FROM (
    SELECT id_question, g1, GREATEST(0.1, LEAST(g2_tilde, 1)) AS g2
    FROM (
      SELECT id_question, IFNULL(-correct/(correct+wrong)+1, 0) AS g1, -2/3*@num_questions_in_subject*(correct+wrong)/@num_questions_answered_in_subject+4/3 AS g2_tilde
      FROM answer_statistics
      WHERE id_question IN (
        SELECT id FROM questions_merged WHERE subject = @subject)
    ) AS g2_tilde_table) AS weight
  ) AS g_tilde_table
ORDER BY weight DESC
    */
    $sql = 
'SELECT id_question, g_tilde*rand() AS weight FROM (
  SELECT id_question, g1, g2, IFNULL(g1/g2,0), g1+g2+IFNULL(g1/g2,0) AS g_tilde FROM (
    SELECT id_question, g1, GREATEST(0.1, LEAST(g2_tilde, 1)) AS g2
    FROM (
      SELECT id_question, IFNULL(-correct/(correct+wrong)+1, 0) AS g1, -2/3*(SELECT count(*) FROM questions_merged WHERE subject = 'Luftrecht')*(correct+wrong)/(SELECT sum(correct)+sum(wrong) FROM answer_statistics WHERE id_question IN (SELECT id FROM questions_merged WHERE subject = 'Luftrecht'))+4/3 AS g2_tilde
      FROM answer_statistics
      WHERE id_question IN (
        SELECT id FROM questions_merged WHERE subject = 'Luftrecht')
    ) AS g2_tilde_table) AS weight
  ) AS g_tilde_table
ORDER BY weight DESC';
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
