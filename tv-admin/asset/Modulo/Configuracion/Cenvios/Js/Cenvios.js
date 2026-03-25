'use strict';

var url = "./Modulo/Configuracion/Cenvios/Ajax/Cenvios.php";
if (typeof window.Toast === 'undefined') {
    window.Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

var confirmarAccionCenvios = (titulo, texto, icono, btnColor, btnText, accion) => {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        showCancelButton: true,
        confirmButtonColor: btnColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: btnText,
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) accion();
    });
};

tsuruVolks.controller('CenviosCtrl', ["$scope","$http", CenviosCtrl]);

function CenviosCtrl($scope, $http){
    var obj = $scope
    obj.estados = [];
    obj.municipios = [];
    obj.envios = [];
    obj.editar = false;

    obj.send = { opc:"" };
    
    obj.btnNuevoEnvio = () => {
        obj.send = {};
        obj.editar = false;
        $("#Cenvios").modal('show');
    }

    obj.btnCrearEnvio = () => {
        if(!obj.send.Precio || obj.send.Precio === '') {
            Toast.fire({ icon: 'warning', title: 'Ingresa un costo de envío válido.' });
            return;
        }

        if(!obj.editar && (!obj.send.Estado || !obj.send.Municipio)) {
            Toast.fire({ icon: 'warning', title: 'Selecciona el Estado y el Municipio primero.' });
            return;
        }

        let titulo = obj.editar ? "¿Editar costo de envío?" : "¿Crear nuevo costo?";
        let texto = obj.editar ? "Se actualizará el precio para este destino." : "Se registrará el nuevo costo de envío.";
        let colorBtn = obj.editar ? "#007bff" : "#28a745";
        let iconBtn = obj.editar ? '<i class="fas fa-save"></i> Guardar' : '<i class="fas fa-check"></i> Crear';

        confirmarAccionCenvios(
            titulo, texto, 'question', colorBtn, iconBtn, 
            () => {
                obj.send.opc = obj.editar ? "edit" : "set";
                obj.sendData();
                $("#Cenvios").modal('hide');
            }
        );
    }

    obj.btnDesactivar = (id) => {
        confirmarAccionCenvios(
            '¿Desactivar costo de envío?',
            'Este destino ya no estará disponible.',
            'error',
            '#dc3545',
            '<i class="fas fa-trash-alt"></i> Sí, desactivar',
            () => {
                obj.send.opc = "off";
                obj.send.id = id;
                obj.sendData();
            }
        );
    }

    obj.getEstados = () => {
        obj.send.opc = "getEstados";
        obj.sendData();
    }

    obj.getEnvios = () => {
        obj.send.opc= "getEnvios";
        obj.sendData();
    }

    obj.btnEditar = (data) => {
        obj.editar = true;
        obj.send = data;
        $("#Cenvios").modal('show'); 
    }

    obj.getMunicipios = () => {
        obj.send.opc = "getMunicipios";
        obj.sendData();
    }

    obj.sendData = () => {
        $http({
            method: 'POST',
            url: url,
            data: obj.send,
        }).then(function successCallback(res){
            if(res.data.Bandera == 1){
                switch(obj.send.opc){
                    case 'getEstados':
                        obj.estados = res.data.Estados;
                        obj.envios = res.data.Envios;
                        break;
                    case 'getMunicipios':
                        obj.municipios = res.data.Data;
                        break;
                    case 'getEnvios':
                        obj.envios = res.data.Envios;
                        break;
                    case 'set':
                    case 'off':
                        obj.getEnvios();
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Operación exitosa' });
                        break;
                    case 'edit':
                        obj.getEnvios();
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Costo actualizado' });
                        break;
                }
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje });
            }
        }, function errorCallback(res){
            Toast.fire({ icon: 'error', title: 'Error: No se realizó la conexión con el servidor' });
        });
    }

    angular.element(document).ready(function(){
        obj.getEstados();
    });
}