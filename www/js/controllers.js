angular.module('datespot.controllers', ['ionic', 'datespot.factories', 'ionic.contrib.ui.tinderCards']  )

// Occasion / Situation Search Choice / Listing
.controller('SearchCtrl', function($scope) {

	 console.log('Loaded the SearchCtrl controller');

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

}) // end SearchCtrl


.controller('DiscoverCtrl', function($scope, $state, $stateParams, $ionicLoading, $cordovaGeolocation, $ionicPopup, SessionManager, SearchQuery, User) {
/*************** CONTROLLER FOR THE DISCOVER/SWIPE VIEW ***************/

	console.log('Loaded the DiscoverCtrl controller');
	/*
	if ($stateParams.occasion.)
	{
		console.log('Asjdhalkfgafgsjdfgksjafjsagfjsagfksafgadskj');
	}
	*/
	var server_limit	= 10; //maximum number of results the server will return in any one time.
	var result_count	= 0;
	var query_offset 	= 0;
	
	// The cards for the template
	$scope.cards = [];
	
	// Show the loading screen
	$ionicLoading.show({ templateUrl: 'templates/loading.html' });
	
	
    // onSuccess Callback - This method accepts a Position object, which contains the current GPS coordinates
	function onGPSLockSuccess(position) { SessionManager.setLocation(position); }
	
    // onError Callback - Say something to the console.
    function onGPSLockError(error) 		{ console.log('ERROR OBTAINING LOCATION'); console.log('code: ' + error.code + '\n' + 'message: ' + error.message + '\n'); }
	
	
	// An alert dialog for debug purposes only
	$scope.showAlert = function() 
	{
	   var alertPopup = $ionicPopup.alert({
		 title: 'No venues were found!',
		 template: 'Please adjust your search preferences.'
	   });
	   alertPopup.then(function(res) {
		 console.log('Thank you for not eating my delicious ice cream cone');
		 $state.go('search');
	   });
	};


	// Now get the location asynchronously
	// Another new JavaScript development I have no idea about....
	// Promises? http://www.html5rocks.com/en/tutorials/es6/promises/
	$cordovaGeolocation.getCurrentPosition().then(onGPSLockSuccess, onGPSLockError).then(function() 
	{
		// Once we have GPS lock, get the closest venues and build the cards
		SearchQuery.performQuery($stateParams.occasion, query_offset).then(function() // query_offset ought to be zero here
		{
			if ( SearchQuery.results.length == 0) { $scope.showAlert(); }
			else
			{
				for (var i = 0; i < SearchQuery.results.length; i++) { $scope.addCard(SearchQuery.results[i]); }
			
				console.log('Finished adding cards');	
			}
			
			   // Hide the loading
			   $ionicLoading.hide();
			   
		  }); // End SearchQuery  
	}); // End get geolocation and then cards
	

	// Add a card to the stack, generally from search results
	$scope.addCard = function(data) 
	{
		result_count++;
		$scope.cards.unshift(angular.extend({}, data));
	}



	// Remove a card from the stack/view
	$scope.cardDestroyed = function(index) 
	{
		
		$scope.cards.splice(index, 1);
		console.log('Card was destroyed: ' + index + ', and that last card is now: ' + $scope.cards.length);
		
		// low on the queue? lets fill it up, but only if we got at least server_limit results in the first place!
		if ( ($scope.cards.length == 0) && (result_count == server_limit) ) // Get the next  
		{
			// Show the loading (again)
			$ionicLoading.show();
			
			//console.log('cards length' + $scope.cards.length);
			//console.log('results count' + result_count);
			//console.log('server_limit count' + server_limit);
			
			console.log('Getting more results from server.....');
			
			// Perform a server query (again, with offset updated)
			query_offset = query_offset+server_limit; // start at the previous offset + server_limit
			SearchQuery.performQuery($stateParams.occasion, query_offset).then(function() 
			{
				if ( SearchQuery.results.length == 0) { $scope.showAlert(); }
				else
				{
					for (var i = 0; i < SearchQuery.results.length; i++) { $scope.addCard(SearchQuery.results[i]); }
					console.log('Finished adding next load of cardscards');	
				}
				
				   // Hide the loading
				   $ionicLoading.hide();   
			  }); // End SearchQuery  		  
		} // end queue check
	} // end cardDestroyed

	
	// Spot Rejected
	$scope.cardSwipedLeft = function(index) {
		console.log('LEFT SWIPE');   
	}

	// Spot Liked
	$scope.cardSwipedRight = function(index) {
		console.log('RIGHT SWIPE');
		User.addSpotToShortlist($scope.cards[index]);   /// TODO: WORK ON THIS TO USE CACHE ETC...
	}


	// Spot Clicked, we can't use a href at this stage for
	// some reason so we need to use the ionic state director
	// and pass the appropriate parameters
	$scope.showDetails = function(vuid) 
	{
		console.log('Click request for card of vuid:' + vuid);

		// use the vuid as the key to viewing the venue
		// http://learn.ionicframework.com/formulas/sharing-data-between-views/
		// http://forum.ionicframework.com/t/pass-data-with-state-go/2897/2
		$state.go('detail', {vuid:  vuid });	
		
	}
})



// CONTROLLER FOR SHORTLIST VIEW (PREVIOUSLY FAVORITES)
.controller('ShortlistCtrl', function($scope, $cordovaGeolocation, SessionManager, User) {
	
	console.log('Loaded the ShortlistCtrl controller');

	// Get the GPS position when we do this 
	$cordovaGeolocation.getCurrentPosition().then(function(position) 
	{
		SessionManager.setLocation(position); 
		
	}); // End get geolocation and then cards
	

	// With the new view caching in Ionic, Controllers are only called
	// when they are recreated or on app start, instead of every page change.
	// To listen for when this page is active (for example, to refresh data),
	// listen for the $ionicView.enter event:
	//
	//$scope.$on('$ionicView.enter', function(e) {
	//});
	// 
	// Use the above to re-calibrate GPS

	$scope.shortlist = User.getShortlist();

	// Remove a spot
	$scope.removeSpot = function(spot, index) 
	{
		User.removeSpotFromShortlist(spot, index);
	}

})


// CONTROLLER FOR DETAILS PAGE
.controller('DetailCtrl', function($scope, $stateParams, SessionManager, CacheService) {

	console.log('Loaded the DetailCtrl controller');
	
	var vuid = $stateParams.vuid;
	console.log('The received Venue Unique ID (vuid) is:' + vuid + '. Checking cache to see if this key exists');
	
	var details = CacheService.get(vuid);
	if ( details !== undefined)
	{
		$scope.spot = details;
		console.log ('Found it in the cache!');
		
		$scope.spot.distance = SessionManager.calculateDistance(details.latitude, details.longitude);
	}
	else
	{
		console.log ('DID NOT FIND IT IN THE CACHE!?');
		
		$state.go('search');
	}	

})




/* Map Controller */
.controller('MapController', function($scope, $stateParams, $ionicLoading, $compile, CacheService) {
	
	 console.log('Loading Map Controller.');
	 
		var vuid = $stateParams.vuid;
		console.log('The received Venue Unique ID (vuid) is:' + vuid + '. Checking cache to see if this key exists');
		
		var details = CacheService.get(vuid);
			
		function initialize() {
		  
			  
			/// HACK: Need to change this to a value of the actual location / event.
			var myLatlng = new google.maps.LatLng(51.530017, -0.120858);
			
			if ( details !== undefined)
			{
				console.log ('Found it in the cache!');
				myLatlng = new google.maps.LatLng(details.latitude, details.longitude);
			}
			

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

