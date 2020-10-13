(function() {
  'use strict';
  angular.module('app.services.model', []).factory('modelManager', ['$http', '$q', 'Model', function($http, $q, Model) {
    var modelsManager = {
        _pool: {},
        _retrieveInstance: function(modelId, modelData) {
            var instance = this._pool[modelId];

            if (instance) {
                instance.setData(modelData);
            } else {
                instance = new Model(modelData);
                this._pool[modelId] = instance;
            }

            return instance;
        },
        _search: function(modelId) {
            return this._pool[modelId];
        },
        _load: function(modelId, deferred) {
            var scope = this;

            $http.get('ourserver/models/' + modelId)
                .success(function(modelData) {
                    var model = scope._retrieveInstance(modelData.id, modelData);
                    deferred.resolve(model);
                })
                .error(function() {
                    deferred.reject();
                });
        },
        /* Public Methods */
        /* Use this function in order to get a model instance by it's id */
        getModel: function(modelId) {
            var deferred = $q.defer();
            var model = this._search(modelId);
            if (model) {
                deferred.resolve(model);
            } 
            else{
                this._load(modelId, deferred);
            }
            return deferred.promise;
        },
        /* Use this function in order to get instances of all the models */
        loadAllModels: function() {
            var deferred = $q.defer();
            var scope = this;
            $http.get('ourserver/models')
                .success(function(modelsArray) {
                    var models = [];
                    modelsArray.forEach(function(modelData) {
                        var model = scope._retrieveInstance(modelData.id, modelData);
                        models.push(model);
                    });

                    deferred.resolve(models);
                })
                .error(function() {
                    deferred.reject();
                });
            return deferred.promise;
        },
        /*  This function is useful when we got somehow the model data and we wish to store it or update the pool and get a model instance in return */
        setModel: function(modelData) {
            var scope = this;
            var model = this._search(modelData.id);
            if (model) {
                model.setData(modelData);
            } else {
                model = scope._retrieveInstance(modelData);
            }
            return model;
        }

    };
    return modelsManager;
}]).factory('Model', ['$http', '$q', function($http,$q) {
    function Model(modelData){
        if (modelData) {
            this.setData(modelData);
        }
        // Some other initializations
    }
    Model.prototype = {
        setData: function(modelData) {
            angular.extend(this, modelData);
        },
        delete: function() {
            //$http.delete('ourserver/books/' + bookId);
        },
        update: function() {
            //$http.put('ourserver/books/' + bookId, this);
        },
    };
    return Model;
}]);

}).call(this);