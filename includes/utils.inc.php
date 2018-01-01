<?php
   /*
      This file includes a utility class that contians modules
      that can be reused by any part of the application.
   */

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

   }