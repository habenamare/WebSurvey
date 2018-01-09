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
      'css/surveys.css'
   ];
   
   require('includes/header.php');
?>

<h1>Surveys</h1>

<a href="new_survey.php" title="create new survey">create new</a>

<section id="surveys-list">

   <?php
      // get Surveys from the database
      $surveys = Database::get_surveys();

      // if Surveys database retrieval successful
      if ($surveys != NULL) {

         // if there is no Survey in the database
         if ($surveys == Database::$EMPTY_RESULT_SET) {

            print('No Surveys. Surveys you created appear here.');

         } else { // Surveys retrieved from the database

            foreach ($surveys as $survey) {
               $hashed_survey_id = 1;
               print('<section class="survey">');
               printf('<h2>%s</h2>', $survey['name']);
   
               print('<div class="survey-operations">');
               printf('<a href="view_result.php?id=%d" title="view result">View Result</a>',
                        $survey['survey_id']);
               printf('<a href="delete_survey.php?id=%d" title="delete this survey">Delete Survey</a>', $survey['survey_id']);
               print('</div>');
   
               print('</section>');
            }

         }
  
      } else { // Surveys database retrieval failed
         // something went wrong when getting Surveys from the database
         Utils::error_occured('Something went wrong. Please try again later.');
      }


   ?>
</section>

<?php
   require('includes/footer.php');
?>