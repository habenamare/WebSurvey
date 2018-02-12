<?php
   // constants for 'header.php'
   const TITLE = 'WebSurvey | Surveys';
   const ADMIN_PAGE = true;

   const DB_ACCESS = true;
   const ACTIVE_NAV_LINK = 'Surveys';
   const NAV_LINKS = [
      [
         'name'     => 'Surveys',
         'location' => '#'
      ],
      [
         'name'     => 'Respondents',
         'location' => 'respondents.php'
      ],
      [
         'name'     => 'Log Out',
         'location' => 'logout.php'
      ]
      
   ];

   const STYLESHEETS = [
      'libs/jquery-ui-1.12.1/jquery-ui.css',      
      'css/surveys.css'
   ];

   const SCRIPTS = [
      'libs/jquery-3.2.1/jquery.min.js',
      'libs/jquery-ui-1.12.1/jquery-ui.js',
      'js/surveys.js'
   ];
   
   require('includes/header.php');
?>

<!-- div for confirmation message -->
<div style="display: none;" id="deletion-confirmation" title="DELETING Survey">
  <p>The Survey and everything associated with the Survey will be
     permanently deleted and cannot be recovered. Are you sure?</p>
</div>

<h1>Surveys</h1>

<?php // display message if redirected from delete_survey.php
   if (isset($_POST['deletion_status']) &&
      isset($_POST['deletion_message'])) {
      if ($_POST['deletion_status'] == "success") {
         printf('<p class="success-message">%s</p>', $_POST['deletion_message']);
      } else if ($_POST['deletion_status'] == "fail") {
         printf('<p class="error-message">%s</p>', $_POST['deletion_message']);
      }
   }
?>

<div id="create-new-survey">
   <a href="new_survey.php" title="create new survey" id="">create new</a>
</div>

<?php
   // get Surveys from the database
   $surveys = Database::get_surveys();

   // if Surveys database retrieval successful
   if ($surveys != NULL) {

      // if there is no Survey in the database
      if ($surveys == Database::$EMPTY_RESULT_SET) {

         print('<p id="no-surveys">');
         print('<strong>No Surveys.</strong> Surveys you created appear here.');
         print('</p>');

      } else { // Surveys retrieved from the database

         foreach ($surveys as $survey) {
            printf('<section id="survey%d" class="survey">', $survey['survey_id']);
            printf('<h2>%s</h2>', $survey['name']);

            print('<div class="survey-operations">');
            printf('<a href="view_responses.php?id=%d" title="view responses">View Responses</a>',
                     $survey['survey_id']);
            print('&nbsp;&nbsp;');
            printf('<a href="delete_survey.php?id=%d" title="delete this survey"
                    class="delete-survey-button">Delete Survey</a>', $survey['survey_id']);
            print('</div>');

            print('</section>');
         }

      }

   } else { // Surveys database retrieval failed
      // something went wrong when getting Surveys from the database
      Utils::error_occured('Something went wrong. Please try again later.');
   }


?>

<?php
   require('includes/footer.php');
?>