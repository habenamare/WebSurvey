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

      //
      public static function add_survey() {

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


   }

   // create instance of 'Database' class to create connection
   new Database();