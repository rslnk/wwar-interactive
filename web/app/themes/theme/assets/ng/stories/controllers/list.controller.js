angular.module('stories.list.controller', [
    'api.service',
    'ui.router',
    'ngSanitize',
    'ngCookies',
    'stories.list.filter',
    'anguvideo'
  ])
  .controller('ListController', [
    '$scope',
    '$filter',
    '$http',
    '$location',
    '$cookies',
    '$cookieStore',
    '$state',
    '$stateParams',
    'lodash',
    'ApiService',
  function ($scope, $filter, $http, $location, $cookies, $cookieStore, $state, $stateParams, lodash, ApiService) {
    $scope.stories = [];
    $scope.config = {};

    $scope.page = 0;
    $scope.perPage = 15;

    $scope.filter = { topics: [], searchText: '' };

    var delete_cookie = function(name) {
          document.cookie = name + '=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    };

    if ($cookies.stories && !$cookies.stories.match('false')) {
      var arr = $cookies.stories.split('/');
      if (arr.length > 2){
        arr.splice(0,2);
        $location.path(arr.join('/'));
        delete_cookie('stories');
      }
    }

    $scope.loadConfig = function () {
      ApiService
        .getConfig()
        .then(function(response){
          $scope.siteConfig = response;
          $scope.loadStories();
        });
    };

    $scope.loadConfig();

    $scope.toggleTopicInFilter = function (topic) {
      var i = $scope.filter.topics.indexOf(topic);
      if (i > -1) {
          $scope.filter.topics.splice(i,1);
      } else {
        $scope.filter.topics.push(topic);
      }
    };

    $scope.resetTopicsFilter = function () {
      $scope.filter.topics = [];
    };

    $scope.loadStories = function () {
      ApiService
        .getStories()
        .then(function(response){
          $scope.stories = response;
          // remove jquery stuff from ng app!!!! workaround for the berline milestone
          setTimeout(function () {
            $('.lightbox').height($('main').height() + 100);
          }, 250);
        });
    };

    $scope.closeLightbox = function () {
      $state.go('^');
    };

    $scope.$watch('filter.topics', function () {
      $scope.checkTopics();
    }, true);

    $scope.checkTopics = function () {
      if ($scope.filter.topics.length === 0) {
        $location.search('topics', null);
      } else {
        $location.search('topics', $scope.filter.topics.join(','));
      }
    };

    $scope.openStory = function (slug) {
      $state.go('list.story', { story: slug });
    };

    $scope.getPages = function () {
      var total = $filter('showStories')($scope.stories, $scope.filter).length;
      var pages = Math.ceil(total/$scope.perPage);
      return new Array( pages );
    };

    $scope.nextPage = function () {
      $scope.page += 1;
    };

    $scope.prevPage = function () {
      $scope.page -= 1;
    };

    $scope.toPage = function (page) {
      $scope.page = page;
    };

    $scope.inPaginatorScope = function (i) {
      var min = $scope.page*$scope.perPage;
      var max = min + $scope.perPage;
      return (i >= min && i < max);
    };

    $scope.activePage = function (i) {
      return (i === $scope.page);
    };

  }]);
