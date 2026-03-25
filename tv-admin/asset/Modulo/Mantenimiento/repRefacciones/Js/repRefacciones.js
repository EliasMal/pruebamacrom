'use strict';
const url = "./Modulo/Mantenimiento/repRefacciones/Ajax/repRefacciones.php";

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

var confirmarMantenimiento = (titulo, texto, icono, btnText, btnColor, accion) => {
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

tsuruVolks.controller('repRefaccionesCtrl', ["$scope", "$http", repRefaccionesCtrl]);

function repRefaccionesCtrl($scope, $http){
    const obj = $scope;
    obj.mantenimiento = {};
    obj.estadoMantenimiento = 0;

    obj.getEstadoMantenimiento = () => {
        $http.post(url, { mantenimiento: { opc: "getEstadoMantenimiento" } }).then(function(res){
            if(res.data.Bandera == 1){
                obj.estadoMantenimiento = res.data.estado;
            }
        });
    };

    obj.btnBloqUS = () => {
        confirmarMantenimiento(
            '¿Activar Modo Mantenimiento?',
            'Esta acción expulsará y bloqueará a todos los usuarios del sistema (excepto a ti).',
            'warning',
            '<i class="fas fa-lock"></i> Sí, Bloquear Accesos',
            '#dc3545',
            () => {
                obj.mantenimiento.opc = "desactivarUS";
                
                $http.post(url, { mantenimiento: obj.mantenimiento }).then(function(res){
                    if(res.data.Bandera == 1){
                        Swal.fire('¡Sistema Bloqueado!', res.data.Mensaje, 'warning');
                        obj.estadoMantenimiento = 1;
                    } else {
                        Swal.fire('Error', res.data.Mensaje, 'error');
                    }
                }, function(res){
                    Toast.fire({ icon: 'error', title: 'Error de conexión al intentar bloquear el sistema.' });
                });
            }
        );
    };

    obj.btnActUS = () => {
        confirmarMantenimiento(
            '¿Reactivar el sistema?',
            'Todos los usuarios podrán volver a iniciar sesión normalmente.',
            'question',
            '<i class="fas fa-unlock"></i> Sí, Reactivar',
            '#28a745',
            () => {
                obj.mantenimiento.opc = "activarUS";
                
                $http.post(url, { mantenimiento: obj.mantenimiento }).then(function(res){
                    if(res.data.Bandera == 1){
                        Swal.fire('¡Sistema Activo!', res.data.Mensaje, 'success');
                        obj.estadoMantenimiento = 0;
                    } else {
                        Swal.fire('Error', res.data.Mensaje, 'error');
                    }
                }, function(res){
                    Toast.fire({ icon: 'error', title: 'Error de conexión al intentar reactivar el sistema.' });
                });
            }
        );
    };

    angular.element(document).ready(function () {
        obj.getEstadoMantenimiento();
    });
}