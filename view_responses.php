<?php
   // constants for 'header.php'
   const TITLE = 'WebSurvey | Survey Responses';
   const ADMIN_PAGE = true;

   const DB_ACCESS = true;
   const ACTIVE_NAV_LINK = 'Survey Responses';
   const NAV_LINKS = [
      [
         'name'     => 'Survey Responses',
         'location' => '#'
      ],
      [
         'name'     => 'Back To Surveys',
         'location' => 'surveys.php'
      ]
      
   ];

   const STYLESHEETS = [
      'css/view-responses.css'
   ];

   const SCRIPTS = [
      'libs/chartjs-2.7.1/Chart.bundle.min.js',
      'libs/jquery-3.2.1/jquery.min.js',
      'js/view-responses.js'
   ];
   
   require('includes/header.php');

?>

<?php // if id is not set throught GET
   if (!isset($_GET['id'])) {
      print('<p>Survey Results could not be displayed</p>');
      require('includes/footer.php');
      exit();
   }
?>

<?php // display Survey name
   
   $survey = Database::get_survey($_GET['id']); 
   
   // if a Survey with $_GET['id'] as 'survey_id'
   // could not be fetched from the database
   if (!$survey) {
      print('<p>Survey Results could not be displayed. Please try again.</p>');
      require('includes/footer.php');
      exit();
   }
   
   printf('<h1>%s Responses</h1>', $survey['name']);

?>

<section id="questions">

<?php // display Survey Questions along with their Choices and responses
      // on this Choices

   $questions = Database::get_questions($_GET['id']);
   // display error message if something goes wrong when
   // retrieving Questions
   if (!$questions) {
      Utils::error_occured('Something went wrong');
   }

   // foreach Question display the question itself and
   // create table containing Choices and how many times
   // they were chosen
   foreach ($questions as $question) {
      // get total number of times chosen for this Question
      $total_chosen = Database::get_total_numberOfTimesChosen($question['question_id']);
      
      // display error message if something goes wrong when
      // retrieving total number of times chosen
      // NOTE: check if $total_chosen is equal to 0 because 0 is
      //       treated as a NULL value in PHP
      if (!$total_chosen && $total_chosen != 0) {
         Utils::error_occured('Something went wrong.');
      }

      print('<section class="question">');

      // display <question_no>. <question text>
      printf('<h2>%d. %s</h2>', $question['question_number'],
                     $question['question']);

      // display Choices with statistical data
      print('<section class="choice-table-holder">');
      print('<table class="choice-table">');
         print('<thead>');
            print('<tr>');
               print('<th>Choices</th>');
               print('<th></th>');
               print('<th></th>');
            print('</tr>');
         print('</thead>');
         print('<tbody>');
            $choices = Database::get_choices($question['question_id']);

            // display error message if something goes wrong when
            // retrieving Choices
            if (!$choices) {
               Utils::error_occured('Something went wrong');
            }

            foreach ($choices as $choice) {
               print('<tr class="choice-row">');
                  printf('<th>%s</th>', $choice['choice']);

                  $no_of_times_chosen = $choice['no_of_times_chosen'];
                  $chosen_in_percent = 0;
                  if ($no_of_times_chosen != 0) {
                     $chosen_in_percent = ( $no_of_times_chosen / $total_chosen ) * 100;                     
                  }
                  
                  printf('<td>%d%s</td>', $chosen_in_percent, '%');
                  printf('<td>%d</td>', $no_of_times_chosen);
               print('</tr>');
            }
         print('</tbody>');
         print('<tfoot>');
            print('<tr>');
               print('<th colspan="2">Total Responses</th>');
               printf('<td><strong>%d</strong></td>', $total_chosen);
            print('</tr>');
         print('</tfoot>');
      print('</table>');
      print('</section>');


      // for each Question create a Pie Chart using 'chart.js'
      // printf('<canvas id="chartForQuestion%d" width="400" height="400">',
      //                $question['question_id']);
      print('<section class="question-chart-holder">');
         print('<canvas class="questionChart">');
         print('</canvas>');
      print('</section>');

      print('</section>');
   }
   
?>

</section>


<?php
   require('includes/footer.php');
?>