'use strict';
var urlContacto = "./Modulo/Control/Contacto/Ajax/Contacto.php";

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

tsuruVolks.controller('ContactoCtrl', ["$scope", "$http", ContactoCtrl]);

function ContactoCtrl($scope, $http){
    var obj = $scope;
    obj.id = 0;
    obj.datos = {};
    
    obj.data = {
        opc: "",
        id: "",
        historico: false
    };

    const confirmarAccionContacto = (titulo, texto, icono, btnText, btnColor, accion) => {
        Swal.fire({
            title: titulo,
            text: texto,
            icon: icono,
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: btnText,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) accion();
        });
    };

    obj.getContactos = () => {
        obj.data.opc = "get";
        obj.sendData();
    };

    obj.getContacto = () => {
        obj.data.opc = "set"; 
        obj.sendData();
    };

    obj.viewMsg = (_id) => {
        location.href = "?mod=Contacto&opc=detalles&id=" + _id;
    };

    obj.btnRegresar = () => {
        location.href = "?mod=Contacto";
    };

    obj.eliminarMsg = (idMsg, event) => {
        if(event) event.stopPropagation();
        
        confirmarAccionContacto(
            '¿Eliminar mensaje?',
            'Esta acción borrará el mensaje de contacto de forma permanente.',
            'warning',
            '<i class="fas fa-trash-alt"></i> Sí, eliminar',
            '#dc3545',
            () => {
                $http.post(urlContacto, { contacto: { opc: "delete", id: idMsg } }).then(function(res){
                    if(res.data.Bandera == 1){
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Mensaje eliminado' });
                        obj.datos = res.data;
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje || 'Error al eliminar' });
                    }
                }, function(res){
                    Toast.fire({ icon: 'error', title: 'Error de conexión con el servidor' });
                });
            }
        );
    };

    obj.toggleLeido = (idMsg, estadoActual, event) => {
        if(event) event.stopPropagation(); 
        let nuevoEstado = estadoActual == 1 ? 0 : 1;
        
        $http.post(urlContacto, { contacto: { opc: "toggle_read", id: idMsg, estado: nuevoEstado } }).then(function(res){
            if(res.data.Bandera == 1){
                obj.datos = res.data;
                Toast.fire({ icon: 'info', title: nuevoEstado ? "Marcado como leído" : "Marcado como no leído" });
            } else {
                Toast.fire({ icon: 'error', title: 'Error al cambiar el estado' });
            }
        }, function(res){
            Toast.fire({ icon: 'error', title: 'Error de conexión con el servidor' });
        });
    };

    obj.toggleDestacado = (idMsg, event) => {
        if(event) event.stopPropagation();
        $http.post(urlContacto, { contacto: { opc: "toggle_star", id: idMsg } }).then(function(res){
            if(res.data.Bandera == 1){
                obj.datos = res.data; 
                Toast.fire({ icon: 'success', title: 'Preferencia actualizada' });
            } else {
                Toast.fire({ icon: 'error', title: 'Error al destacar el mensaje' });
            }
        }, function(res){
            Toast.fire({ icon: 'error', title: 'Error de conexión con el servidor' });
        });
    };

    obj.sendData = () => {
        $http({
            method: 'POST',
            url: urlContacto,
            data: { contacto: obj.data }
        }).then(function(res){
            if(res.data.Bandera == 1){
                obj.datos = res.data; 
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje || 'Error al procesar la solicitud' });
            }
        }, function(res){
            Toast.fire({ icon: 'error', title: 'Error: no se realizó la conexión con el servidor' });
        });
    };

    angular.element(document).ready(function(){
        let params = new URLSearchParams(window.location.search);
        let idUrl = params.get('id');
        
        if (idUrl) { obj.data.id = idUrl; }

        if(obj.data.id != ""){
            obj.getContacto();    
        } else {
            obj.getContactos();
        }
    });
}