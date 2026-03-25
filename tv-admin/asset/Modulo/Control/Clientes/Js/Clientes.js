'use strict';

var urlClientes = "./Modulo/Control/Clientes/Ajax/Clientes.php";
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

tsuruVolks
    .controller('ClientesCtrl', ["$scope", "$http", ClientesCtrl])
    .controller('ClientesCuponesCtrl', ["$scope", "$http", ClientesCuponesCtrl])
    .controller('ClientesPerfilCtrl', ["$scope", "$http", ClientesPerfilCtrl]);


function ClientesCtrl($scope, $http) {
    var obj = $scope;
    obj.Clientes = [];
    obj.data = {
        opc: "",
        historico: false,
        new: false
    };

    obj.loadClientes = () => {
        obj.data.opc = obj.data.new ? 'new' : 'get';
        obj.sendData();
    };

    obj.getClientes = () => { obj.data.new = false; obj.loadClientes(); };
    obj.getnewClientes = () => { obj.loadClientes(); };

    obj.getEstatus = (user, estatus) => {
        let accion = estatus == 1 ? "REACTIVAR" : "DESACTIVAR";
        let msjTexto = estatus == 1 ? `El cliente "${user.nombre}" volverá a tener acceso.` : `El cliente "${user.nombre}" no podrá iniciar sesión.`;
        let icono = estatus == 1 ? 'question' : 'warning';
        let colorBtn = estatus == 1 ? '#28a745' : '#dc3545';

        Swal.fire({
            title: `¿${accion} cliente?`,
            text: msjTexto,
            icon: icono,
            showCancelButton: true,
            confirmButtonColor: colorBtn,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                obj.data.opc = "set";
                obj.data.estatus = estatus;
                obj.data.id = user._id;
                obj.sendData();
            }
        });
    };

    obj.changepass = (user) => {
        Swal.fire({
            title: '¿Generar nueva contraseña?',
            text: `Se creará una contraseña aleatoria para el cliente: ${user.nombre}`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-key"></i> Sí, generar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                obj.data.opc = "pass";
                obj.data.id = user._id;
                obj.sendData();
            }
        });
    };

    obj.btnPerfil = (id) => {
        location.href = "?mod=Clientes&opc=perfil&id=" + id;
    };

    obj.sendData = () => {
        $http({
            method: 'POST',
            url: urlClientes,
            data: { cliente: obj.data }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                if (obj.data.opc == "pass") {
                    $("#showpass").html(res.data.pass);
                    $("#modalpass").modal("show");
                    Toast.fire({ icon: 'success', title: 'Contraseña generada exitosamente' });
                } else {
                    obj.Clientes = res.data.Cliente;
                    if(obj.data.opc == "set") {
                        Toast.fire({ icon: 'success', title: 'Estatus actualizado correctamente' });
                    }
                }
            } else {
                Toast.fire({ icon: 'error', title: 'Error devuelto por el servidor.' });
            }
        }, function errorCallback(res) {
            Toast.fire({ icon: 'error', title: 'Error de conexión con el servidor' });
        });
    };

    angular.element(document).ready(function () {
        obj.data.new = String(obj.data.new).includes("1") || String(obj.data.new).includes("true");
        obj.loadClientes();
    });
}

function ClientesPerfilCtrl($scope, $http) {
    var obj = $scope;
    obj.id;
    obj.cliente = {};
    obj.disabled = true;
    obj.cuponesDisponibles = [];
    obj.cuponesCliente = [];

    obj.getCliente = () => {
        $http({
            method: 'POST',
            url: urlClientes,
            data: { cliente: { opc: "perfil", id: obj.id } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.cliente = res.data.data;
                obj.cliente.count = res.data.count;
                obj.getCuponesCliente();
            }
        }, function errorCallback(res) {
            Toast.fire({ icon: 'error', title: 'Error al cargar los datos del cliente' });
        });
    }

    obj.asignarCupon = (idCupon) => {
        $http.post(urlClientes, { cliente: { opc: "asignarCupon", id: obj.id, id_cupon: idCupon } }).then(() => {
            Toast.fire({ icon: 'success', title: 'Cupón asignado exitosamente' }); 
            obj.getCuponesCliente();
        });
    }

    obj.quitarCupon = (idCupon) => {
        $http.post(urlClientes, { cliente: { opc: "quitarCupon", id: obj.id, id_cupon: idCupon } }).then(() => {
            Toast.fire({ icon: 'info', title: 'Cupón removido del cliente' }); 
            obj.getCuponesCliente();
        });
    }

    obj.getCuponesCliente = () => {
        $http.post(urlClientes, { cliente: { opc: "getCuponesCliente", id: obj.id } }).then(function (res) {
            if (res.data.Bandera == 1) {
                obj.cuponesDisponibles = res.data.disponibles;
                obj.cuponesCliente = res.data.cliente;
            }
        });
    }

    obj.btnEditar = () => {
        if (obj.disabled) {
            obj.disabled = false;
            setTimeout(() => { $("#txtname").focus(); }, 100);
        } else {
            obj.guardarPerfilCliente();
        }
    }

    obj.guardarPerfilCliente = () => {
        Swal.fire({
            title: '¿Guardar cambios?',
            text: "Se actualizará la información del perfil del cliente.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                let datosGuardar = angular.copy(obj.cliente);
                datosGuardar.opc = "updatePerfil";
                datosGuardar.avisoprivacidad = datosGuardar.avisoprivacidad ? 1 : 0; 

                $http({
                    method: 'POST',
                    url: urlClientes,
                    data: { cliente: datosGuardar }
                }).then(function(res){
                    if(res.data.Bandera == 1){
                        Toast.fire({ icon: 'success', title: res.data.mensaje });
                        obj.disabled = true;
                    } else {
                        Toast.fire({ icon: 'error', title: "Error: " + res.data.mensaje });
                    }
                }, function(){
                    Toast.fire({ icon: 'error', title: 'Error de conexión al guardar' });
                });
            }
        });
    }

    angular.element(document).ready(function () {
        obj.getCliente();
    });
}


function ClientesCuponesCtrl($scope, $http) {
    var obj = $scope;
    obj.cupones = [];
    obj.nuevo = {
        es_global: true,
        uso_unico: false
    };

    obj.listar = () => {
        $http({
            method: 'POST',
            url: urlClientes,
            data: { cliente: { opc: "listarCuponesAdmin" } }
        }).then(function(res){
            if(res.data.Bandera == 1){
                obj.cupones = res.data.cupones;
            }
        }, function(){
            Toast.fire({ icon: 'error', title: 'Error cargando los cupones' });
        });
    };

    obj.crearCupon = () => {
        if(!obj.nuevo.codigo || !obj.nuevo.descuento){
            Toast.fire({ icon: 'warning', title: 'Completa los campos obligatorios' });
            return;
        }

        $http({
            method: 'POST',
            url: urlClientes,
            data: {
                cliente: {
                    opc: "crearCupon",
                    codigo: obj.nuevo.codigo,
                    descuento: obj.nuevo.descuento,
                    uso_unico: obj.nuevo.uso_unico ? 1 : 0,
                    fecha_expiracion: obj.nuevo.fecha_expiracion,
                    es_global: obj.nuevo.es_global ? 1 : 0
                }
            }
        }).then(function(res){
            if(res.data.Bandera == 1){
                Toast.fire({ icon: 'success', title: 'Cupón creado correctamente' });
                obj.nuevo = { es_global: true, uso_unico: false };
                obj.listar();
            }
        }, function(){
            Toast.fire({ icon: 'error', title: 'Error al intentar crear el cupón' });
        });
    };

    obj.toggleActivo = (id) => {
        $http({
            method: 'POST',
            url: urlClientes,
            data: { cliente: { opc: "toggleActivoCupon", id: id } }
        }).then(function(res){
            if(res.data.Bandera == 1){
                obj.listar();
            }
        });
    };

    obj.toggleGlobal = (id) => {
        $http({
            method: 'POST',
            url: urlClientes,
            data: { cliente: { opc: "toggleGlobalCupon", id: id } }
        }).then(function(res){
            if(res.data.Bandera == 1){
                obj.listar();
            }
        });
    };

    obj.eliminar = (id) => {
        Swal.fire({
            title: "¿Eliminar cupón?",
            html: "Esta acción borrará el cupón <b>definitivamente</b>.",
            icon: "error",
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result)=>{
            if(result.isConfirmed){
                $http({
                    method: 'POST',
                    url: urlClientes,
                    data: { cliente: { opc: "eliminarCupon", id: id } }
                }).then(function(res){
                    if(res.data.Bandera == 1){
                        Toast.fire({ icon: 'success', title: 'Cupón eliminado correctamente' });
                        obj.listar();
                    }
                });
            }
        });
    };

    angular.element(document).ready(function () {
        obj.listar();
    });
}