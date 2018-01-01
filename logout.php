<?php
   session_start();

   // require utility class with reusable modules
   require_once(__DIR__ . '/includes/utils.inc.php');

   // if the user is logged in
   if(isset($_SESSION['username'])) {
      // unset session variable & redirect to login page
      unset($_SESSION['username']);
      Utils::redirect('login.php');
   } else {
      // redirect to index page
      Utils::redirect('index.php');
   }