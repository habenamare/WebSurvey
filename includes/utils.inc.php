<?php
   /*
      This file includes a utility class that contians modules
      that can be reused by any part of the application.
   */

   require_once(__DIR__ . '/mariadb.inc.php');

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

      /* a function to send e-mails to respondents
         @param $respondent_ids_with_codes, an array containing respondents id
                as KEY and unique submission code as VALUE.
         @param $survey_name, name of the Survey Respondents will be participating
         Returns true if e-mails were sent successfully.
      */
      public static function send_emails($respondent_ids_with_codes, $survey_name) {
         $respondent_ids = array_keys($respondent_ids_with_codes);
         $email_respondents = [];
         
         // obtain Respondents who will be receiving e-mails
         $all_respondents = Database::get_respondents();
         foreach ($all_respondents as $r) {
            if ( in_array( $r['respondent_id'], $respondent_ids ) ) {
               array_push($email_respondents, $r);
            }
         }

         // send mail for each Respondent's e-mail
         foreach ($email_respondents as $r) {
            $r_id = $r['respondent_id'];
            $new_mail = 'Dear ' . $r['first_name'] . ' ' . $r['last_name'] .
                        ', Please participate in ' .
                        $survey_name . ' survey using the following code. ' .
                        $respondent_ids_with_codes[$r_id];

            $accepted_for_delivery = mail($r['email'], 'Participate in Survey', $new_mail);
            // If sending e-mail to one of the Respondents fails, then return false.
            if (!$accepted_for_delivery) {
               return false;
            }
         }

         return true;
      }

   }