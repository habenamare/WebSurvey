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
?>

<h1>Manage Respondents</h1>

<section id="add-new-respondent">
   <h2>Add New Respondent</h2>
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