(function() {
  'use strict';
  angular.module('app.services.accionistas', []).factory('accionistasHomeManager', ['$http', '$q', 'Accionista', function($http, $q, Accionista) {
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
            
             $http.get(ApiBase + '/publics/accionista')
                .success(function(accionistaData) {
                    if(accionistaData.error === undefined){
                        var accionista = scope._retrieveInstance(accionistaData);
                        deferred.resolve(accionista);
                    }
                    else{
                        deferred.reject();
                    }
                })
                .error(function() {
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
    };
    return accionistasManager;
}]).factory('Accionista', ['$http', '$q', 'stateService', function($http, $q, $states) {
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
    };
    return Accionista;
}]);

}).call(this);