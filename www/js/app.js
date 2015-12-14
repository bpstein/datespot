
/* 
 * angular.module is a global place for creating, registering and retrieving Angular modules
 * 'datespot' is the name of the angular module example (also set in a <body> attribute in index.html)
 * the 2nd parameter is an array of 'requires'
 */
 
console.log('LOADED APP.JS');

angular.module('datespot', ['ionic'])

.run(function($ionicPlatform) {
  $ionicPlatform.ready(function() {

    console.log('Application loaded in app.js');

      if(window.cordova && window.cordova.plugins.Keyboard) {
        cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
        cordova.plugins.Keyboard.disableScroll(true);
      }
      if(window.cordova && window.cordova.InAppBrowser) {
        window.open = window.cordova.InAppBrowser.open;
      }
      if(window.StatusBar) {
        StatusBar.styleLightContent();
      }
  });
});





angular.module('datespot', ['ionic', 'datespot.controllers', 'ngCordova.plugins.geolocation', 'ionic.utils'])

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

  // 
  .state('splash', {
    url: '/',
    templateUrl: 'templates/splash.html',
    controller: 'SplashCtrl',
    onEnter: function($state, User) {
      User.checkSession().then(function(hasSession) {
        if (hasSession) $state.go('tab.discover');
      });
    }
  })

  .state('search', {
    url: '/search',
    templateUrl: 'templates/search.html',
    controller: 'SearchCtrl'
  })
  
  .state('discover', {
    url: '/discover/:occasion',
    templateUrl: 'templates/discover.html',
    controller: 'DiscoverCtrl',
    onEnter: function()
	{
		// ????? Check that there is an occasion parameter?	 
    }
  
  })

  .state('shortlist', {
    url: '/shortlist',
    templateUrl: 'templates/shortlist.html',
    controller: 'ShortlistCtrl'
  })

  .state('detail', {
      url: '/detail/:vuid',
      templateUrl: 'templates/detail.html',
      controller: 'DetailCtrl'
  })
  
  
   .state('map', {
      url: '/map/:vuid',
      templateUrl: 'templates/map.html',
      controller: 'MapController'
  });
	
  // The default or the first (on load) state/controller that the application will load
  // If none of the above states are matched, use this as the fallback:
  $urlRouterProvider.otherwise('/search');
 
})

.config(function($ionicConfigProvider) {
    $ionicConfigProvider.backButton.text('').icon('ion-chevron-left').previousTitleText(false);
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
	
   url: 'http://ds.urandom.info',
  
 // url: 			 'http://192.168.56.101',
 
 // url: 'http://192.168.0.16',
  
  
  clientversion: 2

});
