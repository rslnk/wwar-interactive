/*eslint angular/di: [2,"array"]*/
angular.module('dateFormat.directive', [])
  .directive('dateFormat', ['templatesPath', function (templatesPath) {
    return {
      restrict: 'E',
      replace: true,
      templateUrl: templatesPath + 'partials/ng/date.html',
      scope: {
        thedate: '@',
        unknown: '@',
        lang: '@'
      },
      link: function ($scope) {
        var months = {
          'en_US': ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
          'de_DE': ['Januar', 'Februar', 'March', 'April', "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember "],
        };

        $scope.format = function () {
          if( $scope.unknown === "1" ) {
            return $scope.thedate.split('/')[0];
          } else {
            var date = new Date($scope.thedate);
            var d = date.getDate();
            var m = months[$scope.lang][date.getMonth()];
            var y = date.getFullYear();
            return d + ' ' + m + ', ' + y;
          }
        };
      }
    };
}]);
