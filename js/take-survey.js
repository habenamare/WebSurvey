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

      /* Returns 's' or 'm' depending of the 'data-question-type' attribute
         of the 'section#question<questionId>'
      */
      getQuestionTypeOf: function(questionId) {
         return $('#question' + questionId).attr('data-question-type');
      },

      /* Returns a JSON string representation of the completed Survey.
         FORMAT:
            {
               "surveyId": 1,
               "checkedChoices": [
                  21,
                  22,
                  23
               ],
               "submissionCode": "0xwehjkod"
            }
      */
      generateCompletedSurveyJSONString: function() {
         // get survey id from 'section#questions<survey_id>' which is the
         // first 'section' element in the document
         var questionsId = $('section').first()[0].id;
         var surveyId = Helpers.noFromElementId(questionsId);


         var checkedChoicesArray = [];
         // for each Question add checked Choice(s) id to
         // checkedChoicesArray
         $('.question').each(function() {

            $(this).children('input').each(function() {
               if ( $(this).is(':checked') ) {
                  var checkedChoiceElementId = $(this).attr('id');
                  checkedChoicesArray.push(Helpers.noFromElementId(
                                    checkedChoiceElementId));
               }
            });



         });

         // create 'Completed Survey' object
         var completedSurvey = {
            "surveyId": surveyId,
            "checkedChoices": checkedChoicesArray,
            "submissionCode": $('#submissionCode').val()
         };
      
         // return Survey object as JSON string
         return JSON.stringify(completedSurvey);
      }

   };

   // validator modules
   var Validators = {

      /* Returns true if the value inside '#submissionCode' is not
         empty, returns false otherwise.
         */
      validateSubmissionCode: function() {
         if ($('#submissionCode').val().length == 0) {
            return false;
         } else {
            return true;
         }
      },

      /* Returns true if the all Questions are answered,
            meaning for every Single Select Question there is one Choice checked
            meaning for every Multiple Select Question there is at least one Choice checked.
         Returns false otherwise.
      */
      validateCompletedSurvey: function() {
         var invalidCompletedSurvey = false;
         
         // check each Question
         $('.question').each(function() {
            
            var checkedCount = 0;            
            // while checking each Choice's checked property
            // increment checkedCount if Choice is checked
            $(this).children('input').each(function() {
               if ( $(this).is(':checked') ) {
                  checkedCount++;
               }
            });


            // get question type of this Question
            //    Single Select   <- 'data-question-type' == 's'
            //    Multiple Select <- 'data-question-type' == 'm'
            var thisQuestionId = Helpers.noFromElementId($(this).attr('id'));
            var questionType = Helpers.getQuestionTypeOf(thisQuestionId);
            
            
            if (questionType == 's') {
               // if nothing is checked OR more than one checked then set
               // invalidCompletedSurvey to true and then RETURN
               if (checkedCount == 0 || checkedCount > 1) {
                  invalidCompletedSurvey = true;
                  return;
               }
            } else if (questionType == 'm') {
               // if nothing is checked then set invalidCompletedSurvey to true
               // and then RETURN
               if (checkedCount == 0) {
                  invalidCompletedSurvey = true;
                  return;
               }
            }

         });

         // if invalidCompletedSurvey was set to true THEN return false,
         // otherwise return true
         if (invalidCompletedSurvey) {
            return false;
         } else {
            return true;
         }

      }

   };

   // event handlers
   var EventHandlers = {

      /* event handler when 'Finished' or '#finishedButton' is clicked
            make sure every Question is answered
            make sure '#submissionCode' is not empty
            create and send Completed Survey JSON
      */
      finishedButtonClicked: function(event) {
         // return if every Question is not answered
         if (!Validators.validateCompletedSurvey()) {
            alert('Please answer every Question on the Survey to complete the Survey.');
            return;
         }

         // return if '#submissionCode' is empty
         if (!Validators.validateSubmissionCode()) {
            alert('Submission code can not be empty. Please re-enter you Submission code and try again.');
            return;
         }
         
         // send generated 'Completed Survey' JSON string through ajax,
         // the server will then return 'success' or 'fail'
         // depending on how the 'completed survey' processing went on the
         // server side
         $.ajax({
            url: 'take_survey.php',
            data: {
               completedSurveyJSON: Helpers.generateCompletedSurveyJSONString()
            },
            type: 'POST',
            success: function(response) {
               if (response == 'success') {
                  Main.success();
               } else if (response == 'fail') {
                  Main.fail();
               }
            },
            error: Main.fail
         });
         
      }

   };

   // main modules
   var Main = {

      // main module
      onReady: function() {
         
         // hide the loading message
         $('#loadingMessage').hide();
         
         // show the loading method when ajax starts and
         // hide when ajax stops
         $(document)
         .ajaxStart(function () {
            $('#loadingMessage').show();
         })
         .ajaxStop(function () {
            $('#loadingMessage').hide();
         });

         // when 'Finished' button is clicked
         $('#finishedButton').click(EventHandlers.finishedButtonClicked);

      },

      // function to run if the survey was completed successfully
      success: function() {
         $('main').html(
            '<h1>Thank you for taking the time to participate in this survey.</h1>'
         );
      },

      // function to run if the survey was NOT completed successfully
      fail: function() {
         $('#finishedProcessingMessage').html(
            '<p style="color: red">Something went wrong when submitting your answers.</p>' +
            '<p style="color: red">Make sure you entered the submission code from the email you recieved correctly.</p>' +
            '<p style="color: red">Please try again.</p>'
         );
      }
      
   };


   // call main module when 'document' is ready
   $(document).ready(Main.onReady);

})();
   