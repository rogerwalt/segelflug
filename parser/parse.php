<?
/*
// download quiz questions to parse
for i in {1..10}; do wget --load-cookies cookies.txt http://segelflug.ch/\?page_id\=4143 -O $i.html; done

// db-structure
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `question` longtext CHARACTER SET latin1,
  `answer1` text CHARACTER SET latin1,
  `answer1_correct` tinyint(1) DEFAULT NULL,
  `answer2` text CHARACTER SET latin1,
  `answer2_correct` tinyint(1) DEFAULT NULL,
  `answer3` text CHARACTER SET latin1,
  `answer3_correct` tinyint(1) DEFAULT NULL,
  `answer4` text CHARACTER SET latin1,
  `answer4_correct` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `questions_merged` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `question` longtext COLLATE utf8_unicode_ci,
  `answer1` text COLLATE utf8_unicode_ci,
  `answer1_correct` tinyint(1) DEFAULT NULL,
  `answer2` text COLLATE utf8_unicode_ci,
  `answer2_correct` tinyint(1) DEFAULT NULL,
  `answer3` text COLLATE utf8_unicode_ci,
  `answer3_correct` tinyint(1) DEFAULT NULL,
  `answer4` text COLLATE utf8_unicode_ci,
  `answer4_correct` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

// merge questions (make them unique):
TRUNCATE TABLE questions_merged;
INSERT INTO questions_merged (subject, question, answer1, answer2, answer3, answer4, answer1_correct, answer2_correct, answer3_correct, answer4_correct) SELECT subject, question, answer1, answer2, answer3, answer4, answer1_correct, answer2_correct, answer3_correct, answer4_correct
FROM questions
GROUP BY question
ORDER BY subject, id;
*/

$dbh = new PDO('mysql:host=localhost;dbname=segelflug;charset=utf8', 'segelflug', 'AyurBGRjW9cz2qx3');
require_once('simple_html_dom.php');

foreach($argv as $key => $arg) {
	if ($key > 0) {
		echo 'parsing '.$arg."\n";

		$html = str_get_html(file_get_contents($arg));

		// 9 subjects
		$subjects = array();
		for ($i=1; $i<=9; $i++) {
			$subject['text'] = $html->find('div[id=mtq_quiz_results-'.$i.'] em',0)->plaintext;

			// 10 questions per subject
			$questions = array();
			for ($j=1; $j<=10; $j++) {
				echo "\n".$i.' '.$j;

				$question_dom = $html->find('div[id=mtq_question-'.$j.'-'.$i.']',0);
				$question['text'] = $question_dom->find('div.mtq_question_text',0)->plaintext;

				// check if there is an image in the question
				if ($image = $html->find('div[id=mtq_question_text-'.$j.'-'.$i.'] img',0)) {
					echo ' found image ('.$image->src.'), downloading and encoding as base64...';
					$data = file_get_contents($image->src);
					$type = explode('.',$image->src)[2];
					$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
					$question['text'] = $question['text'].'<br><img style="max-width: 300px;" alt="embd img" src="'.$base64.'">';
				}

				// 4 answers per question
				$answers = array();
				foreach ($question_dom->find('table.mtq_answer_table tr') as $tr) {
					$answer['correct'] = $tr->find('div.mtq_correct_marker',0) != null;
					$answer['text'] = $tr->find('div.mtq_answer_text',0)->plaintext;
					$answers[] = $answer;
				}
				$question['answers'] = $answers;
				$questions[] = $question;

				$dbh->query('INSERT INTO questions (subject, question, answer1, answer1_correct, answer2, answer2_correct, answer3, answer3_correct, answer4, answer4_correct) VALUES
		("'.$subject['text'].'",
		'.$dbh->quote($question['text']).',
		"'.$answers[0]['text'].'", '.(int)$answers[0]['correct'].',
		"'.$answers[1]['text'].'", '.(int)$answers[1]['correct'].',
		"'.$answers[2]['text'].'", '.(int)$answers[2]['correct'].',
		"'.$answers[3]['text'].'", '.(int)$answers[3]['correct'].');');
			}
			$subject['questions'] = $question;
			$subjects[] = $subject;
		}
	}
}
