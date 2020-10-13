(function() {
  'use strict';
  angular.module('app.services.mails', []).factory('mailsManager', ['$http', '$q', 'Mail','AppService', function($http, $q, Mail, AppService) {
    var mailsManager = {
        _pool: {},
        _retrieveInstance: function(mailId, mailData) {
            var instance = this._pool[mailId];

            if (instance) {
                instance.setData(mailData);
            } else {
                instance = new Mail(mailData);
                this._pool[mailId] = instance;
            }

            return instance;
        },
        _search: function(mailId) {
            return this._pool[mailId];
        },
        _load: function(mailId, deferred) {
            var scope = this;

            $http.get(ApiBase + '/logmails/' + mailId)
                .success(function(mailData) {
                    var mail = scope._retrieveInstance(mailData.id, mailData);
                    deferred.resolve(mail);
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject();
                });
        },
        /* Public Methods */
        /* Use this function in order to get a mail instance by it's id */
        getMail: function(mailId) {
            var deferred = $q.defer();
            var mail = this._search(mailId);
            if (mail) {
                deferred.resolve(mail);
            } 
            else{
                this._load(mailId, deferred);
            }
            return deferred.promise;
        },
        createMail: function(mailData, option) {
            var deferred = $q.defer();
            var url = '';
            //If there's only one address, this way we won't need to define an array from the controller
            if(!(mailData.to instanceof Array) && mailData.to !== undefined){
                mailData.to = [mailData.to];
            }
            switch(option) {
                case 'admin':
                    url = "/mails/toadmins";
                    break;
                case 'super':
                    url = "/mails/tosupers";
                    break;
                case 'user':
                    url = "/mails/toemailusers";
                    break;
                case 'address':
                    url = "/mails/toemails";
                    break;
                default:
                    url = "/mails/toadmins";
            }
            

            $http({
                 url:       ApiBase + url,
                 method:    "POST",
                 headers:   {'Content-Type': "application/json"},
                 data:      JSON.stringify(mailData),
                 withCredentials: true
                 
            }).success(function(mailData) {
                    if (mailData.success)
                    {
                        deferred.resolve(true);
                    }
                    else
                    {
                        deferred.reject();
                    }
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject();
                });
            return deferred.promise;
        },
        /* Use this function in order to get instances of all the mails */
        loadAllMails: function() {
            var deferred = $q.defer();
            var scope = this;
            //$http.get(ApiBase + '/logmails/all/in')
            $http.get(ApiBase + '/logmails/mine/in')
                .success(function(mailsArray) {
                    var mails = [];
                    mailsArray.forEach(function(mailData) {
                        var mail = scope._retrieveInstance(mailData.id, mailData);
                        mails.push(mail);
                    });

                    deferred.resolve(mails);
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject();
                });
            return deferred.promise;
        },
        loadAllOutMails: function() {
            var deferred = $q.defer();
            var scope = this;
            //$http.get(ApiBase + '/logmails/mine/out')
            $http.get(ApiBase + '/logmails/all/out')
                .success(function(mailsArray) {
                    var mails = [];
                    mailsArray.forEach(function(mailData) {
                        var mail = scope._retrieveInstance(mailData.id, mailData);
                        mails.push(mail);
                    });

                    deferred.resolve(mails);
                })
                .error(function(data, status, headers, config) {
                    AppService.UnAuthorize(status);
                    deferred.reject();
                });
            return deferred.promise;
        },
        /*  This function is useful when we got somehow the mail data and we wish to store it or update the pool and get a mail instance in return */
        setMail: function(mailData) {
            var scope = this;
            var mail = this._search(mailData.id);
            if (mail) {
                mail.setData(mailData);
            } else {
                mail = scope._retrieveInstance(mailData);
            }
            return mail;
        }

    };
    return mailsManager;
}]).factory('Mail', ['$http', '$q', function($http,$q) {
    function Mail(mailData){
        if (mailData) {
            this.setData(mailData);
        }
        // Some other initializations
    }
    Mail.prototype = {
        setData: function(mailData) {
            angular.extend(this, mailData);
        },
        delete: function() {
            //$http.delete('ourserver/books/' + bookId);
        },
        update: function() {
            //$http.put('ourserver/books/' + bookId, this);
        },
    };
    return Mail;
}]);

}).call(this);