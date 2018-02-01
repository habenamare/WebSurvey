$(document).ready(function() {

   var Helpers = {

      /* Returns a random color in hexadecimal format as a string,
            eg. '#0a0dbe'
      */    
      generateRandomColor: function() {
         var hexChars = '0123456789ABCDEF';
         
         var colorInHex = '#';
         for (var i = 0; i < 6; i++) {
            // generate between 0 and 15
            var randomNo = Math.floor(Math.random() * 16);
            colorInHex += hexChars[randomNo];
         }

         return colorInHex;
      },

      /* Returns the Choices represented inside the 'section.question'
         that contains the given element 'currentChart', as an
         array of strings.
      */
      getChoices: function(currentChart) {
         var allChoicesRows = $(currentChart).parent().parent().find('.choice-row');

         var allChoices = [];

         $.each(allChoicesRows, function(i, choiceRow) {
            allChoices.push( $(choiceRow).find('th').text() );
         });

         return allChoices;

      },

      /* Returns the Choices' percentages for the Choices represented
         inside the 'section.question' that contains the given element
         'currentChart', as an array of Numbers.
      */
      getChoicePercentages: function(currentChart) {
         var allChoicesRows = $(currentChart).parent().parent().find('.choice-row');
         
         var allChoicePercentages = [];

         $.each(allChoicesRows, function(i, choiceRow) {
            var percentageTableData = $(choiceRow).find('td')[0];
            var percentageWithoutPercent = $(percentageTableData).text().slice(0, -1);

            allChoicePercentages.push( Number(percentageWithoutPercent)  );
         });

         return allChoicePercentages;
      },

      /* Returns true if the numbers inside the given 'choicePercentages'
         array are not all zero. If the numbers inside the 'choicePercentages'
         array are all zero then false is returned.
      */
      hasResponse: function(choicePercentages) {
         var choicePercentagesSum = 0;

         for (var i = 0; i < choicePercentages.length; i++) {
            choicePercentagesSum += choicePercentages[i];
         }

         if (choicePercentagesSum == 0) {
            return false;
         } else {
            return true;
         }

      },

      /* Returns an array of strings that represent random colors in
         hexadecimal format. The length of the returned array or the number
         of random colors is the given argument 'noOfChoices'.
      */
      getBackgroundColors: function(noOfChoices) {
         var backgroundColors = [];
         
         for (var i = 0; i < noOfChoices; i++) {

            backgroundColors.push(Helpers.generateRandomColor());
         }

         return backgroundColors;

      }

   };


   // for each Chart (canvas element with class '.questionChart')
   var allCharts = document.querySelectorAll('.questionChart');
   for (var i = 0; i < allCharts.length; i++) {

      var currentChart = allCharts[i];

      var currentChartDrawingContext = currentChart.getContext('2d');

      var currentChartData;
      var currentChartLabels;
      var currentChartBackgroundColor;
      var currentChartBorderWidth;

      // Check if the current Question has any Response
      if (Helpers.hasResponse( Helpers.getChoicePercentages(currentChart) ) ) {
         currentChartData = Helpers.getChoicePercentages(currentChart);

         currentChartLabels = Helpers.getChoices(currentChart);

         currentChartBackgroundColor =
                     Helpers.getBackgroundColors(currentChartLabels.length);
         
         // use default borderWidth
         currentChartBorderWidth = undefined;
      } else {
         currentChartData = [1];
         currentChartLabels = ['No Responses'];
         currentChartBackgroundColor = [];

         currentChartBorderWidth = 0;
      }


      // draw Pie Chart for the current Question using 'chart.js'
      var currentPieChart = new Chart(currentChartDrawingContext, {
         type: 'pie',
         data: {
            datasets: [{
               data: currentChartData,
               backgroundColor: currentChartBackgroundColor,
               borderWidth: currentChartBorderWidth
            }],

            // These labels appear in the legend and in the tooltips when
            // hovering different arcs
            labels: currentChartLabels,

         },
         options: {
            responsive: true,
            maintainAspectRatio: false
         }
      });

   }
 
});