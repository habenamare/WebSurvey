   </main>
   
   <footer>
      <p>WebSurvey &copy; 2017</p>
   </footer>

   <?php // load scripts from the SCRIPTS constant
      if (defined('SCRIPTS')) {
         foreach (SCRIPTS as $script) {
            printf('<script src="%s"></script>', $script);

         }
      }

   ?>
</body>

</html>