angular.module('songhop.services', [])
.factory('User', function() {

  var o = {
    favorites: [],
    newFavorites: 0
  }

  o.addSongToFavorites = function(song) {
    // make sure there's a song to add
    if (!song) return false;

    // add to favorites array
    o.favorites.unshift(song);
    o.newFavorites++;
  }

  o.removeSongFromFavorites = function(song, index) {
    // make sure there's a song to add
    if (!song) return false;

    // add to favorites array
    o.favorites.splice(index, 1);
  }

  o.favoriteCount = function() {
  	return o.newFavorites;
  }


  return o;
});