(function() {
   
   // helper modules
   var Helpers = {

      /* Returns the character from the first occurence of a numeric
         character in a String upto the end of the string as a Number.
         eg. 'choice46'   will return 46
             'choice1325' will return 1325
      */
      noFromElementId: function(elementId) {
         // index <- index of the first numeric character
         var index = elementId.search(/[0-9]/g);
   
         // slicedString <- slice string from 'index' upto the end of the string
         var slicedString = elementId.slice(index, elementId.length);
      
         // return slicedString after converting from String to Number
         return Number(slicedString);   
      },

      /* Returns a JSON string representation of the selected Respondents' id.
         FORMAT:
            [ 1, 12, 22 ]
         Returns NULL if no Respondent is selected.
      */
      generateRespondentIdsToSendEmailJSONString: function() {
         var respondentIdsToSendEmail = [];

         // get all checkboxes from the page 
         var checkboxesOnPage = $(':checkbox').toArray();

         // remove the checkbox with id 'allRespondentsCheckbox' from the array
         for (var i = 0; i < checkboxesOnPage.length; i++) {
            if (checkboxesOnPage[i].id == 'allRespondentsCheckbox') {
               // remove the current i-th element from the array
               checkboxesOnPage.splice(i, 1);
               break;
            }
         }

         // for each checkbox on the modified array
         //    if the checkbox is checked
         //       add the respondent id of the Respondent that is associated
         //       with the checkbox into the 'respondentIdsToSendEmailArray' array
         $.each( checkboxesOnPage , function(index, value) {
            var currentCheckbox = $(this)[0];
            
            if (currentCheckbox.checked) {
               // get respondent id from the checkbox's id
               var currentRespondentId = Helpers.noFromElementId(currentCheckbox.id);
               respondentIdsToSendEmail.push(currentRespondentId);
            }
         });

         // return NULL if no Respondent is selected
         if (respondentIdsToSendEmail.length == 0) {
            return null;
         }

         // return 'respondentIdsToSendEmail' array as JSON string
         return JSON.stringify(respondentIdsToSendEmail);
      }

   };
   
   // event handlers
   var EventHandlers = {

      /* event handler when '#sendEmailsButton' is clicked
            send selected Respondents' ids in an array as JSON to the server
      */
      sendEmailsButtonClicked: function(event) {
         // do nothing if no Respondent is selected
         if (!Helpers.generateRespondentIdsToSendEmailJSONString()) {
            return;
         }
         
         // send selected Respondents' ids as JSON string and the survey id
         $.ajax({
            url: 'email_sending_handler.php',
            data: {
               respondentIdsToSendEmail: Helpers.generateRespondentIdsToSendEmailJSONString(),
               // get id of the current Survey from the 'h1' tag id
               surveyId: Helpers.noFromElementId( $('h1')[0].id )
            },
            type: 'POST',
            success: function(response) {
               // the RESPONSE will be an object containing the ids of the Respondents
               // previously sent using ajax, where
               //    - the key is a Respondent's id &
               //    - the value is an array which contains
               //       - either the string 'Success' or 'Fail' depending on
               //          how the sending of the email went for that Respondent and
               //       - an integer that holds the updated number of emails sent to
               //         that particular Respondent, which is fetched from the database
               Main.success(JSON.parse(response));
            },
            error: Main.fail
         });
         
      },

      /* event handler when '#allRespondentsCheckbox' is clicked
            check and uncheck all Respondent checkboxes
      */
      allRespondentsCheckboxChanged: function(event) {
         if (this.checked) {
            // check every checkbox on the page
            $.each( $(':checkbox') , function(key, value) {
               $(this)[0].checked = true;
            });

         } else {
            // uncheckd every checkbox on the page
            $.each( $(':checkbox') , function( key, value ) {
               $(this)[0].checked = false;
            });

         }

      },

      /* event handler when a checkbox that is associated with a Respondent is clicked
            if '#allRespondentsCheckbox' is checked
               then uncheck '#allRespondentsCheckbox'
      */
      aRespondentCheckboxChanged: function(event) {
         if ($('#allRespondentsCheckbox')[0].checked) {
            $('#allRespondentsCheckbox')[0].checked = false;
         }

      }

   };
   
   // main modules
   var Main = {

      // main module
      onReady: function() {
         // hide the waiting message
         $('#waitingMessage').hide();
         
         // show the waiting message when ajax starts and
         // hide when ajax stops
         $(document)
         .ajaxStart(function () {
            // empty the '#finishedProcessingMessage' div to remove
            // any previous outputted message
            $('#finishedProcessingMessage').html('');

            $('#waitingMessage').show();
         })
         .ajaxStop(function () {
            $('#waitingMessage').hide();
         });

         //=== attach event handlers
         // when '#allRespondentsCheckbox' is clicked
         $('#allRespondentsCheckbox').change(EventHandlers.allRespondentsCheckboxChanged);
         
         // when any of a Respondent's checkbox is clicked
         $('.respondentCheckbox').change(EventHandlers.aRespondentCheckboxChanged);

         // when '#sendEmailsButton' button is clicked
         $('#sendEmailsButton').click(EventHandlers.sendEmailsButtonClicked);
         //===

      },

      // function to run if the ajax request returned successfully
      success: function(objectFromServer) {
         // reset 'Sending Status' column of every row to empty string
         $.each( $('.status-TD'), function(key, value) {
            $(this).html('');
         });

         // uncheck every checkbox on page
         $.each( $(':checkbox'), function(key, value) {
            $(this)[0].checked = false;
         });

         
         // an array to keep track of Respondents with fail 'Sending Status',
         // to be used when selecting checkboxes for Respondents with
         // fail 'Sending Status'
         var failedRespondentIds = [];

         // variable to hold the no of Respondent ids that were sent from the server
         var objectFromServerLength = 0;

         // update the 'Sending Status'and the 'No Of Emails Sent' columns for
         // the approriate Respondent rows
         $.each( objectFromServer, function(key, value) {
            // if the sending status is 'Success', update the value of the
            // 'No Of Emails Sent' column for the current Respondent row
            var currentRespondentNoOfEmailsTD = '#noOfEmailsTDFor' + key;
            $(currentRespondentNoOfEmailsTD).html(value[1]);
            
            // create html content based on 'Success' or 'Fail' sending status
            var htmlContent = '<span style="color: ';
            (value[0] == 'Success') ? htmlContent += 'green;"' : htmlContent += 'red;"';
            htmlContent += '>' + value[0] + '</span>';

            // apply created html content to the appropriate 'td' html element
            var currentRespondentStatusTD = '#statusTDFor' + key;
            $(currentRespondentStatusTD).html(htmlContent);

            // keep track of Respondent ids with 'Fail' value
            if (value[0] == 'Fail') {
               failedRespondentIds.push(Number(key));
            }

            objectFromServerLength++;
         });


         // if all the Respondents' sending status was 'Success'
         if (failedRespondentIds.length == 0) {
            $('#finishedProcessingMessage').html(
               '<p style="color: green">All emails were sent successfully.</p>'
            );
         } else {
            // check the checkboxes that are associated with failed 'Sending Status'
            $.each( failedRespondentIds, function(index, value ) {
               var currentRespondentCheckboxId = '#checkboxFor' + value;
               $(currentRespondentCheckboxId)[0].checked = true;
            });

            // DISPLAY ERROR MESSAGE
            // if all the Respondents' sending status was 'Fail'
            if (failedRespondentIds.length == objectFromServerLength) {
               // display message for the user
               $('#finishedProcessingMessage').html(
                  '<p style="color: red">All of the emails were not sent successfully.</p>' +
                  '<p style="color: red">Please try sending the emails again.</p>'
               );
            } else {
               // display message for the user, to click '#sendEmailsButton' again to
               // retry sending emails for the failed ones
               $('#finishedProcessingMessage').html(
                  '<p style="color: gray">Some of the emails were not sent successfully.</p>' +
                  '<p style="color: gray">Please try sending the emails again, the failed ones have been selected.</p>'
               );
            }

         }

      },

      // function to run if the ajax request failed
      fail: function() {
         $('#finishedProcessingMessage').html(
            '<p style="color: red">Something went wrong when trying to send emails.</p>' +
            '<p style="color: red">Please, try again and make sure you can send emails using your network.</p>'
         );
      }
      
   };


   // call main module when 'document' is ready
   $(document).ready(Main.onReady);

})();
      