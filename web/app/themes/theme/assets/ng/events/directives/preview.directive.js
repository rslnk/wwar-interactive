angular
  .module('events.preview.directive', ['ui.router'])
  .directive('eventPreview', function () {
    return {
      restrict: 'E',
      replace: true,
      template: '<div class="event-preview__item">'+
                  '<span class="event-preview__content">'+
                    '<span class="event-preview__year">{{ year }}</span>' +
                    '<h4 class="event-preview__title o-heading c-heading--event-preview">{{ title }}</h4>' +
                  '</span>'+
                '</div>',
      scope: {
        image: '@',
        title: '@',
        year: '@',
        slug: '@'
      },
      link: function (scope, element, attr) {
        element.css('background-image', 'url('+attr.image+')');
      }
    };
  });
