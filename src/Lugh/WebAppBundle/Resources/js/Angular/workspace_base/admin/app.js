
(function() {
  'use strict';
  angular.module('app', ['ngRoute', 'ngAnimate', 'ngStorage', 'ui.bootstrap', 
      'easypiechart', 'mgo-angular-wizard', 'textAngular', 'ui.tree', 
      'ngMap','ngTagsInput', 'app.ui.ctrls', 'app.ui.directives', 
      'app.ui.services', 'main.sharedControllers', 'app.form.validation', 
      'app.ui.form.ctrls', 'app.ui.form.directives', 'app.tables', 'app.map', 
      'app.task', 'app.localization', 'app.chart.ctrls', 'app.chart.directives', 
      'adminWorkspaceControllers','adminWorkspaceDirectives','sharedDirectives',
      'sharedControllers','sharedServices','app.services.accionistas', 'app.services.items', 
      'app.page.ctrls','app.services.notifications','app.services.mails','app.services.adhesions',
      'sharedFilters','angularFileUpload', 'adminServices',"ngLocale",'app.services.actions',
      'app.services.accesos']).config([
    '$routeProvider', function($routeProvider) {
      var template = '<div ng-include="templateUrl" class="animate-fade-up">Loading...</div>';
      return $routeProvider.when('/', {
        redirectTo: '/app/header/pendientes'
      }).when('/dashboard', {
        templateUrl: 'views/dashboard.html',
        resolve: {
            apprequest: function($http, $localStorage, $q){
                return apprequestfunc($http, $localStorage, $q);
            }
        }
      }).when('/404', {
        templateUrl: 'views/pages/404.html'
      }).when('/500', {
        templateUrl: 'views/pages/500.html'
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

}).call(this);
