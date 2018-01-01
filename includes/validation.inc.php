<?php
   /*
      This file includes modules to validate user input.
   */

   /* 'username' requirements:
         - length: >=4 && <=10
         - include only alphanumeric characters (letters or digits)
   */
   function validate_username($username) {
      $username_length = strlen($username);
      if ($username_length >= 4 && $username_length <= 10) {
         if (ctype_alnum($username)) {
            return true;
         }
      }

      return false;
   }

   /* 'password' requirements:
         - length: >=4 && <=15
   */
   function validate_password($password) {
      $password_length = strlen($password);
      if ($password_length >= 4 && $password_length <= 15) {
         return true;
      }

      return false;
   }

   