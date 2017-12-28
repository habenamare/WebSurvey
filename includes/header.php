<?php
   /* This file should be included in every page of the website.
      Purpose:
         - to start new or resume existing session
         - to check if the user is logged in if it is included in
           a page that requires authentication
      The 'footer.php' file should also be included if this file is
      included in a page.
   
      mandatory constants
         TITLE       string,
         ADMIN_PAGE  boolean

      non-mandatory constants
         DB_ACCESS        boolean
         NAV_LINKS        array of link title and the link itself pairs
         ACTIVE_NAV_LINK  string (mandatory if NAV_LINK is defined)
         STYLESHEETS      array of css file paths
   */


   // start new or resume existing session
   session_start();
   
   // check if all mandatory constants are set
      // if not, abort


   // check if the page wants DB access
   if (defined('DB_ACCESS')) {
      if (DB_ACCESS == true) {
         // then, require('mariadb.inc.php')
      }
   }
      // if DB_ACCESS == true
         

?>
<!doctype html>

<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">

   <title><?php print(TITLE) ?></title>
   <meta name="description" content="survey conducting web application">

   <link rel="stylesheet" href="fonts/fonts.css">
   <link rel="stylesheet" href="css/_site.css">
   <?php
      // load stylesheets from the STYLESHEETS constant
      if (defined('STYLESHEETS')) {
         foreach (STYLESHEETS as $style_sheet) {
            printf('<link rel="stylesheet" href="%s">', $style_sheet);
         }
      }

   ?>

   <!--[if lt IE 9]>
      <script src="../js/html5shiv.min.js"></script>
   <![endif]-->
</head>

<body>
   <header>
      <p>WebSurvey</p>
   </header>

   <?php
      // dispaly navigation if (HAS_NAVIGATION == true)
      if (defined('NAV_LINKS') && defined('ACTIVE_NAV_LINK')) {
         
         print('nav');
         print('<ul>');
   
         foreach (NAV_LINKS as $nav_link) {
            print('<li>');

            if ($nav_link['title'] == ACTIVE_NAV_LINK) {
               printf('<a href="%s" class="active">%s</a>', $nav_link['title'], $nav_link['link']);
            } else {
               printf('<a href="%s">%s</a>', $nav_link['title'], $nav_link['link']);
            }

            print('</li>');
         }
   
         print('/<ul>');
         print('/nav');

      }
   ?>

   <main>