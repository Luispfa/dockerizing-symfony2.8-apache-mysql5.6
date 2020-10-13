(function() {
    'use strict';
    angular.module('adminServices', [
    ]).service('ActionsCountEvent',[
        '$rootScope', '$interval', 'accionistasAdminManager','itemsManager', 'stateService', 'adhesionsManager', '$q','$localStorage',
        function($rootScope, $interval, AccionistasManager, ItemsManager, StateService, AdhesionsManager, $q, $localStorage){

            var localAccionistasPendientesCount   = {count: 0};
            var localPropuestasPendientesCount    = {count: 0};
            var localIniciativasPendientesCount   = {count: 0};
            var localOfertasPendientesCount       = {count: 0};
            var localPeticionesPendientesCount    = {count: 0};
            var localTotalItemsPendientesCount    = {count: 0};
            var localThreadsPendientesCount       = {count: 0};
            var localQuestionsPendientesCount     = {count: 0};
            //var localAdhesionsPendientesCount     = {count: 0};
            var localTotalForoPendientesCount     = {count: 0};
            var localTotalPendientesCount         = {count: 0};

            var broadcast = function(localCount, count, broad) {
                if (localCount.count !== count)
                {
                    $rootScope.$broadcast(broad, count);
                    localCount.count = count;
                }
            };

            var update = function() {
                var deferred1 = $q.defer();
                var deferred2 = $q.defer();
                var deferred3 = $q.defer();
                //var deferred4 = $q.defer();
                var deferredf = $q.defer();
                //var all  = $q.all([deferred1.promise, deferred2.promise, deferred3.promise, deferred4.promise]);
                var all  = $q.all([deferred1.promise, deferred2.promise, deferred3.promise]);
                var foro = $q.all([deferredf.promise, deferred3.promise]);

                var accionistasPendientesCount   = 0;
                var propuestasPendientesCount    = 0;
                var iniciativasPendientesCount   = 0;
                var ofertasPendientesCount       = 0;
                var peticionesPendientesCount    = 0;
                var totalItemsPendientesCount    = 0;
                var threadsPendientesCount       = 0;
                var questionsPendientesCount     = 0;
                var totalPendientesCountAcc      = 0;
                var totalPendientesCountItem     = 0;
                //var totalAdhesionPendientesCount = 0;

                /*AccionistasManager.loadAllAppsByState(1).then(function(accionistas){
                    accionistasPendientesCount = accionistas.length;
                    totalPendientesCountAcc += accionistas.length;
                    broadcast(localAccionistasPendientesCount, accionistasPendientesCount, 'accionistasPendientesCount');
                    deferred4.resolve(totalPendientesCountAcc);
                });*/

                AccionistasManager.loadAllByState(1).then(function(accionistas){
                    accionistasPendientesCount = accionistas.length;
                    totalPendientesCountAcc += accionistas.length;
                    broadcast(localAccionistasPendientesCount, accionistasPendientesCount, 'accionistasPendientesCount');
                    deferred1.resolve(totalPendientesCountAcc);
                });


                if($localStorage.foro == true || $localStorage.derecho == true || $localStorage.av == true)
                {
                    ItemsManager.getByState(StateService.getAliasState(1)).then(function(items){
                        angular.forEach(items,function(itemData,itemKey){
                            switch(itemKey) {
                                case $localStorage.foro == true   && 'proposals':
                                    propuestasPendientesCount    = itemData.length;
                                    totalPendientesCountItem    += itemData.length;
                                    totalItemsPendientesCount   += itemData.length;
                                    break;
                                case $localStorage.foro == true   && 'initiatives':
                                    iniciativasPendientesCount   = itemData.length;
                                    totalPendientesCountItem    += itemData.length;
                                    totalItemsPendientesCount   += itemData.length;
                                    break;
                                case $localStorage.foro == true   && 'offers':
                                    ofertasPendientesCount       = itemData.length;
                                    totalPendientesCountItem    += itemData.length;
                                    totalItemsPendientesCount   += itemData.length;
                                    break;
                                case $localStorage.foro == true   && 'requests':
                                    peticionesPendientesCount    = itemData.length;
                                    totalPendientesCountItem    += itemData.length;
                                    totalItemsPendientesCount   += itemData.length;
                                    break;
                                case $localStorage.derecho == true && 'threads':
                                    threadsPendientesCount       = itemData.length;
                                    totalPendientesCountItem    += itemData.length;
                                    break;
                                case $localStorage.av == true     && 'questions':
                                    questionsPendientesCount       = itemData.length;
                                    totalPendientesCountItem    += itemData.length;
                                    break;
                            }
                        });
                        broadcast(localPropuestasPendientesCount,  propuestasPendientesCount,  'propuestasPendientesCount' );
                        broadcast(localIniciativasPendientesCount, iniciativasPendientesCount, 'iniciativasPendientesCount');
                        broadcast(localOfertasPendientesCount,     ofertasPendientesCount,     'ofertasPendientesCount'    );
                        broadcast(localPeticionesPendientesCount,  peticionesPendientesCount,  'peticionesPendientesCount' );
                        broadcast(localThreadsPendientesCount,     threadsPendientesCount,     'threadsPendientesCount'    );
                        broadcast(localQuestionsPendientesCount,   questionsPendientesCount,   'questionsPendientesCount'  );
                        broadcast(localTotalItemsPendientesCount,  totalItemsPendientesCount,  'totalItemsPendientesCount' );
                        deferred2.resolve(totalPendientesCountItem);
                        deferredf.resolve(totalItemsPendientesCount);
                    });

                    deferred3.resolve(0);

                    foro.then(function(data){
                        broadcast(localTotalForoPendientesCount, data[0]+data[1], 'totalForoPendientesCount');
                    });
                }
                else {
                    deferred2.resolve(0);
                    deferred3.resolve(0);
                }

                all.then(function(data){
                    broadcast(localTotalPendientesCount, data[0]+data[1]+data[2]+data[3], 'totalPendientesCount');
                });
            };
            update();
            $rootScope.mails_interval = $interval(update, 60000);

            this.getAccionistasPendientesCount   = function() { return localAccionistasPendientesCount; };
            this.getPropuestasPendientesCount    = function() { return localPropuestasPendientesCount;  };
            this.getIniciativasPendientesCount   = function() { return localIniciativasPendientesCount; };
            this.getOfertasPendientesCount       = function() { return localOfertasPendientesCount;     };
            this.getPeticionesPendientesCount    = function() { return localPeticionesPendientesCount;  };
            this.getTotalItemsPendientesCount    = function() { return localTotalItemsPendientesCount;  };
            this.getThreadsPendientesCount       = function() { return localThreadsPendientesCount;     };
            this.getQuestionsPendientesCount     = function() { return localQuestionsPendientesCount;   };
            this.getTotalPendientesCount         = function() { return localTotalPendientesCount;       };
            //this.getTotalAdhesionPendientesCount = function() { return localAdhesionsPendientesCount; };
            this.getTotalForoPendientesCount     = function() { return localTotalForoPendientesCount;   };
            this.update                          = function() { update(); };

            $rootScope.$on('updatedItem',update);
        }
    ]).service('validateAccionistaAdmin',['documentValidation','$localStorage','jsUtils', function(documentValidation,$localStorage,jsUtils){
        var traducciones = {
            representedBy: "id00176_app:validation:no-representative",
            name:          "id00177_app:validation:no-name",
            documentNum:   "id00178_app:validation:document-num",
            username:      "id00179_app:validation:no-username",
            email:         "id00180_app:validation:email",
            emailc:        "id00183_app:validation:emailc",
            telephone:     "id00278_app:validation:telephone",
            LOPD:          "id00181_app:validation:lopd",
            num_shares:    "id00227_app:validation:num-shares",
            DocumentA:     "id00225_app:validation:documentoa",
            DocumentB:     "id00226_app:validation:documentob"
        };

        var joinFalse = function(boolData){
            var result = {};

            for(var index in boolData){
                if(!boolData[index]){
                    result[index] = traducciones[index];
                }
            }

            return result;
        };

        this.validate = function(data,emailcValid){
            var accionista = data.accionista;
            var user       = data.user;

            var valid = {
                representedBy: accionista.personaType == "pf" || (accionista.personaType == "pj" && accionista.representedBy !== undefined && accionista.representedBy !== ''),
                name:          accionista.name !== undefined &&  accionista.name !== '',
                documentNum:   documentValidation.validate(accionista.documentNum, accionista.documentType),
                username:      (user.username !== undefined && user.username !== ''),
                //https://html.spec.whatwg.org/multipage/forms.html#valid-e-mail-address
                email:         (/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/).test(user.email),
                emailc:        emailcValid || user.email === '',
                num_shares:    ( $localStorage['Config.accionista.accionesMin'] == undefined && Number(jsUtils.cleanNumber(accionista.sharesNum)) > 0) || Number(jsUtils.cleanNumber(accionista.sharesNum)) >= Number($localStorage['Config.accionista.accionesMin']),
            };

            return joinFalse(valid);
        };
    }]);
}).call(this);