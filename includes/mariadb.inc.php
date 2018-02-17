<?php
   /* 
      This file includes modules that interact with
      the database.
   */

   // include the 'Utils' utility class
   require_once(__DIR__ . '/utils.inc.php');

   // get constants DB_HOST, DB_USER, DB_PASSWORD & DB_NAME
   require(__DIR__ . '/../../../WebSurveyNotServed/mariadb.config.php');

   // This class contains static methods that perform database related tasks.  
   class Database {
      
      // constants
      // Used to check if result set returned from the database is empty.
      public static $EMPTY_RESULT_SET = 3;

      private static $conn;

      public function __construct() {
         // data (database) source name
         $dsn = 'mysql:dbname=' . DB_NAME . ';host=127.0.0.1;charset=latin1';

         try {
            // connect to MySQL and set connection to self::$conn
            self::$conn = new PDO($dsn, DB_USER, DB_PASSWORD);
            // use a real or native prepared statement
            self::$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            // set PDO error handling strategy
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

         } catch (PDOException $ex) {
            // if connection fails
            Utils::error_occured('Connection to database failed.');   
         }
         
      }


      // --------------------------------------------------
      // Authentication
      // --------------------------------------------------

      // returns TRUE if the $username exists in the database, otherwise returns FALSE
      public static function username_exists($username) {
         // execute SQL query using $username
         $statement = self::$conn->prepare('SELECT 1 FROM Researcher WHERE username=?');
         $statement->execute([ $username ]);
         
         // if the username exists 1 will be selected , therefore 1 will be returned,
         // else false will be returned
         return $statement->fetchColumn();
      } 

      /* Returns 'hashed_password' for the given $username from the database.
         Whether the username exists or not  must be checked before using this function.
         The 'self::username_exists' function can be used for checking if the username
         exists.
      */
      public static function get_hashed_password($username) {
         $hashed_password = self::$conn->prepare('SELECT hashed_password FROM Researcher WHERE username=?');
         $hashed_password->execute([ $username ]);
         
         return $hashed_password->fetchColumn();
      }

      /* returns TRUE if the $username and $password combination is correct,
         otherwise returns FALSE
      */
      public static function authenticate($username, $password) {
         // check if the username exists
         if (self::username_exists($username)) {
            // get $username's hashed_password from the database
            $hashed_password = self::get_hashed_password($username);

            // check hashed_password from the database with $password
            if (password_verify($password, $hashed_password)) {
               return true;
            } else {
               return false;
            }
            
         } else {

            return false;
         }

      }


      // --------------------------------------------------
      // Survey
      // --------------------------------------------------

      /* Returns an array containing all Surveys.
         A Survey's attribute value can be obtained by using its attribute
         name as key on the returned array.
         Returns Database::$EMPTY_RESULT_SET if result set returned from the
         database is empty.
         Returns NULL if something goes wrong. 
      */
      public static function get_surveys() {
         try {
            $statement = self::$conn->query('SELECT * FROM Survey');
            $surveys = $statement->fetchAll();

            // if returned result set is empty
            if (count($surveys) == 0) {
               return self::$EMPTY_RESULT_SET;
            } else { // returned result set is not empty
               return $surveys;
            }

         } catch (PDOException $ex) {
            return NULL;
         }
         
      }

      /* Returns an array containing a Survey with the given $survey_id.
         Returns false or NULL if a Survey with that id doesn't exist or
         something goes wrong when fetching a Survey.
      */
      public static function get_survey($survey_id) {
         $query = 'SELECT * FROM Survey WHERE survey_id=?';
         try {
            $statement = self::$conn->prepare($query);
            // execute query using the given $survey_id
            $statement->execute([$survey_id]);
            
            $survey = $statement->fetch();
            
            return $survey;
         } catch (PDOException $ex) {
            return NULL;
         }
      }

      /* Survey is created as a transaction consisting of
            - inserting in the Survey details in the Survey table
            - inserting Survey's questions in the Question table
               - inserting each Question's choices in the Choice table
            - inserting Survey's respondents in the SurveyRespondent table
         Returns true if Survey created successfully, returns NULL
         otherwise.
      */
      public static function add_survey($name, $date_created, $expire_date,
                           $questions, $respondent_ids) {
         try {

            //=== start transaction
            self::$conn->beginTransaction();

            //=== create Survey on the database
            $survey_query = 'INSERT INTO Survey (name, date_created, expire_date)
                             VALUES (?, ?, ?)';
            $statement = self::$conn->prepare($survey_query);
            $statement->execute([$name, $date_created, $expire_date]);
            $created_survey_id = self::$conn->lastInsertID();
            //===

            //=== insert Questions with survey_id of the previously created Survey
            foreach ($questions as $question) {
               // insert Question to database
               $question_query = 'INSERT INTO Question (question_number, question, question_type,
                                  survey_id) VALUES (?, ?, ?, ?)';
               $statement = self::$conn->prepare($question_query);
               $statement->execute([
                  $question->questionNo,
                  $question->question,
                  $question->questionType,
                  $created_survey_id
               ]);
               $created_question_id = self::$conn->lastInsertID();

               // insert Question's choices to database with question_id of
               // the previously created Question
               foreach($question->choices as $choice) {
                  $choice_query = 'INSERT INTO Choice (choice, question_id)
                                   VALUES (?, ?)';
                  $statement = self::$conn->prepare($choice_query);
                  $statement->execute([$choice, $created_question_id]);
               }

            }
            //===

            //=== insert Respondents in SurveyRespondent while generating submission codes
            //-- get unique random strings for each Respondent
            $respondents_length = count($respondent_ids);
            $randoms = Utils::create_randoms($respondents_length);
            /*-- insert each Respondent with unique random string into
                 SurveyRespondent                                     */
            for ($i = 0; $i < $respondents_length; $i++) {
               $survey_respondent_query = 'INSERT INTO SurveyRespondent (survey_id,
                     respondent_id, submission_code) VALUES (?, ?, ?);';
               $statement = self::$conn->prepare($survey_respondent_query);
               
               $statement->execute([
                  $created_survey_id,
                  $respondent_ids[$i],
                  $randoms[$i] 
               ]);

            }
            //===

            //=== commit transaction
            self::$conn->commit();

            // Survey created successfully and e-mails sent successfully
            return true;
         } catch (Exception $ex) {
            // rollback transaction
            self::$conn->rollback();

            // Survey creation failed
            return null;
         }

      }

      /* Removes a Survey in the database given $survey_id.
         Returns TRUE if the Survey was removed.
         Returns NULL if trying to remove the Survey was unsuccessful.
      */
      public static function remove_survey($survey_id) {
         $query = 'DELETE FROM Survey WHERE survey_id=?';

         try {
            $statement = self::$conn->prepare($query);
            // execute query using the given $survey_id and get rows
            // affected by query
            $statement->execute([$survey_id]);
            $rows_affected = $statement->rowCount();
            
            if ($rows_affected == 1) {
               return true;
            } else {
               return NULL;
            }
         } catch (PDOException $ex) {
            return NULL;
         }

      }


      // --------------------------------------------------
      // Question
      // --------------------------------------------------

      /* Returns an array of Questions that belong to the Survey of the
         given $survey_id.
         Returns false or NULL if something goes wrong.
      */
      public static function get_questions($survey_id) {
         $query = 'SELECT * FROM Question WHERE survey_id=?';

         try {
            $statement = self::$conn->prepare($query);
            $statement->execute([$survey_id]);
            
            $questions = $statement->fetchAll();

            // We don't check if $quesitons is EMPTY because it is known
            // that a Survey contains at least one Question.
            return $questions;

         } catch (PDOException $ex) {
            return NULL;
         }
      }

      /* Returns the total number of times chosen of the Choices that
         belong to the Question of the given $question_id.
         Returns NULL if something goes wrong.
      */
      public static function get_total_numberOfTimesChosen($question_id) {
         $total_numberOfTimesChosen = 0;

         $choices = self::get_choices($question_id);

         if (!$choices) {
            return NULL;
         }

         foreach ($choices as $choice) {
            $total_numberOfTimesChosen += $choice['no_of_times_chosen'];
         }

         return $total_numberOfTimesChosen;
      }


      // --------------------------------------------------
      // Choice
      // --------------------------------------------------

      /* Returns an array of Choices that belong to the Question of the
         given $question_id.
         Returns false or NULL if something goes wrong.
      */
      public static function get_choices($question_id) {
         $query = 'SELECT * FROM Choice WHERE question_id=?';
         
         try {
            $statement = self::$conn->prepare($query);
            $statement->execute([$question_id]);
            
            $choices = $statement->fetchAll();

            // We don't check if $choices is EMPTY because it is known
            // that a Question contains at least one Choice.
            return $choices;

         } catch (PDOException $ex) {
            return NULL;
         }
      }

      /* Returns true if the Choice with the given $choice_id had
         its no_of_times_chosen column incremented successfully.
         Returns NULL if trying to increment no_of_times_chosen column
         failed.
      */
      private static function increment_no_of_times_chosen($choice_id) {
         $query = 'UPDATE Choice SET no_of_times_chosen = no_of_times_chosen + 1
                   WHERE choice_id=?';
         
         try {
            $statement = self::$conn->prepare($query);
            // execute query using the given $choice_id and get rows
            // affected by query
            $statement->execute([$choice_id]);
            $rows_affected = $statement->rowCount();
            
            if ($rows_affected == 1) {
               return true;
            } else {
               return NULL;
            }
         } catch (PDOException $ex) {
            return NULL;
         }

      }


      // --------------------------------------------------
      // Respondent
      // --------------------------------------------------

      /* Returns an array containing all Respondents.
         A Respondent's attribute value can be obtained by using its attribute
         name as key on the returned array.
         Returns Database::$EMPTY_RESULT_SET if result set returned from the
         database is empty.
         Returns NULL if something goes wrong. 
      */
      public static function get_respondents() {
         try {
            $statement = self::$conn->query('SELECT * FROM Respondent');
            $respondents = $statement->fetchAll();

            // if returned result set is empty
            if (count($respondents) == 0) {
               return self::$EMPTY_RESULT_SET;
            } else { // returned result set is not empty
               return $respondents;
            }

         } catch (PDOException $ex) {
            return NULL;
         }
         
      }

      /* Returns an array containing Respondents that are associated with
         the Survey of the given $survey_id.
         The array contains respondent_id, first_name, last_name of the Respondent
         and also the 'no_of_emails_sent' value of that Respondent when associated
         with the Survey of the given $survey_id.
         Returns NULL if something goes wrong.
      */
      public static function get_respondents_for_survey($survey_id) {    
         $query = 'SELECT R.respondent_id, R.first_name, R.last_name, SR.submission_code, SR.no_of_emails_sent' . ' ' . 
                  'FROM SurveyRespondent AS SR JOIN Respondent AS R' . ' ' .
                                               'ON SR.respondent_id=R.respondent_id' .
                  ' ' . 'WHERE SR.survey_id=?';
         
         try {
            $statement = self::$conn->prepare($query);
            $statement->execute([$survey_id]);
              
            $respondents = $statement->fetchAll();

            // We don't check if $respondents is EMPTY because it is known
            // that a Survey contains at least one Respondent.
            return $respondents;

         } catch (PDOException $ex) {
            return NULL;
         }

      }

      /* Returns an array containing a Respondent with the given $respondent_id.
         Returns false or NULL if a Respondent with that id doesn't exist or
         something goes wrong when fetching a Respondent.
      */
      public static function get_respondent($respondent_id) {
         $query = 'SELECT * FROM Respondent WHERE respondent_id=?';

         try {
            $statement = self::$conn->prepare($query);
            // execute query using the given $respondent_id
            $statement->execute([$respondent_id]);
            
            $respondent = $statement->fetch();
            
            return $respondent;
         } catch (PDOException $ex) {
            return NULL;
         }
      }

      /* Adds a Respondent to the database using the given parameters.
         Returns TRUE if the Respondent is added, Returns NULL otherwise.
      */
      public static function add_respondent($first_name, $last_name, $email) {
         $query = 'INSERT INTO Respondent (first_name, last_name, email)
                   VALUES (?, ?, ?)';
         
         try {
            $statement = self::$conn->prepare($query);
            $statement->execute([$first_name, $last_name, $email]);
            return true;
         } catch (PDOException $ex) {
            return NULL;
         }
      }

      /* Removes a Respondent in the database given $respondent_id. 
         Returns TRUE if the Respondent was removed.
         Returns NULL if trying to remove the Respondent was unsuccessful.
      */
      public static function remove_respondent($respondent_id) {
         $query = 'DELETE FROM Respondent WHERE respondent_id=?';

         try {
            $statement = self::$conn->prepare($query);
            // execute query using the given $respondent_id and get rows
            // affected by query
            $statement->execute([$respondent_id]);
            $rows_affected = $statement->rowCount();
            
            if ($rows_affected == 1) {
               return true;
            } else {
               return NULL;
            }
         } catch (PDOException $ex) {
            return NULL;
         }

      }


      // --------------------------------------------------
      // SurveyRespondent
      // --------------------------------------------------
      
      /* Returns the data inside the 'no_of_emails_sent' column of the
         SurveyRespondent table, for the row containing the given $survey_id
         and $respondent_id;
         Returns NULL if something goes wrong when fetching the column data.
      */
      public static function get_no_of_emails_sent($survey_id, $respondent_id) {
         $query = 'SELECT no_of_emails_sent FROM SurveyRespondent
                   WHERE survey_id=? AND respondent_id=?';

         try {
            // execute SQL query using $username
            $statement = self::$conn->prepare($query);
            $statement->execute([ $survey_id, $respondent_id ]);
            
            return $statement->fetchColumn();
         } catch (PDOException $ex) {
            return NULL;
         }

      }

      /* Returns true if the data inside the 'no_of_emails_sent' column
         of the SurveyRespondent table, for the row containing the given
         $survey_id and $respondent_id was successfully incremented.
         Returns NULL if trying to increment the 'no_of_emails_sent'
         column failed. 
      */
      public static function increment_no_of_emails_sent($survey_id, $respondent_id) {
         $query = 'UPDATE SurveyRespondent SET no_of_emails_sent = no_of_emails_sent + 1
                  WHERE survey_id=? AND respondent_id=?';

         try {
            $statement = self::$conn->prepare($query);
            // execute query using the given $survey_id and $respondent_id
            // and get rows affected by query
            $statement->execute([ $survey_id, $respondent_id ]);
            $rows_affected = $statement->rowCount();
            
            if ($rows_affected == 1) {
               return true;
            } else {
               return NULL;
            }
         } catch (PDOException $ex) {
            return NULL;
         }

      }


      // --------------------------------------------------
      // Reflect a Response in the database
      // --------------------------------------------------

      /* Returns true if the submission code ($submission_code) exists for the
         Survey $survey_id, Returns false otherwise.
         Returns NULL if something goes wrong when checking.
      */
      private static function submission_code_exists($submission_code, $survey_id) {
         $query = 'SELECT 1 FROM SurveyRespondent
                   WHERE survey_id=?
                   AND submission_code=?';

         try {
            $statement = self::$conn->prepare($query);
            $statement->execute([ $survey_id, $submission_code ]);

            // If the given submission code with the given survey_id exists 1
            // will be selected, therefore 1 (true) will be returned.
            // Otherwise false will be returned.
            return $statement->fetchColumn();
            
         } catch (PDOException $ex) {
            return NULL;
         }
         
      }

      /* Returns true if the given submission code for the Survey with the
         given survey_id has already been used. Returns false otherwise.
         Returns NULL if something goes wrong when checking.
         NOTE: 'self::submission_code_exists' function can be used to check if
               the submission code and survey id combination really exist.
      */
      private static function submission_code_is_used($submission_code, $survey_id) {
         $query = 'SELECT submission_code_used FROM SurveyRespondent
                   WHERE survey_id=?
                   AND submission_code=?';

         try {
         $statement = self::$conn->prepare($query);
         $statement->execute([ $survey_id, $submission_code ]);
         $already_used =  $statement->fetchColumn();

         if ($already_used == 1) {
            return true;
         } else if ($already_used == 0) {
            return false;
         }

         
         } catch (PDOException $ex) {
            return NULL;
         }

      }

      /* Returns true if the given submission code's 'submission_code_used'
         column value is updated to 1 successfully.
         Returns NULL otherwise.
      */
      private static function make_submission_code_used($submission_code, $survey_id) {
         $query = 'UPDATE SurveyRespondent SET submission_code_used=1
                   WHERE survey_id=? AND submission_code=?';

         try {
            $statement = self::$conn->prepare($query);
            // execute query using the given $survey_id and $submission_code
            // and get rows affected by query
            $statement->execute([ $survey_id, $submission_code ]);
            $rows_affected = $statement->rowCount();
            
            if ($rows_affected == 1) {
               return true;
            } else {
               return NULL;
            }
            
         } catch (PDOException $ex) {
            return NULL;
         }

      }

      /* Returns true if a response was successfully reflected in the database.
            - $submission_code and $survey_id combination exist in the database
            - $submission_code has not been used to submit a Survey with $survey_id
            - Every chosen Choice's no_of_times_chosen is incremented successfully
              in the database
            - $submission_code's 'submission_code_used' value is set to 1 in the
              database
         Returns false or NULL otherwise.
      */
      public static function add_response($survey_id, $submission_code, $choice_ids) {
         // check if the $submission_code and $survey_id pair exist in the database
         $combination_exists = Database::submission_code_exists($submission_code, $survey_id);
         if (!$combination_exists) {
            return false;
         }

         // check if the $submission_code has already been used
         $submission_code_already_used = Database::submission_code_is_used(
                                    $submission_code, $survey_id);
         if ($submission_code_already_used) {
            return false;
         }

         // if submission code and survey id combination exists AND
         // has not been used yet
         try {
            //=== start transaction
            self::$conn->beginTransaction();

            //=== increment no_of_times_chosen of each Choice with choice_id
            //    belonging in the $choice_ids array
            foreach ($choice_ids as $choice_id) {
               $incremented_successfully = self::increment_no_of_times_chosen($choice_id);
               
               // if incrementing on any of the Choices fails, abort (rollback) transaction
               if (!$incremented_successfully) {
                  throw new Exception();
               }
            }
            //===


            //=== set the submission code's 'submission_code_used' to 1
            $submission_code_used_changed = self::make_submission_code_used(
                                       $submission_code, $survey_id);

            // if trying to change submission code's 'submission_code_used'
            // fails, abort (rollback) transaction
            if (!$submission_code_used_changed) {
               throw new Exception();
            }
            //===

            //=== commit transaction
            self::$conn->commit();
            
            // All Choice's 'no_of_times_chosen' incremented in the database
            // successfully AND the submission code's 'submission_code_used' set
            // to 1 in the database successfully
            return true;

         } catch (Exception $ex) {
            // rollback transaction
            self::$conn->rollback();
            
            return null;
         }
         
      }

   }

   // create instance of 'Database' class to create connection
   new Database();