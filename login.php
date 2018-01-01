<?php

   // constants for 'header.php'
   const TITLE = 'WebSurvey | Login';
   const ADMIN_PAGE = false;

   const DB_ACCESS = true;
   const STYLESHEETS = [
      'css/login.css'
   ];

   require_once(__DIR__ . '/includes/header.php');


   /* if the user is logged in, then redirect
      REMEMBER: session_start() is in 'header.php', so 'header.php' must be
                included before running this piece of code.
   */
   if (isset($_SESSION['username'])) {
      Utils::redirect('index.php');
   }

   // require 'validation.inc.php' for validating user inputs
   require(__DIR__ . '/includes/validation.inc.php');
   // flags for displaying error messages
   $invalid_input = false;
   $invalid_combination = false;
?>


<?php // form submission
   // check if the form is submitted
   if (isset($_POST['username']) && isset($_POST['password'])) {

      // validate $_POST['username'] and $_POST['password']
      if ( validate_username($_POST['username'])
            && validate_password($_POST['password'])) {
         
         // if validation succeeds, then authenticate
         if (Database::authenticate($_POST['username'], $_POST['password'])) {
            print('login successful');
            $_SESSION['username'] = 'haben';
            Utils::redirect('index.php');
         } else {
            // invalid username and password combination, set flag to display error
            $invalid_combination = true;
         }

      } else {
         // incorrect submission, set flag to display error
         $invalid_input = true;
      }
   
   }
?>


<?php // display error after checking $invalid_input or $invalid_combination
   if ($invalid_input) {
      print('<p class="error-message">Invalid username or password.</p>');
      print('<p class="error-message">Please try again.</p>');
   } else if ($invalid_combination) {
      print('<p class="error-message">Invalid username and password combination.</p>');
      print('<p class="error-message">Please try again.</p>');
   }
?>

<h1>Login</h1>

<form method="POST" action="login.php">
   <label for="username">Username</label>
   <input type="text" name="username" id="username">
   <br>

   <label for="password">Password</label>
   <input type="password" name="password" id="password">
   <br>
   
   <button type="Submit">Log In</button>
</form>


<?php
   require(__DIR__ . '/includes/footer.php');
?>