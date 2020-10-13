(function() {
    'use strict';
    angular.module('adminWorkspaceControllers'
    ).controller('adminPendingCtrl', [
        '$scope','itemsManager','accionistasAdminManager','$location','typeService','adhesionsManager','localize',function($scope,itemsManager,accionistasManager,$location,typeService,adhesionsManager,localize){
            $scope.items       = [];
            $scope.threads     = [];
            $scope.questions   = [];
            $scope.accionistas = [];
            $scope.adhesions   = [];

            var types = typeService.getTypesToURL();
            var typesAdh = typeService.getSingularTypes();
            itemsManager.getByState("pending").then(function(itemList){
                $scope.items = $scope.items.concat(itemList['proposals']);
                $scope.items = $scope.items.concat(itemList['initiatives']);
                $scope.items = $scope.items.concat(itemList['offers']);
                $scope.items = $scope.items.concat(itemList['requests']);
                $scope.threads = itemList['threads'];
                $scope.questions = itemList['questions'];
            });

            $scope.typeTags = typeService.getShortTags(true);
            $scope.i18n     = localize.getLocalizedString;

            accionistasManager.loadAllByState(1).then(function(accionistas){
                $scope.accionistas = _.union($scope.accionistas, accionistas);
            });
            
            accionistasManager.loadAllAppsByState(1).then(function(accionistas){
                $scope.accionistas = _.union($scope.accionistas, accionistas);

                for(var key in $scope.accionistas){
                    $scope.accionistas[key].pendientes = 1;
                    for(var key2 in $scope.accionistas[key].app){
                        if($scope.accionistas[key].app[key2].state == 1)
                            $scope.accionistas[key].pendientes += 1;
                    }
                }
            });

            /*//Descomentar para incluir adhesiones//
            adhesionsManager.loadAllByState(1).then(function(adhesions){
                $scope.adhesions = adhesions;

                var length = 0;
                for(var i in $scope.adhesions){
                    length += $scope.adhesions[i].length;
                }

                $scope.adhesions.length = length;
            });*/

            $scope.loadItem = function(item){
                $location.path("/app/foro/"+types[item.type]).search("id",item.id);
            };

            $scope.loadAccionista = function(accionista){
                //$location.path("/app/profile-admin").search("id",accionista.id);
                $location.path("/app/users/profile").search("id",accionista.id);
            };

            $scope.loadAdhesion = function(adhesion,type){
                $location.path("/app/foro/adhesion/"+typesAdh[type]).search("id",adhesion.id);
            };

            $scope.goToThread = function(thread){
                $location.path("/app/derecho/solicitud").search("id",thread.id);
            };
            $scope.goToQuestion = function(question){
                $location.path("/app/av/ruego").search("id",question.id);
            };
        }
    ]);

}).call(this);
