<?php
   /*
      This file includes a utility class that contians modules
      that can be reused by any part of the application.
   */

   // require 'PHPMailer' library for sending e-mails
   use PHPMailer\PHPMailer\PHPMailer;
   use PHPMailer\PHPMailer\Exception;
   
   require_once(__DIR__ . '/../libs/PHPMailer-6.0.3/src/Exception.php');
   require_once(__DIR__ . '/../libs/PHPMailer-6.0.3/src/PHPMailer.php');
   require_once(__DIR__ . '/../libs/PHPMailer-6.0.3/src/SMTP.php');

   // for DB access
   require_once(__DIR__ . '/mariadb.inc.php');

   // get constants SENDING_EMAIL_ADDRESS and SENDING_EMAIL_PASSWORD which
   // are used as username and password respectively, when sending e-mails
   require(__DIR__ . '/../../../WebSurveyNotServed/email.config.php');

   class Utils {

      // This module should be called when an error happens.
      // It takes a variable that describes the error as an argument. 
      public static function error_occured($some_error) {
         exit('FROM Utility Class ' . $some_error);
      }

      // a function to redirect to another page
      public static function redirect($url) {
         header('Location: ' . $url);
         exit();
      }

      //===================================================================
      //=== functions to create unique 9-digit random strings

      // Returns a 9-digit random string
      private static function generate_random() {
         $random_string = "";

         $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
                       'abcdefghijklmnopqrstuvwxyz' .
                       '0123456789';

         $max = strlen($characters);
    
         for ($i = 0; $i < 9; $i++) {
            $random_num = random_int(0, $max-1);
            $random_string .= $characters[$random_num];
         }
    
        return $random_string;
      }

      /* Returns an array containing n 9-digit random strings
         where n is the passed argument $no_of_randoms.
      */ 
      private static function generate_randoms($no_of_randoms) {
         $randoms = [];

         // create random strings
         for ($i = 0; $i < $no_of_randoms; $i++) {
            $random_string = self::generate_random();
            array_push($randoms, $random_string);
         }

         return $randoms;
      }

      /* Returns true if the strings in an array are unique, returns false
         otherwise.
      */
      private static function are_unique($string_array) {
         $found_not_unique = false;

         for ($i = 0; $i < count($string_array); $i++) {

            for ($j = 0; $j < count($string_array); $j++) {
               if ($i == $j) {
                  continue;
               }

               if ($string_array[$i] == $string_array[$j]) {
                  $found_not_unique = true;
                  break 2;
               }
            }

         }

         if ($found_not_unique) {
            return false;
         } else {
            return true;
         }
      }

      /* Returns an array of n unique 9-digit random strings
         where n is the passed argument $no_of_randoms.
         This function makes sure that the random strings are unique
         by using the function 'self::are_unique($string_array).
      */
      public static function create_randoms($no_of_randoms) {
         $randoms = [];
         $not_unique_randoms = true;

         while ($not_unique_randoms) {
            $randoms = self::generate_randoms($no_of_randoms);
            if (self::are_unique($randoms)) {
               $not_unique_randoms = false;
            }
         }

         return $randoms;
      }
      //===================================================================

      /* a function to send a single e-mail
         @param $respondent, array representing a single Respondent from the database
         @param $survey, array representing a single Survey from the database
         @param $submission_code, submission code needed by the Respondent to
                                  participate on the Survey
         Returns true if email was sent successfully, returns false otherwise.
      */
      public static function send_email($respondent, $survey, $submission_code) {
         // configure settings to send e-mail
         $mail = new PHPMailer(true);
         
         try {
         
            // enable SMTP debugging
            //$mail->SMTPDebug = 3;                               
            
            // set PHPMailer to use SMTP
            $mail->isSMTP();
         
            // set SMTP host name                          
            $mail->Host = 'smtp.gmail.com';
         
            // set SMTPAuth to true if SMTP host requires authentication to send email
            $mail->SMTPAuth = true;   
         
            // provide username and password     
            $mail->Username = SENDING_EMAIL_ADDRESS;                 
            $mail->Password = SENDING_EMAIL_PASSWORD;                           
            
            // if SMTP requires TLS encryption then set it
            $mail->SMTPSecure = "tls";                           
         
            // set TCP port to connect to 
            $mail->Port = 587;                                   
         
            $mail->From = SENDING_EMAIL_ADDRESS;
            $mail->FromName = "Web Survey";

            $respondent_name = $respondent['first_name'] . $respondent['last_name'];
            $mail->addAddress($respondent['email'], $respondent_name);
         
            $mail->isHTML(true);
         
            // message body in HTML
            $message_body = 
               '<div style="font-family: sans-serif; text-align: center;">' .
               '<h1 style="background: #dbdbdb;">' .
                  $survey['name'] .
               '</h1>'.
               '<p>We would really appreciate it if you would take the' .
               '   time to complete this Survey.</p>' .
               '<p>Please ' .
               '<a href="localhost/WebSurvey/take_survey.php?id=' .
                  $survey['survey_id'] .
               '" title="click here to take the survey"' .
               '  target="_blank">CLICK HERE</a> to go to the survey.</p>' .
               '<p>You need the submission code ' .
               '<strong>' . $submission_code .
               '</strong> to successfully submit your answers.</p>' .
                '</div>';
            
            // message in plain text form
            $message_plain_text = 'Currently, there is no plain text version of this email.';

            $mail->Subject = 'Participate on ' . $survey['name']  . '.';
            $mail->Body = $message_body;
            $mail->AltBody = $message_plain_text;
         
            $mail->send();

            // message sent successfully
            return true;
         } catch (Exception $ex) {
            // message could not be sent
            return false;
         }

      }

   }