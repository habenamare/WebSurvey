<?php
   // constants for 'header.php'
   const TITLE = 'WebSurvey | Removing....';
   const ADMIN_PAGE = true;

   const DB_ACCESS = true;

   const SCRIPTS = [
      'js/delete-survey.js'
   ];
   
   require('includes/header.php');
?>

<?php // remove survey with id $_GET['id']
   if (isset($_GET['id'])) {
      if(Database::remove_survey($_GET['id'])) {
         $deletion_status = "success";
         $deletion_message = "Survey removed successfully";
      } else {
         $deletion_status = "fail";
         $deletion_message = "Survey could not be removed";
      }
   } else {
      Utils::error_occured('Application error. Could not complete request.');
   }
?>

<form method="POST" action="surveys.php" id="hiddenForm">
   <input type="hidden" name="deletion_status" value="<?php print($deletion_status); ?>">
   <input type="hidden" name="deletion_message" value="<?php print($deletion_message); ?>">
</form>

<?php
   include('includes/footer.php');
?>