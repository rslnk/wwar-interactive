angular
  .module('stories.preview.directive', ['ui.router'])
  .directive('storyPreview', function () {
    return {
      restrict: 'E',
      replace: true,
      template: '<div class="story-preview__item {{ story.preview_image_color }}" style="background-image: url({{story.preview_image}})">'+
                  '<div class="story-preview__color-overlay {{ story.preview_image_color }}"></div>' +
                  '<div class="story-preview__content">'+
                    '<div class="story-preview__title">' +
                      '<h2 class="o-heading c-heading--story-preview {{ story.preview_image_color }}">{{ story.title }}</h2>' +
                    '</div>' +
                    '<div class="story-preview__hero">'+
                      '<h3><strong>{{ story.hero }}</strong>, {{ story.city[0].term_name }}</h3>' +
                    '</div>'+
                    '<div class="story-preview__icon u-icon u-icon-play-negative"></div>' +
                  '</div>'+
                '</div>',
      link: function (scope, element) {

      }
    };
  });
