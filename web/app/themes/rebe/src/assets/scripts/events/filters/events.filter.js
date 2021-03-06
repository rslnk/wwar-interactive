angular.module('events.filter', [])
  .filter('showEvents', function () {
    return function (events, filter) {
      var result = [];

      function filterIsSet () {
        return typeof filter !== angular.isUndefined && (typeof filter.topics !== angular.isUndefined || typeof filter.searchText !== angular.isUndefined);
      }

      function topicsFilterIsSet () {
        return typeof filter.topics !== angular.isUndefined && filter.topics.length > 0 && filter.topics instanceof Array;
      }

      function withinTopicsFilter (topics) {
        var ts = topics.map(function (topic) {
          return topic.term_slug;
        });

        for (var i = 0, l = ts.length; i < l; i++){
          if (filter.topics.indexOf(ts[i]) > -1) {
            return true;
          }
        }

        return false;
      }

      function filterByTopics () {
        events.map(function (event) {
          if (!event.topics || !withinTopicsFilter(event.topics)) {
            event.hide = true;
          }
          else {
            event.hide = false;
          }
        });
      }

      function showAll () {
        events.map(function (event) {
          event.hide = false;
        });
      }

      function filterEvents () {
        if (filterIsSet() && topicsFilterIsSet()){
          filterByTopics();
        } else {
          showAll();
        }
        return events;
      }

      return filterEvents();
    };
});
