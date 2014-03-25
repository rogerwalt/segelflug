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
    echo '50 / '.$dbh->query($sql)->rowCount();
}

function print_table_for_subject($dbh, $subject) {
    //$sql = 'SELECT * FROM questions_merged WHERE subject = '.$dbh->quote($subject).' ORDER BY id asc';
    /*
    Original weighting query:
CREATE DEFINER=`meindatenbankben`@`%` FUNCTION `gauss`(mean float, stdev float) RETURNS float
BEGIN
set @x=rand(), @y=rand();
set @gaus = ((sqrt(-2*log(@x))*cos(2*pi()*@y))*stdev)+mean;
return @gaus;
END$$

DELIMITER ;

SET @subject = 'Sprechfunk für Segelflieger';

SET @num_questions_in_subject = (SELECT count(*) FROM questions_merged WHERE subject = @subject);

SET @num_questions_answered_in_subject = (SELECT sum(correct)+sum(wrong) FROM answer_statistics WHERE id_question IN (SELECT id FROM questions_merged WHERE subject = 'Luftrecht'));

SELECT ROUND(@num_questions_answered_in_subject/@num_questions_in_subject, 2) AS average_times_questions_answered, correct+wrong AS times_answered, ROUND(wrong/(correct+wrong)*100) AS percentage_wrong, correct, wrong, wrong/(correct+wrong), correct+wrong, g1, g2_tilde, g2, g_tilde, random_number, weight, question FROM (
  SELECT *, g_tilde*random_number AS weight FROM (
    SELECT *, g1+3*g2+IFNULL(g1/g2,0) AS g_tilde, (SELECT `gauss` (1, 0.1) AS `gauss`) as random_number FROM (
      SELECT *, GREATEST(0.1, LEAST(g2_tilde, 1)) AS g2
      FROM (
        SELECT *, IFNULL(-correct/(correct+wrong)+1, 0) AS g1, -2/3*@num_questions_in_subject*(correct+wrong)/@num_questions_answered_in_subject+4/3 AS g2_tilde
        FROM answer_statistics
        WHERE id_question IN (
          SELECT id FROM questions_merged WHERE subject = @subject)
      ) AS g2_tilde_table) AS weight
    ) AS g_tilde_table
  JOIN questions_merged ON g_tilde_table.id_question = questions_merged.id
) AS other_table
ORDER BY weight DESC
    */
    $sql = 
'SELECT id_question, correct+wrong AS times_answered, ROUND(wrong/(correct+wrong)*100) AS percentage_wrong, g_tilde*(SELECT `gauss` (1, 0.1) AS `gauss`) AS weight, questions_merged.* FROM (
  SELECT *, IFNULL(g1/g2,0), g1+3*g2+IFNULL(g1/g2,0) AS g_tilde FROM (
    SELECT *, GREATEST(0.1, LEAST(g2_tilde, 1)) AS g2
    FROM (
      SELECT *, IFNULL(-correct/(correct+wrong)+1, 0) AS g1, -2/3*(SELECT count(*) FROM questions_merged WHERE subject = '.$dbh->quote($subject).')*(correct+wrong)/(SELECT sum(correct)+sum(wrong) FROM answer_statistics WHERE id_question IN (SELECT id FROM questions_merged WHERE subject = '.$dbh->quote($subject).'))+4/3 AS g2_tilde
      FROM answer_statistics
      WHERE id_question IN (
        SELECT id FROM questions_merged WHERE subject = '.$dbh->quote($subject).')
    ) AS g2_tilde_table) AS weight
  ) AS g_tilde_table
JOIN questions_merged ON g_tilde_table.id_question = questions_merged.id
ORDER BY weight DESC';
    $str = '';
    try{
        foreach($dbh->query($sql) as $row) {
            // shuffle answers
            $answers = array();
            $ans = array();
            $ans['answer'] = $row['answer1'];
            $ans['correct'] = $row['answer1_correct'];
            $answers[] = $ans;
            $ans['answer'] = $row['answer2'];
            $ans['correct'] = $row['answer2_correct'];
            $answers[] = $ans;
            $ans['answer'] = $row['answer3'];
            $ans['correct'] = $row['answer3_correct'];
            $answers[] = $ans;
            $ans['answer'] = $row['answer4'];
            $ans['correct'] = $row['answer4_correct'];
            $answers[] = $ans;
            shuffle($answers);

            $str .= '<tr data-id="'.$row['id'].'"><td>'.$row['question'].'</td><td class="answer" data-correct="'.$answers[0]['correct'].'">'.$answers[0]['answer'].'</td><td class="answer" data-correct="'.$answers[1]['correct'].'">'.$answers[1]['answer'].'</td><td class="answer" data-correct="'.$answers[2]['correct'].'">'.$answers[2]['answer'].'</td><td class="answer" data-correct="'.$answers[3]['correct'].'">'.$answers[3]['answer'].'</td></tr>'."\n";
            if ($row['percentage_wrong'] >= 50) {
                $str .= '<tr style="background-color: #fcc;"><td colspan="5" style="font-weight: bold;">'.$row['times_answered'].' mal beantwortet, '.$row['percentage_wrong'].'% falsch</td></tr>'."\n";
            } elseif ($row['percentage_wrong'] <= 20 && $row['times_answered'] >= 10) {
                $str .= '<tr style="background-color: #cf9;"><td colspan="5">'.$row['times_answered'].' mal beantwortet, '.$row['percentage_wrong'].'% falsch</td></tr>'."\n";
            } elseif ($row['times_answered'] > 0) {
                $str .= '<tr style="background-color: #cff;"><td colspan="5">'.$row['times_answered'].' mal beantwortet, '.$row['percentage_wrong'].'% falsch</td></tr>'."\n";
            } else {
                $str .= '<tr style="background-color: #fcc;"><td colspan="5" style="font-weight: bold;">'.$row['times_answered'].' mal beantwortet</td></tr>'."\n";
            }
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

function print_subject_table($dbh) {
$sql = 'SELECT
  subject,
  ROUND(AVG(wrong/(correct+wrong))*100) AS average_percentage_wrong,
  ROUND(SUM(correct+wrong)/COUNT(id),2) AS average_times_question_answered,
  COUNT(id) AS number_of_questions
FROM answer_statistics
JOIN questions_merged ON id_question = id
GROUP BY subject';

$statement = $dbh->prepare($sql);
$statement->execute();
$stats = $statement->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

$str = '<table><thead><tr><th>Fach</th><th># eine Frage durchschn. beantwortet</th><th>% eine Frage durchschn. falsch beantwortet</th><th>Fragen total</th></tr></thead><tbody>';
$str .= '<tr><td><a href="#Luftrecht">10 Luftrecht</a></td><td>'.$stats['Luftrecht'][0]['average_times_question_answered'].'</td><td>'.$stats['Luftrecht'][0]['average_percentage_wrong'].'%</td><td>'.$stats['Luftrecht'][0]['number_of_questions'].'</td></tr>'."\n";
$str .= '<tr><td><a href="#Luftfahrzeugkenntnis">20 Allgemeine Luftfahrzeugkenntnis</a></td><td>'.$stats['Allgemeine Luftfahrzeugkenntnis'][0]['average_times_question_answered'].'</td><td>'.$stats['Allgemeine Luftfahrzeugkenntnis'][0]['average_percentage_wrong'].'%</td><td>'.$stats['Allgemeine Luftfahrzeugkenntnis'][0]['number_of_questions'].'</td></tr>'."\n";
$str .= '<tr><td><a href="#Flugleistungen">30 Flugleistungen und Flugplanung</a></td><td>'.$stats['Flugleistungen und Flugplanung'][0]['average_times_question_answered'].'</td><td>'.$stats['Flugleistungen und Flugplanung'][0]['average_percentage_wrong'].'%</td><td>'.$stats['Flugleistungen und Flugplanung'][0]['number_of_questions'].'</td></tr>'."\n";
$str .= '<tr><td><a href="#Menschliches">40 Menschliches Leistungsvermögen</a></td><td>'.$stats['Menschliches Leistungsvermögen'][0]['average_times_question_answered'].'</td><td>'.$stats['Menschliches Leistungsvermögen'][0]['average_percentage_wrong'].'%</td><td>'.$stats['Menschliches Leistungsvermögen'][0]['number_of_questions'].'</td></tr>'."\n";
$str .= '<tr><td><a href="#Meteorologie">50 Meteorologie</a></td><td>'.$stats['Meteorologie'][0]['average_times_question_answered'].'</td><td>'.$stats['Meteorologie'][0]['average_percentage_wrong'].'%</td><td>'.$stats['Meteorologie'][0]['number_of_questions'].'</td></tr>'."\n";
$str .= '<tr><td><a href="#Navigation">60 Navigation</a></td><td>'.$stats['Navigation'][0]['average_times_question_answered'].'</td><td>'.$stats['Navigation'][0]['average_percentage_wrong'].'%</td><td>'.$stats['Navigation'][0]['number_of_questions'].'</td></tr>'."\n";
$str .= '<tr><td><a href="#Betriebsverfahren">70 Betriebsverfahren</a></td><td>'.$stats['Betriebsverfahren'][0]['average_times_question_answered'].'</td><td>'.$stats['Betriebsverfahren'][0]['average_percentage_wrong'].'%</td><td>'.$stats['Betriebsverfahren'][0]['number_of_questions'].'</td></tr>'."\n";
$str .= '<tr><td><a href="#Grundlagen">80 Grundlagen des Fluges</a></td><td>'.$stats['Grundlagen des Fluges'][0]['average_times_question_answered'].'</td><td>'.$stats['Grundlagen des Fluges'][0]['average_percentage_wrong'].'%</td><td>'.$stats['Grundlagen des Fluges'][0]['number_of_questions'].'</td></tr>'."\n";
$str .= '<tr><td><a href="#Sprechfunk">90 Sprechfunk für Segelflieger</a></td><td>'.$stats['Sprechfunk für Segelflieger'][0]['average_times_question_answered'].'</td><td>'.$stats['Sprechfunk für Segelflieger'][0]['average_percentage_wrong'].'%</td><td>'.$stats['Sprechfunk für Segelflieger'][0]['number_of_questions'].'</td></tr>'."\n";
$str .= '</tbody></table>';

echo $str;
}
?>
<!DOCTYPE html>
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
                                //$(object).parent('tr').fadeOut(5000, function() { $(this).remove(); });
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
    <? print_subject_table($dbh) ?>

    <h2 id="Luftrecht">10 Luftrecht</h2>
    <? print_numberof_questions_for_subject($dbh, 'Luftrecht') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead><tr>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
            </tr>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Luftrecht') ?></tbody>
    </table>

    <h2 id="Luftfahrzeugkenntnis">20 Allgemeine Luftfahrzeugkenntnis</h2>
    <? print_numberof_questions_for_subject($dbh, 'Allgemeine Luftfahrzeugkenntnis') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead><tr>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
            </tr>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Allgemeine Luftfahrzeugkenntnis') ?></tbody>
    </table>

    <h2 id="Flugleistungen">30 Flugleistungen und Flugplanung</h2>
    <? print_numberof_questions_for_subject($dbh, 'Flugleistungen und Flugplanung') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead><tr>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
            </tr>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Flugleistungen und Flugplanung') ?></tbody>
    </table>

    <h2 id="Menschliches">40 Menschliches Leistungsvermögen</h2>
    <? print_numberof_questions_for_subject($dbh, 'Menschliches Leistungsvermögen') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead><tr>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
            </tr>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Menschliches Leistungsvermögen') ?></tbody>
    </table>

    <h2 id="Meteorologie">50 Meteorologie</h2>
    <? print_numberof_questions_for_subject($dbh, 'Meteorologie') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead><tr>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
            </tr>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Meteorologie') ?></tbody>
    </table>

    <h2 id="Navigation">60 Navigation</h2>
    <? print_numberof_questions_for_subject($dbh, 'Navigation') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead><tr>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
            </tr>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Navigation') ?></tbody>
    </table>

    <h2 id="Betriebsverfahren">70 Betriebsverfahren</h2>
    <? print_numberof_questions_for_subject($dbh, 'Betriebsverfahren') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead><tr>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
            </tr>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Betriebsverfahren') ?></tbody>
    </table>

    <h2 id="Grundlagen">80 Grundlagen des Fluges</h2>
    <? print_numberof_questions_for_subject($dbh, 'Grundlagen des Fluges') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead><tr>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
            </tr>
        </thead>
        <tbody><? print_table_for_subject($dbh, 'Grundlagen des Fluges') ?></tbody>
    </table>

    <h2 id="Sprechfunk">90 Sprechfunk für Segelflieger</h2>
    <? print_numberof_questions_for_subject($dbh, 'Sprechfunk für Segelflieger') ?> Fragen.
    <table style="border: solid black 1px;">
        <thead><tr>
            <th>Frage</th>
            <th>Antwort 1</th>
            <th>Antwort 2</th>
            <th>Antwort 3</th>
            <th>Antwort 4</th>
            </tr>
        </thead>
        <tbody><? print_table_for_subject($dbh,     'Sprechfunk für Segelflieger') ?></tbody>
    </table>
</body>
</html>

