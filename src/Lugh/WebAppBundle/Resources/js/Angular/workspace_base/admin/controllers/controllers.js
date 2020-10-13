(function() {
    'use strict';
    angular.module('adminWorkspaceControllers', [
    ]).controller('MailsSwitch', ['$scope', "$routeParams", function($scope, $routeParams){
            var lMail = function(id) {
                $scope.template = WebDefault + '/admin_workspace/views/app/header/mail/single.html';
                $scope.mai_id = id;
            };
            
          $scope.templates =
                { 
            inbox:   WebDefault + '/admin_workspace/views/app/header/mail/inbox.html',
            outbox:  WebDefault + '/admin_workspace/views/app/header/mail/outbox.html',
            compose: WebDefault + '/admin_workspace/views/app/header/mail/compose.html',
            error:   WebDefault + '/admin_workspace/views/app/header/mail/error.html'
            };
          if ($routeParams.mai_id == undefined)
          {
              $scope.template = $scope.templates['inbox'];
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
    '$scope', 'ActionsCountEvent','$rootScope', function($scope, ActionsCountEvent, $rootScope) {
        $scope.accionistasPendientesCount   = ActionsCountEvent.getAccionistasPendientesCount;
        $scope.propuestasPendientesCount    = ActionsCountEvent.getPropuestasPendientesCount;
        $scope.iniciativasPendientesCount   = ActionsCountEvent.getIniciativasPendientesCount;
        $scope.ofertasPendientesCount       = ActionsCountEvent.getOfertasPendientesCount;
        $scope.peticionesPendientesCount    = ActionsCountEvent.getPeticionesPendientesCount;
        $scope.totalItemsPendientesCount    = ActionsCountEvent.getTotalItemsPendientesCount;
        $scope.threadsPendientesCount       = ActionsCountEvent.getThreadsPendientesCount;
        $scope.questionsPendientesCount     = ActionsCountEvent.getQuestionsPendientesCount;
        //$scope.totalAdhesionPendientesCount = ActionsCountEvent.getTotalAdhesionPendientesCount;
        $scope.totalforoPendientesCount     = ActionsCountEvent.getTotalForoPendientesCount;  
        $scope.totalPendientesCount         = ActionsCountEvent.getTotalPendientesCount;    
        
        $rootScope.$on('accionistasPendientesCount',    function(event, args) { $scope.accionistasPendientesCount = args;   });
        $rootScope.$on('propuestasPendientesCount',     function(event, args) { $scope.propuestasPendientesCount = args;    });
        $rootScope.$on('iniciativasPendientesCount',    function(event, args) { $scope.iniciativasPendientesCount = args;   });
        $rootScope.$on('ofertasPendientesCount',        function(event, args) { $scope.ofertasPendientesCount = args;       });
        $rootScope.$on('peticionesPendientesCount',     function(event, args) { $scope.peticionesPendientesCount = args;    });
        $rootScope.$on('totalItemsPendientesCount',     function(event, args) { $scope.totalItemsPendientesCount = args;    });
        $rootScope.$on('threadsPendientesCount',        function(event, args) { $scope.threadsPendientesCount = args;       });
        $rootScope.$on('questionsPendientesCount',      function(event, args) { $scope.questionsPendientesCount = args;     });
        $rootScope.$on('totalPendientesCount',          function(event, args) { $scope.totalPendientesCount = args;         });
        //$rootScope.$on('totalAdhesionPendientesCount',  function(event, args) { $scope.totalAdhesionPendientesCount = args; });
        $rootScope.$on('totalForoPendientesCount',      function(event, args) { $scope.totalforoPendientesCount = args;     });
        
        
    }]).controller('MailComposeToUserCtrl',[
        '$scope','$routeParams','mailsManager','mailEventOut','logger','$rootScope', function($scope,$routeParams,mailsManager,mailEventOut,logger,$rootScope){
            

            $scope.mail = {

                subject: '',
                body:    ''
            };
            mailsManager.getMail($scope.mai_id).then(function(mail){
                $scope.mail.to = mail.userfromname;
                $scope.mail.subject = '';
                if(typeof mail.subject != 'undefined'){
                    
                    if(typeof mail.subject.split(" : ")[1] != 'undefined'){
                        $scope.mail.subject = 'Re: '+mail.subject.split(" : ")[1];
                    }
                    else{
                        $scope.mail.subject = 'Re: '+mail.subject;
                    }
                }
            });

            $scope.valid = function(){
                return $scope.mail.subject !== '' && $scope.mail.body !== '';
            };

            $scope.sendMail = function() {
                var tomail = $scope.userto;
                var option = $scope.option;
                if ($routeParams.email !== undefined)
                {
                    tomail = $routeParams.email;
                    //option = 'address';
                    option = 'user';
                }
                var data = {
                    subject: $scope.mail.subject,
                    body:    $scope.mail.body,
                    to:      tomail
                };
                
                mailsManager.createMail(data, option).then(function() {
                    mailEventOut.update();
                    logger.logSuccess("Mail enviado correctamente");
                },function(reason) {
                    logger.logError("Error al enviar el mail");
                });

                $rootScope.$broadcast('loadTemplateMail', 'inbox');
            }
        }
    ]).controller('registroAdminFormCtrl',[
        '$scope','$http','anonymousToken','$location','logger','localize','documentValidation','jsUtils','validateAccionistaAdmin','$anchorScroll','$timeout','uploaderProvider','$localStorage','$modal',
        function($scope,$http,anonymousToken,$location,logger,localize,documentValidation,jsUtils,validateAccionista, $anchorScroll,$timeout,uploaderProvider,$localStorage, $modal){

            var modalInstance;
            /*$scope.model = { //No longer required cause the captcha is not included.
                key: '6LeaivUSAAAAACc-MpRhDk5UW07UT0hr8qXhvKuT'
            };*/

            var uploaderA = uploaderProvider.get("documentacionAUploader");
            var uploaderB = uploaderProvider.get("documentacionBUploader");

            $scope.showAlertsTop    = $localStorage['Config.register.alertsTop'] == 1;
            $scope.showAlertsField  = $localStorage['Config.register.alertsField'] == 1;
            $scope.accionesMin      = $localStorage['Config.accionista.accionesMin'] === undefined ? 0 : $localStorage['Config.accionista.accionesMin'];
            $scope.i18n             = localize.getLocalizedString;
            $scope.token            = anonymousToken.getToken();
            $scope.emailc           = "";
            $scope.errors           = [];
            $scope.langs = {};

            var languages  = {
                es:{label:"language_spanish",   value:'es_es'},
                ca:{label:"language_catalan",   value:'ca_es'},
                gl:{label:"language_gaelican",  value:'gl_es'},
                en:{label:"language_english",   value:'en_gb'}
            };
            _.map(JSON.parse($localStorage['Config.langs.active']), function(value,key){
                var lang = languages[key];
                if(value === 1)$scope.langs[lang.value] = lang.label;
            });

            $scope.updateLang = function(){
                return $scope.lang = localize.getLanguage();
            };

            $scope.docTypes = {
                nif:    "id00155_app:doctype:nif",
                cif:    "id00156_app:doctype:cif",
                nie:    "id00157_app:doctype:nie",
                otros:  "id00158_app:doctype:other"
            };

            $scope.newAccionista = {
                accionista: {
                    name:           '',
                    personaType:    "pf",
                    documentType:   "nif",
                    documentNum:    "",
                    sharesNum:      0,
                    representedBy:  "",
                    telephone:      ""
                },
                user: {
                    username:        '',
                    email:          "",
                    token:          $scope.token
                },
                LOPD: false,
                lang: 'es_es'
            };

            var checkErrors = function(){
                $scope.errors = [];
                $scope.validation = validateAccionista.validate($scope.newAccionista,!$scope.form.$error.equal);

                var error = false;
                for(var index in $scope.validation){
                    error = true;
                    $scope.validation[index] = $scope.i18n($scope.validation[index]);
                    $scope.errors.push({
                        type: 'warning',
                        msg:  $scope.validation[index]
                    });
                }

                if(error){
                    $timeout(function(){
                        $location.hash('topForm');
                        $anchorScroll();
                    });
                }

                return !error;
            };

            $scope.closeAlert = function(index){
                $scope.errors.splice(index,1);
            };

            $scope.submit = function(form){
                //$scope.newAccionista.reCaptcha = vcRecaptchaService.data();

                if(checkErrors()){
                    $scope.newAccionista.accionista.sharesNum = jsUtils.cleanNumber($scope.newAccionista.accionista.sharesNum);
                    modalInstance = $modal.open({
                        templateUrl: "myModalContent.html",
                        backdrop: 'static'
                    });
                    $http.post(ApiBase + '/accionistas', $scope.newAccionista)
                        .success(function(data) {
                            if(typeof data.success !== 'undefined'){
                                window.location.href = WebDefault + '/';
                                modalInstance.dismiss('Valid');
                            }
                            else if(data.error !== undefined)
                            {
                                modalInstance.dismiss('Error');
                                logger.logError(data.error);
                                //vcRecaptchaService.reload();
                            }
                        })
                        .error(function() {
                            modalInstance.dismiss('Error');
                            //vcRecaptchaService.reload();
                        });
                }
            }
        }
    ]).controller('estadisticasCtrl',
        ['$scope','itemsManager','accionistasAdminManager',"actionsManager",'$http','$q',
            function($scope,ItemsManager,AccionistasManager,ActionsManager,$http,$q){
/*
* 1 ->  Pendiente
* 2 ->  Publico
* 3 ->  Retornado
* 4 ->  Rechazado
* tambien los threads
* */
                var data = {
                    propuestas:     {publicado : 0},
                    iniciativas:    {publicado : 0},
                    ofertasderep:   {publicado : 0 , descartado : 0},
                    propuestasderep:{publicado : 0 , pendiente : 0},
                    usuarios:       {publicado : 0 , aprobacion : 0 , revision : 0},
                    visitas:        {}
                };
                $scope.accionistas  = new Array();
                $scope.foro         = new Array();
                $scope.derecho      = new Array();
                $scope.voto         = new Array();

                AccionistasManager.loadAllAccionistas().then(function(accionistas){

                    accionistas.forEach(function(accionista) {
                        if(accionista.item_accionista.state == 1)
                            data.usuarios.aprobacion++;

                        if(accionista.item_accionista.state == 2){
                            data.usuarios.publicado++;
                            $scope.accionistas.push({
                                tipo:     'id00470_admin:header:estadisticas-de-uso:lista:accionista',
                                nombre:   accionista.name,
                                acciones: accionista.shares_num
                            });
                        }

                        if(accionista.item_accionista.state == 3)
                            data.usuarios.revision++;
                    });

                    $scope.usuariosNaccionistas = {
                        publicado:              data.usuarios.publicado  + ' - (' + accionistas.length   + ')'  ,
                        pendienteDeAprobacion:  data.usuarios.aprobacion + ' - (' + accionistas.length   + ')'  ,
                        pendienteDeRevision:    data.usuarios.revision   + ' - (' + accionistas.length   + ')'
                    };
                });


                ItemsManager.getAll().then(function(items){

                    items.proposals.forEach(function(proposal) {
                        if(proposal.state == 2){
                            data.propuestas.publicado++;
                            proposal.getAdhesions().then(function(adhesions){
                                $scope.foro.push({tipo:'id00452_admin:header:estadisticas-de-uso:propuestas', autor:proposal.autor.name+' ('+proposal.autor.shares_num+')', titulo:proposal.title, adhesiones:adhesions.length});
                            });
                        }
                    });

                    items.initiatives.forEach(function(initiative) {
                        if(initiative.state == 2){
                            data.iniciativas.publicado++;
                            initiative.getAdhesions().then(function(adhesions) {
                                $scope.foro.push({tipo:'id00453_admin:header:estadisticas-de-uso:iniciativas', autor:initiative.autor.name+' ('+initiative.autor.shares_num+')', titulo:initiative.title, adhesiones:adhesions.length});
                            });
                        }
                    });

                    items.offers.forEach(function(offer) {
                        if(offer.state == 2){
                            data.ofertasderep.publicado++;
                            offer.getAdhesions().then(function(adhesions) {
                                $scope.foro.push({tipo:'id00454_admin:header:estadisticas-de-uso:oferta-representacion', autor:offer.autor.name+' ('+offer.autor.shares_num+')', titulo:offer.title, adhesiones:adhesions.length});
                            });
                        }

                        if(offer.state == 4)
                            data.ofertasderep.descartado++;
                    });

                    items.requests.forEach(function(request) {
                        if(request.state == 2){
                            data.propuestasderep.publicado++;
                            request.getAdhesions().then(function(adhesions) {
                                $scope.foro.push({tipo:'id00455_admin:header:estadisticas-de-uso:propuesta-representacion', autor:request.autor.name+' ('+request.autor.shares_num+')', titulo:request.title, adhesiones:adhesions.length});
                            });
                        }

                        if(request.state == 1)
                            data.propuestasderep.pendiente++;
                    });

                    items.threads.forEach(function(thread){
                        ItemsManager.getByTypeID("threads",thread.id).then(function(thethread){
                            $scope.derecho.push({autor:thread.autor.name + ' ('+thread.autor.shares_num+')' , titulo:thread.subject, mensajes:thread.messages.length});
                        });
                    });



                    $scope.propuestas = {publicado:  data.propuestas.publicado  + ' - (' + items.proposals.length   + ')'};

                    $scope.iniciativas ={publicado:  data.iniciativas.publicado + ' - (' + items.initiatives.length + ')'};

                    $scope.ofertasDeRepresentacion = {
                        publicado:  data.ofertasderep.publicado     + ' - (' + items.offers.length   + ')',
                        descartado: data.ofertasderep.descartado    + ' - (' + items.offers.length   + ')'
                    };

                    $scope.propuestasDeRepresentacion = {
                        publicado:  data.propuestasderep.publicado  + ' - (' + items.requests.length   + ')',
                        pendiente: data.propuestasderep.pendiente + ' - (' + items.requests.length   + ')'
                    };

                });


                ActionsManager.getVotos().then(function(votos){
                    votos.forEach(function(voto){

                        $scope.voto.push({
                            tipo:(voto.discr == 0)? 'id00475_admin:header:estadisticas-de-uso:lista:voto' : 'id00476_admin:header:estadisticas-de-uso:lista:delegacion',
                            autor: voto.accionista.name,
                            acciones:voto.shares_num
                        });
                    });
                });

                ActionsManager.getDelegaciones().then(function(votos){
                    votos.forEach(function(voto){

                        $scope.voto.push({
                            tipo:(voto.discr == 0)? 'id00475_admin:header:estadisticas-de-uso:lista:voto' : 'id00476_admin:header:estadisticas-de-uso:lista:delegacion',
                            autor: voto.accionista.name,
                            acciones:voto.shares_num
                        });
                    });
                });
                var deferred = $q.defer();
                $http.post(WebDefault + '/admin_workspace/statisticsgraph',
                {params:[
                    {type:1,period:'day'   , date:'last60'},//date:{start:'-2 months',end:''}},
                    {type:2,period:'day'   , date:'date'},//date:{start:'yesterday',end:''}},
                    {type:2,period:'year'  , date:'date'}//date:{start:'-1 hour'  ,end:''}},
                ]})
                .success(function(visitas) {
                    data.visitas.bimensual  = visitas.response[0];
                    data.visitas.hoy        = visitas.response[1];
                    data.visitas.horas      = visitas.response[2];

                    deferred.resolve(visitas);
                })
                .error(function() {
                    deferred.reject();
                });
                var a = deferred.promise;

                a.then(function(){
                    $scope.visitas = {
                        bimensual:  data.visitas.bimensual  || 'https://header.foroelectronico.es/f_stats/ImageGraphVisitDays',
                        porhoras:   data.visitas.horas      || 'https://header.foroelectronico.es/f_stats/ImageGraphVisitHours/period/year/date/2015-07-08',
                        hoy:        data.visitas.hoy        || 'https://header.foroelectronico.es/f_stats/ImageGraphVisitHours/date/2015-07-08',
                    };
                });
                //console.log(a);

                var deferred2 = $q.defer();
                $http.get(WebDefault + '/admin_workspace/lastvisitoractions')
                .success(function(actions) {
                        //console.log(actions);
                    $scope.lastVisitoractions = actions.lastVisitorActions;
                    deferred2.resolve(actions);
                })
                .error(function() {
                    deferred2.reject();
                });






            }
    ]);
}).call(this);
