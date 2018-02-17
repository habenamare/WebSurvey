<?php
   // if JSON is sent through ajax
   //    process JSON
   //    send response
   //    and then exit
   require_once(__DIR__ . '/includes/mariadb.inc.php');
   if (isset($_POST['surveyJSON'])) {
      $survey = json_decode($_POST['surveyJSON']);

      $name = $survey->name;
      $date_created = $survey->dateCreated;
      $expire_date = $survey->expireDate;
      $questions = $survey->questions;
      $respondentsId = $survey->respondentsId;
      
      $success = Database::add_survey($name, $date_created, $expire_date,
                              $questions, $respondentsId);
      
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
   const TITLE = 'WebSurvey | Create New Survey';
   const ADMIN_PAGE = true;

   const DB_ACCESS = true;
   const ACTIVE_NAV_LINK = 'New Survey';
   const NAV_LINKS = [
      [
         'name'     => 'New Survey',
         'location' => '#'
      ],
      [
         'name'     => 'Back To Surveys',
         'location' => 'surveys.php'
      ]
      
   ];
   const STYLESHEETS = [
      'css/new-survey.css',
      'libs/jquery-ui-1.12.1/jquery-ui.css'
   ];

   // constant for 'footer.php'
   const SCRIPTS = [
      'libs/jquery-3.2.1/jquery.min.js',
      'libs/jquery-ui-1.12.1/jquery-ui.js',
      'js/new-survey.js'
   ];
   
   require('includes/header.php');
?>

<!-- div for notification message -->
<div style="display: none;" id="notificationMessage" title="Warning">
</div>

<h1>Create New Survey</h1>

<div id="loadingMessage">Creating Survey, Please wait...</div>
<div id="creationMessage"></div>

<div id="create-survey-button">
   <button id="createSurveyButton">Create Survey</button>
</div>

<div id="survey-details">
   <label for="surveyName">Survey Name</label>
   <input type="text" id="surveyName">
   &nbsp;&nbsp;&nbsp;&nbsp;
   <label for="expireDate">Expire Date</label>
   <input type="date" id="expireDate" value="<?php print(date('Y-m-d')); ?>">
</div>

<section id="respondents">
   <h2>Choose Respondent for this survey</h2>
   <?php
      $all_respondents = Database::get_respondents();

      if ($all_respondents == Database::$EMPTY_RESULT_SET) {
         print('<p>No Respondents. Please go to the <a href="respondents.php">Respondents page
                                 </a> to create new Respondents.</p>');
      } else {
         foreach ($all_respondents as $respondent) {
            printf('<input id="respondent%d" type="checkbox" name="respondent">', $respondent['respondent_id']);
            printf('<label for="respondent%d">%s %s</label>', $respondent['respondent_id'],
                        $respondent['first_name'], $respondent['last_name']);
            printf('<br>');
         }
      }

   ?>
</section>

<section id="createNewQuestion">
   <h2>Create New Question</h2>
   <p><b>NOTE:</b> Questions will be numbered starting from 1 in the
   order they are created.</p>

   <textarea id="newQuestionText" cols="140" rows="3"></textarea>
   
   <br>

   <input type="radio" id="singleSelect" name="questionType" value="s" checked="checked">
   <label for="singleSelect">Single Select</label>
   
   <input type="radio" id="multipleSelect" name="questionType" value="m">   
   <label for="multipleSelect">Multiple Select</label>

   <br>

   <button id="addQuestionButton">Add Question</button>
</section>

<section id="questions">
   <h2>Questions</h2>
</section>

<?php
   require('includes/footer.php');
?>