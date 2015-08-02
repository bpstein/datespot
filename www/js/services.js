/**********************************************************************
 *
 *	script file		: services.js
 *	
 *	begin			: 1 August 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *
 **********************************************************************/

 
/* DateSpot Angular Module *******************************************
 * 
 * 	name 	:	datespot.userservices
 *  purpose	:   Datespot User Services Module, contains the following
 * 				factories: User, FactoryFuck
 **********************************************************************/

angular.module('datespot.userservices', [])
// User factory within the module
.factory('User', function() {
	
  console.log('Loaded the User Factory');

  var o = {
    favorites: [],
    newFavorites: 0
  }

  o.addSpotToFavorites = function(spot) {
    // make sure there's a date spot to add
    if (!spot) return false;

    // add to favorites array
    o.favorites.unshift(spot);
    o.newFavorites++;
  }

  o.removeSpotFromFavorites = function(spot, index) {
    // make sure there's a date spot to add
    if (!spot) return false;

    // add to favorites array
    o.favorites.splice(index, 1);
  }

  o.favoriteCount = function() {
  	return o.newFavorites;
  }
  return o;
  
}) // notice the termination here, as we're terminating the factory ONLY

// Recommendations Factory
.factory('FactoryFuck', function() {
	
    console.log('Loaded the FactoryFuck Factory');	
	
	return {
		Scrot: function(){
			
			  console.log('So we managed to load some fucking factory. It worked?');
		}
	}
		
}); // notice the termination here, as we're terminating the factory AND the module! Extra ';'




/* DateSpot Angular Module *******************************************
 * 
 * 	name 	:	datespot.jsonservices
 *  purpose	:   Datespot JSON Services, contains the following
 * 				factories: Recommendations, GetImage
 **********************************************************************/
 
angular.module('datespot.jsonservices', [])
.factory('Recommendations', function($http, SERVER) {
	
  console.log('Loaded the Recommendations Factory');	
  console.log('The Server Address is at: ' + SERVER.url);
  
  // We need to insure we are always returing a value from a factory definition
  var o = {
    queue: [],
    newFavorites: 0
  }

  // Function: Get Venues 
  o.getVenues = function() {
	  
	var url = SERVER.url + '/client.php?a=all';
	console.log('Getting venues from ' + url);
	
    return $http({
      method: 'GET',
      url: url
    }).success(function(data)
	{
		  // merge data into the queue
		o.queue = o.queue.concat(data.points); // get the array of 'points'

		// OK so we've apparently received something here, we need to loop through the results
		console.log('Looping through results...');
		for(i in data.points) 
		{	
			console.log(data.points[i]);
		}
	 	
	 // console.log(data.points); 
    });
	
  } // end getVenues

  
  // Function: Next Venue 
  o.nextVenue = function() {
    // pop the index 0 off
    o.queue.shift();

    // low on the queue? lets fill it up
    if (o.queue.length <= 3) {
     // o.getVenues(); // we don't do this as our JSON provides all venues currently
    }
  }
  
  // Return the function definitions
  return o;
  
});

/*
angular.module('datespot.jsonservices', [])
.factory('PersonService', function($http){
	var BASE_URL = "http://api.randomuser.me/";
	var items = [];
	
	return {
		GetFeed: function(){
			return $http.get(BASE_URL+'?results=10').then(function(response){
				items = response.data.results;
				return items;
			});
		},
		GetNewUsers: function(){
			return $http.get(BASE_URL+'?results=2').then(function(response){
				items = response.data.results;
				return items;
			});
		},
		GetOldUsers: function(){
			return $http.get(BASE_URL+'?results=10').then(function(response){
				items = response.data.results;
				return items;
			});
		}
	}
})

*/
