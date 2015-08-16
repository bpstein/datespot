/**********************************************************************
 *
 *	script file		: controllers.js
 *	
 *	begin			: 1 August 2015
 *	copyright		: Ben Stein and Grant Bartlett
 *  description		: 
 *
 **********************************************************************/
 
 
angular.module('datespot.controllers', ['ionic', 'datespot.userservices', 'datespot.jsonservices'])

/*
 * 	Controller for the FILTER page
 */
.controller('FilterCtrl', function($scope, User) {

  $scope.runFilter = function (bool) { 	
		// To be expanded and perform the jSON query when
		// the user has changed the search parameters
		console.log('Runfilter clicked!'); 
  }

  $scope.datePickerCallback = function (val) {
    if(typeof(val)==='undefined'){      
        console.log('Date not selected');
    }
    else{
        console.log('Selected date is : ', val);
    }
};  

}) // end of FilterCtrl Controller Definition

/*
 *	Controller for the DISCOVER page
 */

// .controller('ScrollCtrl', ['$scope', function($scope) {
//   $scope.data = {
//     isLoading: false
//   };
// }]);

.controller('DiscoverCtrl', function($scope, $timeout, User, Recommendations, FactoryFuck) {
	
	// Test the factory here. 
	FactoryFuck.Scrot();
	
	

  // our first three DateSpots
  /*
  $scope.spots = [
     {
        "name":"WC Clapham | Wine & Charcuterie",
        "address":"Clapham Common South Side, London SW4 7AA",
        "image_url":"http://www.we-heart.com/upload-images/wcclapham2.jpg"
     }
  ];	
  */
  
  $scope.spots = [
     {
          
     }
  ];  
  


	// Get our recommended venues
	Recommendations.getVenues()
		.then(function(){
			
		  $scope.currentSpot = Recommendations.queue[0];
		  
		  console.log($scope.currentSpot);
	});

	
  // fired when we favorite / skip a date spot.
  $scope.sendFeedback = function (bool) {
    // first, add to favorites if they favorited
    if (bool) User.addSpotToFavorites($scope.currentSpot);
  	$scope.currentSpot.rated = bool;
  	$scope.currentSpot.hide = true;

    // Drop the current venue from the results list and load the next one.
	Recommendations.nextVenue();

    $timeout(function() {
      // $timeout to allow animation to complete
      $scope.currentSpot = Recommendations.queue[0];
	  
	  console.log('Loading Venue: ');
	  console.log( $scope.currentSpot );
	  
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
 *	Controller for the FAVOURITES page
 */
.controller('FavoritesCtrl', function($scope, User) {
  // get the list of our favorites from the user service
  $scope.favorites = User.favorites;

  $scope.removeSpot = function(spot, index) {
    User.removeSpotFromFavorites(spot, index);
  }
})

// Controller to manage the datespot shortlist
// .controller('ListCtrl', function($scope) {
//   $scope.spots = [
//     {
//       name: "Sexy Venue",
//       description: "this is a sweet venue"
//     },

//     {
//       name: "Seductive Spot",
//       description: "ideal to impress"
//     }
//   ];
// })


/*
Controller for our tab bar
*/
.controller('TabsCtrl', function($scope, User) {
  $scope.enteringFavorites = function() {
    User.newFavorites = 0;
  }
  
  $scope.favCount = User.favoriteCount;
  
});