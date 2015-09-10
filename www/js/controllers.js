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

// CONTROLLER FOR TABS NAVIGATION AND SHORTLIST COUNTER
.controller('TabsCtrl', function($scope, User) {
  $scope.enteringShortlist = function() {
    User.newShortlist = 0;
  }

  $scope.shortlistCount = User.shortlistCount;
})

// CONTROLLER FOR NAVIGATION BUTTONS
.controller('ButtonCtrl', function($scope, User) {
  $scope.rightButtons = [{
    type: 'button-clear',
    content: 'Shortlist',
    tap: function(e) {}
  }];
})

// CONTROLLER FOR THE SEARCH VIEW
.controller('SearchCtrl', function($scope, User) {
  $scope.runFilter = function (bool) {  
    // To be expanded and perform the jSON query when
    // the user has changed the search parameters
    console.log('Runfilter clicked!'); 

    $scope.save = function() {
      $state.go('shortlist');
    };
  }

})

// CONTROLLER FOR THE DISCOVER/SWIPE VIEW
.controller('DiscoverCtrl', function($scope, $timeout, $ionicLoading, User, Recommendations, FactoryFuck, TDCardDelegate) {
	
  // Loading screen while app pulls data form server
  var showLoading = function() {
    $ionicLoading.show({
      template: '<i class="ion-spinner">Seducing...</i>',
      noBackdrop: true
    });
  }

  var hideLoading = function() {
    $ionicLoading.hide();
  }

  showLoading();

	// Test the factory here. 
	FactoryFuck.Scrot();
  
  $scope.spots = [
     {
          
     }
  ];  
  
	// Get our recommended venues
	Recommendations.getVenues()
		.then(function(){
			
		  $scope.currentSpot = Recommendations.queue[0];
		  
		  console.log($scope.currentSpot);
	})
  .then(function(){
    // turn loading off
    hideLoading();
    $scope.currentSpot.loaded = true;
  });

	
  // fired when we favorite / skip a date spot.
  $scope.sendFeedback = function (bool) {
    // first, add to favorites if they favorited
    if (bool) User.addSpotToShortlist($scope.currentSpot);
  	$scope.currentSpot.rated = bool;
  	$scope.currentSpot.hide = true;
    // $scope.addCard();

    // Drop the current venue from the results list and load the next one.
	Recommendations.nextVenue();

    $timeout(function() {
      // $timeout to allow animation to complete
      $scope.currentSpot = Recommendations.queue[0];
      $scope.currentSpot.loaded = false;
	  
	  console.log('Loading Venue: ');
	  console.log( $scope.currentSpot );
	  
    }, 250);

  }

  $scope.spotDestroyed = function(index) {
    $scope.spots.splice(index, 1);
  };

  $scope.spotSwiped = function(index) {
    console.log('sweetswipe');
    var newSpot = // new spot data
    $scope.spots.push(newSpot);
    
    $scope.currentSpot.rated = true;
  };

  $scope.spotSwipedLeft = function(index) {
    console.log('LEFT SWIPE');
    //$scope.spots[index].rated = false;
    $scope.sendFeedback(false);
    //$scope.spotSwiped();
  };
  $scope.spotSwipedRight = function(index) {
    console.log('RIGHT SWIPE');
    //$scope.spotSwiped();
    $scope.sendFeedback(true);
  };
})

// CONTROLLER FOR SHORTLIST VIEW (PREVIOUSLY FAVORITES)
.controller('ShortlistCtrl', function($scope, User) {
  // With the new view caching in Ionic, Controllers are only called
  // when they are recreated or on app start, instead of every page change.
  // To listen for when this page is active (for example, to refresh data),
  // listen for the $ionicView.enter event:
  //
  //$scope.$on('$ionicView.enter', function(e) {
  //});
  $scope.shortlist = User.shortlist;

  // $scope.spots = Spots.all();
  // $scope.remove = function(spot) {
  //   Spots.remove(spot);
  // };

  $scope.removeSpot = function(spot, index) {
    User.removeSpotFromShortlist(spot, index);
  }
})

// CONTROLLER FOR DETAILS PAGE
.controller('DetailCtrl', function($scope, Spots) {
  $scope.spot = Spots.get;
})

// CONTROLLER FOR BACK BUTTON
// .controller('BackCtrl', function($scope, $ionicHistory) {
//   $scope.myGoBack = function () {
//     $ionicHistory.goBack();
//   };
// })

// CONTROLLER FOR LISTING OF OCCASION TYPES ON SEACH VIEW
.controller('OccasionCtrl', function($scope, $ionicPopup) {
  $scope.items = [{
      name: "First Date",
      id: 1,
      tag: "She said 'yes.' Choose a place that'll impress.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/firstdate2.jpg"
  }, {
      name: "Just drinks",
      id: 2,
      tag: "Hip spots to grab a drink with a date or friend.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/justdrinks.jpg"
  },
     {
      name: "Dinner date",
      id: 3,
      tag: "Suave restaurants, tasty treats and cheap eats.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/dinnerdate.jpg"
  },
    {
      name: "Brunch or coffee",
      id: 4,
      tag: "Casual coffee or brunch ideas.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/M1LK-Sweetcorn-fritters-bacon-862x575.jpg"
    },

    {
      name: "Fun with friends",
      id: 5,
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "img/funwithfriends.jpg"
    },

    {
      name: "Get weird",
      id: 6,
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "img/letsgetweird.png"
    },

    {
      name: "Go all out",
      id: 7,
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "https://s3-us-west-1.amazonaws.com/datespot/occasions/goallout.jpg"
    },
    {
      name: "Something sunny",
      id: 8,
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "/img/somethingsunny.jpg"
    },
    {
      name: "Cheap eats",
      id: 9,
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "/img/cheapeats.png"
    },
    {
      name: "Hen night",
      id: 10,
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "/img/hennight.jpg"
    },
    {
      name: "stag night",
      id: 11,
      tag: "Something informal with friends in pubs, bars or clubs.",
      url: "/img/stagnight.jpeg"
    }];
});
