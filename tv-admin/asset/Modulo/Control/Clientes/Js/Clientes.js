/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var url = "./Modulo/Control/Clientes/Ajax/Clientes.php";
tsuruVolks
    .controller('ClientesCtrl', ["$scope", "$http", ClientesCtrl])
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
    let tagsactivos = [];
    
    document.querySelector(".nav-item-help").addEventListener('click',function(){
        document.querySelector('.talk-bubble').classList.toggle("block");
        this.classList.toggle("white_c");
    });
    document.querySelector(".closeHelp").addEventListener('click',function(){
        document.querySelector('.talk-bubble').classList.remove("block");
        document.querySelector('.nav-item-help').classList.remove("white_c");
    });
    document.querySelector(".fa-angle-double-right").addEventListener('click',function(){
        this.style.display="none";
        document.querySelector(".fa-angle-double-left").style.display="flex";
        document.querySelector(".help1").style.display="none";
        document.querySelector(".help2").style.display="block";
    });

    document.querySelector(".fa-angle-double-left").addEventListener('click',function(){
        this.style.display="none";
        document.querySelector(".fa-angle-double-right").style.display="flex";
        document.querySelector(".help1").style.display="block";
        document.querySelector(".help2").style.display="none";
    });


    obj.getCliente = () => {

        $http({
            method: 'POST',
            url: url,
            data: { cliente: { opc: "perfil", id: obj.id } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.cliente = res.data.data;
                obj.cliente.count = res.data.count;
            }

            if (obj.cliente.cupon_nombre != null && obj.cliente.cupon_nombre != '') {
                obj.cliente.miscupones = obj.cliente.cupon_nombre.split(",");
                tagsactivos = obj.cliente.miscupones;
            }
            obj.cupones();
        }, function errorCallback(res) {
            console.log("Entra a sendData error: ", res);
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    setTimeout(function() {
        let cupones = document.querySelectorAll(".cupones__tag");
        cupones.forEach((element) => element.addEventListener('dblclick', eliminarCupon));
    }, 500);

    //funcion para eliminar un tag de los cupones del usuario
    function eliminarCupon(event) {
        if (confirm("¿Guardar cupones para este usuario?")) {
            event.preventDefault();
            const index = tagsactivos.indexOf(this.innerText);
            tagsactivos.splice(index, 1);
            this.remove();
            tagsactivos = tagsactivos.toString();

            $http({
                method: 'POST',
                url: url,
                data: { cliente: { opc: "cuponGuardar", id: obj.id, tagdata: tagsactivos } }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {

                    toastr.success("Cupon eliminado");
                    location.reload();
                }
                
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    };

    obj.cupones = () => {
        const tagsInput = document.querySelector("#tags_input");
        if (tagsInput) {

            const tagsDiv = document.querySelector("#tags");
            const tagsInputHidden = document.querySelector('[name="tags"]');

            let tags = [];
            if(tagsactivos != ""){
                tags=tagsactivos;   
            }
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
                    etiqueta.classList.add('enlace');
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
    }

    obj.cuponDelete = () => {
        if (confirm("¿Seguro de borrar los cupones del usuario?")) {
            $http({
                method: 'POST',
                url: url,
                data: { cliente: { opc: "cuponDelete", id: obj.id } }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    toastr.success("cupones eliminados");
                    location.reload();
                }

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }

    obj.cuponDeleteAll = () => {
        if (confirm("¿Seguro de borrar los cupones de TODOS los usuario?")) {
            $http({
                method: 'POST',
                url: url,
                data: { cliente: { opc: "cuponDeleteAll", id: obj.id } }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    toastr.success("cupones eliminados");
                    location.reload();
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
                        toastr.success("cupones guardados");
                        location.reload();
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
                        toastr.success("cupones guardados");
                        location.reload();
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