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

   /*
      Respondent 'first_name' or 'last_name' requirements:
         - length: >=2 && <=20
         - include only alphabetic characters (letters)    
   */
   function validate_respondent_name($name) {
      $name_length = strlen($name);
      if ($name_length >= 2 && $name_length <= 20) {
         if (ctype_alpha($name)) {
            return true;
         }
      }

      return false;
   }

   /*
      Respondent 'email' requirements:
         - validates e-mail addresses against the syntax in RFC 822, with the
           exceptions that comments and whitespace folding and dotless domain
           names are not supported. (from php.net documentation of 'FILTER_VALIDATE_EMAIL') 
   */
   function validate_respondent_email($email) {
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
         return true;
      }

      return false;
   }

   