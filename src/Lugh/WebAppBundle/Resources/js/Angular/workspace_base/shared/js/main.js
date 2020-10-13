// Generated by CoffeeScript 1.7.1
(function() {
  'use strict';
  angular.module('main.sharedControllers', []).controller('AppCtrl', [
    '$scope', '$location', 'localize', '$localStorage', 'paramRequest', 
    function($scope, $location, localize, $localStorage, paramRequest) {
      $scope.isSpecificPage = function() {
        var path;
        path = $location.path();
        return _.contains(['/404','/down', '/pages/500', ], path);
      };
      $scope.isNoNavPage = function() {
        var path;
        path = $location.path();
        return _.contains(['/dashboard', '/app/voto/voto', '/app/av/voto', '/app/profile', '/app/resetPassword',
            'app/inicio','app/registro' ,'app/contactar' ,'app/requisitos' ,
            '/app/avisoLegal','/app/retornar','/app/pendiente', '/app/header/notificaciones', 
            '/app/header/mail/mail','/app/registroCertificado','/app/forgot',
            '/app/voto/retornar', '/app/foro/retornar', '/app/derecho/retornar', '/app/av/retornar'], path);
      };
      switch (SLocale) {
          case 'en_gb':
            localize.setLanguage('EN-US');
            break;
          case 'es_es':
            localize.setLanguage('ES-ES');
            break;
          case 'ca_es':
            localize.setLanguage('CA-CA');
            break;
          case 'gl_es':
            localize.setLanguage('GA-GA');
            break;
        }
      return $scope.main = {
        brand: $localStorage['Config.customer.title'] === undefined ? '' : $localStorage['Config.customer.title'],
        name: 'Usuario'
      };
    }
  ]).controller('HeaderCtrl', ['$scope', 'AppService', '$localStorage', 
      function($scope, AppService, $localStorage){
        $scope.doLogout = function()
        {
            AppService.doLogout();
        }
        $scope.brand = function() {
                return $localStorage['Config.customer.title'] === undefined ? '' : $localStorage['Config.customer.title'];
        };
}]);


}).call(this);
