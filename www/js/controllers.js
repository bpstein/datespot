/**********************************************************************
 *
 *	script file		: controllers.js
 *	
 *	begin			: 1 August 2015
 *	copyright		: Ben Stein and Grant Bartlett
 *  description		: 
 *
 **********************************************************************/
 
angular.module('datespot.controllers', ['ionic', 'datespot.userservices', 'datespot.jsonservices', 'ionic.contrib.ui.tinderCards']  )

/*************** CONTROLLER FOR THE DISCOVER/SWIPE VIEW ***************/
.controller('DiscoverCtrl', function($scope, $timeout, $stateParams, $ionicLoading, User, Recommendations, TDCardDelegate) {
	
  console.log('Loaded the DiscoverCtrl controller');
  
  // Show the loading page.
  function showLoading()
  {
	  $ionicLoading.show({
				// template: 'Loading...' + text
				templateUrl: 'templates/loading.html'
				//noBackdrop: true,		 
				//template: '<div class="heart"><i class="icon ion-heart"></i></div><div class="loadingMessage">Seducing...</div>'
		});  
  }

  // Hide the loading page.
  function hideLoading()
  {
	  $ionicLoading.hide();
  }

  // We will load this page.
  showLoading();
  
  
  /*
  $scope.shortlistCount = User.shortlistCount;

  $scope.enteringShortlist = function() {
	console.log('Entering the Shortlist');
	User.newShortlist = 0;
  }

  $scope.leavingShortlist = function() {
	console.log('Leaving the Shortlist');
    Recommendations.getVenues();
  }

  // Loading screen while app pulls data form server
  var showLoading = function() {
	  
	console.log('Showing Loading Screen....');
	
    $ionicLoading.show({
      templateUrl: 'templates/loading.html',
      noBackdrop: true
    })
  }

  var hideLoading = function() {
	  
	console.log('Hiding Loading Screen....');	  
    $ionicLoading.hide();
  }

  */
  
	

	/*

  .then(function(){
	  
	console.log('Hiding Loading Screen....');
	
    // turn loading off
    hideLoading();
    $scope.currentSpot.loaded = true;
	
  });
  */
  
  // The cards
  $scope.cards = [];
  
  
	// GET THE VENUES AND BUILD THE CARDS
	Recommendations.getVenues()
		.then(function(){
		  $scope.currentSpot = Recommendations.queue[0]; // set the inital view to this
		  
		  console.log('The current spot is....');
		  console.log($scope.currentSpot);	  
		  hideLoading();
		 console.log('The recommended queue length is: ' + Recommendations.queue.length);
		// console.log('Length is:');
		// console.log(Recommendations.queue.length);
		
	  // Go through the database and add cards
	 //for(var i = 0; i < Recommendations.queue.length; i++) $scope.addCard(i);
	  for(var i = 0; i < 5; i++) $scope.addCard(i); // HACK: only load the first 25 from the database right now
	  
	  
	  console.log('Finished adding cards');		
	  
	 
  
	});
	
/*
  var cardTypes = [
    { image_url: 'max.jpg' },
    { image_url: 'ben.png' },
    { image_url: 'perry.jpg' },
  ];
  */

 

  // Remove a card from the stack
  $scope.cardDestroyed = function(index) {
	  
    $scope.cards.splice(index, 1);
	
	console.log('Card was destroyed: ' + index);
	
	var lastcard = $scope.cards.length-1;
	
	console.log('That last card is now: ' + lastcard);
	
	$scope.currentSpot = $scope.cards[lastcard]; // last card
	
	console.log($scope.cards[lastcard]);
	
  };

  // Add a card to the stack
  $scope.addCard = function(id) {
	  
   // var newCard = cardTypes[Math.floor(Math.random() * cardTypes.length)];
  //  newCard.id = Math.random();
  //  $scope.cards.unshift(angular.extend({}, newCard));
	
	
	console.log('Adding card from Recommendations array of id: ' + id);
	$scope.cards.unshift(angular.extend({}, Recommendations.queue[id]));
  }
  
 // for(var i = 0; i < 3; i++) $scope.addCard(1);

  
  // Spot Liked
  $scope.spotSwiped = function(index) {
    console.log('sweetswipe');
    var newSpot = // new spot data
    $scope.spots.push(newSpot);
    $scope.currentSpot.rated = true;
  };
  

  $scope.cardSwipedLeft = function(index) {
    console.log('LEFT SWIPE');
  //  $scope.addCard();
  };
  
  $scope.cardSwipedRight = function(index) {
    console.log('RIGHT SWIPE');
//    $scope.addCard();
	
  };
  
    $scope.test2 = function() {
    console.log('testing 123');
  };
  
  
})



// CONTROLLER FOR SHORTLIST VIEW (PREVIOUSLY FAVORITES)
.controller('ShortlistCtrl', function($scope, User) {
	
 console.log('Loaded the ShortlistCtrl controller');



  // With the new view caching in Ionic, Controllers are only called
  // when they are recreated or on app start, instead of every page change.
  // To listen for when this page is active (for example, to refresh data),
  // listen for the $ionicView.enter event:
  //
  //$scope.$on('$ionicView.enter', function(e) {
  //});

  $scope.shortlist = User.shortlist;
  
  $scope.removeSpot = function(spot, index) {
    User.removeSpotFromShortlist(spot, index);
  }

  // $scope.spots = Spots.all();

  // $scope.remove = function(spot) {
  //   Spots.remove(spot);
  // };

})


// CONTROLLER FOR DETAILS PAGE
.controller('DetailCtrl', function($scope, $stateParams, User, Spots) {

	console.log('Loaded the DetailCtrl controller');

	$scope.spot = Spots.get($stateParams.spotVuid);

  // $scope.spots = Spots.all();
})


// CONTROLLER FOR LISTING OF OCCASION TYPES ON SEACH VIEW
.controller('SearchCtrl', function($scope, $ionicLoading) {
	
 console.log('Loaded the OccasionCtrl controller');

 $scope.items = [{
      name: "First Date",
      id: "firstdate",
      tag: "She said 'yes.' Choose a place that'll impress.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/firstdate2.jpg"
  }, {
      name: "Fancy a drink",
      id: "drinksonly",
      tag: "Hip spots to grab a drink with a date or friend.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/justdrinks.jpg"
  },
     {
      name: "Dinner date",
      id: "dinnerdate",
      tag: "Suave restaurants, tasty treats and cheap eats.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/dinnerdate.jpg"
  },
    {
      name: "Brunch or coffee",
      id: "brunch",
      tag: "Casual coffee or brunch ideas.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/M1LK-Sweetcorn-fritters-bacon-862x575.jpg"
    },

    {
      name: "Fun with friends",
      id: "friends",
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "img/funwithfriends.jpg"
    },

    {
      name: "Let's get weird",
      id: "activedate",
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "img/letsgetweird.png"
    },

    {
      name: "Go all out",
      id: "goallout",
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/goallout.jpg"
    },
    {
      name: "Fun in the sun",
      id: "funinthesun",
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "/img/somethingsunny.jpg"
    },
    {
      name: "Cheap eats",
      id: "cheapeat",
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "/img/cheapeats.png"
     },
     {
       name: "Hens night",
       id: "hennight",
       tag: "Something informal with friends in pubs, bars or clubs.",
       url: "/img/hennight.jpg"
     },
     {
       name: "Stag night",
       id: "stagnight",
       tag: "Something informal with friends in pubs, bars or clubs.",
       url: "/img/stagnight.jpeg"
    }];
		
	// Function runFilter
	$scope.runFilter = function (id) {  
	 
		// To be expanded and perform the jSON query when
		// the user has changed the search parameters
		console.log('Runfilter clicked!'); 
		
		$ionicLoading.show({
			// template: 'Loading...' + text
			//templateUrl: 'templates/loading.html',
			//noBackdrop: true,		 
			template: '<ion-spinner icon="spiral"></ion-spinner><br /><span>Insert text for ' + id + ' occasion.</span>'
	});
	
	/*
	$scope.save = function() {
	  $state.go('shortlist');
	};
	*/
	
  } // end runFilter
 
}) // end SearchCtrl


/* Map Controller */
.controller('MapController', function($scope, $ionicLoading) {
 
    google.maps.event.addDomListener(window, 'load', function() {
        var myLatlng = new google.maps.LatLng(37.3000, -120.4833);
 
        var mapOptions = {
            center: myLatlng,
            zoom: 16,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
 
        var map = new google.maps.Map(document.getElementById("map"), mapOptions);
 
        navigator.geolocation.getCurrentPosition(function(pos) {
            map.setCenter(new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude));
            var myLocation = new google.maps.Marker({
                position: new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude),
                map: map,
                title: "My Location"
            });
        });
 
        $scope.map = map;
    });
 
})



/* Geolocation */
.controller('GeoCtrl', function($cordovaGeolocation) {
	
  console.log('Loaded the GeoLocation Factory');	

  var posOptions = {timeout: 10000, enableHighAccuracy: false};
  $cordovaGeolocation
    .getCurrentPosition(posOptions)
    .then(function (position) {
      var lat  = position.coords.latitude
      var long = position.coords.longitude
    }, function(err) {
      // error
    });


  var watchOptions = {
    frequency : 1000,
    timeout : 3000,
    enableHighAccuracy: false // may cause errors if true
  };

  var watch = $cordovaGeolocation.watchPosition(watchOptions);
  watch.then(
    null,
    function(err) {
      // error
    },
    function(position) {
      var lat  = position.coords.latitude
      var long = position.coords.longitude
  });


  watch.clearWatch();
  // OR
  $cordovaGeolocation.clearWatch(watch)
    .then(function(result) {
      // success
      }, function (error) {
      // error
    });
});



/*
// CONTROLLER FOR THE SPLASH PAGE 
.controller('SplashCtrl', function($scope, $state, User) {
	
  console.log('Loaded the SplashCtrl controller');

	
  $scope.submitForm = function (username, signingUp) {
    User.auth(username, signingUp).then(function(){
      $state.go('discover');
    }, function() {
      alert('Hmmm... try another username.');
    });
  }
})

// CONTROLLER FOR NAVIGATION BUTTONS
.controller('ButtonCtrl', function($scope, User) {
	
  console.log('Loaded the ButtonCtrl controller');

  
  $scope.rightButtons = [{
    type: 'button-clear',
    content: 'Shortlist',
    tap: function(e) {}
  }];
})
*/

