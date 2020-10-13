(function() {
    'use strict';
    angular.module('sharedFilters',[])
    .filter("nl2br", [
        function() {
            return function(data) {
                if (!data) return data;
                return data.replace(/\n\r?/g, '<br />');
            };
    }]).filter('entitiesDecode', [ function(){
        return function(val) {
          if (val) return html_entity_decode(val);
        };
    }]).filter('formattedNumber',[
        'jsUtils',function(jsUtils){
            return function(val) {
                return jsUtils.formatNumber(val);
            }
        }
    ]).filter('truncateString',[ function(){
            /*
             * Ejemplo:
             * {{v | truncateString:10:'...'}}
             * corta el string en el ultimo espacio antes del 10º caracter, y añade '...'
             * {{v | truncateString:10:'...':true}}
             * no mira los espacios, corta en el 10º caracter sin más y añade '...'
             */
            return function(value,length,tail,cutWord){
                if(!value) return '';

                var max = parseInt(length);
                if(!max) return value;
                if(value.length <= max) return value;

                value = value.substr(0,max);
                if(!cutWord){
                    var lastspace = value.lastIndexOf(' ');
                    if(lastspace != -1){
                        value = value.substr(0,lastspace);
                    }
                }

                return value + (tail || ' ...');
            }
        }
    ]);
}).call(this);