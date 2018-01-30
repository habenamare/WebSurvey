$(document).ready(function() {

   // when any of the 'Delete Survey' links is clicked
   $('.delete-survey-button').click(function(event) {
      // clickedDeleteSurveyLink <- the 'a' tag that was clicked
      var clickedDeleteSurveyLink = $(this);
      
      // prevent the default behaviour when a link is clicked
      event.preventDefault();

      // popup a Dialog to ask the user for confirmation
      $('#deletion-confirmation').dialog({
         resizable: false,
         height: 'auto',
         width: 400,
         modal: true,
         buttons: {
            "I am sure, Remove Survey": function() {
               // if the 'I am sure...' button was clicked then
               // allow the default behaviour of clicking a link
               // which is redirecting the user to
               // 'delete_survey.php?id=<surveyId>'
               window.location.href = clickedDeleteSurveyLink[0].href;
            },
            Cancel: function() {
               // if the 'Cancel' button was clicked then
               // just close the popup dialog
               $(this).dialog('close');
            }
         }
      });

   });

});