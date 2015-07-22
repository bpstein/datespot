angular.module('datespot.controllers', ['ionic', 'datespot.services'])

/*
Controller for the filter page
*/

.controller('FilterCtrl', function($scope, User) {

  
})

/******
ENTER FILTER FUNCTIONALITY HERE
******/


/*
Controller for the discover page
*/
.controller('DiscoverCtrl', function($scope, $timeout, User, Reccomendations) {
	

   // get our first songs
  Recommendations.getNextVenues()
    .then(function(){
      $scope.currentSong = Recommendations.queue[0];
    });
  
  
  	
/*
  // our first three DateSpots
  $scope.spots = [
     {
        "title":"WC Clapham | Wine & Charcuterie",
        "artist":"Clapham Common South Side, London SW4 7AA",
        "image_small":"http://www.we-heart.com/upload-images/wcclapham2.jpg",
        "image_large":"http://www.we-heart.com/upload-images/wcclapham2.jpg"
     },
     {
        "title":"Duck & Waffle",
        "artist":"Heron Tower, 110 Bishopsgate, London EC2N 4AY",
        "image_small":"http://cdni.condenast.co.uk/646x430/d_f/DuckandWaffle01_V_28Nov12_pr_b_646x430.jpg",
        "image_large":"http://cdni.condenast.co.uk/646x430/d_f/DuckandWaffle01_V_28Nov12_pr_b_646x430.jpg"
     },
     {
        "title":"214 Bermondsey",
        "artist":"214 Bermondsey St SE1 3TQ",
        "image_small":"http://www.tastingbritain.co.uk/wp-content/uploads/2014/08/DSC5762.jpg",
        "image_large":"http://www.tastingbritain.co.uk/wp-content/uploads/2014/08/DSC5762.jpg"
      }
  ];
  
*/
  
  

  // initialize the current date spot
  $scope.currentSpot = angular.copy($scope.spots[0]);

  // fired when we favorite / skip a date spot.
  $scope.sendFeedback = function (bool) {
    // first, add to favorites if they favorited
    if (bool) User.addSpotToFavorites($scope.currentSpot);
  	$scope.currentSpot.rated = bool;
  	$scope.currentSpot.hide = true;

  	
  	$timeout(function() {
	  	// set the current date spot to one of our three date spots
	    var randomSpot = Math.round(Math.random() * ($scope.spots.length - 1));

	    // update current date spot in scope
	    $scope.currentSpot = angular.copy($scope.spots[randomSpot]);
	  }, 250);
  }

  $scope.spotDestroyed = function(index) {
    $scope.spots.splice(index, 1);
  };

  $scope.spotSwiped = function(index) {
    var newSpot = // new spot data
    $scope.spot.push(newSpot);
  };
  
})


/*
Controller for the favorites page
*/
.controller('FavoritesCtrl', function($scope, User) {
  // get the list of our favorites from the user service
  $scope.favorites = User.favorites;

  $scope.removeSpot = function(spot, index) {
    User.removeSpotFromFavorites(spot, index);
  }

})


/*
Controller for our tab bar
*/
.controller('TabsCtrl', function($scope, User) {
  $scope.enteringFavorites = function() {
    User.newFavorites = 0;
  }

  $scope.favCount = User.favoriteCount;
});