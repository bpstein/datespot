
/* 
 * angular.module is a global place for creating, registering and retrieving Angular modules
 * 'datespot' is the name of the angular module example (also set in a <body> attribute in index.html)
 * the 2nd parameter is an array of 'requires'
 */

angular.module('datespot', ['ionic', 'datespot.controllers', 'datespot.services', 'ionic.contrib.ui.tinderCards'])

.run(function($ionicPlatform) {
  $ionicPlatform.ready(function() {

    console.log('Application loaded in app.js');

      if(window.cordova && window.cordova.plugins.Keyboard) {
        cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
        cordova.plugins.Keyboard.disableScroll(true);
      }
      if(window.StatusBar) {
        StatusBar.styleLightContent();
      }
  });
})

angular.module('datespot', ['ionic', 'datespot.controllers'])



/* 
 * GUI State Provider Code - Link the various tabs to the relevant
 * template files as reuqired and their default state on load.
 */
.config(function($stateProvider, $urlRouterProvider) {

  // Ionic uses AngularUI Router, which uses the concept of states.
  // Learn more here: https://github.com/angular-ui/ui-router.
  // Set up the various states in which the app can be.
  // Each state's controller can be found in controllers.js.
  $stateProvider


  // Set up an abstract state for the tabs directive:
  .state('tab', {
    url: '/tab',
    abstract: true,
    templateUrl: 'templates/tabs.html',
    controller: 'TabsCtrl'
  })

  // Each tab has its own nav history stack:

  .state('tab.search', {
    url: '/search',
    views: {
      'search': {
        templateUrl: 'templates/search.html',
        controller: 'SearchCtrl'
      }
    }
  })

  .state('tab.discover', {
    url: '/discover',
    views: {
      'discover': {
        templateUrl: 'templates/discover.html',
        controller: 'DiscoverCtrl'
      }
    }
  })

  .state('tab.shortlist', {
    url: '/shortlist',
    views: {
      'shortlist': {
        templateUrl: 'templates/shortlist.html',
        controller: 'ShortlistCtrl'
      }
    }
  })

  .state('detail', {
      url: '/detail/:spotId',
      templateUrl: 'templates/detail.html',
          controller: 'DetailCtrl'
  });
	
  // The default or the first (on load) state/controller that the application will load
  // If none of the above states are matched, use this as the fallback:
  $urlRouterProvider.otherwise('/tab/search');
 
})

.directive('noScroll', function() {
  return {
    restrict: 'A',
    link: function($scope, $element, $attr) {
      $element.on('touchmove', function(e) {
        e.preventDefault();
      });
    }
  }
})

.constant('SERVER', {

  // DateSpot Public URL
  //url: 'http://192.168.0.17'
  
  url: 'http://ds.urandom.info'
  
});

// var $range = $(".js-range-slider");

// $range.ionRangeSlider({
//     type: "single",
//     postfix: " $",
//     grid: true,
//     values: ["7.5", "15", "35", "65", "99", "125", "150", "199", "299", "399", "499"]
// });

