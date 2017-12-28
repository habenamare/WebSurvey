<?php
   // constants for 'header.php'
   const TITLE = 'WebSurvey | Login';
   const ADMIN_PAGE = false;
   const DB_ACCESS = true;
   const STYLESHEETS = [
      'css/login.css'
   ];

   require('includes/header.php');
?>

<?php // form submission
   // check if the form is submitted
   if ($_SERVER['REQUEST_METHOD'] == 'POST') {

      // validate $_POST['user'] and $_POST['pass']
      if ( Database.validate_username_input($_POST['username'])
               && Database.validate_password_input($_POST['password'])) {
         
         // if validation succeeds, then authenticate
         if (Database.authenticate($_POST['username'], $_POST['password'])) {
            print('login successful');
         } else {
            print('login failed');
         }

      } else {
         // incorrect submission, display error
      }
   
   }
?>

<?php
// SESSION Test , Remove if not needed
//    $_SESSION['user'] = 'haben';
//    unset($_SESSION['user']);

//    if (isset($_SESSION['user'])) {
//       print('Logged In.');
//    } else {
//       print('Not Logged In.');
//    }
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
   require('includes/footer.php');
?>