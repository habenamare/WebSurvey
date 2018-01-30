$(document).ready(function() {
   
      // when any of the 'Delete' links is clicked
      $('.delete-respondent-button').click(function(event) {
         // clickedDeleteLink <- the 'a' tag that was clicked
         var clickedDeleteLink = $(this);
         
         // prevent the default behaviour when a link is clicked
         event.preventDefault();
   
         // popup a Dialog to ask the user for confirmation
         $('#deletion-confirmation').dialog({
            resizable: false,
            height: 'auto',
            width: 400,
            modal: true,
            buttons: {
               "I am sure, Remove Respondent": function() {
                  // if the 'I am sure...' button was clicked then
                  // allow the default behaviour of clicking a link
                  // which is redirecting the user to
                  // 'delete_respondent.php?id=<respondentId>'
                  window.location.href = clickedDeleteLink[0].href;
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