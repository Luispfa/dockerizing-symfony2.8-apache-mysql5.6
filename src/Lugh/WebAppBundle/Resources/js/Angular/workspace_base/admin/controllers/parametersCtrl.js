(function() {
    'use strict';
    angular.module('adminWorkspaceControllers'
    ).controller("parametersCtrl",[
        "$scope","$http","$location", function($scope,$http,$location) {
            $scope.parametersList = "";
            $scope.parametersData = [];

            var updateTextArea = function(parametros) {
                $scope.parametersData = parametros;

                angular.forEach(parametros,function(parametro,key){
                    $scope.parametersList += parametro.key_param + " = " + parametro.value_param;

                    if(parametro.observaciones !== undefined) {
                        $scope.parametersList += "; " + parametro.observaciones;
                    }

                    $scope.parametersList += "\n";
                });
            };

            var sendParameter = function(parameter, isNew, id) {
                if(isNew){
                    $http.post(ApiBase+'/parametros',parameter).success(function(response){
                        //Actualizamos la información existente
                        $scope.parametersData.push(parameter);
                    }).catch(function() { throw "Error al enviar parámetro: " + parameter.key;});
                }
                else{
                    $http.put(ApiBase+'/parametros/'+id,parameter).success(function(response){
                        //Actualizamos la información existente
                        angular.forEach($scope.parametersData,function(oldParam,index){
                            if(oldParam.key_param === response.key){
                                $scope.parametersData[index].value_param   = parameter.value;
                                $scope.parametersData[index].observaciones = parameter.observaciones;
                            }
                        });
                    }).catch(function() { throw "Error al enviar parámetro: " + parameter.key;});
                }
            };

            $scope.paramsValid = function() {
                var newData = $scope.parametersList;
                var lines = newData.split("\n");

                var stop = false;
                angular.forEach(lines,function(line){
                    if(stop)
                        return;

                    if(!~line.indexOf("=")){     //No hay un "="
                        return;
                    }

                    var aux = line.split("=");

                    //Key o value vacío
                    if(aux[0].replace(" ","") === "" || aux[1].split(";")[0].replace(" ","") === "" ){ 
                        stop = true;
                        return;
                    }
                });
                return !stop;
            }

            /**
             * Parsea la lista de parámetros y devuelve la información
             * al server si se trata de un parámetro nuevo o modificado
             */
            var parseParameters = function(oldData, newData) {
                var lines = newData.split("\n");

                //Line format: Key=Value;Comment
                angular.forEach(lines,function(line){
                    if(!~line.indexOf("="))              //No hay un "="
                        return;

                    var aux = line.split("=");
                    var key = aux[0];

                    aux = aux[1].split(";");

                    var val = aux[0];
                    var com = aux[1];

                    //Eliminamos espacios a principio y final del parámetro
                    while(key.slice(-1) === " ") {
                        key = key.slice(0,-1);
                    }
                    while(val.slice(0,1) === " "){
                        val = val.slice(1,val.length);
                    }
                    while(val.slice(-1) === " "){
                        val = val.slice(0,-1);
                    }
                    if(com === undefined) {
                        com = "";
                    }

                    var oldParam = oldData.filter(function(obj){
                        return obj.key_param === key;
                    })[0];

                    //Si el parámetro ya existía y no ha cambiado
                    if(oldParam !== undefined && oldParam.value_param === val && oldParam.observaciones === com)
                        return;

                    var id;
                    if(oldParam === undefined)
                        id = undefined;
                    else
                        id = oldParam.id;

                    sendParameter({
                        key:     key,
                        value:   val,
                        observaciones: com
                    },oldParam === undefined,id);
                });
            };

            //Pedimos los parámetros al servidor
            $http.get(ApiBase+'/parametros').success(function(parametros) {
                updateTextArea(parametros);
            }).catch(function(){
                $location.path("/404");
            });

            $scope.submitParameters = function() {
                parseParameters($scope.parametersData,$scope.parametersList);
            };
        }
    ]);
}).call(this);