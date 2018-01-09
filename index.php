<?php
   
   // require utility class with reusable modules
   require_once(__DIR__ . '/includes/utils.inc.php');

   // makes 'surveys.php' the home(index) page
   Utils::redirect('surveys.php');