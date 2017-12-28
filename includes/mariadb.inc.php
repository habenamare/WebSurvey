<?php
   /* 
      This file includes modules that interact with
      the database.
   */

   // include the 'Utils' utility class
   require('utils.inc.php');

   // get constants DB_HOST, DB_USER, DB_PASSWORD & DB_NAME
   require('../../../WebSurveyNotServed/mariadb.config.php');

   // This class contains static methods that perform database related tasks.  
   class Database {
      
      private static $conn;

      public function __construct() {
         // connect to MySQL and set connection to self::$conn
         self::$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

         // if connection fails 
         if (self::$conn->connect_error) {
            Utils::error_occured('Connection to database failed.');
            //Utils::error_occured(self::$conn->connect_error);
         }
         
      }

   }

   // create instance of 'Database' class to create connection
   new Database();