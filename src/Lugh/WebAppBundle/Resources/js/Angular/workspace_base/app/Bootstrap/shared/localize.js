// Generated by CoffeeScript 1.7.1
(function() {
  'use strict';
  angular.module('app.localization', []).factory('localize', [
    '$http', '$rootScope', '$window', function($http, $rootScope, $window) {
      var localize;
      localize = {
        language: '',
        url: void 0,
        resourceFileLoaded: false,
        successCallback: function(data) {
          localize.dictionary = data;
          localize.resourceFileLoaded = true;
          return $rootScope.$broadcast('localizeResourcesUpdated');
        },
        setLanguage: function(value) {
          localize.language = value.toLowerCase().split("-")[0];
          return localize.initLocalizedResources();
        },
        setUrl: function(value) {
          localize.url = value;
          return localize.initLocalizedResources();
        },
        buildUrl: function() {
          if (!localize.language) {
            localize.language = ($window.navigator.userLanguage || $window.navigator.language).toLowerCase();
            localize.language = localize.language.split("-")[0];
          }
          return 'i18n/resources-locale_' + localize.language + '.js';
        },
        initLocalizedResources: function() {
          var url;
          url = localize.url || localize.buildUrl();
          return $http({
            method: "GET",
            url: url,
            cache: false
          }).success(localize.successCallback).error(function() {
            return $rootScope.$broadcast('localizeResourcesUpdated');
          });
        },
        getLocalizedString: function(value) {
          var result, valueLowerCase;
          result = void 0;
          if (localize.dictionary && value) {
            valueLowerCase = value.toLowerCase();
            if (localize.dictionary[valueLowerCase] === '') {
              result = value;
            } else {
              result = localize.dictionary[valueLowerCase];
            }
          } else {
            result = value;
          }
          return result;
        }
      };
      return localize;
    }
  ]).directive('i18n', [
    'localize', function(localize) {
      var i18nDirective;
      i18nDirective = {
        restrict: "EA",
        updateText: function(ele, input, placeholder, value, translateValues) {
          var result;
          result = void 0;
          var tValues = null;
          if (typeof translateValues != "undefined") {
              tValues = JSON.parse(JSON.stringify(eval("(" + translateValues + ")")));
          }
          if (input === 'i18n-placeholder') {
            result = localize.getLocalizedString(placeholder);
          } 
          else if (input === 'i18n-value') {
            result = localize.getLocalizedString(value);
          }
          else if (input.length >= 1) {
            result = localize.getLocalizedString(input);
          }
          for (var translateKey in tValues)
          {
              result = result.replace("{" + translateKey + "}", tValues[translateKey]);
          }
          
          if (input === 'i18n-placeholder') {
            return ele.attr('placeholder', result);
          } 
          else if (input === 'i18n-value') {
            return ele.attr('value', result);
          }
          else if (input.length >= 1) {
            return ele.text(result);
          }
        },
        link: function(scope, ele, attrs) {
          scope.$on('localizeResourcesUpdated', function() {
            return i18nDirective.updateText(ele, attrs.i18n, attrs.placeholder, attrs.value, attrs.translateValues);
          });
          return attrs.$observe('i18n', function(value) {
            return i18nDirective.updateText(ele, value, attrs.placeholder, attrs.value, attrs.translateValues);
          });
        }
      };
      return i18nDirective;
    }
  ]).controller('LangCtrl', [
    '$scope', 'localize', function($scope, localize) {
      $scope.lang = 'Castellano';
      $scope.setLang = function(lang) {
        switch (lang) {
          case 'English':
            localize.setLanguage('EN-US');
            break;
          case 'Castellano':
            localize.setLanguage('ES-ES');
            break;
          case 'Catalan':
            localize.setLanguage('CA-CA');
            break;
          case 'Gallego':
            localize.setLanguage('GA-GA');
            break;
        }
        return $scope.lang = lang;
      };
      return $scope.getFlag = function() {
        var lang;
        lang = $scope.lang;
        return lang;
      };
    }
  ]);

}).call(this);
