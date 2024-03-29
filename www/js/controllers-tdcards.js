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

.controller('DiscoverCtrl', function($scope, $timeout, $stateParams, $ionicLoading, User, Recommendations, $cordovaGeolocation, $state) {
/*************** CONTROLLER FOR THE DISCOVER/SWIPE VIEW ***************/
	
  console.log('Loaded the DiscoverCtrl controller');
    
    // onSuccess Callback
    // This method accepts a Position object, which contains the
    // current GPS coordinates
    //
		var onSuccess = function(position) {
		
		$scope.position_data = [{
		  name: "Latitude",
		  value: position.coords.latitude
		},
		 {
		   name: "Longitude",
		   value: position.coords.longitude
		}
		];
		
		$scope.postion_link = 'http://maps.google.co.uk/?q=' + position.coords.latitude + ',' + position.coords.longitude;
		
		/*
        alert('Latitude: '          + position.coords.latitude          + '\n' +
              'Longitude: '         + position.coords.longitude         + '\n' +
              'Altitude: '          + position.coords.altitude          + '\n' +
              'Accuracy: '          + position.coords.accuracy          + '\n' +
              'Altitude Accuracy: ' + position.coords.altitudeAccuracy  + '\n' +
              'Heading: '           + position.coords.heading           + '\n' +
              'Speed: '             + position.coords.speed             + '\n' +
              'Timestamp: '         + position.timestamp                + '\n');
			  
		*/
		
		}; // end onSuccess
	

    // onError Callback receives a PositionError object
    //
    function onError(error) {
        alert('code: '    + error.code    + '\n' +
              'message: ' + error.message + '\n');
    }
	
	// var posOptions = { timeout: 5000, enableHighAccuracy: false, maximumAge: 5000 };
	
  
	// Show the loading page.
	  function showLoading()
	  {
		$ionicLoading.show({
				// template: 'Loading...' + text
				templateUrl: 'templates/loading.html'
				//noBackdrop: true,		 
				//template: '<div class="heart"><i class="icon ion-heart"></i></div><div class="loadingMessage">Seducing...</div>'
		});  
		
		
	
	
	
	console.log($cordovaGeolocation.getCurrentPosition());
	
	// Another new JavaScript development I have no idea about....
	// Promises? http://www.html5rocks.com/en/tutorials/es6/promises/
	$cordovaGeolocation.getCurrentPosition().then(onSuccess, onError);

	
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
	  for(var i = 5; i < 15; i++) $scope.addCard(i); // HACK: only load the first 5 from the database right now
	  
	  
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
  /*
  // This code isn't used with this tinder cards library
  $scope.spotSwiped = function(index) {
    console.log('sweetswipe');
    var newSpot = // new spot data
    $scope.spots.push(newSpot);
    $scope.currentSpot.rated = true;
  };
  */
  
  // Spot Rejected
  $scope.cardSwipedLeft = function(index) {
    console.log('LEFT SWIPE');
   // $scope.addCard(index);
   
  };
  
  // Spot Liked
  $scope.cardSwipedRight = function(index) {
    console.log('RIGHT SWIPE');
  //  $scope.addCard(index);
   User.addSpotToShortlist($scope.cards[index]);  
	
  };
  
    $scope.test2 = function() {
		console.log('Showing Detail');
		
		$state.go('detail');
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
      url: "img/firstdate.jpg"
  }, {
      name: "Fancy a drink?",
      id: "drinksonly",
      tag: "Hip spots to grab a drink with a date or friend.",
      url: "img/justdrinks.jpg"
  }, {
      name: "Brunch or coffee",
      id: "brunch",
      tag: "Casual coffee or brunch ideas.",
      url: "img/brunch.jpg"
  }, {
      name: "Fun in the sun",
      id: "funinthesun",
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "img/funinthesun.jpg"
  }, {
      name: "Dinner date",
      id: "dinnerdate",
      tag: "Suave restaurants, tasty treats and cheap eats.",
      url: "img/dinnerdate.jpg"
  }, {
      name: "Fun with friends",
      id: "friends",
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "img/funwithfriends.jpg"
  }, {
      name: "Let's get weird",
      id: "activedate",
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "img/letsgetweird.jpg"
  }, {
      name: "Cheap eats",
      id: "cheapeat",
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "img/cheapeats.jpg"
    },

    // {
    //   name: "Go all out",
    //   id: "goallout",
    //   tag: "Something informal with friends in pubs, bars or clubs.",
    //   url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/goallout.jpg"
    // },

    //  {
    //    name: "Hens night",
    //    id: "hennight",
    //    tag: "Something informal with friends in pubs, bars or clubs.",
    //    url: "/img/hennight.jpg"
    //  },

    //  {
    //    name: "Stag night",
    //    id: "stagnight",
    //    tag: "Something informal with friends in pubs, bars or clubs.",
    //    url: "/img/stagnight.jpeg"
    // }
    ];
		
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
.controller('MapController', function($scope, $ionicLoading, $cordovaGeolocation) {
	
	console.log('Loaded MapController');
	
	
	
    // onSuccess Callback
    // This method accepts a Position object, which contains the
    // current GPS coordinates
    //
    var onSuccess = function(position) {
        alert('Latitude: '          + position.coords.latitude          + '\n' +
              'Longitude: '         + position.coords.longitude         + '\n' +
              'Altitude: '          + position.coords.altitude          + '\n' +
              'Accuracy: '          + position.coords.accuracy          + '\n' +
              'Altitude Accuracy: ' + position.coords.altitudeAccuracy  + '\n' +
              'Heading: '           + position.coords.heading           + '\n' +
              'Speed: '             + position.coords.speed             + '\n' +
              'Timestamp: '         + position.timestamp                + '\n');
    };


    // onError Callback receives a PositionError object
    //
    function onError(error) {
        alert('code: '    + error.code    + '\n' +
              'message: ' + error.message + '\n');
    }
	
	// var posOptions = { timeout: 5000, enableHighAccuracy: false, maximumAge: 5000 };
	
	
	console.log($cordovaGeolocation.getCurrentPosition());
	
	// Another new JavaScript development I have no idea about....
	// Promises? http://www.html5rocks.com/en/tutorials/es6/promises/
	$cordovaGeolocation.getCurrentPosition().then(onSuccess, onError);
	
	
	
	
	 //  navigator.geolocation.getCurrentPosition(onSuccess, onError);
		
	//console.log(position);
	
	
	
	/*
 
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
	
	*/
 
})


/* Map Controller */
/*
.controller('LocationCtrol', function($scope, $ionicLoading) {
 

    // onSuccess Callback
    // This method accepts a Position object, which contains the
    // current GPS coordinates
    //
    var onSuccess = function(position) {
        alert('Latitude: '          + position.coords.latitude          + '\n' +
              'Longitude: '         + position.coords.longitude         + '\n' +
              'Altitude: '          + position.coords.altitude          + '\n' +
              'Accuracy: '          + position.coords.accuracy          + '\n' +
              'Altitude Accuracy: ' + position.coords.altitudeAccuracy  + '\n' +
              'Heading: '           + position.coords.heading           + '\n' +
              'Speed: '             + position.coords.speed             + '\n' +
              'Timestamp: '         + position.timestamp                + '\n');
    };

    // onError Callback receives a PositionError object
    //
    function onError(error) {
        alert('code: '    + error.code    + '\n' +
              'message: ' + error.message + '\n');
    }

    navigator.geolocation.getCurrentPosition(onSuccess, onError);
 
})
*/




/* Geolocation Background */
/*
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
*/



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

