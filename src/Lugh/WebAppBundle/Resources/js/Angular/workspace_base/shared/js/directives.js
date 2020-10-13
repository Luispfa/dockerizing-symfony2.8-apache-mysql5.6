(function() {
    'use strict';
    angular.module('sharedDirectives',[
    ]).directive('reloadDirectiveOn',[
        '$compile', function($compile){
            return {
                link: function(scope,element,attrs){
                    var compile;
                    var backup = element.html();
                    (compile = function(){
                        element.html('');
                        element.html($compile(backup)(scope));
                    })();

                    scope.$on(attrs['reloadDirectiveOn'],function(){
                        compile();
                    });
                }
            }
        }
    ]).directive("dottedNumber",[
        'jsUtils', function(jsUtils){
            return {
                restrict: 'AE',
                require: 'ngModel',
                link: function (scope, element, attrs, modelCtrl) {
                    modelCtrl.$parsers.push(function(inputValue) {
                        var transformedInput;
                        if(inputValue !== undefined){
                            transformedInput = jsUtils.formatNumber(inputValue);
                        } else {
                            transformedInput = "0";
                        }

                        if (transformedInput != inputValue) {
                            modelCtrl.$setViewValue(transformedInput);
                            modelCtrl.$render();
                        }

                        return transformedInput;
                    });
                }
            };
        }
    ]).directive("dottedMin",[
        'jsUtils', function(jsUtils){
            return {
                restrict: 'A',
                require:  'ngModel',
                link: function($scope,element,attrs,modelCtrl){
                    if(attrs.active === undefined){
                        modelCtrl.$parsers.push(function(viewValue){
                            var data = jsUtils.cleanNumber(viewValue);
                            var min  = jsUtils.cleanNumber(attrs['dottedMin']);
                            
                            modelCtrl.$setValidity(attrs.ngModel, Number(data) >= Number(min));

                            return viewValue;
                        });
                    }
                    else {
                        element.bind('blur',function(){
                            var data = jsUtils.cleanNumber(modelCtrl.$viewValue);
                            var min  = jsUtils.cleanNumber(attrs['dottedMin']);

                            if(Number(data) < Number(min)){
                                modelCtrl.$setViewValue(jsUtils.formatNumber(attrs['dottedMin']));
                                modelCtrl.$render();
                            }
                        });
                    }
                }
            }
        }
    ]).directive("loadModal",[
        '$modal', function($modal){
            return {
                scope: {},
                restrict: 'E',
                replace: true,
                templateUrl: WebDefault + "/home/sharedviews/modalInstance.html"
            };
        }
    ]).directive('hideByConfig',[
        '$localStorage','paramRequest', 
        function($localStorage, paramRequest){
            
            /*
             * Elimina elementos según voto, foro o derecho electrónico 
             * estén activados/desactivados 
             */
            
            var directiveDef = {
                restrict: "A",
                link: function($scope,element,attrs) {
                    var removeElements = function() {
                        if($localStorage['Config.hide.'+attrs['hideByConfig']] == 1)
                            element.remove();
                    };
                    
                    paramRequest.load().then(function(){
                        removeElements();
                    });
                }
            };
            
            return directiveDef;
        }
    ]).directive("infoModal",[
        '$modal', 'localize', function($modal, localize){
            return {
                scope: {},
                restrict: "E",
                compile:function(){
                    return{
                        pre: function($scope, element, attrs, controller){
                            $scope.body = '';
                            $scope.openModal = function() {
                                $modal.open({
                                    templateUrl: "myModalContent.html",
                                    controller: 'ModalInstanceCtrl',
                                    resolve: {
                                        title: function() {
                                            return localize.getLocalizedString(attrs.title);
                                        },
                                        body: function() {
                                            return localize.getLocalizedString(attrs.tag);
                                        }
                                    }
                                });
                            };
                        }
                    };
                },
                replace: true,
                templateUrl: WebDefault + "/home/sharedviews/infoButton.html"
            };
        }
    ]).controller('ModalInstanceCtrl', [
    '$scope', '$modalInstance', 'body', 'title', function($scope, $modalInstance, body, title) {
      $scope.body = body;
      $scope.title= title;
      $scope.ok = function() {
        $modalInstance.close();
      };
      $scope.cancel = function() {
        $modalInstance.dismiss("cancel");
      };
    }]).directive('uploadDocument',[ 'uploaderProvider','$timeout','anonymousToken',

        /*
         * Monta el widget de subida de archivos
         * Para evitar duplicidad y mejorar la claridad del código
         *
         * Uso: 
         * description, title e instructions son opcionales,
         * puramente estéticos o informativos
         *
         * uploaderID es muy importante (obligatorio) si se usa más
         * de un uploader en la misma vista, de lo contrario tendrán
         * la misma instancia.
         *
         * Utiliza uploaderService.get(uploaderID,scope) para obtener
         * la instancia del uploader desde el controlador
         *
         * El atributo isolatedtoken hace que, en caso de haber más
         * de una instancia de uploader funcionando, estas funcionen con
         * tokens diferentes, de lo contrario tendrán el mismo.
         *
         * En el momento de enviar el token, es importante que se saque
         * del objeto uploader con uploader.getToken() porque,
         * debido a la asincronia de js, podría ser diferente
         * del que hemos pasado por parámetro.
         *
         */

        function(uploaderProvider,$timeout,anonymousToken) { 
            
            return {
                scope: {},
                restrict: "E",
                compile:function(){
                    return{
                        pre: function($scope, element, attrs, controller){
                            $scope.description = attrs.description;
                            $scope.title       = attrs.title;
                            $scope.instructions= attrs.instructions;
                            $scope.id          = attrs.uploaderid;
                            $scope.isolatedToken = (attrs.isolatedtoken == '');
                            $scope.isPublic    = attrs.ispublic ? true : false;
                            $scope.label       = attrs.label;
                            
                            var token = attrs.isolatedToken ? anonymousToken.newToken() : anonymousToken.getToken();
                            $scope.uploader = uploaderProvider.get($scope.id,$scope,token);
                        }
                    };
                },
                replace: true,
                templateUrl: WebDefault + "/home/sharedviews/upload.html"
            };
        }
    ]).directive('noPaste', [
        /* 
         * Impide pegar en el elemento que tenga la directiva
         */
        function(){
            return{
                restrict: 'AE',
                link: function($scope,element,attrs,ctrl){
                    element.bind("paste",function(e){
                        e.preventDefault();
                    });
                }
            };
        }

    ]).directive('entitiesDecode', ['$parse',
        function($parse) {
            return {
            require: '?ngModel',
                link: function(scope, element, attrs, ngModelController) {
                  
                    var modelGetter = $parse(attrs['ngModel']);
                    var modelSetter = modelGetter.assign;
                    
                    ngModelController.$parsers.push(function(data) {
                      //convert data from view format to model format
                      //data = htmlentities(data)
                      return data; //converted
                    });
                    ngModelController.$formatters.push(function(data) {
                      if (data)
                      {
                          data = html_entity_decode(data);
                          modelSetter(scope, data);
                      }
                      return data; //converted
                    });
                }
        }
    }]).directive('highlightActiveScope', [
        function() {
            return {
                restrict: "A",
                controller: [
                    '$scope', '$element', '$attrs', function($scope, $element, $attrs) {
                        var highlightActive, links, path;
                        links = $element.find('a');
                        path = function() {
                            return $scope.template;
                        };
                        highlightActive = function(links, path, templates) {
                            //path = '#' + path;
                            return angular.forEach(links, function(link) {
                                var $li, $link, href;
                                $link = angular.element(link);
                                $li = $link.parent('li');
                                href = $link.attr('template');
                                if ($li.hasClass('active')) {
                                    $li.removeClass('active');
                                }
                                if (path.indexOf(templates[href]) === 0) {
                                    return $li.addClass('active');
                                }
                            });
                        };
                        highlightActive(links, $scope.template, $scope.templates);
                        return $scope.$watch(path, function(newVal, oldVal) {
                            if (newVal === oldVal) {
                                return;
                            }
                            return highlightActive(links, $scope.template,$scope.templates);
                        });
                    }
                ]
            };
        }
    ])/*.directive('handleError',[
        function() {
            return {
                require: "ngModel",
                restrict: "A",
                link: function($scope,element,attrs,ctrl) {
                    $scope.onError[attrs.handleError] = function() {
                        element.addClass("alert alert-danger"); //@TODO
                    };
                }
            }
        }
    ])*/
    .directive('appDashElement',[
        '$localStorage','$http','resetLocalStorage', function($localStorage, $http, resetLocalStorage){

            /*
             * Elimina elementos según voto, foro o derecho electrónico
             * estén activados/desactivados
             */

            var directiveDef = {
                restrict: "A",
                link: function($scope,element,attrs,ctrl) {
                    element.toggle();
                    var removeElemnts = function() {
                        switch(attrs.appDashElement){
                            default:
                                console.err("Incorrect attribute: " + attrs.appDashElement);
                                break;
                            case "voto":
                                if(!$localStorage.platforms.voto){
                                    $(element).remove();
                                }
                                else if(($localStorage.voto === undefined || $localStorage.voto === 0) && $localStorage.platforms.voto){
                                    element.toggle();
                                    element.children().attr('disabled','disabled');
                                }
                                else if($localStorage.voto){
                                    element.toggle();
                                }
                                break;
                            case "derecho":
                                if(!$localStorage.platforms.derecho){
                                    $(element).remove();
                                }
                                else if(($localStorage.derecho === undefined || $localStorage.derecho === 0) && $localStorage.platforms.derecho){
                                    element.toggle();

                                    element.children().attr('disabled','disabled');
                                }
                                else if($localStorage.derecho){
                                    element.toggle();
                                }
                                break;
                            case "av":
                                if(!$localStorage.platforms.av){
                                    $(element).remove();
                                }
                                /*if(($localStorage.av === undefined || $localStorage.av === 0) &&
                                 ($localStorage.platforms.av === undefined || $localStorage.platforms.av === 0)){
                                 $(element).remove();
                                 }*/
                                else if(($localStorage.av === undefined || $localStorage.av === 0) && $localStorage.platforms.av){
                                    element.toggle();

                                    element.children().attr('disabled','disabled');
                                }
                                else if($localStorage.av){
                                    element.toggle();
                                }
                                break;
                            case "foro":
                                if(!$localStorage.platforms.foro){
                                    $(element).remove();
                                }
                                else if(($localStorage.foro === undefined || $localStorage.foro === 0) && $localStorage.platforms.foro){
                                    element.toggle();
                                    element.children().attr('disabled','disabled');
                                }
                                else if($localStorage.foro){
                                    element.toggle();
                                }
                                break;
                        }
                    };

                    if($localStorage.voto === undefined)
                    {
                        $http.get(WebDefault + '/' + NameController + '/' +'apprequest')
                            .success(function(apprequest) {
                                resetLocalStorage.reset(apprequest, $localStorage)
                                $localStorage.$default(apprequest)
                                removeElemnts();
                            })
                    }
                    else
                    {
                        removeElemnts();
                    }

                }
            };

            return directiveDef;
        }
    ])
    .directive('appElement',[
        '$localStorage','$http','resetLocalStorage', function($localStorage, $http, resetLocalStorage){
            
            /*
             * Elimina elementos según voto, foro o derecho electrónico 
             * estén activados/desactivados 
             */
            
            var directiveDef = {
                restrict: "A",
                link: function($scope,element,attrs,ctrl) {
                    element.toggle();
                    var removeElemnts = function() {
                        switch(attrs.appElement){
                            default:
                                console.log("Incorrect attribute: " + attrs.appElement);
                                break;
                            case "voto":
                                if(($localStorage.voto === undefined || $localStorage.voto === 0) &&
                                    ($localStorage.platforms.voto === undefined || $localStorage.platforms.voto === 0)){
                                    element.remove();
                                }
                                else if(($localStorage.voto === undefined || $localStorage.voto === 0) && $localStorage.platforms.voto){
                                    element.toggle();
                                    element.children().attr('disabled','disabled');
                                }
                                else if($localStorage.voto){
                                    element.toggle();
                                }
                                break;
                            case "derecho":
                                if(($localStorage.derecho === undefined || $localStorage.derecho === 0) &&
                                    ($localStorage.platforms.derecho === undefined || $localStorage.platforms.derecho === 0)){
                                    element.remove();
                                }
                                else if(($localStorage.derecho === undefined || $localStorage.derecho === 0) && $localStorage.platforms.derecho){
                                    element.toggle();
                                    
                                    element.children().attr('disabled','disabled');
                                }
                                else if($localStorage.derecho){
                                    element.toggle();
                                }
                                break;
                            case "av":
                                if(($localStorage.av === undefined || $localStorage.av === 0) &&
                                    ($localStorage.platforms.av === undefined || $localStorage.platforms.av === 0)){
                                    element.remove();
                                }
                                else if(($localStorage.av === undefined || $localStorage.av === 0) && $localStorage.platforms.av){
                                    element.toggle();
                                    
                                    element.children().attr('disabled','disabled');
                                }
                                else if($localStorage.av){
                                    element.toggle();
                                }
                                break;
                            case "foro":
                                if(($localStorage.foro === undefined || $localStorage.foro === 0) &&
                                    ($localStorage.platforms.foro === undefined || $localStorage.platforms.foro === 0)){
                                    element.remove();
                                }
                                else if(($localStorage.foro === undefined || $localStorage.foro === 0) && $localStorage.platforms.foro){
                                    element.toggle();
                                    element.children().attr('disabled','disabled');
                                }
                                else if($localStorage.foro){
                                    element.toggle();
                                }
                                break;
                        }
                    };
                    
                    if($localStorage.voto === undefined) 
                    {
                        $http.get(WebDefault + '/' + NameController + '/' +'apprequest')
                        .success(function(apprequest) {
                            resetLocalStorage.reset(apprequest, $localStorage)
                            $localStorage.$default(apprequest)
                            removeElemnts();
                        })
                    }
                    else
                    {
                        removeElemnts();
                    }
                    
                }
            };
            
            return directiveDef;
        }
    ]).directive('appElementAdmin',[
            '$localStorage','$http','resetLocalStorage', function($localStorage, $http, resetLocalStorage){

                /*
                 * Elimina elementos según voto, foro o derecho electrónico
                 * estén activados/desactivados
                 */

                var directiveDef = {
                    restrict: "A",
                    link: function($scope,element,attrs,ctrl) {
                        element.toggle();
                        var removeElemnts = function() {
                            switch(attrs.appElementAdmin){
                                default:
                                    console.log("Incorrect attribute: " + attrs.appElementAdmin);
                                    break;
                                case "voto":
                                    if($localStorage.platforms.voto === undefined || $localStorage.platforms.voto === false){
                                        element.remove();
                                    }
                                    else if($localStorage.platforms.voto === true){
                                        element.toggle();
                                    }
                                    break;
                                case "derecho":
                                    if($localStorage.platforms.derecho === undefined || $localStorage.platforms.derecho === false){
                                        element.remove();
                                    }
                                    else if($localStorage.platforms.derecho === true){
                                        element.toggle();
                                    }
                                    break;
                                case "av":
                                    if($localStorage.platforms.av === undefined || $localStorage.platforms.av === false){
                                        element.remove();
                                    }
                                    else if($localStorage.platforms.av === true){
                                        element.toggle();
                                    }
                                    break;
                                case "foro":
                                    if($localStorage.platforms.foro === undefined || $localStorage.platforms.foro === false){
                                        element.remove();
                                    }
                                    else if($localStorage.platforms.foro === true){
                                        element.toggle();
                                    }
                                    break;
                            }
                        };

                        if($localStorage.voto === undefined)
                        {
                            $http.get(WebDefault + '/' + NameController + '/' +'apprequest')
                                .success(function(apprequest) {
                                    resetLocalStorage.reset(apprequest, $localStorage)
                                    $localStorage.$default(apprequest)
                                    removeElemnts();
                                })
                        }
                        else
                        {
                            removeElemnts();
                        }

                    }
                };

                return directiveDef;
            }
        ]).directive('langElement',[
        '$localStorage','$http','resetLocalStorage', 'paramRequest', 
        function($localStorage, $http, resetLocalStorage, paramRequest){
            
            /*
             * Elimina elementos según array de langs
             * estén activados/desactivados 
             */
            
            var directiveDef = {
                restrict: "A",
                link: function($scope,element,attrs,ctrl) {
                    element.toggle();
                    var removeElements = function(langs) {
                        
                       for(var l in langs) {
                           if (attrs.langElement == l && langs[l] === 1)
                           {
                               element.toggle();
                           }
                           else if (attrs.langElement == l)
                           {
                               element.remove();
                           }
                       }
                    }
                    paramRequest.get('Config.langs.active').then(function(langs){
                        removeElements($.parseJSON(langs));
                    });
                }
            };
            
            return directiveDef;
        }
    ]).directive('appOnlyElement',[
        '$localStorage','$http','resetLocalStorage', function($localStorage, $http, resetLocalStorage){
            
            /*
             * Elimina elementos según voto, foro o derecho electrónico 
             * estén activados/desactivados 
             */
            
            var directiveDef = {
                restrict: "A",
                link: function($scope,element,attrs,ctrl) {
                    element.toggle();
                    var removeElemnts = function() {
                        var apps = [];
                        if ($localStorage.platforms.voto === true) apps.push(true);
                        if ($localStorage.platforms.derecho === true) apps.push(true);
                        if ($localStorage.platforms.av === true) apps.push(true);
                        if ($localStorage.platforms.foro === true) apps.push(true);
                        if (apps.length > 1)
                        {
                            element.toggle();
                        }
                        else
                        {
                            element.remove();
                        }
                    }
                    
                    if($localStorage.voto === undefined) 
                    {
                        $http.get(WebDefault + '/' + NameController + '/' +'apprequest')
                        .success(function(apprequest) {
                            resetLocalStorage.reset(apprequest, $localStorage)
                            $localStorage.$default(apprequest)
                            removeElemnts();
                        })
                    }
                    else
                    {
                        removeElemnts();
                    }
                    
                }
            };
            
            return directiveDef;
        }
    ]).directive('anchor',[
        function(){
            return {
                link: function(scope,element,attrs){
                    element.bind('click',function(event){
                        event.stopPropagation();
                        var off = scope.$on('$locationChangeStart', function(ev) {
                            off();
                            ev.preventDefault();
                        });
                    });
                }
            }
        }
    ]).directive('ngRepeatEvent',[
        "$timeout","$rootScope",function($timeout,$rootScope){
            /**
             *  Dado que Angular no avisa de cuándo termina un ng-repeat,
             *  esta directiva crea un evento cuando eso ocurre.
             *  Esto permite realizar modificaciones en el DOM, posteriores
             *  a la carga del ng-repeat.
             *
             *  Es recomendable pasarle un identificador a no ser que esté claro
             *  que sólo se usará una vez en una página.
             */

            return {
                restrict: 'A',
                link: function (scope, element, attr) {
                    (scope.checkEvent = function() {
                        var identifier = "";
                        if (attr['ngRepeatEvent'] !== undefined) {
                            identifier = attr['ngRepeatEvent'];
                        }
                        if (scope.$last === true) {
                            $timeout(function () {
                                $rootScope.$broadcast('ngRepeatFinished[' + identifier + ']');
                            });
                        }
                    })();
                }
            }
        }
    ]).directive('strictNgIf',[
        function(){

            /**
             * El funcionamiento es exactamente el mismo que el de ng-if
             * pero no se actualiza cuando cambia el contenido
             */
            return {
                scope: {strictNgIf: '='},
                link: function(scope,element,attrs){
                    if(!scope.strictNgIf){
                        element.replaceWith('<!-- strict-ng-if: '+attrs['strictNgIf'] +' -->')
                    }
                }
            }
        }
    ]).directive('adaptiveRow',[

        /*
         * Adapta la clase col-sm-* de los elementos internos de la row,
         * según el número de estos, al ancho de la fila.
         * No sirve para filas de 7, 9 ni 11 elementos, dado que en estos
         * casos no puede repartirse el espacio equitativamente.
         *
         * El atributo adapt-on-event será el nombre del evento
         * que hará que se ejecute de nuevo.
         *
         * Con el atributo adapt-sizes podemos especificar para qué tamaños queremos adaptar.
         * (Por defecto sólo para "md"). Los tamaños que no estén en sizes no se modificarán.

         * Por ejemplo: adaptive-row adapt-on-event="ngRepeatEvent" adapt-sizes="md sm xs"
         */

        function(){
            var TOTAL_ROWS = 12;

            var compile = function(scope,element,attrs) {
                var num_children = element.children().length;

                var sizes = ["md"];
                if(attrs.adaptSizes !== undefined) {
                    sizes = attrs.adaptSizes.split(" ");
                }
                
                if((TOTAL_ROWS % num_children) === 0 || num_children === 5 || num_children === 8 || num_children === 10){
                    
                    //Eliminamos las clases "col-" existentes
                    var classList;
                    angular.forEach(element.children(), function(child){
                        classList = [];
                        angular.forEach(child.classList,function(className){
                            classList.push(className);
                        });
                        angular.forEach(classList, function(className){
                            if(className.substr(0,4) === "col-"){
                                angular.forEach(sizes,function(size){
                                    if(~className.indexOf(size)){
                                        element.find(child).removeClass(className);
                                    }
                                });
                            }
                        });
                    });

                    angular.forEach(sizes,function(size){
                        //Si hay 5, 10 o 8 elementos, aplicamos un offset para centrarlos
                        if(num_children === 5 || num_children === 10){
                            element.find(element.children()[0]).addClass('col-'+size+'-offset-1');
                        }
                        if(num_children === 8){
                            element.find(element.children()[0]).addClass('col-'+size+'-offset-2');
                        }

                        //Finalmente cuadramos según el número de nodos
                        element.children().addClass('col-'+size+'-' + parseInt(TOTAL_ROWS / num_children));
                    });
                }
            };

            var postLink = function(scope,element,attrs){
                compile(scope,element,attrs);

                if(attrs.adaptOnEvent !== undefined){
                    var events = attrs.adaptOnEvent.split(" ");

                    angular.forEach(events,function(event) {
                        scope.$on(event,
                            function(){
                                compile(scope,element,attrs);
                            }
                        );
                    });
                }
                
                scope.$watch(element.children().length,function(){
                    compile(scope,element,attrs);
                });
            };
            
            return {
                restrict: "A",
                link: postLink
            };
        }
    ]).directive('adaptiveDashRow',[
        function(){

            var TOTAL_ROWS = 12;

            var compile = function(scope,element,attrs) {
                var num_children = element.children().length;

                var sizes = ["md"];
                var max_cols =["md-12"];
                if(attrs.adaptSizes !== undefined) {
                    sizes = attrs.adaptSizes.split(" ");
                }
                if(attrs.adaptSizes !== undefined) {
                    max_cols = attrs.maxCols.split(" ");
                }

                if((TOTAL_ROWS % num_children) === 0 || num_children === 5 || num_children === 8 || num_children === 10){

                    //Eliminamos las clases "col-" existentes
                    var classList;
                    angular.forEach(element.children(), function(child){
                        classList = [];
                        angular.forEach(child.classList,function(className){
                            classList.push(className);
                        });
                        angular.forEach(classList, function(className){
                            if(className.substr(0,4) === "col-"){
                                angular.forEach(sizes,function(size){
                                    if(~className.indexOf(size)){
                                        element.find(child).removeClass(className);
                                    }
                                });
                            }
                        });
                    });

                    angular.forEach(sizes,function(size){
                        //Si hay 5, 10 o 8 elementos, aplicamos un offset para centrarlos
                        if(num_children === 5 || num_children === 10){
                            element.find(element.children()[0]).addClass('col-'+size+'-offset-1');
                        }
                        if(num_children === 8){
                            element.find(element.children()[0]).addClass('col-'+size+'-offset-2');
                        }
                        var max = false;
                        for(var i in max_cols){
                            if(max_cols[i].indexOf(size) != -1){
                                max = parseInt( max_cols[i].split("-")[1] );
                            }
                        }
                        if(max !== false){
                            angular.forEach(element.children(),function(e,i){
                                var rest = num_children%max;
                                var final_children = num_children - i <= rest ?  rest : max;
                                var refer = parseInt(TOTAL_ROWS / final_children);
                                $(e).addClass('col-'+size+'-'+refer);
                            });

                        }else{
                            //Finalmente cuadramos según el número de nodos
                            element.children().addClass('col-'+size+'-' + parseInt(TOTAL_ROWS / num_children));
                        }

                    });
                }
            };

            var postLink = function(scope,element,attrs){
                compile(scope,element,attrs);

                if(attrs.adaptOnEvent !== undefined){
                    var events = attrs.adaptOnEvent.split(" ");

                    angular.forEach(events,function(event) {
                        scope.$on(event,
                            function(){
                                compile(scope,element,attrs);
                            }
                        );
                    });
                }

                scope.$watch(element.children().length,function(){
                    compile(scope,element,attrs);
                });
            };

            return {
                restrict: "A",
                link: postLink
            };

        }
    ]).directive('imgHolder', [
        function() {
            return {
                restrict: 'A',
                link: function(scope, ele, attrs) {
                    return Holder.run({
                        images: ele[0]
                    });
                }
            };
        }
    ]).directive('customBackground', function() {
        return {
            restrict: "A",
            controller: [
                '$scope', '$element', '$location', function($scope, $element, $location) {
                    var addBg, path;
                    path = function() {
                        return $location.path();
                    };
                    addBg = function(path) {
                        $element.removeClass('body-home body-special body-tasks body-lock body-nonav body-voto');
                        switch (path) {
                            case '/':
                                return $element.addClass('body-home');
                            case '/404':
                            case '/down':
                            case '/pages/500':
                                return $element.addClass('body-special');
                            case '/dashboard':
                            case '/app/profile':
                            case '/app/resetPassword':
                            case '/app/inicio':
                            case '/app/forgot':
                            case '/app/registro':
                            case '/app/registroCertificado':
                            case '/app/contactar':
                            case '/app/requisitos':
                            case '/app/avisoLegal':
                            case '/app/retornar':
                            case '/app/foro/retornar':
                            case '/app/voto/retornar':
                            case '/app/derecho/retornar':
                            case '/app/av/retornar':
                            case '/app/pendiente':
                            case '/app/header/notificaciones':
                            case '/app/header/mail/mail':
                                return $element.addClass('body-nonav');
                            case '/app/voto/voto':
                            case '/app/av/voto':
                                return $element.addClass('body-voto');
                            case '/pages/lock-screen':
                                return $element.addClass('body-special body-lock');
                            case '/tasks':
                                return $element.addClass('body-tasks');
                        }
                    };
                    addBg($location.path());
                    return $scope.$watch(path, function(newVal, oldVal) {
                        if (newVal === oldVal) {
                            return;
                        }
                        return addBg($location.path());
                    });
                }
            ]
        };
    }).directive('uiColorSwitch', [
        function() {
            return {
                restrict: 'A',
                link: function(scope, ele, attrs) {
                    return ele.find('.color-option').on('click', function(event) {
                        var $this, hrefUrl, style;
                        $this = $(this);
                        hrefUrl = void 0;
                        style = $this.data('style');
                        if (style === 'loulou') {
                            hrefUrl = 'styles/main.css';
                            $('link[href^="styles/main"]').attr('href', hrefUrl);
                        } else if (style) {
                            style = '-' + style;
                            hrefUrl = 'styles/main' + style + '.css';
                            $('link[href^="styles/main"]').attr('href', hrefUrl);
                        } else {
                            return false;
                        }
                        return event.preventDefault();
                    });
                }
            };
        }
    ]).directive('toggleMinNav', [
        '$rootScope', function($rootScope) {
            return {
                restrict: 'A',
                link: function(scope, ele, attrs) {
                    var $content, $nav, $window, Timer, app, updateClass;
                    app = $('#app');
                    $window = $(window);
                    $nav = $('#nav-container');
                    $content = $('#content');
                    ele.on('click', function(e) {
                        if (app.hasClass('nav-min')) {
                            app.removeClass('nav-min');
                        } else {
                            app.addClass('nav-min');
                            $rootScope.$broadcast('minNav:enabled');
                        }
                        return e.preventDefault();
                    });
                    Timer = void 0;
                    updateClass = function() {
                        var width;
                        width = $window.width();
                        if (width < 768) {
                            return app.removeClass('nav-min');
                        }
                    };
                    return $window.resize(function() {
                        var t;
                        clearTimeout(t);
                        return t = setTimeout(updateClass, 300);
                    });
                }
            };
        }
    ]).directive('collapseNav', [
        function() {
            return {
                restrict: 'A',
                link: function(scope, ele, attrs) {
                    var $a, $aRest, $lists, $listsRest, app;
                    $lists = ele.find('ul').parent('li');
                    $lists.append('<i class="fa fa-caret-right icon-has-ul"></i>');
                    $a = $lists.children('a');
                    $listsRest = ele.children('li').not($lists);
                    $aRest = $listsRest.children('a');
                    app = $('#app');
                    $a.on('click', function(event) {
                        var $parent, $this;
                        if (app.hasClass('nav-min')) {
                            return false;
                        }
                        $this = $(this);
                        $parent = $this.parent('li');
                        $lists.not($parent).removeClass('open').find('ul').slideUp();
                        $parent.toggleClass('open').find('ul').slideToggle();
                        return event.preventDefault();
                    });
                    $aRest.on('click', function(event) {
                        return $lists.removeClass('open').find('ul').slideUp();
                    });
                    return scope.$on('minNav:enabled', function(event) {
                        return $lists.removeClass('open').find('ul').slideUp();
                    });
                }
            };
        }
    ]).directive('highlightActive', [
        function() {
            return {
                restrict: "A",
                controller: [
                    '$scope', '$element', '$attrs', '$location', function($scope, $element, $attrs, $location) {
                        var highlightActive, links, path;
                        links = $element.find('a');
                        path = function() {
                            return $location.path();
                        };
                        highlightActive = function(links, path) {
                            path = '#' + path;
                            return angular.forEach(links, function(link) {
                                var $li, $link, href;
                                $link = angular.element(link);
                                $li = $link.parent('li');
                                href = $link.attr('href');
                                if ($li.hasClass('active')) {
                                    $li.removeClass('active');
                                }
                                if (path.indexOf(href) === 0) {
                                    return $li.addClass('active');
                                }
                            });
                        };
                        highlightActive(links, $location.path());
                        return $scope.$watch(path, function(newVal, oldVal) {
                            if (newVal === oldVal) {
                                return;
                            }
                            return highlightActive(links, $location.path());
                        });
                    }
                ]
            };
        }
    ]).directive('toggleOffCanvas', [
        function() {
            return {
                restrict: 'A',
                link: function(scope, ele, attrs) {
                    return ele.on('click', function() {
                        return $('#app').toggleClass('on-canvas');
                    });
                }
            };
        }
    ]).directive('slimScroll', [
        function() {
            return {
                restrict: 'A',
                link: function(scope, ele, attrs) {
                    return ele.slimScroll({
                        height: attrs.scrollHeight || '100%'
                    });
                }
            };
        }
    ]).directive('goBack', [
        function() {
            return {
                restrict: "A",
                controller: [
                    '$scope', '$element', '$window', function($scope, $element, $window) {
                        return $element.on('click', function() {
                            return $window.history.back();
                        });
                    }
                ]
            };
        }
    ]);
}).call(this);      
