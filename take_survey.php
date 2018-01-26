<?php
   // if JSON is sent through ajax
   //    process JSON
   //    send response
   //    and then exit
   require_once(__DIR__ . '/includes/mariadb.inc.php');

   if (isset($_POST['completedSurveyJSON'])) {
      $completed_survey = json_decode($_POST['completedSurveyJSON']);

      $completed_survey_id = $completed_survey->surveyId;
      $completed_with_submission_code = $completed_survey->submissionCode;
      $checked_choice_ids = $completed_survey->checkedChoices;
      
      $success = Database::add_response($completed_survey_id, $completed_with_submission_code,
                                          $checked_choice_ids);

      if ($success) {
         printf('success');
      } else {
         printf('fail');
      }
      
      exit();
   }
?>

<?php
   // constants for 'header.php'
   const TITLE = 'WebSurvey | Take Survey';
   const ADMIN_PAGE = false;

   const DB_ACCESS = true;

   const STYLESHEETS = [
      'css/take-survey.css'
   ];

   const SCRIPTS = [
      'js/jquery-3.2.1.min.js',
      'js/take-survey.js'
   ];
   
   require('includes/header.php');

?>

<?php // if id is not set throught GET
   if (!isset($_GET['id'])) {
      print('<p>Application Error. Make sure the url is correct.</p>');
      require('includes/footer.php');
      exit();
   }
?>

<?php // display Survey name & also check if the Survey has expired

   $survey = Database::get_survey($_GET['id']); 
   
   // if a Survey with $_GET['id'] as 'survey_id'
   // could not be fetched from the database
   if (!$survey) {
      print('<p>Application Error. Please try reloading page.</p>');
      require('includes/footer.php');
      exit();
   }

   //========= if Survey has expired
   
   // today in yyyy-mm-dd format
   //      eg. 2018-01-25
   $today = date('Y-m-d');
   $survey_expire_date = $survey['expire_date'];
   $converted_survey_expire_date = strtotime($survey_expire_date);

   // if Survey expire date has passed
   // NOTE: If today is the Survey's expire date then
   //       today is the last possible day to take the Survey
   //       (it is allowed to take the Survey today).
   if (time() > $converted_survey_expire_date && 
                        $today != $survey_expire_date) { 
      print('<p>This survey has expired.</p>');
      print('<p>We are no longer taking responses for this Survey.</p>');
      require('includes/footer.php');
      exit();
   }
   //================================================



   
   printf('<h1>Thank you for taking part in %s.</h1>', $survey['name']);

?>

<div id="loadingMessage">Processing your answers, Please wait...</div>
<div id="finishedProcessingMessage"></div>

<section id="questions<?php print($survey['survey_id']); ?>">

<?php // display Survey Questions along with their Choices
      // display Choices as html 'input' elements
      //    - type="radio"    if the question is Single select
      //    - type="checkbox" if the question is Multiple select

   $questions = Database::get_questions($_GET['id']);

   // display error message if something goes wrong when
   // retrieving Questions
   if (!$questions) {
      Utils::error_occured('Something went wrong');
   }

   // foreach Question display the question itself and Choices inside the Question
   foreach ($questions as $question) {

      printf('<section id="question%d" class="question" data-question-type="%s">',
                     $question['question_id'], $question['choice_type']);

      // display <question_no>. <question text>
      printf('<h2>%d. %s</h2>', $question['question_number'],
                     $question['question']);


      $choices = Database::get_choices($question['question_id']);

      // display error message if something goes wrong when
      // retrieving Choices
      if (!$choices) {
         Utils::error_occured('Something went wrong');
      }

      foreach ($choices as $choice) {

         // display Choice as input[type="radio"] for Single Select Question
         //                or input[type="checkbox"] for Multiple Select Question
         if ($question['choice_type'] == 's') {
            printf('<input type="radio" name="question%d" id="choice%d">',
                  $question['question_id'], $choice['choice_id']);
         } else if ($question['choice_type'] == 'm') {
            printf('<input type="checkbox" name="question%d" id="choice%d">',
                  $question['question_id'], $choice['choice_id']);        
         }
         printf('<label for="choice%d">%s</label>',
                           $choice['choice_id'], $choice['choice']);
         print('<br>');

      }

      print('</section>');
   }
   
?>

</section>

<label for="submissionCode">Submission Code</label>
<input type="text" id="submissionCode">
<br>
<button id="finishedButton">Finished</button>


<?php
   require('includes/footer.php');
?>