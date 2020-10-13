(function() {
  'use strict';

  angular.module('app.services.items', [
	]).factory('itemsManager', ['$http', '$q', 'Item', 'ItemsList','stateService', 'typeService','AppService',
	function($http, $q, Item, ItemsList, stateService, typeService, AppService) {
		var types = typeService.getTypes();

		var itemsManager = {
			_pool: new ItemsList(true),
			_retrieveInstance: function(type, itemData){
				var instance = this._pool.getItemByID(type,itemData.id);

				if(instance) {
					instance.setData(itemData,type);
				} else {
					instance = new Item(itemData,type);
					this._pool.push(type,instance);
				}

				return instance;
			},
			_searchByTypeID: function(type, ID){
				var result = this._pool.getByID(type,ID);
				return (result.empty()) ? false : result;
			},
			_searchByTypeState: function(type,state){
				var typeList = this._pool.getByType(type);
				var result   = new ItemsList();

				angular.forEach(typeList[type], function(item,ID){
					if(item.state == state){
						result.push(type,item);
					}
				});

				return (result.empty()) ? false : result;
			},
			_searchByState: function(state){
				var result = new ItemList();
				for(var type in types){
					var search = _searchByTypeState(type,state);

					if(search){
						result.merge(search);
					}
				}

				return (result.empty()) ? false : result;
			},
			_searchByType: function(type){
				var result = this._pool.getByType(type);
				return (result.empty()) ? false : result;
			},
			_load: function(type, ID, deferred){
				var scope = this;
				$http.get(ApiBase + '/' + type + '/' + ID)
					.success(function(itemData){
						if(itemData.error === undefined){
							var item = new ItemsList();
							item.push(type, scope._retrieveInstance(type, itemData[type]));

							deferred.resolve(item);
						}else{
							deferred.reject();
						}
					}).error(function(data, status, headers, config) {
							AppService.UnAuthorize(status);
						deferred.reject();
					});
				return deferred.promise;
			},
			_loadAllByType: function(type,deferred){
				var scope = this;
				var item;

				$http.get(ApiBase + '/' + type)
					.success(function(itemsArray){
						if(itemsArray.error === undefined){
							var items = new ItemsList();
							
							angular.forEach(itemsArray[type],function(itemData){
								item = scope._retrieveInstance(type, itemData);
								items.push(type,item);
							});

							deferred.resolve(items);
						}
						else{
							deferred.reject();
						}
					}).error(function(data, status, headers, config) {
						AppService.UnAuthorize(status);
						deferred.reject();
					});
				return deferred.promise;
			},
			_loadAllByTypeState: function(type,state,deferred){
				var scope = this;
				var item;

				$http.get(ApiBase + '/' + type + '/' + state + '/state')
					.success(function(itemsArray){
						if(itemsArray.error === undefined){
							var items = new ItemsList();
							
							angular.forEach(itemsArray[type],function(itemData){
								item = scope._retrieveInstance(type, itemData);
								items.push(type,item);
							});

							deferred.resolve(items);
						}
						else{
							deferred.reject();
						}
					}).error(function(data, status, headers, config) {
						AppService.UnAuthorize(status);
						deferred.reject();
					});
				return deferred.promise;
			},
			_loadAllByState: function(state,deferred){
				var scope = this;
				var item;

				$http.get(ApiBase + '/items/' + state)
					.success(function(itemsArray) {
						if(itemsArray.error === undefined){
							var items = new ItemsList();

							angular.forEach(itemsArray,function(itemList, type){
								angular.forEach(itemList,function(itemData){
									item = scope._retrieveInstance(type,itemData);
									items.push(type,item);
									deferred.resolve(items);
								});
							});

							deferred.resolve(items);
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
			getByType: function(type){
				var deferred = $q.defer();
				this._loadAllByType(type, deferred);
				return deferred.promise;
			},
			getByTypeState: function(type,state){
				var deferred = $q.defer();
				this._loadAllByTypeState(type,state,deferred);
				return deferred.promise;
			},
			getByTypeID: function(type,ID){
				var deferred = $q.defer();
				var item = this._searchByTypeID(type,ID);
				this._load(type,ID,deferred);
				return deferred.promise;
			},
			getByState: function(state){
				var deferred = $q.defer();
				this._loadAllByState(state,deferred);
				return deferred.promise;
			},
			getAll: function(){
				var deferred = $q.defer();
				var scope = this;

				$http.get(ApiBase + '/items')
					.success(function(itemsArray) {
						if(itemsArray.error === undefined){
							var items = new ItemsList();

							angular.forEach(itemsArray,function(itemList, type){
								angular.forEach(itemList,function(itemData){
									var item = scope._retrieveInstance(type,itemData);
									items.push(type,item);
								});
							});

							deferred.resolve(items);
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
			getAllByAccionista: function(accID, type){
				var scope = this;
				var deferred = $q.defer();
				$http.get(ApiBase + '/accionistas/' + accID + '/' + type)
				.success(function(itemsArray) {
					if(itemsArray.error === undefined){
						var item;
						var items = new ItemsList();

						angular.forEach(types,
						function(type){
							angular.forEach(itemsArray[type],function(itemData){
								item = scope._retrieveInstance(type,itemData);
								items.push(type,item);
							});
						});

						deferred.resolve(items);
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
			getAdheredByAccionista: function(accID){
				var sTypes = typeService.getSingularTypes();

				var scope = this;
				var deferred = $q.defer();
				$http.get(ApiBase + '/accionistas/' + accID + '/adhesions')
				.success(function(itemsArray) {
					if(itemsArray.error === undefined){
						var item;
						var state;
						var items = new ItemsList();

						angular.forEach(types,
						function(type){
							angular.forEach(itemsArray["adhesions_" + type],function(adhData){
								item = scope._retrieveInstance(type,adhData[sTypes[type]]);
								item.adhState = adhData.state;
								items.push(type,item);
							});
						});

						deferred.resolve(items);
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
			putPending: function(item){
				var item = this._searchByTypeID(item.type,item.id)[item.type][0];
				return item.update(JSON.stringify(item),"pending");
			},
			newItem: function(item){
				this._pool.push(item.type,item);
				return item.create();
			},
			testNew: function(type){
				var deferred = $q.defer();

				$http({
					url:       ApiBase + '/' + type + '/tests',
					method:    "POST",
					headers:   {'Content-Type': "application/json"},
					withCredentials: true 
				}).success(function(response){
					if(response.success !== undefined){
						deferred.resolve(true);
					} else if(response.error !== undefined) {
                                                deferred.resolve(response);
                                        } 
                                        else {
						deferred.resolve(false);
					}
				}).catch(function(error){
					console.log('Test error: ' + error);
					deferred.resolve(false);
				})

				return deferred.promise;
			},
			putState: function(item,state){
				return item.update(item,state);
			}
		};

		return itemsManager;
	}

   ]).factory('Item', ['$http', '$q', 'stateService','AppService','$rootScope','$modal',function($http,$q,stateService, AppService, $rootScope, $modal) {
	function Item(itemData,type){
		if(itemData && type){
			this.setData(itemData, type);
		}
		//Other initializations
	}

	Item.prototype = {
		setData: function(itemData,type) {
			angular.extend(this, itemData);
			this.type = type;
		},
		create: function(){
			var deferred = $q.defer();
			var scope    = this;
			$http({
				 url:       ApiBase + '/' + this.type,
				 method:    "POST",
				 headers:   {'Content-Type': "application/json"},
				 data:      JSON.stringify(scope),
				 withCredentials: true 
			}).success(function(item){
				if(item.error === undefined)
					scope.setData(item.success,scope.type);
				deferred.resolve(item);
			}).error(function(data, status, headers, config) {
				AppService.UnAuthorize(status);
				deferred.reject();
			});
			return deferred.promise;
		},
		delete: function() {
			//$http.delete('ourserver/books/' + bookId);
		},
		update: function(itemData,state,message) {
			var deferred = $q.defer();
			var scope = this;

			itemData         = (itemData == undefined) ? this : itemData;
			state            = (state    == undefined) ? stateService.getAliasState(this.state) : state;
			itemData.message = (message  == undefined) ? this.message : message;

			$http({
				 url:       ApiBase + '/' + itemData.type + '/' + itemData.id + '/' + state,
				 method:    "PUT",
				 headers:   {'Content-Type': "application/json"},
				 data:      itemData,
				 withCredentials: true 
			}).success(function(item) {
				if(item.success){
					scope.setData(item.success,itemData.type);
					deferred.resolve(true);
					$rootScope.$broadcast('updatedItem');
				}
				else if(item.error !== undefined)
				{
					deferred.resolve(item);
				}else{
					deferred.reject(item.error);
				}
			}).error(function(data, status, headers, config) {
				AppService.UnAuthorize(status);
				deferred.reject();
			});
			return deferred.promise;
		},
        testUpdate: function(itemData,state,message) {
			var deferred = $q.defer();
			var scope = this;

			itemData         = (itemData == undefined) ? this : itemData;
			state            = (state    == undefined) ? stateService.getAliasState(this.state) : state;
			itemData.message = (message  == undefined) ? this.message : message;

			$http({
				 url:       ApiBase + '/' + itemData.type + '/' + itemData.id + '/tests/' + state,
				 method:    "PUT",
				 headers:   {'Content-Type': "application/json"},
				 data:      itemData,
				 withCredentials: true 
			}).success(function(item) {
				if(item.success){
					deferred.resolve(true);
				}
				else if(item.error !== undefined)
				{
					deferred.resolve(item);
				}else{
					deferred.reject(item.error);
				}
			}).error(function(data, status, headers, config) {
				AppService.UnAuthorize(status);
				deferred.reject();
			});
			return deferred.promise;
		},
		getAdhesions: function() {
			var deferred = $q.defer();
			$http.get(ApiBase+'/'+this.type+'/'+this.id+'/adhesions')
				.success(function(adhesions){
					if(adhesions.error === undefined){
						deferred.resolve(adhesions.adhesions);
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
		adherir: function(accionista){
			var deferred = $q.defer();
			var scope = this;

			this.adherido(accionista).then(function(adhesion){
				if(adhesion == 0){
					$http.post(ApiBase + '/'+scope.type+'/' + scope.id + '/adhesions')
					.success(function(data){
						if(data.error === undefined){
							deferred.resolve(data);
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
				else{
					$http.put(ApiBase + '/adhesion'+scope.type+'/' + adhesion.id + '/pending')
					.success(function(data){
						if(data.error === undefined){
							deferred.resolve(data);
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
			});
						
			return deferred.promise;
		},
		cancelarAdhesion: function(adhesionID){
			var deferred = $q.defer();
			var scope = this;

			$http.put(ApiBase + '/adhesion'+this.type+'/' + adhesionID + '/retornate')
				.success(function(data){
					if(data.error === undefined){
						deferred.resolve(data);
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
		adherido: function(accionista){
			var deferred = $q.defer();
			$http.get(ApiBase + '/'+this.type+'/' + this.id + '/adhesions')
				.success(function(data){
					if(data.error === undefined){
						for(var key in data.adhesions){
							if(data.adhesions[key].accionista.id === accionista.id){
								deferred.resolve(data.adhesions[key]);
								return;
							}
						}
						deferred.resolve(0);
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
		sendComment: function(comment, sendMail, token){
			var deferred = $q.defer();
			var scope = this;
			token    = token    || false;
			sendMail = sendMail || false;

			$http.put(ApiBase + "/" + this.type + "/" + this.id + "/message",
					  JSON.stringify({message: comment, token: token, sendMail: sendMail}))
				.success(function(data){
					if(data.error === undefined){
						deferred.resolve(true);
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
		reload: function(){
			var deferred = $q.defer();
			var scope = this;
			$http.get(ApiBase+"/"+this.type+"/"+this.id)
				.success(function(data){
					scope.setData(data[scope.type],scope.type);
					deferred.resolve(true);
				})
				.error(function(data, status, headers, config) {
					AppService.UnAuthorize(status);
					deferred.reject();
				});
			return deferred.promise;
		},
		existingRequests: function(accionista){
			var deferred = $q.defer();

			var check = function(items){
				for(var i in items){
					if(items[i].state == 2 || items[i].state == 1){
						deferred.resolve(true);
						return true;
					}
				}
				return false;
			};

			accionista.getRequests().then(function(requests){
				if(check(requests.requests)) return;
				deferred.resolve(false);
			});

			return deferred.promise;
		},
		existingOffersRequests: function(accionista){
			var deferred = $q.defer();

			var check = function(items){
				for(var i in items){
					if(items[i].state == 2 || items[i].state == 1){
						deferred.resolve(true);
						return true;
					}
				}
				return false;
			};

			accionista.getOffers().then(function(offers){
				if(check(offers.offers)) return;
				accionista.getRequests().then(function(requests){
					if(check(requests.requests)) return;
					deferred.resolve(false);
				});
			});

			return deferred.promise;
		},
		existingAdhesionOffersRequests: function(accionista){
			var deferred = $q.defer();
			var scp = this;

			var check = function(items){
				for(var i in items){
					if((items[i].state == 2 || items[i].state == 1) && 
					   ((items[i].offer   && items[i].offer.id   != scp.id) ||
						(items[i].request && items[i].request.id != scp.id))
					  ){
						deferred.resolve(true);
						return true;
					}
				}
				return false;
			};

			accionista.getAdhesionsoffers().then(function(offers){
				if(check(offers.adhesions_offers)) return;
				accionista.getAdhesionsrequests().then(function(requests){
					if(check(requests.adhesions_requests)) return;
					deferred.resolve(false);
				});
			});

			return deferred.promise;
		},
		adherible: function(accionista){
			var deferred = $q.defer();
			var scope = this;

			this.adherido(accionista).then(function(adhesion){
				if(adhesion == 0){
					$http.post(ApiBase + '/'+scope.type+'/' + scope.id + '/adhesions/tests')
						.success(function(response){
							if(response.success !== undefined){
								deferred.resolve(true);
							} else {
								deferred.resolve(false);
							}
						}).catch(function(error){
							console.log('Test failed: ' + error);
							deferred.resolve(false);
						});
				} else {
					$http.put(ApiBase + '/adhesion'+scope.type+'/' + adhesion.id + '/tests/pending')
						.success(function(response){
							if(response.success !== undefined){
								deferred.resolve(true);
							} else {
								deferred.resolve(false);
							}
						}).catch(function(error){
							console.log('Test failed: ' + error);
							deferred.resolve(false);
						});
				}
			});

			/*var scp = this;
			var adherible = {
				adherido:             -1,
				offerRequest:         -1,
				offerRequestAdhesion: -1
			};

			scp.adherido(accionista).then(function(adherido){
				adherible.adherido = adherido;
				scp.existingOffersRequests(accionista).then(function(existing){
					adherible.offerRequest = existing;
					scp.existingAdhesionOffersRequests(accionista).then(function(existing){
						adherible.offerRequestAdhesion = existing;
						deferred.resolve(adherible);
					});
				});
			});*/

			return deferred.promise;
		}
	};
	return Item;
}]).factory('ItemsList',['typeService', function(typeService){
	var types = typeService.getTypes();

	function ItemsList(orderByID){
		this.orderByID   = (orderByID) ? true : false; //OrderByID podría ser null
		this.proposals   = (orderByID) ?  {}  :  [];
		this.initiatives = (orderByID) ?  {}  :  [];
		this.offers      = (orderByID) ?  {}  :  [];
		this.requests    = (orderByID) ?  {}  :  [];
		this.threads     = (orderByID) ?  {}  :  [];
                this.questions   = (orderByID) ?  {}  :  [];
                this.desertions   = (orderByID) ?  {}  :  [];
	};

	ItemsList.prototype = {
		//Devuelve una LISTA con el Item dentro,
		//para obtener sólo el item: getItemByID
		getByID: function(type,ID){
			if(!this.orderByID){
				console.log("error: using getByID on a not id-ordered ItemsList");
			}
			if(this[type][ID]){
				return (new ItemsList()).push(type,this[type][ID]);
			}
			return (new ItemsList());
		},
		getItemByID: function(type,ID){
			if(!this.orderByID){
				console.log("error: using getByID on a not id-ordered ItemsList");
			}
			return this[type][ID];
		},
		getByType: function(type){
			return (new ItemsList()).cloneType(type,this);
		},
		push: function(type,value){
			if(this.orderByID){
				this[type][value.id] = value;
			}else{
				this[type].push(value);
			}
			return this;
		},
		empty: function(){
			var scope = this;
			for(var type in types){
				for(var key in scope[types[type]]){
					return false;
				}
			}
			return true;
		},
		emptyForo: function(){
			var scope = this;
			for(var type in types){
				if(types[type] == "threads") continue;
                if(types[type] == "questions") continue;
				
				for(var key in scope[types[type]]){
					return false;
				}
			}
			return true;
		},
		emptyByState: function(state){
			var scope = this;
			for(var type in types){
				for(var key in scope[types[type]]){
					if(scope[types[type]][key].state == state){
						return false;
					}
				}
			}
			return true;
		},
		cloneType: function(type,listB){
			//Es el mejor modo de copiar un array asociativo, 
			//con un "=" se copiaría su referencia
			for(var key in listB[type]){
				this.push(type,listB[type][key]);
			}
			return this;
		},
		merge: function(listB){
			var scope = this;
			angular.forEach(types,
			function(type){
				for(var key in listB[type]){
					if(!scope[type][key]){
						scope.push(type, listB[type][key]);
					}
				}
			});
		}
	};

	return ItemsList;
}]);

}).call(this);