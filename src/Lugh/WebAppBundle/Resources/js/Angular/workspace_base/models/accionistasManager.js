(function() {
  'use strict';
  angular.module('app.services.accionistas', []).factory('accionistasManager', ['$http', '$q', 'Accionista', 'AppService', function($http, $q, Accionista, AppService) {
    var accionistasManager = {
        _accionista: null,
        _retrieveInstance: function(accionistaData) {
            var instance = this._accionista;

            if (instance) {
                instance.setData(accionistaData);
            } else {
                instance = new Accionista(accionistaData);
                this._accionista = instance;
            }

            return instance;
        },
        _load: function(deferred) {
            var scope = this;

             $http.get(ApiBase + '/accionistas/user')
                .success(function(accionistaData) {
                    if(accionistaData.error === undefined){
                        var accionista = scope._retrieveInstance(accionistaData);
                        deferred.resolve(accionista);
                    }
                    else{
                        deferred.reject();
                    }
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject();
                });
        },
        /* Public Methods */
        /* Use this function in order to get a book instance by it's id */
        getAccionista: function() {
            var deferred = $q.defer();
            var accionista = this._accionista;
            if (accionista) {
                deferred.resolve(accionista);
            } else {
                this._load(deferred);
            }
            return deferred.promise;
        },

        getAccionistaUpdated: function(){
            var deferred = $q.defer();
            var accionista = this._accionista;
            this._load(deferred);
            return deferred.promise;
        }
    };
    return accionistasManager;
}]).factory('accionistasAdminManager', ['$http', '$q', 'Accionista','AppService', function($http, $q, Accionista, AppService) {
    var accionistasManager = {
        _pool: {},
        _retrieveInstance: function(accionistaId, accionistaData) {
            var instance = this._pool[accionistaId];

            if (instance) {
                instance.setData(accionistaData);
            } else {
                instance = new Accionista(accionistaData);
                this._pool[accionistaId] = instance;
            }

            return instance;
        },
        _search: function(accionistaId) {
            return this._pool[accionistaId];
        },
        _load: function(accionistaId, deferred) {
            var scope = this;

             $http.get(ApiBase + '/accionistas/' + accionistaId)
                .success(function(accionistaData) {
                    if(accionistaData.error === undefined){
                        var accionista = scope._retrieveInstance(accionistaId, accionistaData);
                        deferred.resolve(accionista);
                    }
                    else{
                        deferred.reject();
                    }
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject();
                });
        },
        /* Public Methods */
        /* Use this function in order to get a book instance by it's id */
        getAccionista: function(accionistaId) {
            var deferred = $q.defer();
            var accionista = this._search(accionistaId);
            if (accionista) {
                deferred.resolve(accionista);
            } else {
                this._load(accionistaId, deferred);
            }
            return deferred.promise;
        },
        /* Use this function in order to get instances of all the books */
        loadAllAccionistas: function() {
            var deferred = $q.defer();
            var scope = this;
            $http.get(ApiBase + '/accionistas')
                .success(function(accionistasArray) {
                    if(accionistasArray.error === undefined){
                        var accionistas = [];
                        accionistasArray.forEach(function(accionistaData) {
                            var accionista = scope._retrieveInstance(accionistaData.id, accionistaData);
                            accionistas.push(accionista);
                        });

                        deferred.resolve(accionistas);
                    }
                    else{
                        deferred.reject();
                    }
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject();
                });
            return deferred.promise;
        },
        loadAllByState: function(stateNum){
            var deferred = $q.defer();

            this.loadAllAccionistas().then(function(accionistasList){
                var filtered = accionistasList.filter(function(accionista){
                    return accionista.item_accionista.state == stateNum;
                });

                deferred.resolve(filtered);
            }).catch(function(){
                deferred.reject();
            });

            return deferred.promise;
        },
        loadAllAppsByState: function(stateNum){
            var deferred = $q.defer();

            this.loadAllAccionistas().then(function(accionistasList){
                var filtered = accionistasList.filter(function(accionista){
                    var appState = false;
                    for (var i in accionista.app)
                    {
                        appState = (appState == true) ? true : accionista.app[i].state == stateNum;
                    }
                    return appState;
                });

                deferred.resolve(filtered);
            }).catch(function(){
                deferred.reject();
            });

            return deferred.promise;
        },
        loadAllByCertificate: function(stateCertificate){
            var deferred = $q.defer();

            this.loadAllAccionistas().then(function(accionistasList){
                var filtered = accionistasList.filter(function(accionista){
                    return accionista.is_user_cert == stateCertificate;
                });

                deferred.resolve(filtered);
            }).catch(function(){
                deferred.reject();
            });

            return deferred.promise;
        },
    };
    return accionistasManager;
}]).factory('Accionista', ['$http', '$q', 'stateService', 'itemsManager','AppService', '$rootScope', function($http, $q, $states, itemsManager,AppService,$rootScope) {
    function Accionista(accionistaData) {
        if (accionistaData) {
            this.setData(accionistaData);
        }
        // Some other initializations
    };
    Accionista.prototype = {
        setData: function(accionistaData) {
            angular.extend(this, accionistaData);
        },
        delete: function() {
            //$http.delete('ourserver/books/' + bookId);
        },
        update: function() {
            //$http.put('ourserver/books/' + bookId, this);
        },
        appChange: function($apps, sendMail) {
            var deferred = $q.defer();
            var scope = this;
            sendMail = sendMail || false;
            
            $http.put(ApiBase + '/accionistas/' + this.id + '/' + 'app', $apps)
            .success(function(accionistaData) {
                if(accionistaData.error === undefined){
                    scope.setData(accionistaData.success);
                    deferred.resolve(scope);
                    $rootScope.$broadcast('updatedItem');
                }
                else{
                    deferred.reject();
                }
            })
            .error(function(data, status, headers, config) {
                AppService.UnAuthorize(status);
                deferred.reject();
            });
            return deferred.promise;
        },
        stateChange: function(stateID,message,sendMail) {
            var deferred = $q.defer();
            var scope = this;
            sendMail = sendMail || false;
            
            $http.put(ApiBase + '/accionistas/' + this.id + '/' + $states.getAliasState(stateID),{"message": message, "sendMail": sendMail})
            .success(function(accionistaData) {
                if(accionistaData.error === undefined){
                    scope.setData(accionistaData.success);
                    deferred.resolve(scope);
                    $rootScope.$broadcast('updatedItem');
                }
                else{
                    deferred.reject();
                }
            })
            .error(function(data, status, headers, config) {
                AppService.UnAuthorize(status);
                deferred.reject();
            });
            return deferred.promise;
        },
        appStateChange: function(appID,stateID,message,sendMail) {
            var deferred = $q.defer();
            var scope = this;
            sendMail = sendMail || false;
            
            $http.put(ApiBase + '/accionistas/' + this.id + '/apps/' + appID + '/' + $states.getAliasState(stateID),{"message": message, "sendMail": sendMail})
            .success(function(accionistaData) {
                if(accionistaData.error === undefined){
                    scope.setData(accionistaData.success);
                    deferred.resolve(scope);
                    $rootScope.$broadcast('updatedItem');
                }
                else{
                    deferred.reject();
                }
            })
            .error(function(data, status, headers, config) {
                AppService.UnAuthorize(status);
                deferred.reject();
            });
            return deferred.promise;
        },
        acreditar: function(acreditado) {
            var deferred = $q.defer();
            var scope = this;
            
            $http.put(ApiBase + '/accionistas/' + acreditado + '/acreditado')
            .success(function(accionistaData) {
                if(accionistaData.error === undefined){
                    scope.setData(accionistaData.success);
                    deferred.resolve(scope);
                    $rootScope.$broadcast('updatedItem');
                }
                else{
                    deferred.reject();
                }
            })
            .error(function(data, status, headers, config) {
                AppService.UnAuthorize(status);
                deferred.reject();
            });
            return deferred.promise;
        },
        _getItems: function(type) {
            return itemsManager.getAllByAccionista(this.id,type);
        },
        _getElement: function(deferred, itemName) {
            var scope = this;
            var items = this[itemName];
            if (items) {
                deferred.resolve(scope);
            }
            else {
                $http.get(ApiBase + '/accionistas/' + this.id + '/' + itemName)
                .success(function(accionistaData) {
                    if(accionistaData.error === undefined){
                        scope.setData(accionistaData);
                        deferred.resolve(scope);
                    }
                    else{
                        deferred.reject(accionistaData.error);
                    }
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject(data);
                });
            }
            return deferred.promise;
        },
        getItems: function() {
            return this._getItems('item');
        },
        getOffers: function() {
            return this._getItems('offers');
        },
        getProposals: function() {
            return this._getItems('proposals');
        },
        getInitiatives: function() {
            return this._getItems('initiatives');
        },
        getRequests: function() {
            return this._getItems('requests');
        },
        getThreads: function() {
            return this._getItems('threads');
        },
        getQuestions: function() {
            return this._getItems('questions');
        },
        getAdhesionsinitiatives: function() {
            var deferred = $q.defer();
            this._getElement(deferred, 'adhesionsinitiatives');
            return deferred.promise;
        },
        getAdhesionsoffers: function() {
            var deferred = $q.defer();
            this._getElement(deferred, 'adhesionsoffers');
            return deferred.promise;
        },
        getAdhesionsrequests: function() {
            var deferred = $q.defer();
            this._getElement(deferred, 'adhesionsrequests');
            return deferred.promise;
        },
        getAdhesionsproposals: function() {
            var deferred = $q.defer();
            this._getElement(deferred, 'adhesionsproposals');
            return deferred.promise;
        },
        getAccions: function() {
            var deferred = $q.defer();
            this._getElement(deferred, 'accion');
            return deferred.promise;
        },
        getAccesos: function(discr) {
            var deferred = $q.defer();
            this._getElement(deferred, 'accesos/' + discr);
            return deferred.promise;
        },
        getDocuments: function() {
            var deferred = $q.defer();
            this._getElement(deferred, 'document');
            return deferred.promise;
        },
        getLives: function(){
            var deferred = $q.defer();
            this._getElement(deferred, 'live');
            return deferred.promise;
        },
        myLives: function(){
            var deferred = $q.defer();
            var itemName = 'live';
            var scope = this;
            var items = this[itemName];
            if (items) {
                deferred.resolve(scope);
            }
            else {
                $http.get(ApiBase + '/av/lives')
                .success(function(accionistaData) {
                    if(accionistaData.error === undefined){
                        scope.setData(accionistaData);
                        deferred.resolve(scope);
                    }
                    else{
                        deferred.reject(accionistaData.error);
                    }
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject(data);
                });
            }
            return deferred.promise;
        },
        getLastAccion: function() {
            var deferred = $q.defer();
            this._getElement(deferred, 'lastaccion');
            return deferred.promise;
        },
        getApp: function(discr) {
            for (var i in this.app) {
                if ($states.getIDByAppName(discr) == this.app[i].discr)
                {
                    return this.app[i];
                }
            }
            return undefined;
        },
        logAccess: function(discr) {
            var deferred = $q.defer();
            var scope = this;
            $http.put(ApiBase + '/accionistas/' + this.id + '/accesos/' + discr)
            .success(function(accionistaData) {
                if(accionistaData.error === undefined){
                    scope.setData(accionistaData.success);
                    deferred.resolve(scope);
                    $rootScope.$broadcast('updatedItem');
                }
                else{
                    deferred.reject();
                }
            })
            .error(function(data, status, headers, config) {
                AppService.UnAuthorize(status);
                deferred.reject();
            });
        }
    };
    return Accionista;
}]);

}).call(this);