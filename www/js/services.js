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
});