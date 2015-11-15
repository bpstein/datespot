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

.controller('DiscoverCtrl', function($scope, $state, $stateParams, $ionicLoading, $cordovaGeolocation, $ionicPopup, User, Search, TDCardDelegate) {
/*************** CONTROLLER FOR THE DISCOVER/SWIPE VIEW ***************/
	
	console.log('Loaded the DiscoverCtrl controller');
	
	//$localstorage.set('name', 'Max');
    // console.log($localstorage.get('name'));

	// For querying the database.
	var query_offset = 0;
	
    
    // onSuccess Callback
    // This method accepts a Position object, which contains the
    // current GPS coordinates
    //
	function onGPSLockSuccess(position) {		
		$scope.position_latitude 	= position.coords.latitude;
		$scope.position_longitude	= position.coords.longitude;
		///$scope.showAlert();
	}; // end onSuccess
	
    // onError Callback receives a PositionError object

    //
    function onGPSLockError(error) {
		console.log('ERROR OBTAINING LOCATION');
		console.log('code: '    + error.code    + '\n' +
					'message: ' + error.message + '\n');				
    }
	
	
	// An alert dialog for debug purposes only
	//
	$scope.showAlert = function() {
	   var alertPopup = $ionicPopup.alert({
		 title: 'No venues found!',
		 template: 'Occasion ID: ' + $stateParams.occasion + '<br />Your Lat: ' + $scope.position_latitude + '<br />Your Lon: ' + $scope.position_longitude
	   });
	   alertPopup.then(function(res) {
		 console.log('Thank you for not eating my delicious ice cream cone');
	   });
	 };
	
	
	// OK. Lets kick things off.. Show Loading
	//
	$ionicLoading.show({
		templateUrl: 'templates/loading.html'
	});  

	
	// The cards variable for the template...
	$scope.cards = [];

	// Add a card to the stack, generally from search results
	$scope.addCard = function(data) 
	{
		$scope.cards.unshift(angular.extend({}, data));
	}
  	
	

	// Now get the location asynchronously
	// Another new JavaScript development I have no idea about....
	// Promises? http://www.html5rocks.com/en/tutorials/es6/promises/
	$cordovaGeolocation.getCurrentPosition().then(onGPSLockSuccess, onGPSLockError)
	.then(function() {
		
	
		// Once we have GPS lock, get the closest venues and build the cards
		Search.getResults($stateParams.occasion,  $scope.position_latitude, $scope.position_longitude, query_offset )
			.then(function()
			{
				if ( Search.results.length == 0)
				{
					$scope.showAlert();		
				}
				else
				{
					for (var i = 0; i < Search.results.length; i++)
					{
						$scope.addCard(Search.results[i]);
					}
					/* Load only one card at a time, this cards libary permits multiple
					 * cards overlapping each other at a time but I think this sucks */
					//$scope.cards 		= Search.results;			
					console.log('Finished adding cards');		
				}
				
			   // Hide the loading
			   $ionicLoading.hide();		  
			  // console.log('Finished adding cards');		


		  });
		  
	}); // get geolocation and then cards
	

  // Remove a card from the stack
  $scope.cardDestroyed = function(index) 
  {

    $scope.cards.splice(index, 1);
	console.log('Card was destroyed: ' + index);
	
	var lastcard = $scope.cards.length-1;
	console.log('That last card is now: ' + lastcard);
	
	
	
    // low on the queue? lets fill it up
    if (Search.results.length <= 2) {
     // o.getVenues(); // we don't do this as our JSON provides all venues currently
    }
	
  };

  // Spot Rejected
  $scope.cardSwipedLeft = function(index) {
    console.log('LEFT SWIPE');   
    $scope.cardDestroyed(index);
   
  };
  
  // Spot Liked
  $scope.cardSwipedRight = function(index) {
    console.log('RIGHT SWIPE');
  //  $scope.addCard(index);
	User.addSpotToShortlist($scope.cards[index]);  
	
  };
  
  
  // Spot Clicked, we can't use a href at this stage for
  // some reason so we need to use the ionic state director
  // and pass the appropriate parameters
  $scope.cardClicked = function(index) 
  {
		console.log('Click request for  card of index:' + index);
		
		var vuidOfClicked = $scope.cards[index]['vuid'];
		console.log('.. which has a VUID of: ' + vuidOfClicked);
		
		// use the vuid as the key to viewing the venue
		// http://learn.ionicframework.com/formulas/sharing-data-between-views/
		// http://forum.ionicframework.com/t/pass-data-with-state-go/2897/2
		
		$state.go('detail', {vuid:  vuidOfClicked });	
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
.controller('DetailCtrl', function($scope, $stateParams, Search) {

	console.log('Loaded the DetailCtrl controller');
	
	console.log('The state vuid param :-):');
	console.log($stateParams.vuid);
	
	$scope.spot = Search.getVenueByVUID($stateParams.vuid);
	
	console.log($scope.spot);
	/*

	$scope.spot = Spots.get($stateParams.vuid);
	
	console.log('The selected spot details are as follows:');
	console.log($scope.spot);
	
	*/

  // $scope.spots = Spots.all();
})


// CONTROLLER FOR LISTING OF OCCASION TYPES ON SEACH VIEW
.controller('SearchCtrl', function($scope, $ionicLoading) {
	
 console.log('Loaded the OccasionCtrl controller');

 $scope.occasions = [{
      name: "First Date",
      id: "firstdate",
      tag: "She said 'yes.' Choose a place that'll impress.",
      imgurl: "img/firstdate.jpg"
  }, {
      name: "Fancy a drink?",
      id: "drinksonly",
      tag: "Hip spots to grab a drink with a date or friend.",
      imgurl: "img/justdrinks.jpg"
  }, {
      name: "Brunch or coffee",
      id: "brunch",
      tag: "Casual coffee or brunch ideas.",
      imgurl: "img/brunch.jpg"
  }, {
      name: "Fun in the sun",
      id: "funinthesun",
      tag: "Something informal with friends in pubs, bars or clubs.",
      imgurl: "img/funinthesun.jpg"
  }, {
      name: "Dinner date",
      id: "dinnerdate",
      tag: "Suave restaurants, tasty treats and cheap eats.",
      imgurl: "img/dinnerdate.jpg"
  }, {
      name: "Fun with friends",
      id: "friends",
      tag: "Something informal with friends in pubs, bars or clubs.",
      imgurl: "img/funwithfriends.jpg"
  }, {
      name: "Let's get weird",
      id: "activedate",
      tag: "Something informal with friends in pubs, bars or clubs.",
      imgurl: "img/letsgetweird.jpg"
  }, {
      name: "Cheap eats",
      id: "cheapeat",
      tag: "Something informal with friends in pubs, bars or clubs.",
      imgurl: "img/cheapeats.jpg"
    }

    // {
    //   name: "Go all out",
    //   id: "goallout",
    //   tag: "Something informal with friends in pubs, bars or clubs.",
    //   imgurl: "https://s3-us-west-1.amazonaws.com/datespot/occasions/goallout.jpg"
    // },

    //  {
    //    name: "Hens night",
    //    id: "hennight",
    //    tag: "Something informal with friends in pubs, bars or clubs.",
    //    imgurl: "/img/hennight.jpg"
    //  },

    //  {
    //    name: "Stag night",
    //    id: "stagnight",
    //    tag: "Something informal with friends in pubs, bars or clubs.",
    //    imgurl: "/img/stagnight.jpeg"
    // }
    ];
		
	// Function runFilter
	/*
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
	
	
		$scope.save = function() {
		  $state.go('shortlist');
		};
		
	} // end runFilter
	*/
 
}) // end SearchCtrl


/* Map Controller */
.controller('MapController', function($scope, $ionicLoading, $compile) {
	
	 console.log('Loading Map Controller.');
			
      function initialize() {
		  
		/// HACK: Need to change this to a value of the actual location / event.
        var myLatlng = new google.maps.LatLng(51.530017, -0.120858);
        
        var mapOptions = {
          center: myLatlng,
          zoom: 16,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
		
        var map = new google.maps.Map(document.getElementById("map"),
            mapOptions);
        
        //Marker + infowindow + angularjs compiled ng-click
        var contentString = "<div><a ng-click='clickTest()'>Click me!</a></div>";
        var compiled = $compile(contentString)($scope);

        var infowindow = new google.maps.InfoWindow({
          content: compiled[0]
        });

        var marker = new google.maps.Marker({
          position: myLatlng,
          map: map,
          title: 'Uluru (Ayers Rock)'
        });

		/*
        google.maps.event.addListener(marker, 'click', function() {
          infowindow.open(map,marker);
        });
		*/

        $scope.map = map;
      };
	  
	  
      google.maps.event.addDomListener(window, 'load', initialize);
      
      $scope.centerOnMe = function() {
        if(!$scope.map) {
          return;
        }

        $scope.loading = $ionicLoading.show({
          content: 'Getting current location...',
          showBackdrop: false
        });

        navigator.geolocation.getCurrentPosition(function(pos) {
          $scope.map.setCenter(new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude));
          $ionicLoading.hide();
        }, function(error) {
          alert('Unable to get location: ' + error.message);
        });
      };
      
      $scope.clickTest = function() {
        alert('Example of infowindow with ng-click')
      };
	  
	  initialize();
      
})
	
	



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

