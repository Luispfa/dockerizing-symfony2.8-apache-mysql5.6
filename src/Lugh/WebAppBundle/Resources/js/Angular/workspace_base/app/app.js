
(function() {
  'use strict';
  
  angular.module('app', ['ngRoute', 'ngAnimate', 'ngStorage', 'ui.bootstrap', 
      'easypiechart', 'mgo-angular-wizard', 'textAngular', 'ui.tree', 
      'ngMap', 'ngTagsInput', 'app.ui.ctrls', 'app.ui.directives', 
      'app.ui.services', 'main.sharedControllers', 'app.form.validation', 
      'app.ui.form.ctrls', 'app.ui.form.directives', 'app.tables', 'app.map', 
      'app.task', 'app.localization', 'app.chart.ctrls', 'app.chart.directives', 
      'workspaceControllers','workspaceDirectives','sharedDirectives',
      'sharedControllers','sharedServices','app.services.accionistas','app.services.junta',
      'app.services.items', 'app.services.notifications', 'app.page.ctrls','angularFileUpload',
      'app.services.mails', 'app.services.steps', 'mailServices','sharedFilters', 'appServices',
      'app.services.adhesions']).config([
    '$routeProvider', function($routeProvider) {
      var template = '<div ng-include="templateUrl" class="animate-fade-up">Loading...</div>';
      return $routeProvider.when('/', {
        redirectTo: '/dashboard'
       }).when('/dashboard', {
        //templateUrl: 'views/app/dashboard.html',
        template: template,
        controller: 'DashBoardController',
        resolve: {
            apprequest: function($http, $localStorage, $q){
                return apprequestfunc($http, $localStorage, $q);
            }
        }
      }).when('/app/voto/voto', {
        //templateUrl: 'views/app/dashboard.html',
        template: template,
        controller: 'RetornateAppController'
      }).when('/app/foro/dashboard-foro', {
        //templateUrl: 'views/app/dashboard.html',
        template: template,
        controller: 'RetornateAppController'
      }).when('/app/derecho/dashboard-derecho', {
        //templateUrl: 'views/app/dashboard.html',
        template: template,
        controller: 'RetornateAppController'
      }).when('/app/av/dashboard-av', {
        //templateUrl: 'views/app/dashboard.html',
        template: template,
        controller: 'RetornateAppController'
      }).when('/404', {
        templateUrl: 'sharedviews/pages/404.html'
      }).when('/500', {
        templateUrl: 'sharedviews/pages/500.html'
      }).when('/down', {
        templateUrl: 'sharedviews/pages/down.html'
      }).when('/:route*', {
        template: template,
        controller: 'RouteController',
        resolve: {
            apprequest: function($http, $localStorage, $q){
                return apprequestfunc($http, $localStorage, $q);
            },
            routerequest: function($http, $q) {
                return routerequestfunc($http, $q);
            }
        }
      }).otherwise({
        redirectTo: '/404'
      });
    }
  ]).controller('RouteController',  function($scope, $routeParams, $location, routerequest){

            if (routerequest.indexOf('/' + $routeParams.route) > -1)
            {
                $scope.templateUrl = 'views/' + $routeParams.route + '.html' ;
            }
            else
            {
                $location.path("/404").replace();
            }
  }).controller('DashBoardController', function($scope, $localStorage, $location){

            var app = dashBoardApp($localStorage);
            if (app !== undefined)
            {
                switch(app) {
                    case 'voto':
                        $location.path("/app/voto/voto").replace();
                        break;
                    case 'foro':
                        $location.path("/app/foro/dashboard-foro").replace();
                        break;
                    case 'derecho':
                        $location.path("/app/derecho/dashboard-derecho").replace();
                        break;
                    case 'av':
                        $location.path("/app/av/dashboard-av").replace();
                        break;
                    default:
                        $scope.templateUrl = 'views/app/dashboard.html' ;
                }
            }
            else
            {
                $scope.templateUrl = 'views/app/dashboard.html' ;
            }
  }).controller('RetornateAppController', function($scope, $routeParams, $location,$localStorage){
            
            switch($location.path()){
              case "/app/voto/voto":
                if ($localStorage.voto === 0){$location.path("/down").replace()                           } 
                if ($localStorage.voto === 1){$scope.templateUrl = 'views' + $location.path() + '.html'; } 
                if ($localStorage.voto === 2){$location.path("/app/voto/retornar").replace();            }
                break;
              case "/app/foro/dashboard-foro":
                if ($localStorage.foro === 0){$location.path("/down").replace()                           } 
                if ($localStorage.foro === 1){$scope.templateUrl = 'views' + $location.path() + '.html'; } 
                if ($localStorage.foro === 2){$location.path("/app/foro/retornar").replace();            }
                break;
              case "/app/derecho/dashboard-derecho":
                if ($localStorage.derecho === 0){$location.path("/down").replace()                           } 
                if ($localStorage.derecho === 1){$scope.templateUrl = 'views' + $location.path() + '.html'; } 
                if ($localStorage.derecho === 2){$location.path("/app/derecho/retornar").replace();         } 
                break;
              case "/app/av/dashboard-av":
                if ($localStorage.av === 0){$location.path("/down").replace()                           } 
                if ($localStorage.av === 1){$scope.templateUrl = 'views' + $location.path() + '.html'; } 
                if ($localStorage.av === 2){$location.path("/app/av/retornar").replace();         } 
                break;
              default:
                $scope.templateUrl = 'views/app/dashboard.html' ;
            }

  });
  
  function apprequestfunc($http, $localStorage, $q){
        var deferred = $q.defer();
        $http.get(WebDefault + '/' + NameController + '/' +'apprequest')
        .success(function(apprequest) {
            //if (apprequest.voto !== undefined)
            {
                reset(apprequest,$localStorage);
                $localStorage.$default(apprequest)
                deferred.resolve(apprequest);
            }
        })
        .error(function() {
            deferred.reject();
        });
        return deferred.promise;
  };
  
  function reset(apprequest,$localStorage) {
        angular.forEach(apprequest, function(value, key) {
           delete $localStorage[key];
        });
  };
  
  function routerequestfunc($http, $q){
        var deferred = $q.defer();
        $http.get(WebDefault + '/' + NameController + '/' +'routerequest')
        .success(function(routerequest) {
            deferred.resolve(routerequest);
        })
        .error(function() {
            deferred.reject();
        });
        return deferred.promise;
  };
  
  function dashBoardApp($localStorage)
  {
      var apps = [];
      $localStorage['platforms']['voto'] === true ? apps.push('voto') : null;
      $localStorage['platforms']['foro'] === true ? apps.push('foro') : null;
      $localStorage['platforms']['derecho'] === true ? apps.push('derecho') : null;
      $localStorage['platforms']['av'] === true ? apps.push('av') : null;
      
      return apps.length === 1 ? _.first(apps) : undefined;
  }

}).call(this);
