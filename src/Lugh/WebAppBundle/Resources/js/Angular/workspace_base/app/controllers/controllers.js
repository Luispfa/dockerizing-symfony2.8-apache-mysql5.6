(function() {
    'use strict';
    angular.module('workspaceControllers', [
    ]).controller('DashboardCtrl', ['$scope', function($scope) {}]
 ).controller('HeaderCtrl', ['$scope', 'accionistasManager', 'localize','AppService','$localStorage', 
     function($scope, accionistasManager, localize, AppService, $localStorage){
        $scope.i18n     = localize.getLocalizedString;
         
        $scope.username = "id00073_app:header:user";
        $scope.doLogout = function()
        {
            AppService.doLogout();
        }
        $scope.brand = function() {
                return $localStorage['Config.customer.title'] === undefined ? '' : $localStorage['Config.customer.title'];
        };
        accionistasManager.getAccionista().then(function(accionista){
                $scope.username = accionista.name;
                $scope.main.name = accionista.name;
                $scope.is_user_cert = accionista.is_user_cert;
            }).catch(function(){
                
            });
  }]).controller('MailsSwitch', ['$scope', "$routeParams", function($scope, $routeParams){
            var lMail = function(id) {
                $scope.template = WebDefault + '/workspace/views/app/header/mail/single.html';
                $scope.mai_id = id;
            };
            
          $scope.templates =
          {
            inbox:   WebDefault + '/workspace/views/app/header/mail/inbox.html',
            outbox:  WebDefault + '/workspace/views/app/header/mail/outbox.html',
            compose: WebDefault + '/workspace/views/app/header/mail/compose.html',
            error:   WebDefault + '/workspace/views/app/header/mail/error.html'
          };
          
          if ($routeParams.mai_id == undefined)
          {
              $scope.template = $scope.templates['inbox'];
          }
          else if ($scope.templates[$routeParams.mai_id] !== undefined)
          {
              $scope.template = $scope.templates[$routeParams.mai_id];
              $scope.userto = '';
              $scope.option = 'admin';
          }
          else {
              lMail($routeParams.mai_id);
          }
          $scope.$on('loadTemplateMail', function(event, args) {
                    $scope.template = $scope.templates[args];
          });
          $scope.loadTemplateMail = function(template, option, userto) {
              $scope.template = $scope.templates[template];
              $scope.userto = userto;
              $scope.option = option;
          };

          $scope.loadMail = function(id) {
            lMail(id);
        };
        
  }]).controller('NavCtrl', [
    
    function() {    
        
        
  }]).controller('MailComposeCtrl', [
        '$scope','$routeParams','mailsManager','mailEventOut','logger','$rootScope', function($scope,$routeParams,mailsManager,mailEventOut,logger,$rootScope){
            $scope.mail = {
                subject: '',
                body:    ''
            };

            $scope.valid = function(){
                return $scope.mail.subject !== '' && $scope.mail.body !== '';
            };

            $scope.sendMail = function() {
                var tomail = $scope.userto;
                var option = $scope.option;
                if ($routeParams.email !== undefined)
                {
                    tomail = $routeParams.email;
                    option = 'address';
                }
                var data = {
                    subject: $scope.mail.subject,
                    body:    $scope.mail.body,
                    to:      tomail
                };

                
                mailsManager.createMail(data, option).then(function() {
                    mailEventOut.update();
                    logger.logSuccess("id00213_app:logger:mail-success");
                },function(reason) {
                    logger.logError("id00214_app:logger:mail-error");
                });

                $rootScope.$broadcast('loadTemplateMail', 'inbox');
            }
            
            $scope.sendMailContacto = function() {
                $scope.mail.subject = 'Solicitud de contacto - ' + $scope.mail.subject;
                $scope.sendMail();
            }
            
            $scope.sendMailError = function() {
                $scope.mail.subject = 'Notificar error - ' + $scope.mail.subject;
                $scope.sendMail();
            }
        }
  ]);
}).call(this);      