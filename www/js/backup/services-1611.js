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
    shortlist: [],
    newShortlist: 0
  }

  o.addSpotToShortlist = function(spot) {
	  
	console.log("Adding Spot to Shortlist: " + spot);
	
    // make sure there's a date spot to add
    if (!spot) return false;

    // add to shortlist array
    o.shortlist.unshift(spot);
    o.newShortlist++;
  }

  o.removeSpotFromShortlist = function(spot, index) {
    // make sure there's a date spot to add
    if (!spot) return false;

    // add to shortlist array
    o.shortlist.splice(index, 1);
  }

  o.shortlistCount = function() {
  	return o.newShortlist;
  }
  return o;
  
}) // notice the termination here, as we're terminating the factory ONLY

/*
.factory('Spots', function() {
	
	console.log('Loaded the Spots Factory');
	  
	var spots = [{

	}];

	return {
		all: function() {
			return spots;
		},
		remove: function(spot) {
			spots.splice(spots.indexOf(spot), 1);
		},
		get: function(spotVuid) {
			for (var i = 0; i < spots.length; i++) {
				
				console.log(spots[i].vuid);
				
				if (spots[i].vuid === parseInt(spotVuid)) {
					return spots[i];
				}
			}
			return null;
		}
	};
});
*/


/* DateSpot Angular Module *******************************************
 * 
 * 	name 	:	datespot.jsonservices
 *  purpose	:   Datespot JSON Services, contains the following
 * 				factories: Search
 **********************************************************************/
 
angular.module('datespot.jsonservices', [])
.factory('Search', function($http, SERVER, $localstorage) {

  // http://learn.ionicframework.com/formulas/data-the-right-way/
  // http://mcgivery.com/ionic-using-factories-and-web-services-for-dynamic-data/

  console.log('Loaded the Search Factory');	
  console.log('The Server Address is at: ' + SERVER.url);
  
  // We need to insure we are always returing a value from a factory definition
  var o = {
    results: [],
	lat: null,
	lon: null,
	offset: 0
  }
  
	// Do we have a token
	var token = $localstorage.get('dstoken');

   // Function: getVenues
   o.getResults = function(scenarioid, lat, lon, offset) {
	  
	//var url = SERVER.url + '/client.json.php?a=all';
	var url = SERVER.url + '/client.json.php?ver=' + SERVER.clientversion + '&sid=' + scenarioid +'&originLat=' + lat + '&originLong=' + lon + '&o=' + offset + '&token=' + token;
	console.log('Server query: ' + url);
	
    return $http({
      method: 'GET',
      url: url
    }).success(function(data)
	{
		if ( data.success == true)
		{
			console.log('We got ' + data.queryresults.length + ' results from the server!');
			
			// Persis these
			o.lat 		= lat;
			o.lon 		= lon;
			o.offset 	= offset;
			
			$localstorage.set('dstoken', data.token);
			token = data.token;
			
						
			  // Iterate through the results
			  for(var j= 0; j < data.queryresults.length; j++)
			  {
					var found = false;
					var dataPoint = data.queryresults[j];
					
					// ignore any dupes
					for(var i= 0; i<o.results.length; i++)
					{
						if(o.results[i].vuid === dataPoint.vuid){
							found = true;
						}
					}
					
					if(!found){
						console.log('Pushing result element from server onto search results queue array.');
						o.results.push(dataPoint);
					}
			  } // loop through results			  
		 } // end succcess
			  
    });
	
  } // end getVenues

  
  // Function: Next Venue -> Need to persist the start point, lat and long from the server.
  // http://learn.ionicframework.com/formulas/infinite-lists/
  o.dropResult = function() 
  {
    // pop the index 0 off
    o.results.shift();

  } // nextVenue
  
  
  o.getVenueByVUID = function(vuid)
  {
		for (var i = 0; i < o.results.length; i++) 
		{
			if (o.results[i].vuid === vuid) 
			{
				console.log('Found a match to vuid '+ vuid + '! Happy days.');
				return o.results[i];
			}
		}
		
		return null;  
		
  } // end getVenue
  
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




// install   :     cordova plugin add cordova-plugin-geolocation
// link      :     https://github.com/apache/cordova-plugin-geolocation
angular.module('ngCordova.plugins.geolocation', [])

  .factory('$cordovaGeolocation', ['$q', function ($q) {

    return {
      getCurrentPosition: function (options) {
		  
		console.log('Executing getCurrentPosition');

        var q = $q.defer();

        navigator.geolocation.getCurrentPosition(function (result) {
          q.resolve(result);
        }, function (err) {
          q.reject(err);
        }, options);

        return q.promise;
      },

      watchPosition: function (options) {
        var q = $q.defer();

        var watchID = navigator.geolocation.watchPosition(function (result) {
          q.notify(result);
        }, function (err) {
          q.reject(err);
        }, options);

        q.promise.cancel = function () {
          navigator.geolocation.clearWatch(watchID);
        };

        q.promise.clearWatch = function (id) {
          navigator.geolocation.clearWatch(id || watchID);
        };

        q.promise.watchID = watchID;

        return q.promise;
      },

      clearWatch: function (watchID) {
        return navigator.geolocation.clearWatch(watchID);
      }
    };
  }]);
