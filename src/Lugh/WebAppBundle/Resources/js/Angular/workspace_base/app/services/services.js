(function() {
    'use strict';
	angular.module('appServices', [
    ]).service('intervalService', [
        '$interval', '$localStorage', 'intervalFact', 'JuntaService', 'ActionsCountEvent',
        function($interval, $localStorage, intervalFact, JuntaService, ActionsCountEvent) {
            var _intervals  = [];
            var time        = $localStorage['Config.service.time'] === undefined ? 60000 : $localStorage['Config.service.time'];
            
            this.subscribe = function(event) {
                switch(event){
					case 'Junta':
						return _createInterval(JuntaService);
						break;
					case 'ActionsCount':
                        return _createInterval(ActionsCountEvent);
						break;
				}
            }
            
            this.unsubscribe = function(event) {
                switch(event){
					case 'Junta':
						_removeInterval(JuntaService);
						break;
					case 'ActionsCount':
                        _removeInterval(ActionsCountEvent);
						break;
				}
            }
            
            var _searchInterval = function(Service) {
                var interval = new intervalFact(Service);
                for (var i in _intervals) {
                    if (_intervals[i].service.getBroadCastName() === interval.service.getBroadCastName()) {
                        return i;
                    }
                }
                return _intervals.push(interval) - 1;
                
            };
            
            var _createInterval = function(Service) {

                

                var indexCreate  = _searchInterval(Service);
                var intervalFact = _intervals[indexCreate];
                
                if (intervalFact.interval === null){
                    intervalFact.interval = $interval(intervalFact.service.update, time);
                    intervalFact.service.update();
                }
                
                intervalFact.count++;

                return intervalFact.service.getBroadCastName();
            };

            var _removeInterval = function(Service) {
                
                

                var indexRemove  = _searchInterval(Service);
                var intervalFact = _intervals[indexRemove];

                intervalFact.count--;

                if(intervalFact.count <= 0){

                    $interval.cancel(_intervals[indexRemove].interval);
                    _intervals.splice(indexRemove,1);

                }
            };
        }
    ]).factory('intervalFact', [ 
        function() {
            function intervalFact(service){
                this.service = service;
            }
            intervalFact.prototype.count    = 0;
            intervalFact.prototype.interval = null;
            intervalFact.prototype.service  = null;
            
            return intervalFact;
        }
    ]).service('ActionsCountEvent',[
        '$rootScope', '$interval','itemsManager', 'stateService', '$q','$localStorage', 'adhesionsManager','accionistasManager',
        function($rootScope, $interval, ItemsManager, StateService, $q, $localStorage, AdhesionsManager, accionistasManager){
            var broadCastName = 'totalForoPendientesCount';

            var localTotalForoPendientesCount     = {count: 0};
            
            var broadcast = function(localCount, count, broad) {
                if (localCount.count !== count)
                {
                    $rootScope.$broadcast(broad, count);
                    localCount.count = count;
                }
            };
            
            var update = function() {
                var deferredf = $q.defer();
                var deferreda = $q.defer();
                var foro = $q.all([deferredf.promise, deferreda.promise]);

                var totalItemsPendientesCount    = 0;
                var totalAdhesionPendientesCount = 0;
                 
                if($localStorage.foro == true)
                {
                   ItemsManager.getByState(StateService.getAliasState(3)).then(function(items){
                           angular.forEach(items,function(itemData,itemKey){
                               switch(itemKey) {
                                     case $localStorage.foro == true && 'proposals':
                                         totalItemsPendientesCount   += itemData.length;
                                         break;
                                     case $localStorage.foro == true && 'initiatives':
                                         totalItemsPendientesCount   += itemData.length;
                                         break;
                                     case $localStorage.foro == true && 'offers':
                                         totalItemsPendientesCount   += itemData.length;
                                         break;
                                     case $localStorage.foro == true && 'requests':
                                         totalItemsPendientesCount   += itemData.length;
                                         break;
                                 }
                           });
                           //broadcast(localTotalItemsPendientesCount,  totalItemsPendientesCount,  'totalItemsPendientesCount' );
                           deferredf.resolve(totalItemsPendientesCount);
                   });

                   accionistasManager.getAccionista().then(function(accionista){
                       AdhesionsManager.loadAllByState(1).then(function(adhesions){
                            for(var i in adhesions){
                                for (var j in adhesions[i]){
                                    if(adhesions[i][j].accionista.id == accionista.id) {
                                        adhesions[i].splice(j,1);
                                    }
                                }
                                totalAdhesionPendientesCount += adhesions[i].length;
                            }
                            
                             //broadcast(localAdhesionsPendientesCount,  totalAdhesionPendientesCount,  'totalAdhesionPendientesCount' );
                             deferreda.resolve(totalAdhesionPendientesCount);
                        });
                   });
                   

                }    
                else 
                {
                   deferredf.resolve(0);
                   deferreda.resolve(0);
                } 
                 
                foro.then(function(data){
                    broadcast(localTotalForoPendientesCount, data[0]+data[1], broadCastName);
                });
             };
             
            //update();
            //$rootScope.mails_interval = $interval(update, 60000);

            this.getTotalForoPendientesCount     = function() { return localTotalForoPendientesCount; };
            this.update                          = function() { update(); };
            this.getBroadCastName                = function() { return broadCastName; };

            $rootScope.$on('updatedItem',update);
        }
    ]).service('JuntaService', [
        '$rootScope', 'juntaManager',
        function($rootScope, juntaManager) {
            var broadCastName = 'juntaBroadcast';
            
            this.getBroadCastName = function() {
                return broadCastName;
            };
            
            this.update = function() {
                juntaManager.getJunta().then(function(junta) {
                    $rootScope.$broadcast(broadCastName, junta);
                });
            };
        }
    ]);
}).call(this);