(function() {
    'use strict';

    angular.module('adminWorkspaceControllers'
    ).controller('adhesionProposalCtrl',[
        '$scope', '$routeParams', 'adhesionsManager', 'stateService', 'logger', '$location', function($scope, $routeParams, adhesionsManager, stateService, logger, $location){
            if($routeParams.id == undefined)
                $location.path("/404").replace();

            adhesionsManager.getByTypeID("proposals",$routeParams.id).then(function(adhesion){
                $scope.adhesion = adhesion;
            }).catch(function(){
                $location.path("/404").replace();
            });

            $scope.setState = function(){
                $scope.adhesion.setState(stateService.getAliasState($scope.adhesion.state)).then(function(){
                    logger.logSuccess("Estado modificado correctamente.");
                }).catch(function(){
                    logger.logError("Se ha producido un error al modificar el estado de la adhesión.");
                });
            }
        }
    ]).controller('adhesionInitiativeCtrl',[
        '$scope', '$routeParams', 'adhesionsManager', 'stateService', 'logger', '$location', function($scope, $routeParams, adhesionsManager, stateService, logger, $location){
            if($routeParams.id == undefined)
                $location.path("/404").replace();

            adhesionsManager.getByTypeID("initiatives",$routeParams.id).then(function(adhesion){
                $scope.adhesion = adhesion;
            }).catch(function(){
                $location.path("/404").replace();
            });

            $scope.setState = function(){
                $scope.adhesion.setState(stateService.getAliasState($scope.adhesion.state)).then(function(){
                    logger.logSuccess("Estado modificado correctamente.");
                }).catch(function(){
                    logger.logError("Se ha producido un error al modificar el estado de la adhesión.");
                });
            }
        }
    ]).controller('adhesionOfferCtrl',[
        '$scope', '$routeParams', 'adhesionsManager', 'stateService', 'logger', '$location', function($scope, $routeParams, adhesionsManager, stateService, logger, $location){
            if($routeParams.id == undefined)
                $location.path("/404").replace();

            adhesionsManager.getByTypeID("offers",$routeParams.id).then(function(adhesion){
                $scope.adhesion = adhesion;
            }).catch(function(){
                $location.path("/404").replace();
            });

            $scope.setState = function(){
                $scope.adhesion.setState(stateService.getAliasState($scope.adhesion.state)).then(function(){
                    logger.logSuccess("Estado modificado correctamente.");
                }).catch(function(){
                    logger.logError("Se ha producido un error al modificar el estado de la adhesión.");
                });
            }
        }
    ]).controller('adhesionRequestCtrl',[
        '$scope', '$routeParams', 'adhesionsManager', 'stateService', 'logger', '$location', function($scope, $routeParams, adhesionsManager, stateService, logger, $location){
            if($routeParams.id == undefined)
                $location.path("/404").replace();
            
            adhesionsManager.getByTypeID("requests",$routeParams.id).then(function(adhesion){
                $scope.adhesion = adhesion;
            }).catch(function(){
                $location.path("/404").replace();
            });

            $scope.setState = function(){
                $scope.adhesion.setState(stateService.getAliasState($scope.adhesion.state)).then(function(){
                    logger.logSuccess("Estado modificado correctamente.");
                }).catch(function(){
                    logger.logError("Se ha producido un error al modificar el estado de la adhesión.");
                });
            }
        }
    ]);

}).call(this);