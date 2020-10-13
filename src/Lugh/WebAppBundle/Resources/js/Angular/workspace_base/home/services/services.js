(function() {
    'use strict';
	angular.module('homeServices', [
	]).service('validateAccionista',['documentValidation','$localStorage','jsUtils', function(documentValidation,$localStorage,jsUtils){
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

        this.validate = function(data,emailcValid,documentACount,documentBCount,withCertificate){
            var accionista = data.accionista;
            var user       = data.user;

            var requiredDocA = $localStorage['Config.require.doca'] == 1 || (withCertificate && $localStorage['Config.require.doca-certificate'] == 1) || (!withCertificate && $localStorage['Config.require.doca-user'] == 1);
            var requiredDocB = $localStorage['Config.require.docb'] == 1 || (withCertificate && $localStorage['Config.require.docb-certificate'] == 1) || (!withCertificate && $localStorage['Config.require.docb-user'] == 1);

            var valid = {
                representedBy: ($localStorage['Config.require.representative'] != 1 && accionista.personaType == '') || accionista.personaType == "pf" || (accionista.personaType == "pj" && accionista.representedBy !== undefined && accionista.representedBy !== ''),
                name:          ($localStorage['Config.require.name'] != 1          && accionista.name        == '') || accionista.name !== undefined &&  accionista.name !== '',
                documentNum:   ($localStorage['Config.require.numero-doc'] != 1     && accionista.documentNum == '') || documentValidation.validate(accionista.documentNum, accionista.documentType),
                username:      ($localStorage['Config.require.username'] != 1       && user.username          == '') || withCertificate || (user.username !== undefined && user.username !== ''),
                //https://html.spec.whatwg.org/multipage/forms.html#valid-e-mail-address
                email:         ($localStorage['Config.require.email'] != 1 && user.email == '') || (/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/).test(user.email),
                emailc:        emailcValid || user.email === '',
                telephone:     ($localStorage['Config.require.telephone'] != 1 && accionista.telephone == '') || accionista.telephone !== undefined && accionista.telephone !== '',
                LOPD:          $localStorage['Config.require.LOPD'] != 1 || data.LOPD,
                num_shares:    ($localStorage['Config.accionista.accionesMin'] == undefined && Number(jsUtils.cleanNumber(accionista.sharesNum)) > 0) || Number(jsUtils.cleanNumber(accionista.sharesNum)) >= Number($localStorage['Config.accionista.accionesMin']),
                DocumentA:     !requiredDocA || documentACount > 0,
                DocumentB:     !requiredDocB || documentBCount > 0
            };

            return joinFalse(valid);
        };
        
        this.validateRacc = function(data,emailcValid,documentACount,documentBCount,withCertificate){
            var accionista = data.accionista;
            var user       = data.user;

            var requiredDocA = $localStorage['Config.require.doca'] == 1 || (withCertificate && $localStorage['Config.require.doca-certificate'] == 1) || (!withCertificate && $localStorage['Config.require.doca-user'] == 1);
            var requiredDocB = $localStorage['Config.require.docb'] == 1 || (withCertificate && $localStorage['Config.require.docb-certificate'] == 1) || (!withCertificate && $localStorage['Config.require.docb-user'] == 1);

            var valid = {
                representedBy: ($localStorage['Config.require.representative'] != 1 && accionista.personaType == '') || accionista.personaType == "pf" || (accionista.personaType == "pj" && accionista.representedBy !== undefined && accionista.representedBy !== ''),
                name:          ($localStorage['Config.require.name'] != 1          && accionista.name        == '') || accionista.name !== undefined &&  accionista.name !== '',
                documentNum:   ($localStorage['Config.require.numero-doc'] != 1     && accionista.documentNum == '') || documentValidation.validate(accionista.documentNum, accionista.documentType),
                username:      ($localStorage['Config.require.username'] != 1       && user.username          == '') || (user.username !== undefined && user.username !== '' && documentValidation.validate(user.username, accionista.documentType)),
                //https://html.spec.whatwg.org/multipage/forms.html#valid-e-mail-address
                email:         ($localStorage['Config.require.email'] != 1 && user.email == '') || (/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/).test(user.email),
                emailc:        emailcValid || user.email === '',
                telephone:     ($localStorage['Config.require.telephone'] != 1 && accionista.telephone == '') || accionista.telephone !== undefined && accionista.telephone !== '',
                LOPD:          $localStorage['Config.require.LOPD'] != 1 || data.LOPD,
                num_shares:    ($localStorage['Config.accionista.accionesMin'] == undefined && Number(jsUtils.cleanNumber(accionista.sharesNum)) > 0) || Number(jsUtils.cleanNumber(accionista.sharesNum)) >= Number($localStorage['Config.accionista.accionesMin']),
                DocumentA:     !requiredDocA || documentACount > 0,
                DocumentB:     !requiredDocB || documentBCount > 0
            };

            return joinFalse(valid);
        };
    }]);
}).call(this);