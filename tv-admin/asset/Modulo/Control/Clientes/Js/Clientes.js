/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var url = "./Modulo/Control/Clientes/Ajax/Clientes.php";

tsuruVolks
    .controller('ClientesCtrl', ["$scope", "$http", ClientesCtrl])
    .controller('ClientesPerfilCtrl', ["$scope", "$http", ClientesPerfilCtrl]);

(function () {
    const tagsInput = document.querySelector("#tags_input");
    if (tagsInput) {

        const tagsDiv = document.querySelector("#tags");
        const tagsInputHidden = document.querySelector('[name="tags"]');

        let tags = [];

        //Escuchar cambios en el input
        tagsInput.addEventListener("keypress", guardarTag)

        function guardarTag(e) {

            if (e.keyCode === 44 || e.keyCode === 13) {
                if (e.target.value.trim() === '' || e.target.value < 1) {
                    return
                }
                e.preventDefault();

                tags = [...tags, e.target.value.trim()]
                tagsInput.value = '';

                mostrarTags();
            }

        }
        function mostrarTags() {
            tagsDiv.textContent = '';

            tags.forEach(tag => {
                const etiqueta = document.createElement('LI');
                etiqueta.classList.add('formulario__tag');
                etiqueta.textContent = tag;
                etiqueta.ondblclick = eliminarTag;
                tagsDiv.appendChild(etiqueta);
            })
            actualizarInputHidden();
        }

        function eliminarTag(e) {
            e.target.remove();
            tags = tags.filter(tag => tag !== e.target.textContent);
            actualizarInputHidden();
        }

        function actualizarInputHidden() {
            tagsInputHidden.value = tags.toString();
        }
    }

})()

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
        obj.data.opc = obj.data.new ? 'new' : 'get';
        obj.sendData();
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

            } else {

            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
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

    obj.getCliente = () => {
        $http({
            method: 'POST',
            url: url,
            data: { cliente: { opc: "perfil", id: obj.id } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.cliente = res.data.data;
                console.log(obj.cliente);
            } else {

            }
            if (obj.cliente.cupon_nombre != null && obj.cliente.cupon_nombre != '') {
                obj.cliente.miscupones = obj.cliente.cupon_nombre.split(",");
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.cuponDelete = () => {
        if (confirm("¿Seguro de borrar los cupones del usuario?")) {
            $http({
                method: 'POST',
                url: url,
                data: { cliente: { opc: "cuponDelete", id: obj.id } }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    console.log(obj.id);
                    toastr.success("cupones eliminados");
                    location.reload();
                } else {

                }

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }

    obj.cuponGuardar = () => {
        const tagsInputHidden = document.querySelector('[name="tags"]');
        const tagsData = tagsInputHidden.value;
        if (tagsData != '') {
            if (confirm("¿Guardar cupones para este usuario?")) {
                $http({
                    method: 'POST',
                    url: url,
                    data: { cliente: { opc: "cuponGuardar", id: obj.id, tagdata: tagsData } }
                }).then(function successCallback(res) {
                    if (res.data.Bandera == 1) {
                        console.log(obj.id);
                        toastr.success("cupones guardados");
                        location.reload();
                    } else {
                    }
                }, function errorCallback(res) {
                    toastr.error("Error: no se realizo la conexion con el servidor");
                });
            }
        }
    }

    obj.cuponGuardarAll = () => {
        const tagsInputHidden = document.querySelector('[name="tags"]');
        const tagsData = tagsInputHidden.value;
        if (tagsData != '') {
            if (confirm("Esto Guardara los cupones en TODOS LOS USUARIOS, ¿Seguro deseas guardar cambios?")) {
                $http({
                    method: 'POST',
                    url: url,
                    data: { cliente: { opc: "cuponGuardarAll", id: obj.id, tagdata: tagsData } }
                }).then(function successCallback(res) {
                    if (res.data.Bandera == 1) {
                        console.log(obj.id);
                        toastr.success("cupones guardados");
                        location.reload();
                    } else {

                    }

                }, function errorCallback(res) {
                    toastr.error("Error: no se realizo la conexion con el servidor");
                });
            }
        }
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