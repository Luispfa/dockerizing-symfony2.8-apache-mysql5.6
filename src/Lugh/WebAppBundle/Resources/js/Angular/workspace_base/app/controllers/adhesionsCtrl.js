(function() {
    'use strict';

    angular.module('workspaceControllers'
    ).controller('adhesionProposalCtrl',[
        '$scope', '$routeParams', 'adhesionsManager', 'stateService', 'logger', '$location', 
        function($scope, $routeParams, adhesionsManager, stateService, logger, $location){
            
            if($routeParams.id == undefined)
                $location.path("/404").replace();

            adhesionsManager.getByTypeID("proposals",$routeParams.id).then(function(adhesion){
                $scope.adhesion = adhesion;
            }).catch(function(){
                $location.path("/404").replace();
            });

            $scope.setState = function(){
                $scope.adhesion.setState(stateService.getAliasState($scope.adhesion.state)).then(function(adhesion){
                    if (adhesion.error === undefined)
                        logger.logSuccess("id00403_app:logger:change-state-successful");
                    else
                        logger.logError(adhesion.error);
                }).catch(function(){
                    logger.logError("id00201_app:logger:server-error");
                });
            }
            
        }
    ]).controller('adhesionInitiativeCtrl',[
        '$scope', '$routeParams', 'adhesionsManager', 'stateService', 'logger', '$location', 
        function($scope, $routeParams, adhesionsManager, stateService, logger, $location){
            if($routeParams.id == undefined)
                $location.path("/404").replace();

            adhesionsManager.getByTypeID("initiatives",$routeParams.id).then(function(adhesion){
                $scope.adhesion = adhesion;
            }).catch(function(){
                $location.path("/404").replace();
            });

            $scope.setState = function(){
                $scope.adhesion.setState(stateService.getAliasState($scope.adhesion.state)).then(function(adhesion){
                    if (adhesion.error === undefined)
                        logger.logSuccess("id00403_app:logger:change-state-successful");
                    else
                        logger.logError(adhesion.error);
                }).catch(function(){
                    logger.logError("id00201_app:logger:server-error");
                });
            }
        }
    ]).controller('adhesionOfferCtrl',[
        '$scope', '$routeParams', 'adhesionsManager', 'stateService', 'logger', '$location', 
        function($scope, $routeParams, adhesionsManager, stateService, logger, $location){
            if($routeParams.id == undefined)
                $location.path("/404").replace();

            adhesionsManager.getByTypeID("offers",$routeParams.id).then(function(adhesion){
                $scope.adhesion = adhesion;
            }).catch(function(){
                $location.path("/404").replace();
            });

            $scope.setState = function(){
                $scope.adhesion.setState(stateService.getAliasState($scope.adhesion.state)).then(function(adhesion){
                    if (adhesion.error === undefined)
                        logger.logSuccess("id00403_app:logger:change-state-successful");
                    else
                        logger.logError(adhesion.error);
                }).catch(function(){
                    logger.logError("id00201_app:logger:server-error");
                });
            }
        }
    ]).controller('adhesionRequestCtrl',[
        '$scope', '$routeParams', 'adhesionsManager', 'stateService', 'logger', '$location', 
        function($scope, $routeParams, adhesionsManager, stateService, logger, $location){
            if($routeParams.id == undefined)
                $location.path("/404").replace();
            
            adhesionsManager.getByTypeID("requests",$routeParams.id).then(function(adhesion){
                $scope.adhesion = adhesion;
            }).catch(function(){
                $location.path("/404").replace();
            });

            $scope.setState = function(){
                $scope.adhesion.setState(stateService.getAliasState($scope.adhesion.state)).then(function(adhesion){
                    if (adhesion.error === undefined)
                        logger.logSuccess("id00403_app:logger:change-state-successful");
                    else
                        logger.logError(adhesion.error);
                }).catch(function(){
                    logger.logError("id00201_app:logger:server-error");
                });
            }
        }
    ]);

}).call(this);