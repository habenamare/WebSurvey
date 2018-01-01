<?php
   // constants for 'header.php'
   const TITLE = 'WebSurvey | Home';
   const ADMIN_PAGE = true;
   
   require('includes/header.php');
   //unset($_SESSION['username']);
?>

<h1>WebSurvey Homepage</h1>

<a href="logout.php">Log Out</a>

<?php
   require('includes/footer.php');
?>