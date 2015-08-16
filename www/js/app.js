
/* 
 * angular.module is a global place for creating, registering and retrieving Angular modules
 * 'datespot' is the name of the angular module example (also set in a <body> attribute in index.html)
 * the 2nd parameter is an array of 'requires'
 */

(function() {

  var app = angular.module('datespot', ['ionic', 'datespot.controllers'])

  app.run(function($ionicPlatform) {
    $ionicPlatform.ready(function() {

      console.log('Application loaded in app.js');

      if(window.cordova && window.cordova.plugins.Keyboard) {
        cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
      }
      if(window.StatusBar) {
        StatusBar.styleDefault();
      }
    });
  });
  
}());


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

  .state('tab.filter', {
    url: '/filter',
    views: {
      'tab-filter': {
        templateUrl: 'templates/filter.html',
        controller: 'FilterCtrl'
      }
    }
  })

  .state('tab.discover', {
    url: '/discover',
    views: {
      'tab-discover': {
        templateUrl: 'templates/discover.html',
        controller: 'DiscoverCtrl'
      }
    }
  })

  .state('tab.favorites', {
      url: '/favorites',
      views: {
        'tab-favorites': {
          templateUrl: 'templates/favorites.html',
          controller: 'FavoritesCtrl'
        }
      }
    })

  .state('detail', {
    url: '/detail',
    templateUrl: 'templates/detail.html'
  })
	
  // The default or the first (on load) state/controller that the application will load
  // If none of the above states are matched, use this as the fallback:
  $urlRouterProvider.otherwise('/tab/discover');
 
})


.constant('SERVER', {

  // DateSpot Public URL
  //url: 'http://192.168.0.17'
  
  url: 'http://ds.urandom.info'
  
});

