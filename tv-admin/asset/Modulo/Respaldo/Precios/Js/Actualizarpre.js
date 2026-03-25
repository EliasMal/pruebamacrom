'use strict';

var url = "./Includes/Cron/getRefacciones.php";

tsuruVolks.controller('PreciosCrtl', ["$scope", "$http", "$timeout", PreciosCrtl]);

function PreciosCrtl($scope, $http, $timeout) {
    var obj = $scope;
    obj.flag = false;
    obj.usr = "root";
    obj.logData = [];

    const confirmarSincronizacion = (titulo, texto, accionConfirmada) => {
        Swal.fire({
            title: titulo,
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#de0007',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-sync-alt"></i> Sí, iniciar sincronización',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            backdrop: `rgba(0,0,0,0.6)`
        }).then((result) => {
            if (result.isConfirmed) {
                accionConfirmada();
            }
        });
    };

    // --- SINCRONIZACIÓN PRINCIPAL (SEICOM) ---
    obj.btnActualizar = () => {
        confirmarSincronizacion(
            '¿Sincronizar con SEICOM?',
            'Esta acción actualizará los precios y existencias de todo el catálogo. Puede tardar unos minutos.',
            () => {
                $timeout(() => { obj.flag = true; });
                $http.post(url, { usr: obj.usr, opc: "inicio_sync" }).then(function(){
                    $http({
                        method: 'POST',
                        url: "https://volks.dyndns.info:444/service.asmx/datos_art",
                        headers: { 'Content-Type': "application/x-www-form-urlencoded" },
                        transformResponse: function(data){ return $.parseXML(data); }
                    }).then(function(res){
                        var xml = $(res.data);
                        var json = xml.find("string");
                        
                        obj.enviar(json[0].innerHTML);
                        
                    }, function(res){
                        $timeout(() => { obj.flag = false; });
                        toastr.error("Error crítico: No se pudo establecer conexión con el servidor SEICOM.");
                    });
                    
                });
            }
        );
    };

    obj.enviar = (jsonStr) => {
        $http.post(url, { usr: obj.usr, opc: "set", json: jsonStr }).then(function(res){
            $timeout(() => { obj.flag = false; });
            if(res.data.Bandera == 1){
                obj.logData = res.data.Data;

                Swal.fire({
                    title: '¡Sincronización Exitosa!',
                    text: res.data.mensaje,
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                });
                
            } else {
                toastr.error(res.data.mensaje);
            }
        }, function(res){
            $timeout(() => { obj.flag = false; });
            toastr.error("Error: Falló la comunicación con el servidor local al guardar.");
        });
    };

    obj.btnActualizar2 = () => {
        confirmarSincronizacion(
            '¿Forzar Sincronización Local?',
            'El sistema intentará actualizar desde los datos almacenados en el servidor local.',
            () => {
                $timeout(() => { obj.flag = true; });
                
                $http.post(url, { usr: obj.usr, opc: "set" }).then(function(res){
                    $timeout(() => { obj.flag = false; });
                    if(res.data.Bandera == 1){
                        obj.logData = res.data.Data;
                        
                        Swal.fire({
                            title: '¡Sincronización Exitosa!',
                            text: res.data.mensaje,
                            icon: 'success',
                            confirmButtonColor: '#28a745'
                        });
                        
                    } else {
                        toastr.error(res.data.mensaje);
                    }
                }, function(res){
                    $timeout(() => { obj.flag = false; });
                    toastr.error("Error de conexión local.");
                });
            }
        );
    };

    obj.getLog = () => {
        $http.post(url, { opc: "get" }).then(function(res){
            if(res.data.Bandera == 1){
                obj.logData = res.data.Data;
            } else {
                toastr.error("No se pudo cargar el historial de actualizaciones.");
            }
        });
    };

    angular.element(document).ready(function(){
        obj.getLog();
    });
}