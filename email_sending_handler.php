<?php
   // require utility class with reusable modules
   require_once(__DIR__ . '/includes/utils.inc.php');

   // require for database access
   require_once(__DIR__ . '/includes/mariadb.inc.php');

   // if the appropriate data has not been sent using POST
   // stop executing script
   if (!isset($_POST['respondentIdsToSendEmail']) ||
         !isset($_POST['surveyId'])) {
      Utils::error_occured('Application Error.');
   }

   // get data sent from the frontend
   $respondents_ids_to_send_email = json_decode($_POST['respondentIdsToSendEmail']);
   
   $survey_id = json_decode($_POST['surveyId']);
   // get an array that represents the Survey with the given $survey_id
   // from the database
   $survey = Database::get_survey($survey_id);

   // data to be sent to the frontend
   $data_for_frontend = [];


   // get Respondents that are associated with the Survey of the given
   // $survey_id
   $survey_respondents = Database::get_respondents_for_survey($survey_id);

   // for each Respondent that is associated with the Survey of the given $survey_id
   foreach ($survey_respondents as $respondent) {
      $status_string = 'Fail';
      $current_respondent_id = $respondent['respondent_id'];
      
      // if the Respondent's id exists in the $respondents_ids_to_send_email list
      if (in_array($current_respondent_id, $respondents_ids_to_send_email)) {
         // get an array that represents the current Respondent from the database
         $respondent_from_database = Database::get_respondent($respondent['respondent_id']);
         
         // SEND email to Respondent
         $success = Utils::send_email($respondent_from_database, $survey,
                        $respondent['submission_code']);

         // if the email was sent successfully                        
         if ($success) {
            $status_string = 'Success';
            // increment 'no_of_emails_sent' column in the SurveyRespondent table
            Database::increment_no_of_emails_sent($survey_id, $current_respondent_id);
         }

         // create an array that contains the email sending status of the
         // current Respondent and the updated 'no_of_emails_sent' column value
         // from the database
         $updated_no_of_emails_sent = Database::get_no_of_emails_sent($survey_id,
                                                      $current_respondent_id);
         $array = [ $status_string, $updated_no_of_emails_sent ];

         // create an associative array that contains the current Respondent's
         // id as key and the above $array as value
         $current_respondent_array = [ $current_respondent_id => $array ];

         // add the above associative array to the array that will be sent to the
         // front end
         $data_for_frontend += $current_respondent_array;
      }

   }

   // send the array for the frontend as JSON
   print(json_encode($data_for_frontend));