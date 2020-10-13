(function() {
  'use strict';

  angular.module('app.services.adhesions', [
    ]).factory('adhesionsManager', ['$http', '$q', 'typeService', 'Adhesion','AppService',
        function($http, $q, typeService, Adhesion,AppService){
            var adhesionsManager = {

                getByTypeID: function(type,ID){
                    var deferred = $q.defer();
                    var capTypes = typeService.getCapPluralTypes();

                    $http.get(ApiBase + '/adhesion' + type + '/' + ID)
                        .success(function(adhesionData){
                            if(adhesionData.error === undefined){
                                var adhesion = new Adhesion(adhesionData["adhesion"+capTypes[type]], type);
                                deferred.resolve(adhesion);
                            }else{
                                deferred.reject();
                            }
                        })
                        .error(function(data, status, headers, config) {
                            AppService.UnAuthorize(status);
                            deferred.reject();
                        });

                    return deferred.promise;
                },
                getAll: function(){
                    var deferred = $q.defer();

                    $http.get(ApiBase + '/adhesions')
                        .success(function(adhesions){
                            deferred.resolve(adhesions);
                        })
                        .error(function(data, status, headers, config) {
                            AppService.UnAuthorize(status);
                            deferred.reject();
                        });

                    return deferred.promise;
                },
                loadAllByState: function(stateNum){
                    var deferred = $q.defer();

                    this.getAll().then(function(adhesions){
                        adhesions.adhesionProposals = 
                            adhesions.adhesionProposals.filter(function(adhesion){
                                return adhesion.state == stateNum;
                            });
                        adhesions.adhesionInitiatives = 
                            adhesions.adhesionInitiatives.filter(function(adhesion){
                                return adhesion.state == stateNum;
                            });
                        adhesions.adhesionRequests = 
                            adhesions.adhesionRequests.filter(function(adhesion){
                                return adhesion.state == stateNum;
                            });
                        adhesions.adhesionOffers = 
                            adhesions.adhesionOffers.filter(function(adhesion){
                                return adhesion.state == stateNum;
                            });

                        deferred.resolve(adhesions);
                    }).catch(function(){
                        deferred.reject();
                    });

                    return deferred.promise;
                }
            };

            return adhesionsManager;
        }
    ]).factory('Adhesion',['$http','$q','typeService','AppService','$rootScope',
        function($http, $q, typeService,AppService,$rootScope){
            function Adhesion(adhesionData,type){
                if(adhesionData && type){
                    this.setData(adhesionData,type);
                }
            }

            Adhesion.prototype = {
                setData: function(itemData,type) {
                    angular.extend(this, itemData);
                    this.type = type;
                },
                setState: function(state){
                    var scope = this;
                    var deferred = $q.defer();
                    var capTypes = typeService.getCapPluralTypes();

                    $http.put(ApiBase + '/adhesion' + this.type + '/' + this.id + '/' + state)
                        .success(function(adhesionData){
                            if(adhesionData.error === undefined){
                                scope.setData(adhesionData["adhesion"+capTypes[scope.type]], scope.type);
                                deferred.resolve(true);
                                $rootScope.$broadcast('updatedItem');
                            }
                            else if(adhesionData.error !== undefined) {
                                deferred.resolve(adhesionData);	
                            }
                            else {
                                deferred.reject(adhesionData.error);
                            }
                        })
                        .error(function(data, status, headers, config) {
                            AppService.UnAuthorize(status);
                            deferred.reject();
                        });

                    return deferred.promise;
                },
                testSetState: function(state){
                    var scope = this;
                    var deferred = $q.defer();
                    var capTypes = typeService.getCapPluralTypes();

                    $http.put(ApiBase + '/adhesion' + this.type + '/' + this.id + '/tests/' + state)
                        .success(function(adhesionData){
                            if(adhesionData.error === undefined){
                                //scope.setData(adhesionData["adhesion"+capTypes[scope.type]], scope.type);
                                deferred.resolve(true);
                                //$rootScope.$broadcast('updatedItem');
                            }
                            else if(adhesionData.error !== undefined) {
                                deferred.resolve(adhesionData);
				
                            }
                            else {
                                deferred.reject(adhesionData.error);
                            }
                        })
                        .error(function(data, status, headers, config) {
                            AppService.UnAuthorize(status);
                            deferred.reject();
                        });

                    return deferred.promise;
                }
            };

            return Adhesion;
        }
    ]);

}).call(this);