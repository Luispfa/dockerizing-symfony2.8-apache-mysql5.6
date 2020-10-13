(function() {
  'use strict';
  angular.module('app.services.junta', []).factory('juntaManager', ['$http', '$q', 'Junta', function($http, $q, Junta) {
    var modelsManager = {
        _instance: null,
        _retrieveInstance: function(juntaData) {
            var instance = this._instance;

            if (instance !== null) {
                instance.setData(juntaData);
            } else {
                instance = new Junta(juntaData);
                this._instance = instance;
            }

            return instance;
        },
        _search: function() {
            return this._instance;
        },
        _load: function(deferred) {
            var scope = this;

            $http.get(ApiBase + '/juntas')
                .success(function(juntaData) {
                    var junta = scope._retrieveInstance(juntaData);
                    deferred.resolve(junta);
                })
                .error(function() {
                    deferred.reject();
                });
        },
        /* Public Methods */
        /* Use this function in order to get a model instance by it's id */
        getJunta: function() {
            var deferred = $q.defer();
            /*var model = this._search();
            if (model) {
                deferred.resolve(model);
            } 
            else{
                this._load(deferred);
            }*/
            this._load(deferred);
            return deferred.promise;
        }

    };
    return modelsManager;
}]).factory('Junta', ['$http', '$q', function($http,$q) {
    function Junta(juntaData){
        if (juntaData) {
            this.setData(juntaData);
        }
        // Some other initializations
    }
    Junta.prototype = {
        setData: function(juntaData) {
            angular.extend(this, juntaData);
        },
        delete: function() {
            //$http.delete('ourserver/books/' + bookId);
        },
        update: function() {
            //$http.put('ourserver/books/' + bookId, this);
        },
    };
    return Junta;
}]);

}).call(this);