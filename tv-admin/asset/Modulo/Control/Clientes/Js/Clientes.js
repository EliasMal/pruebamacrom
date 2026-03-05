var url = "./Modulo/Control/Clientes/Ajax/Clientes.php";
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
    }

    obj.getClientes = () => {
        obj.data.opc = "get";
        obj.sendData();
    }

    obj.getnewClientes = () => {
        setTimeout(()=>{
            obj.data.opc = obj.data.new ? 'new' : 'get';
            obj.sendData();
        },200);
    }

    obj.getEstatus = (id, estatus) => {
        obj.data.opc = "set"
        obj.data.estatus = estatus;
        obj.data.id = id;
        obj.sendData();
    }

    obj.changepass = (id) => {
        obj.data.opc = "pass";
        obj.data.id = id;
        obj.sendData();
    }

    obj.btnPerfil = (id) => {
        location.href = "?mod=Clientes&opc=perfil&id=" + id;
    }

    obj.sendData = () => {
        setTimeout(() => {
            $http({
                method: 'POST',
                url: url,
                data: { cliente: obj.data }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    if (obj.data.opc == "pass") {
                        $("#showpass").html(res.data.pass);
                        $("#modalpass").modal("show");
                    } else {
                        obj.Clientes = res.data.Cliente;
                    }

                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }, 100);
    }

    angular.element(document).ready(function () {
        obj.data.new = obj.data.new == "1" ? true : false;
        if (obj.data.new) {
            obj.getnewClientes();
        } else {
            obj.getClientes();
        }

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
            url: url,
            data: { cliente: { opc: "perfil", id: obj.id } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.cliente = res.data.data;
                obj.cliente.count = res.data.count;
                obj.getCuponesCliente();
            }
        }, function errorCallback(res) {
            console.log("Entra a sendData error: ", res);
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.asignarCupon = (idCupon) => {
        $http({
            method: 'POST',
            url: url,
            data: {
                cliente: {
                    opc: "asignarCupon",
                    id: obj.id,
                    id_cupon: idCupon
                }
            }
        }).then(() => {
            toastr.success("Cupón asignado");
            obj.getCuponesCliente();
        });
    }

    obj.quitarCupon = (idCupon) => {
        $http({
            method: 'POST',
            url: url,
            data: {
                cliente: {
                    opc: "quitarCupon",
                    id: obj.id,
                    id_cupon: idCupon
                }
            }
        }).then(() => {
            toastr.success("Cupón eliminado");
            obj.getCuponesCliente();
        });
    }

    obj.getCuponesCliente = () => {

        $http({
            method: 'POST',
            url: url,
            data: {
                cliente: {
                    opc: "getCuponesCliente",
                    id: obj.id
                }
            }
        }).then(function (res) {

            if (res.data.Bandera == 1) {
                obj.cuponesDisponibles = res.data.disponibles;
                obj.cuponesCliente = res.data.cliente;
            }

        }, function () {
            toastr.error("Error cargando cupones");
        });
    }

    obj.btnEditar = () => {
        if (obj.disabled) {
            obj.disabled = false;
            $("#txtname").focus();
        } else {
            obj.disabled = true;
        }
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
    // 🔹 Listar cupones admin
    obj.listar = () => {

        $http({
            method: 'POST',
            url: url,
            data: {
                cliente: {
                    opc: "listarCuponesAdmin"
                }
            }
        }).then(function(res){

            if(res.data.Bandera == 1){
                obj.cupones = res.data.cupones;
            }

        }, function(){
            toastr.error("Error cargando cupones");
        });
    };
    // 🔹 Crear cupón
    obj.crearCupon = () => {

        if(!obj.nuevo.codigo || !obj.nuevo.descuento){
            Swal.fire("Error","Completa los campos obligatorios","error");
            return;
        }

        $http({
            method: 'POST',
            url: url,
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
                Swal.fire("Éxito","Cupón creado correctamente","success");
                obj.nuevo = { es_global: true, uso_unico: false };
                obj.listar();
            }

        }, function(){
            toastr.error("Error al crear cupón");
        });
    };
    // 🔹 Activar / Desactivar
    obj.toggleActivo = (id) => {

        $http({
            method: 'POST',
            url: url,
            data: {
                cliente: {
                    opc: "toggleActivoCupon",
                    id: id
                }
            }
        }).then(function(res){
            if(res.data.Bandera == 1){
                obj.listar();
            }
        });

    };
    // 🔹 Global / No Global
    obj.toggleGlobal = (id) => {

        $http({
            method: 'POST',
            url: url,
            data: {
                cliente: {
                    opc: "toggleGlobalCupon",
                    id: id
                }
            }
        }).then(function(res){
            if(res.data.Bandera == 1){
                obj.listar();
            }
        });

    };
    // 🔹 Eliminar cupón
    obj.eliminar = (id) => {

        Swal.fire({
            title: "¿Eliminar cupón?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar"
        }).then((result)=>{

            if(result.isConfirmed){

                $http({
                    method: 'POST',
                    url: url,
                    data: {
                        cliente: {
                            opc: "eliminarCupon",
                            id: id
                        }
                    }
                }).then(function(res){

                    if(res.data.Bandera == 1){
                        toastr.success("Cupón eliminado");
                        obj.listar();
                    }

                });

            }
        });

    };
    // 🔹 Inicializar
    angular.element(document).ready(function () {
        obj.listar();
    });

}