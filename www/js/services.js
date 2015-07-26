angular.module('datespot.services', [])
.factory('User', function() {

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
}

// Pull the next venue recommendations from the server
// .factory('Recommendations', function($http, SERVER) {
//   var o = {
//     queue: []
//   };
  
//   return o;
// })

);

angular.module('datespot.jsonservices', [])
.factory('Recommendations', function($http, SERVER) {

  var o = {
    queue: [],
    newFavorites: 0
  }

  o.getNextSongs = function() {
    return $http({
      method: 'GET',
      url: SERVER.url + '/recommendations'
    }).success(function(data){
      // merge data into the queue
      o.queue = o.queue.concat(data);
    });
  }

  o.nextSong = function() {
    // pop the index 0 off
    o.queue.shift();

    // low on the queue? lets fill it up
    if (o.queue.length <= 3) {
      o.getNextSongs();
    }
  }
});







//  o.getNextVenues = function() {
//     return $http({
//       method: 'GET',
//       url: SERVER.url + '/testclient.php'
//     }).success(function(data){

//       alert('hello');
//       // merge data into the queue
//       o.queue = o.queue.concat(data);
//     });
//  }

