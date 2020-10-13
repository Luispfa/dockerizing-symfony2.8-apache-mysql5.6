(function() {
    'use strict';
    angular.module('sharedControllers', [
    ]).controller('NotificationsCtrl', ['$scope','$rootScope','notificationsManager', '$interval', '$localStorage',
        function($scope, $rootScope, notificationsManager, $interval, $localStorage){
        var mapid = function(obj) {
            return obj.id; 
        }
        var update = function() {
            notificationsManager.loadAllNotifications().then(function(notifications) {
                if (JSON.stringify(notifications) !== JSON.stringify($rootScope.numNotifications))
                { 
                    var filterid = function(obj){
                        return _.indexOf(notifys,obj.id) !== -1; 
                    }
                    var notifysParse = ($localStorage.notifys === undefined) ? [] : $localStorage.notifys;
                    var notifys = _.difference(notifications.map(mapid), notifysParse);
                    var notifysFiltered = notifications.filter(filterid);
                    $rootScope.numNotifications = notifysFiltered;

                }  
            });
        };       
        $scope.notificationsArray = function() {
            notificationsManager.loadAllNotifications().then(function(notifications) {
                var notifysParse = ($localStorage.notifys === undefined) ? [] : $localStorage.notifys;
                var notifys = _.difference(notifications.map(mapid), notifysParse);
                delete $localStorage.notifys;
                $localStorage.$default({notifys:_.union(notifysParse,notifys)});
                $scope.notifications = notifications;
                $rootScope.numNotifications = [];
            });
        };
        update();
        //$interval.cancel($rootScope.notifications_interval);
        //$rootScope.notifications_interval = $interval(update, 60000);
        $scope.notNotifications = function() {
            return ($rootScope.numNotifications === undefined) ? true : $rootScope.numNotifications.length === 0;
        }
  }]).controller('MailsCtrl', ['$scope','mailEvent', '$location', function($scope, mailEvent, $location){
        
        $scope.mails_in = mailEvent.getMailsIn();
        $scope.$on('mails_in', function(event, args) {
                    $scope.mails_in = args;
                });
        $scope.notMails = function(){
            return mailEvent.getMailsIn() == 0;
        }; 
        $scope.loadSingleMail = function(id) {
            $location.path("/app/header/mail/mail").search("mai_id",id);
        };
        
        
  }]).controller('MailsOutCtrl', ['$scope','mailEventOut',  function($scope, mailEventOut){
        
        $scope.mails_out = mailEventOut.getMailsOut();
        $scope.$on('mails_out', function(event, args) {
                    $scope.mails_out = args;
                });
        $scope.notMailsOut = function(){
            return mailEventOut.getMailsOut() == 0;
        };

  }]).controller('MailSingleCtrl', ['$scope','mailsManager','$location','$sce', function($scope, mailsManager, $location,$sce){
        mailsManager.getMail($scope.mai_id).then(function(mail){
                $scope.mail = {
                    subject: mail.subject,
                    body: mail.body,
                    mailfrom: mail.mailfrom,
                    mailto: mail.mailto,
                    userfromname: mail.userfromname,
                    usertoname: mail.usertoname,
                    date_time: mail.date_time
                };
            }).catch(function(){
                $location.path("/404").replace();
            });
            $scope.to_trusted = function(html_code) {
                return $sce.trustAsHtml(html_code);
            }
  }]);
 }).call(this);