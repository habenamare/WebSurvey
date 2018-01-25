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
               $question_query = 'INSERT INTO Question (question_number, question, choice_type,
                                  survey_id) VALUES (?, ?, ?, ?)';
               $statement = self::$conn->prepare($question_query);
               $statement->execute([
                  $question->questionNo,
                  $question->question,
                  $question->choiceType,
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
            /*-- populate $respondent_ids_with_codes with
                 respondent_id as KEY and submission_code as VALUE */ 
            $respondent_ids_with_codes = [];
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

               // populate $respondent_ids_with_codes
               $r_id = $respondent_ids[$i];
               $respondent_ids_with_codes[$r_id] = $randoms[$i];

            }
            //===
            
            //=== send e-mails
            // if email sending is unsuccessful, rollback transaction
            $sending_success = Utils::send_emails($respondent_ids_with_codes, $name);
            if (!$sending_success) {
               // e-mails sending failed
               throw new Exception();
            }
            //===

            // Survey created successfully and e-mails sent successfully
            //=== commit transaction
            self::$conn->commit();
            
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

   }

   // create instance of 'Database' class to create connection
   new Database();