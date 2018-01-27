(function() {

   // count variables for creating ids
   var questionCount = 1;
   var choiceCount = 1;

   // helper modules
   var Helpers = {

      // Returns current date in ISO format
      getToday: function() {
         var today = new Date();
         
         var month = (today.getMonth() + 1);
         if (month >=1 && month <=9) {
            month = '0' + month;
         }

         var day = today.getDate();
         if (day >=1 && day <=9) {
            day = '0' + day;
         }

         return today.getFullYear() + '-' + month + '-' + day;
      },

      /* Returns the characters from the first occurence of a numeric
         character in a String upto the end of the string as a Number.
         eg. 'question1'       will retrun 1
             'choice1'         will return 1
             'choiceForQNo234' will return 234
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

      /* Returns a JSON string representation of the created Survey.
         FORMAT:
            {
               "name": "Employee Survey",
               "questions": [
                  {
                     "questionText": "This is the first question text.",
                     "questionNo": 1,
                     "choices": [
                        "choice1",
                        "choice2",
                        "choice3"
                     ],
                     "questionType": "s"
                  },
                  {
                     "questionText": "This is the second question.",
                     "questionNo": 2,
                     "choices": [
                        "choice1",
                        "choice2",
                        "choice3"
                     ],
                     "questionType": "m"
                  }
               ],
               "respondentsId": [
                  1,
                  2,
                  3,
                  4,
                  5
               ]
            }
      */
      generateSurveyJSONString: function() {
         // survey name
         var surveyName = $('#surveyName').val();


         /* 'respondentsId' array
               push checked Respondents' ids to 'respondentsId' array
         */
         var respondentsIdArray = [];
         $('input[name="respondent"]:checked').each(function() {
            respondentsIdArray.push(Helpers.noFromElementId($(this).attr('id')));
         });
      
         
         /* 'questions' array
               for each Question create Question object and push it to
               'questions' array
         */
         var questionsArray = [];
         var questionNoCount = 1;

         $('.question').each(function() {
            // get question text from 'h3' heading
            var questionText = $(this).find('h3').text();

            /* 'choices' array inside question
                  for each Choice inside Question find the Choice's text 
                  and push it to 'choices' array
            */
            var choicesArray = [];
            $(this).find('label').each(function() {
               choicesArray.push($(this).text());
            });

            // push Question object to questionsArray
            questionsArray.push({
               "question": questionText,
               "questionNo": questionNoCount,
               "choices": choicesArray,
               "questionType": Helpers.getQuestionTypeOf(
                                       Helpers.noFromElementId($(this).attr('id'))
                                                      )
            });

            // increment questionNoCount
            questionNoCount++;
         });

      
         // create Survey object
         var survey = {
            "name": surveyName,
            "dateCreated": Helpers.getToday(),
            "expireDate": $('#expireDate').val(),
            "respondentsId": respondentsIdArray,
            "questions": questionsArray
         };
      
         // return Survey object as JSON string
         return JSON.stringify(survey);
      }

   };

   // validator modules
   var Validators = {

      /* Returns true if the value inside '#surveyName' is not
         empty, returns false otherwise.
       */
      validateSurveyName: function() {
         if ($('#surveyName').val().length == 0) {
            return false;
         } else {
            return true;
         }
      },

      /* Returns true if inputed #expireDate value is in the correct format,
         returns false otherwise.
       */
      validateExpireDateInput: function() {
         var re = new RegExp('\\d{4}-\\d{2}-\\d{2}', 'g');
         
         return re.test($('#expireDate').val());
      },

      /* Returns false if there is no Respondent checked, returns
         true otherwise.
       */
      validateSurveyRespondents: function() {
         if ($('input[name="respondent"]:checked').length == 0) {
            return false;
         } else {
            return true;
         }
      },

      /* Returns true if the Survey contains at least one Question
         with one Choice inside it, otherwise returns false.
         NOTE: If there are more than one Questions then each Question
               must have at least one Choice.
      */
      validateSurvey: function() {

         var noOfQuestions = $('#questions').children('section').length;
         var aQuestionWithNoChoice = false;
         // if there is at least one Question
         if (noOfQuestions > 0) {
            // for each Question check if there is at least one Choice
            $('#questions').children('section').each(function() {
               var choicesLength = $(this).find('div.choice').length;
               console.log('test ' + choicesLength);
               if (choicesLength == 0 ) {
                  aQuestionWithNoChoice = true;
               }
            });

            // if there is a Question with no Choice return false, otherwise
            // return true;
            if (aQuestionWithNoChoice) {
               return false;
            } else {
               return true;
            }

         } else { // if there are no Questions
            return false;
         }
      },

      /* Returns true if the value inside '#newQuestionText textarea' is not empty,
         returns false otherwise.
      */
      validatenewQuestionText: function() {
         if ($('#newQuestionText').val().length == 0) {
            return false;
         } else {
            return true;
         }
      },

      // Returns true if 'choiceText' is not empty, returns false otherwise.
      validateChoiceInput: function(choiceText) {
         if(choiceText.length == 0) {
            return false;
         } else {
            return true;
         }
      }

   };

   // event handlers
   var EventHandlers = {

      /* event handler when 'Create Survey' is clicked
            make sure that Survey name is valid input
            make sure there is at least one Question with One Choice
            make sure there is at least one Respondent selected
            create and send Survey JSON

      */
      createSurveyButtonClicked: function(event) {
         // return if Survey Name is invalid input
         if (!Validators.validateSurveyName()) {
            alert('#surveyName can not be empty');
            return;
         }

         // return if Expire Date is invalid input
         if (!Validators.validateExpireDateInput()) {
            alert('Incorrect expire date inputed.');
            return;
         }

         // return if there is no Respondent checked
         if (!Validators.validateSurveyRespondents()) {
            alert('A survey must have at least one respondent.');
            return;
         }
         
         // return if there is not at least one Question containing
         // one Choice
         if (!Validators.validateSurvey()) {
            alert('A survey must at least have one Question containing at least ' +
                     'one choice. NOTE: Every Question must contain at least one Choice.');
            return;
         }
         
         // send generated Survey JSON string through ajax,
         // the server will then return 'success' or 'fail' depending
         // on how the survey creation went on the server side
         $.ajax({
            url: 'new_survey.php',
            data: {
               surveyJSON: Helpers.generateSurveyJSONString()
            },
            type: 'POST',
            success: function(response) {
               if (response == 'success') {
                  Main.surveySuccess();
               } else if (response == 'fail') {
                  Main.surveyFail();
               }
            },
            error: Main.surveyFail
         });
         
      },

      /* event handler when 'Add Question' is clicked
            create 'section'
               with id = question<questionCount>
                     class = question
                     data-question-type = "s|m" (depending on the checked radio button)
               containing 'button' to remove question
                  with id = removeQuestionButton<questionCount>
                       class = remove-question
               containing 'h3' tag which will contain the question text
               'div' tag which will contain
                  a textfield to enter a Choice &
                  a button to add the inputed Choice to the Question
               'section' tag to hold choices with
                  id    = choicesFor<questionCount> &
                  class = choices 
                  with an 'h4' tag to hold the text 'Choices'
            append 'section' to #questions
            increment questionCount
      */
      addQuestionButtonClicked: function(event) {
         // return if #newQuestionText is not valid input
         if (!Validators.validatenewQuestionText()) {
            alert('#newQuestionText cannot be empty');
            return;
         }

         var questionId = questionCount;
         var selectedQuestionType = $('input[name="questionType"]:checked').val();
         var questionText = $('#newQuestionText').val();

         // create parent 'section'
         var questionSection = $('<section id="question' + questionId +
                        '" class="question" data-question-type="' +
                        selectedQuestionType + '"></section');


         // create the other elements
         var questionTextH3 = $('<h3>' + questionText + '</h3>');
         

         var removeQuestionButton = $('<button id="removeQuestionButton' +
                              questionId + '">Remove Question</button>');
         removeQuestionButton.click(EventHandlers.removeQuestionButtonClicked);

         
         var addNewChoiceDiv = $('<div id="addNewChoiceFor' + questionId + 
                        '" class="add-new-choice">' +
                        '<input type="text" id="choiceInputFor' + questionId + '">' +
                        '</div>');
         var addNewChoiceButton = $('<button id="addChoiceButtonFor' + questionId +
                              '" class="addChoiceButton">Add Choice</button>');
         addNewChoiceButton.click(EventHandlers.addChoiceButtonClicked);
         addNewChoiceDiv.append(addNewChoiceButton);

         
         var choicesSection = $('<section id="choicesFor' + questionId +
                              '" class="choices"></section>');
         var choicesH4 = $('<h4>Choices</h4>');
         choicesSection.append(choicesH4);
                  
                  
         // append other elements to parent 'section'
         questionSection.append(questionTextH3);
         questionSection.append(removeQuestionButton);
         questionSection.append(addNewChoiceDiv);
         questionSection.append(choicesSection);
         

         // append parent 'section' to #questions
         $('#questions').append(questionSection);
         
         // increment questionCount
         questionCount++;
      },

      /* event handler when any of the 'Add Choice' buttons is clicked
            create 'div' with
               id = choiceDiv<choiceCount> and class = choice
            create 'input' with
               type = "radio|checkbox" depending on the question type,
               disabled = disabled,
               id = choice<choiceCount>
            create 'label' with
               for = choice<choiceCoutn>
            create 'button with
               id = removeChoice<choiceCount>
            append parent 'div' to #choicesFor<questionId>
            increment choiceCount
      */
      addChoiceButtonClicked: function(event) {
         // the new Choice's Question questionId and data-question-type
         var questionId = Helpers.noFromElementId(event.target.id);
         var questionType = Helpers.getQuestionTypeOf(questionId);

         var choiceInputText = $('#choiceInputFor' + questionId).val();

         // return if #choiceInputFor<questionIdOfNewChoice> is not valid input
         if (!Validators.validateChoiceInput(choiceInputText)) {
            alert('#choiceInputFor' + questionId + ' can not be empty');
            return;
         }


         // create parent 'div'
         var choiceDiv = $('<div id="choiceDiv' + choiceCount + 
                  '" class="choice"></div>')


         // create other elements
         var choice;
         if (questionType == 's') {
            choice = $('<input type="radio" disabled="disabled" id="choice' +
                              choiceCount + '">');
         } else if (questionType == 'm') {
            choice = $('<input type="checkbox" disabled="disabled" id="choice' +
                              choiceCount + '">');
         }


         var choiceLabel = $('<label for="choice' + choiceCount + '">' +
                              choiceInputText + '</label>')
         

         var removeChoiceButton = $('<button id="removeChoice' + choiceCount +
                              '">remove</button>');
         removeChoiceButton.click(EventHandlers.removeChoiceButtonClicked);


         // append other elements to parent 'div'
         choiceDiv.append(choice);
         choiceDiv.append(choiceLabel);
         choiceDiv.append(removeChoiceButton);

         // append parent 'div' to #choicesFor<questionId>
         $('#choicesFor' + questionId).append(choiceDiv);

         // increment choiceCount
         choiceCount++;
      },

      // event handler when any of the 'Remove Question' buttons is clicked
      removeQuestionButtonClicked: function(event) {
         // get questionId of the question to be removed from the
         // 'Remove Question' button id
         var questionToBeRemovedId = Helpers.noFromElementId(event.target.id);
         
         // remove #question<questionToBeRemovedId> from the DOM
         $('#question' + questionToBeRemovedId).remove();

         // notify that the question was removed
         alert('Question Removed');
      },

      // event handler when any of the 'remove' buttons (for choices) is clicked
      removeChoiceButtonClicked: function(event) {
         // get choiceId of the choice to be removed from the
         // 'remove' button id
         var choiceToBeRemovedId = Helpers.noFromElementId(event.target.id);

         // remove #choiceDiv<choiceToBeRemoved> from the DOM
         $('#choiceDiv' + choiceToBeRemovedId).remove();

         // notify that the choice was remoed
         alert('Choice Removed');
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

         // when 'Create Survey' is clicked
         $('#createSurveyButton').click(EventHandlers.createSurveyButtonClicked);

         // when 'Add Question' is clicked
         $('#addQuestionButton').click(EventHandlers.addQuestionButtonClicked);
         
         // when any of the 'Add Choice' is clicked
         $('.addChoiceButton').click(EventHandlers.addChoiceButtonClicked);

      },

      // function to run when survey creation is successful
      surveySuccess: function() {
         $('main').html(
            '<p style="color: green">Survey Created Successfully.</p>' +
            '<a href="new_survey.php">Create Another Survey</a>' +
            '<a href="surveys.php>Back to Surveys</a>'
         );
      },

      // function to run when survey creation fails
      surveyFail: function() {
         $('#creationMessage').html(
            '<p style="color: red">Survey could not be created. Please try again.</p>'
         );
      }
      
   };


   // call main module when 'document' is ready
   $(document).ready(Main.onReady);

})();
