/* DateSpot Angular Module *******************************************
 * 
 * 	name 	:	datespot.jsonservices
 *  purpose	:   Datespot JSON Services, contains the following
 * 				factories: Search
 **********************************************************************/

angular.module('datespot.factories', [])


// Persisting data across controllers
.factory('SessionManager', function($localstorage) {
	
	console.log('Loaded the SessionManager Factory');	
	
	var token_name = 'dstoken';
	var o = {
		userLat: null,
		userLon: null,
	}

	o.setLocation = function (userLat,userLon) { 
		o.userLat = userLat; o.userLon = userLon;	
		console.log('Session location set to: ' + o.userLat +', '+ o.userLon );
	}
	
	o.getToken = function () 		{ return $localstorage.get(token_name); }
	o.setToken = function (token) 	{ $localstorage.set(token_name, token); }
	
	o.positionKnown = function ()
	{	
		if ((o.userLat != null) && (o.userLon != null)) { return true; } else { return false; }
	} // end position known
	
	
	/**
	 * Returns the distance from 'this' point to destination point (using haversine formula).
	 *
	 * @param   {LatLon} point - Latitude/longitude of destination point.
	 * @param   {number} [radius=6371e3] - (Mean) radius of earth (defaults to radius in metres).
	 * @returns {number} Distance between this point and destination point, in same units as radius.
	 *
	 * @example
	 *     var p1 = new LatLon(52.205, 0.119), p2 = new LatLon(48.857, 2.351);
	 *     var d = p1.distanceTo(p2); // Number(d.toPrecision(4)): 404300
	 */
	o.calculateDistance = function (Lat, Lon)
	{
		// Do we know where we are?
		if (o.positionKnown() )
		{
			var p1 = new LatLon(Lat, Lon), p2 = new LatLon(o.userLat, o.userLon);

			console.log('Our position:' + o.userLat + ', ' + o.userLon + '. PoI: ' + Lat + ', ' + Lon);
			console.log(p1);
			console.log(p2);
			
			var d = p1.distanceTo(p2); // distance in meters by default
				d = Number(d.toPrecision(1));
			
			console.log('Distance in meters: '+ d);
			return (d/1000) + ' km';			
		}
		else
		{
			return '';
		}		
	} // end calculate distance
	
	return o;
})

// nothing beats a good cache.
// https://www.phase2technology.com/blog/caching-json-arrays-using-cachefactory-in-angularjs-1-2-x/
.factory('CacheService', function($cacheFactory) {
   var cache = $cacheFactory('cacheService', {
    // capacity: 3 // optional - turns the cache into LRU cache
   });
   
   return cache;
   
})



// http://learn.ionicframework.com/formulas/data-the-right-way/
// http://mcgivery.com/ionic-using-factories-and-web-services-for-dynamic-data/
.factory('SearchQuery', function($http, SERVER, SessionManager, CacheService) {
   console.log('Loaded the SearchQuery Factory');
  
   // We need to insure we are always returning a value from a factory definition
   var o = {
		results: [],
		offset: 0
	}

   // Function: getVenues
   o.performQuery = function(scenarioid, offset) 
   {
	   
	   // Clear the results
	   o.results = [];
	  
		var url = SERVER.url + '/client.json.php?ver=' + SERVER.clientversion + '&sid=' + scenarioid +'&originLat=' + SessionManager.userLat + '&originLong=' + SessionManager.userLon + '&o=' + offset + '&token=' + SessionManager.getToken();
		console.log('Server query: ' + url);
		
		return $http({
		  method: 'GET',
		  url: url,
		  cache: true // cache the result, uses cacheFactory but differently by URL.
		}).success(function(data)
		{
			if ( data.success == true)
			{
				// 'queryresults' is the name of the element in the JSON returned from the server
				// that contains all the venues / results.
				console.log('We got ' + data.queryresults.length + ' results from the server!');
								
				  // Iterate through the results
				  for(var j= 0; j < data.queryresults.length; j++)
				  {
						var found = false;
						var dataPoint = data.queryresults[j];
						
						// ignore any dupes
						for(var i= 0; i<o.results.length; i++)
						{
							// this logic is never used given we always flush o.results.
							// Keeping it here anyway.
							if(o.results[i].vuid === dataPoint.vuid){ 
								found = true;
							}
						}
						
						if(!found){
							console.log('Pushing result element from server onto search results queue array.');
							o.results.push(dataPoint);
						}
						
					// Add the result to the cache, by venue unique id
					CacheService.put(dataPoint.vuid, dataPoint);	
	
				  } // loop through results			  
			 } // end succcess
			 
		});
	
  } // end getVenues

  
  // Function: Next Venue -> Need to persist the start point, lat and long from the server.
  // http://learn.ionicframework.com/formulas/infinite-lists/
  /*
  o.dropResult = function() 
  {
    // pop the index 0 off
    o.results.shift();

  } // nextVenue
  

  // No longer required as we have the results cache.
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
  */
  
  // Return the function definitions
  return o;
  
})



/* DateSpot Angular Module *******************************************
 * 
 * 	name 	:	datespot.userservices
 *  purpose	:   Datespot User Services Module, contains the following
 * 				factories: User, FactoryFuck
 **********************************************************************/

.factory('User', function(SessionManager, CacheService) 
{
	
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
  
}); // notice the termination here, as we're terminating the factory ONLY









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



/*
.factory('StorageManager', function ($cookieStore, $localStorage) {

    var localStoreAvailable = typeof (Storage) !== "undefined";
	
	var o = {};
	
    o.store = function (name, details) {
        if (localStoreAvailable) {
            if (angular.isUndefined(details)) {
                details = null;
            } else if (angular.isObject(details) || angular.isArray(details) || angular.isNumber(+details || details)) {
                details = angular.toJson(details);
            };
            sessionStorage.setItem(name, details);
        } else {
            $cookieStore.put(name, details);
        };
    };

    o.persist = function(name, details) {
        if (localStoreAvailable) {
            if (angular.isUndefined(details)) {
                details = null;
            } else if (angular.isObject(details) || angular.isArray(details) || angular.isNumber(+details || details)) {
                details = angular.toJson(details);
            };
            localStorage.setItem(name, details);
        } else {
            $cookieStore.put(name, details);
        }
    };

    o.get = function (name) {
        if (localStoreAvailable) {
            return getItem(name);
        } else {
            return $cookieStore.get(name);
        }
    };

    o.destroy = function (name) {
        if (localStoreAvailable) {
            localStorage.removeItem(name);
            sessionStorage.removeItem(name);
        } else {
            $cookieStore.remove(name);
        };
    };

    var getItem = function (name) {
        var data;
        var localData = localStorage.getItem(name);
        var sessionData = sessionStorage.getItem(name);

        if (sessionData) {
            data = sessionData;
        } else if (localData) {
            data = localData;
        } else {
            return null;
        }

        if (data === '[object Object]') { return null; };
        if (!data.length || data === 'null') { return null; };

        if (data.charAt(0) === "{" || data.charAt(0) === "[" || angular.isNumber(data)) {
            return angular.fromJson(data);
        };

        return data;
    };

    return o;
}]) 
*/

  
  
  
 // http://blog.thoughtram.io/angular/2015/07/07/service-vs-factory-once-and-for-all.html
 /*
  
  .service('sessionService', ['$cookieStore', function ($cookieStore) {
    var localStoreAvailable = typeof (Storage) !== "undefined";
    this.store = function (name, details) {
        if (localStoreAvailable) {
            if (angular.isUndefined(details)) {
                details = null;
            } else if (angular.isObject(details) || angular.isArray(details) || angular.isNumber(+details || details)) {
                details = angular.toJson(details);
            };
            sessionStorage.setItem(name, details);
        } else {
            $cookieStore.put(name, details);
        };
    };

    this.persist = function(name, details) {
        if (localStoreAvailable) {
            if (angular.isUndefined(details)) {
                details = null;
            } else if (angular.isObject(details) || angular.isArray(details) || angular.isNumber(+details || details)) {
                details = angular.toJson(details);
            };
            localStorage.setItem(name, details);
        } else {
            $cookieStore.put(name, details);
        }
    };

    this.get = function (name) {
        if (localStoreAvailable) {
            return getItem(name);
        } else {
            return $cookieStore.get(name);
        }
    };

    this.destroy = function (name) {
        if (localStoreAvailable) {
            localStorage.removeItem(name);
            sessionStorage.removeItem(name);
        } else {
            $cookieStore.remove(name);
        };
    };

    var getItem = function (name) {
        var data;
        var localData = localStorage.getItem(name);
        var sessionData = sessionStorage.getItem(name);

        if (sessionData) {
            data = sessionData;
        } else if (localData) {
            data = localData;
        } else {
            return null;
        }

        if (data === '[object Object]') { return null; };
        if (!data.length || data === 'null') { return null; };

        if (data.charAt(0) === "{" || data.charAt(0) === "[" || angular.isNumber(data)) {
            return angular.fromJson(data);
        };

        return data;
    };

    return this;
 }])

 */
