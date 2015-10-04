
angular.module('ngCordova', [
  'ngCordova.plugins'
]);
angular.module('ngCordova.plugins', [	 'geolocation',	 'googleMap']);//#### Begin Individual Plugin Code ####// install   :   cordova -d plugin add https://github.com/Wizcorp/phonegap-facebook-plugin.git --variable APP_ID="123456789" --variable APP_NAME="myApplication"
// link      :   https://github.com/Wizcorp/phonegap-facebook-plugin

/* globals facebookConnectPlugin: true */
angular.module('ngCordova.plugins.facebook', [])

  .provider('$cordovaFacebook', [function () {

    /**
      * Init browser settings for Facebook plugin
      *
      * @param {number} id
      * @param {string} version
      */
    this.browserInit = function (id, version) {
      this.appID = id;
      this.appVersion = version || 'v2.0';
      facebookConnectPlugin.browserInit(this.appID, this.appVersion);
    };

    this.$get = ['$q', function ($q) {
      return {
        login: function (permissions) {
          var q = $q.defer();
          facebookConnectPlugin.login(permissions, function (res) {
            q.resolve(res);
          }, function (res) {
            q.reject(res);
          });

          return q.promise;
        },

        showDialog: function (options) {
          var q = $q.defer();
          facebookConnectPlugin.showDialog(options, function (res) {
            q.resolve(res);
          }, function (err) {
            q.reject(err);
          });
          return q.promise;
        },

        api: function (path, permissions) {
          var q = $q.defer();
          facebookConnectPlugin.api(path, permissions, function (res) {
            q.resolve(res);
          }, function (err) {
            q.reject(err);
          });
          return q.promise;
        },

        getAccessToken: function () {
          var q = $q.defer();
          facebookConnectPlugin.getAccessToken(function (res) {
            q.resolve(res);
          }, function (err) {
            q.reject(err);
          });
          return q.promise;
        },

        getLoginStatus: function () {
          var q = $q.defer();
          facebookConnectPlugin.getLoginStatus(function (res) {
            q.resolve(res);
          }, function (err) {
            q.reject(err);
          });
          return q.promise;
        },

        logout: function () {
          var q = $q.defer();
          facebookConnectPlugin.logout(function (res) {
            q.resolve(res);
          }, function (err) {
            q.reject(err);
          });
          return q.promise;
        }
      };
    }];
  }]);
// install   :     cordova plugin add cordova-plugin-geolocation
// link      :     https://github.com/apache/cordova-plugin-geolocation

angular.module('ngCordova.plugins.geolocation', [])

  .factory('$cordovaGeolocation', ['$q', function ($q) {

    return {
      getCurrentPosition: function (options) {
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
// install   :
// link      :

// Google Maps needs ALOT of work!
// Not for production use

angular.module('ngCordova.plugins.googleMap', [])

  .factory('$cordovaGoogleMap', ['$q', '$window', function ($q, $window) {

    var map = null;

    return {
      getMap: function (options) {
        var q = $q.defer();

        if (!$window.plugin.google.maps) {
          q.reject(null);
        } else {
          var div = document.getElementById('map_canvas');
          map = $window.plugin.google.maps.Map.getMap(options);
          map.setDiv(div);
          q.resolve(map);
        }
        return q.promise;
      },

      isMapLoaded: function () { // check if an instance of the map exists
        return !!map;
      },
      addMarker: function (markerOptions) { // add a marker to the map with given markerOptions
        var q = $q.defer();
        map.addMarker(markerOptions, function (marker) {
          q.resolve(marker);
        });

        return q.promise;
      },
      getMapTypeIds: function () {
        return $window.plugin.google.maps.mapTypeId;
      },
      setVisible: function (isVisible) {
        var q = $q.defer();
        map.setVisible(isVisible);
        return q.promise;
      },
      // I don't know how to deallocate te map and the google map plugin.
      cleanup: function () {
        map = null;
        // delete map;
      }
    };
  }]);
