<?php
   // constants for 'header.php'
   const TITLE = 'WebSurvey | Send Emails';
   const ADMIN_PAGE = true;

   const DB_ACCESS = true;
   const ACTIVE_NAV_LINK = 'Send Emails';
   const NAV_LINKS = [
      [
         'name'     => 'Send Emails',
         'location' => '#'
      ],
      [
         'name'     => 'Back To Surveys',
         'location' => 'surveys.php'
      ]
      
   ];

   const STYLESHEETS = [
      'css/send-emails.css'
   ];

   const SCRIPTS = [
      'libs/jquery-3.2.1/jquery.min.js',
      'js/send-emails.js'
   ];
   
   require('includes/header.php');

?>

<?php // if id is not set throught GET
   if (!isset($_GET['id'])) {
      print('<p>Application Error.</p>');
      require('includes/footer.php');
      exit();
   }
?>

<div id="waitingMessage">Sending Emails. Please wait...</div>
<div id="finishedProcessingMessage"></div>

<?php // display h1 with Survey name, and inject the Survey's id into the h1 tag's id
   
   $survey = Database::get_survey($_GET['id']); 
   
   // if a Survey with $_GET['id'] as 'survey_id'
   // could not be fetched from the database
   if (!$survey) {
      print('<p>Application Error. Please try again.</p>');
      require('includes/footer.php');
      exit();
   }
   
   printf('<h1 id="survey%d">Send Emails for <em>%s</em></h1>',
               $survey['survey_id'], $survey['name']);

?>

<section id="respondents">

<?php // display Respondents that belong in the Survey with survey_id
      // $_GET['id'] along with the number of emails sent to each Respondent

   
   // $survey_respondents can not be EMPTY because a Survey has at least
   // one Respondent
   $survey_respondents = Database::get_respondents_for_survey($_GET['id']);

   // display the '#sendEmailsButton' button that will instruct the backend
   // to send email
   print('<button id="sendEmailsButton">Send Emails for Selected Respondents</button>');

   // beginning HTML table markup
   print('<table>');
   print('<caption>Select Respondents to send email to</caption>');
   print('<thead>');
      print('<tr>');
         print('<th>');
            print('<input type="checkbox" id="allRespondentsCheckbox">');
            print('<label id="select-all-label" for="allRespondentsCheckbox">Select All</label>');
         print('</th>');
         print('<th>First Name</th>');
         print('<th>Last Name</th>');
         print('<th>No Of Emails Sent</th>');
         print('<th>Sending Status</th>');
      print('</tr>');
   print('</thead>');
   print('<tbody>');

   foreach ($survey_respondents as $respondent) {

      printf('<tr id="respondentRow%d">', $respondent['respondent_id']);
         print('<td>');
            printf('<input type="checkbox" id="checkboxFor%d" class="respondentCheckbox">',
                              $respondent['respondent_id']);
         print('</td>');
         printf('<td>%s</td>', $respondent['first_name']);
         printf('<td>%s</td>', $respondent['last_name']);
         printf('<td id="noOfEmailsTDFor%d">%d</td>', $respondent['respondent_id'],
                                          $respondent['no_of_emails_sent']);
         printf('<td id="statusTDFor%d" class="status-TD"></td>',
                        $respondent['respondent_id']);
      print('</tr>');

   }

   // ending HTML table markup
   print('</tbody>');
   print('</table>');


   // display the Respondents with a 'Send Email' link for each Respondnet
    
?>

</section>


<?php
   require('includes/footer.php');
?>