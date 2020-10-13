(function() {
	'use strict';
	angular.module('app.services.accesos', [])
	.factory('accesosManager',[
		"$http","assocArray","$q", function($http,assocArray,$q){
			var accesosManager = {
				/*getVotos: function(){
					var deferred = $q.defer();
					$http.get(ApiBase + '/votos').success(function(response){
						deferred.resolve(response.votos);
					}).catch(function(error){
						console.log(error);
						deferred.reject();
					});

					return deferred.promise;
				},*/
                                getAvs: function(){
					var deferred = $q.defer();
					$http.get(ApiBase + '/accesos/av').success(function(response){
						deferred.resolve(response.accesos);
					}).catch(function(error){
						console.log(error);
						deferred.reject();
					});

					return deferred.promise;
				},
				/*getDelegaciones: function(){
					var deferred = $q.defer();
					$http.get(ApiBase + '/delegacions').success(function(response){
						deferred.resolve(response.delegaciones);
					}).catch(function(error){
						console.log(error);
						deferred.reject();
					});

					return deferred.promise;
				},
				getAnulaciones: function(){
					var deferred = $q.defer();
					$http.get(ApiBase + '/anulacions').success(function(response){
						deferred.resolve(response.anulaciones);
					}).catch(function(error){
						console.log(error);
						deferred.reject();
					});

					return deferred.promise;
				},
				getAcciones: function(){
					var deferred = $q.defer();
					$http.get(ApiBase + '/accions').success(function(response){
						deferred.resolve(response.accions);
					}).catch(function(error){
						console.log(error);
						deferred.reject();
					});

					return deferred.promise;
				},
                                getAccionesAv: function(){
					var deferred = $q.defer();
					$http.get(ApiBase + '/accions/av').success(function(response){
						deferred.resolve(response.accions);
					}).catch(function(error){
						console.log(error);
						deferred.reject();
					});

					return deferred.promise;
				},
                                getAccionesVe: function(){
					var deferred = $q.defer();
					$http.get(ApiBase + '/accions/ve').success(function(response){
						deferred.resolve(response.accions);
					}).catch(function(error){
						console.log(error);
						deferred.reject();
					});

					return deferred.promise;
				},
				getAccion: function(id){
					var deferred = $q.defer();
					$http.get(ApiBase + '/accions/' + id).success(function(response){
						deferred.resolve(response.accions);
					}).catch(function(error){
						console.log(error);
						deferred.reject();
					});

					return deferred.promise;
				}*/
			}
			return accesosManager;
		}
	]);
}).call(this);