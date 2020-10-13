(function() {
    'use strict';

    angular.module('adminWorkspaceControllers'
    ).controller('editMessageCtrl',[
        '$scope', '$routeParams', '$http', '$window', 'logger', '$location','anonymousToken','uploaderProvider', function($scope, $routeParams, $http, $window, logger, $location, anonymousToken, uploaderProvider){
            $scope.message = {body: ''};
            $scope.originalMessage = '';

            $scope.alerts = [];
            var checkOwner = function(autorID){
                $http.get(ApiBase+'/users/userid')
                    .success(function(user){
                        if(user.id !== autorID){
                        $scope.alerts.push({
                                type: 'warning',
                                msg:  '¡Atención! el mensaje en edición pertenece a otro usuario.'
                            });
                        }
                    });
            };
            

            $scope.isValid = function(){
                return ($scope.message.body !== '' && $scope.message.body !== $scope.originalMessage) 
                    || $scope.uploader.queue.length != 0;
            }
            
            $http.get(ApiBase + '/messages/' + $routeParams.id)
                .success(function(messageData){
                    $scope.message = messageData["messages"];
                    $scope.originalMessage = $scope.message.body;
                    checkOwner($scope.message.autor.id);

                    $scope.uploader.setAdditional($scope.message.documents);
                }).catch(function(){
                    $location.path("/404").replace();
                });

            $scope.submit = function(message){
                message.token = $scope.uploader.getToken();
                $http.put(ApiBase + '/messages/' + message.id, message)
                    .success(function(){
                        logger.logSuccess("Mensaje editado correctamente.");
                        $window.history.back();
                    })
                    .error(function(){
                        logger.logError("Se ha producido un error. Por favor, inténtelo de nuevo.");
                    });
            };

            var uploaderInit;
            (uploaderInit = function(){
               $scope.uploaderID = "messageUploader"+$routeParams.id;
               $scope.uploader   = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken() );
             })();
        }
    ]);

}).call(this);