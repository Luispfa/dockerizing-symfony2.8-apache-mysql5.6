(function() {
  'use strict';
  angular.module('app.services.notifications', []).factory('notificationsManager', ['$http', '$q', 'Notification', 'AppService', function($http, $q, Notification, AppService) {
    var notificationsManager = {
        _pool: {},
        _retrieveInstance: function(notificationId, notificationData) {
            var instance = this._pool[notificationId];

            if (instance) {
                instance.setData(notificationData);
            } else {
                instance = new Notification(notificationData);
                this._pool[notificationId] = instance;
            }

            return instance;
        },
        _search: function(notificationId) {
            return this._pool[notificationId];
        },
        _load: function(notificationId, deferred) {
            var scope = this;

            $http.get('ourserver/notifications/' + notificationId)
                .success(function(notificationData) {
                    var notification = scope._retrieveInstance(notificationData.id, notificationData);
                    deferred.resolve(notification);
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject();
                });
        },
        /* Public Methods */
        /* Use this function in order to get a notification instance by it's id */
        getNotification: function(notificationId) {
            var deferred = $q.defer();
            var notification = this._search(notificationId);
            if (notification) {
                deferred.resolve(notification);
            } 
            else{
                this._load(notificationId, deferred);
            }
            return deferred.promise;
        },
        /* Use this function in order to get instances of all the notifications */
        loadAllNotifications: function() {
            var deferred = $q.defer();
            var scope = this;
            $http.get(ApiBase + '/logmails/mine/workflow')
                .success(function(notificationsArray) {
                    var notifications = [];
                    notificationsArray.forEach(function(notificationData) {
                        var notification = scope._retrieveInstance(notificationData.id, notificationData);
                        notification.notification = notification.notification.replace('external','');
                        notifications.push(notification);
                    });

                    deferred.resolve(notifications);
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject();
                });
            return deferred.promise;
        },
        /*  This function is useful when we got somehow the notification data and we wish to store it or update the pool and get a notification instance in return */
        setNotification: function(notificationData) {
            var scope = this;
            var notification = this._search(notificationData.id);
            if (notification) {
                notification.setData(notificationData);
            } else {
                notification = scope._retrieveInstance(notificationData);
            }
            return notification;
        }

    };
    return notificationsManager;
}]).factory('Notification', ['$http', '$q', function($http,$q) {
    function Notification(notificationData){
        if (notificationData) {
            this.setData(notificationData);
        }
        // Some other initializations
    }
    Notification.prototype = {
        setData: function(notificationData) {
            angular.extend(this, notificationData);
        },
        delete: function() {
            //$http.delete('ourserver/books/' + bookId);
        },
        update: function() {
            //$http.put('ourserver/books/' + bookId, this);
        },
    };
    return Notification;
}]);

}).call(this);