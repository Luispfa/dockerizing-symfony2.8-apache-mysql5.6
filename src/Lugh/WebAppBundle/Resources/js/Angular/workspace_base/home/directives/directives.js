(function() {
    'use strict';
    angular.module('homeDirectives',[
    ]).directive('validateDoc',[ 'documentValidation',

        /*
         * Valida campo de Documentación NIF/CIF/NIE
         */

        function(documentValidation) {
            var tipoDoc = "nif";

            var linkFunction = function(scope,element,attrs,ctrl){

                var checkOnType = function(value,oldValue){
                    if(value !== oldValue){
                        tipoDoc   = value;
                        var valid = documentValidation.validate(ctrl.$viewValue,tipoDoc);

                        ctrl.$setValidity('newAccionista.accionista.documentNum',valid);
                        ctrl.$setValidity('accionista.document_num',valid);
                    }
                };

                var checkOnNum = function(value,oldValue){
                    var valid = documentValidation.validate(ctrl.$viewValue,tipoDoc);
                    ctrl.$setValidity('newAccionista.accionista.documentNum',valid);
                    ctrl.$setValidity('accionista.document_num',valid);
                };

                //Si modificamos el tipo de documento
                scope.$watch('newAccionista.accionista.documentType', checkOnType);
                scope.$watch('accionista.document_type', checkOnType);
                
                scope.$watch('newAccionista.accionista.documentNum', checkOnNum);
                scope.$watch('accionista.document_num', checkOnNum);
            };

            var directiveDef = {
                require: 'ngModel',
                restrict: "AE",
                link: linkFunction,
                scope: false
            };
            
            return directiveDef;
        }
    ]).directive('validateUsername',[ 'documentValidation',

        /*
         * Valida campo de usuario sea un Documento NIF/CIF/NIE
         */

        function(documentValidation) {
            var tipoDoc = "nif";

            var linkFunction = function(scope,element,attrs,ctrl){

                var checkOnType = function(value,oldValue){
                    if(value !== oldValue){
                        tipoDoc   = value;
                        var valid = documentValidation.validate(ctrl.$viewValue,tipoDoc);

                        ctrl.$setValidity('newAccionista.user.username',valid);
                        ctrl.$setValidity('user.username',valid);
                    }
                };

                var checkOnNum = function(value,oldValue){
                    var valid = documentValidation.validate(ctrl.$viewValue,tipoDoc);
                    ctrl.$setValidity('newAccionista.user.username',valid);
                    ctrl.$setValidity('user.username',valid);
                };

                //Si modificamos el tipo de documento
                scope.$watch('newAccionista.accionista.documentType', checkOnType);
                scope.$watch('accionista.document_type', checkOnType);
                
                scope.$watch('newAccionista.user.username', checkOnNum);
                scope.$watch('user.username', checkOnNum);
            };

            var directiveDef = {
                require: 'ngModel',
                restrict: "AE",
                link: linkFunction,
                scope: false
            };
            
            return directiveDef;
        }
    ]).directive('requiredLabel',[
        '$localStorage','paramRequest', function($localStorage, paramRequest){
            
            /*
             * Añade un asterisco (*) al final de un label si este es requerido
             */
            
            var directiveDef = {
                restrict: "A",
                link: function($scope,element,attrs,ctrl) {
                    var showRequired = function() {
                        if($localStorage['Config.require.' + attrs.requiredLabel] == 1){
                            element.append('*');
                        }
                        else {
                            try {
                                var requiredArray = eval(attrs.requiredLabel); 
                            } catch (e) {
                                
                            }
                            if (_.isArray(requiredArray) && requiredArray.length > 0)
                            {
                                var flg = '';
                                for (var i in requiredArray) {
                                    if($localStorage['Config.require.' + requiredArray[i]] == 1){
                                        flg = '*';
                                    }
                                }
                                element.append(flg);
                            }
                        }
                    }

                    paramRequest.load().then(function(){
                        showRequired();
                    });
                }
            };
            
            return directiveDef;
        }
    ]).directive('requiredInput',[
        '$localStorage','paramRequest', function($localStorage, paramRequest){
            
            /*
             * Añade validación a los input requeridos
             */
            
            var directiveDef = {
                restrict: "A",
                link: function($scope,element,attrs,ctrl) {
                    var showRequired = function() {
                        if($localStorage['Config.require.' + attrs.requiredInput] == 1){
                            element.attr('required',true);
                        }
                    }

                    paramRequest.load().then(function(){
                        showRequired();
                    });
                }
            };
            
            return directiveDef;
        }
    ]);
}).call(this);      
