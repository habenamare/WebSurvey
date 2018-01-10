<?php
   // constants for 'header.php'
   const TITLE = 'WebSurvey | Respondents';
   const ADMIN_PAGE = true;

   const DB_ACCESS = true;
   const ACTIVE_NAV_LINK = 'Respondents';
   const NAV_LINKS = [
      [
         'name'     => 'Surveys',
         'location' => 'surveys.php'
      ],
      [
         'name'     => 'Respondents',
         'location' => '#'
      ],
      [
         'name'     => 'Log Out',
         'location' => 'logout.php'
      ]
      
   ];

   const STYLESHEETS = [
      'css/respondents.css'
   ];
   
   require('includes/header.php');

   // require 'validation.inc.php' for validating user inputs
   require(__DIR__ . '/includes/validation.inc.php');
   // flags for displaying messages
   $invalid_input = false;
   $respondent_created = false;
   $respondent_creation_failed = false;
?>

<?php // form submission
   // check if the form is submitted
   if (isset($_POST['first_name']) && isset($_POST['last_name']) &&
                        isset($_POST['email'])) {

      // validate $_POST['first_name'], $_POST['last_name'] and $_POST['email']
      if (validate_respondent_name($_POST['first_name'])
            && validate_respondent_name($_POST['last_name'])
            && validate_respondent_email($_POST['email'])) {
         
         // if validation succeeds, then create new Respondent in the database
         $success = Database::add_respondent($_POST['first_name'],
                           $_POST['last_name'], $_POST['email']);
         if ($success) { // insertion successful
            $respondent_created = true;
         } else if ($success == NULL) {
            $respondent_creation_failed = true;
         }

      } else {
         // incorrect submission, set flag to display error message
         $invalid_input = true;
      }
   
   }
?>

<h1>Manage Respondents</h1>

<?php // display error after checking flags
   if ($invalid_input) {
      print('<p class="error-message">Your input was invalid.</p>');
      print('<p class="error-message">Make sure <em>First Name</em> and
             <em>Last Name</em> are greater than 2 characters and not more than 20
             characters.</p>');
      print('<p class="error-message">Make sure also you have entered a valid email.</p>');
      print('<p class="error-message">Please try again.</p>');
   } else if ($respondent_created) {
      printf('<p class="success-message">Respondent %s %s has been created.</p>',
               $_POST['first_name'], $_POST['last_name']);
   } else if ($respondent_creation_failed) {
      print('<p class="error-message">Something went wrong when trying to create the respondent.</p>');
   }
?>

<section id="add-new-respondent">
   <h2>Add New Respondent</h2>

   <form method="POST">
      <label for="firstName">First Name</label>
      <input type="text" name="first_name" id="firstName">
      <label for="lastName">Last Name</label>
      <input type="text" name="last_name" id="lastName">
      <label for="email">Email</label>
      <input type="email" name="email" id="email">

      <button type="submit">Add Respondent</button>
   </form>
</section>

<section id="respondents-list">
   <h2>Respondents List</h2>

   <?php
      // get Respondents from the database
      $respondents = Database::get_respondents();

      // if database retrieval successful
      if ($respondents != NULL) {

         // beginning HTML table markup
         print('<table>');
         print('<thead>');
            print('<tr>');
               print('<th>Name</th>');
               print('<th>E-mail</th>');
               print('<th></th>');
            print('</tr>');
         print('</thead>');
         print('<tbody>');

         // if there is no Respondent in the database
         if ($respondents == Database::$EMPTY_RESULT_SET) {

            print('<tr><td colspan="3">');
            print('<strong>No Respondents.</strong>Respondents you created appear in this table.');
            print('</td></tr>');

         } else { // Respondents retrieved from the database

            foreach ($respondents as $respondent) {

               print('<tr>');
                  printf('<td>%s %s</td>', $respondent['first_name'], $respondent['last_name']);
                  printf('<td>%s</td>', $respondent['email']);
                  printf('<td><a href="remove_respondent.php?id=%d">Remove</a></td>',
                                 $respondent['respondent_id']);
               print('</tr>');

            }

         }

         // ending HTML table markup
         print('</tbody>');
         print('</table>');

  
      } else { // Respondents database retrieval failed
         // something went wrong when getting Respondents from the database
         Utils::error_occured('Something went wrong. Please try again later.');
      }


   ?>
</section>


<?php
   require('includes/footer.php');
?>