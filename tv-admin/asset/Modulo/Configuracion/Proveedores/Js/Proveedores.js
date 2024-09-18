'use strict'

var url = "./Modulo/Configuracion/Proveedores/Ajax/Proveedores.php";

tsuruVolks
    .controller('ProveedoresCrtl', ["$scope", "$http", ProveedoresCrtl]);

function ProveedoresCrtl($scope, $http) {
    var obj = $scope;
    obj.Proveedor = {};
    obj.dataProveedor = [];
    obj.nuevo = true;
    obj.btnsave = false;
    obj.historico = false;
    obj.img = "Images/boxed-bg.jpg";

    obj.btnNuevo = () => {
        obj.nuevo = true;
        obj.Proveedor = {};
        obj.Proveedor.opc = "new";
        obj.img = "Images/boxed-bg.jpg";
        document.getElementById("txtfile").value = "";
        $("#mProveedor").modal("show");

    }
    obj.getImagen = (foto, id) => {
        return foto ? obj.dominio + "/images/Marcasrefacciones/" + id + ".png" : "Images/boxed-bg.jpg";
    }

    obj.btnEditar = (marca) => {
        obj.nuevo = false;
        obj.Proveedor = marca;
        obj.Proveedor.opc = "edit"
        obj.img = obj.Proveedor.foto ? obj.dominio + "/images/Marcasrefacciones/" + obj.Proveedor._id + ".png" : "Images/boxed-bg.jpg";

        $("#mProveedor").modal("show");
    }

    obj.btnEditarProveedor = () => {
        obj.btnsave = true;
        if (confirm("Estas seguro de guardar los cambios?")) {
            obj.nuevo = true;
            $http({
                method: 'POST',
                url: url,
                data: { Proveedor: obj.Proveedor },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.Proveedor) {
                        formData.append(m, data.Proveedor[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    $("#mProveedor").modal("hide");
                    obj.getProvedores();
                    obj.btnsave = false;
                    toastr.success(res.data.mensaje);
                } else {
                    toastr.error(res.data.mensaje);
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }
    //*.macromautopoartes.com
    obj.getProvedores = () => {
        setTimeout(() => {
            $http({
                method: 'POST',
                url: url,
                data: { Proveedor: { opc: "buscar", historico: obj.historico ? 0 : 1 } },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.Proveedor) {
                        formData.append(m, data.Proveedor[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.dataProveedor = res.data.data;
                    obj.dominio = res.data.dominio;
                } else {
                    toastr.error(res.data.mensaje);
                }


            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }, 100);
    }

    obj.btnCrearProveedor = () => {
        obj.btnsave = true;
        $http({
            method: 'POST',
            url: url,
            data: { Proveedor: obj.Proveedor },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.Proveedor) {
                    formData.append(m, data.Proveedor[m]);
                }
                //formData.append("file",data.file);
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                toastr.success(res.data.mensaje);
                obj.getProvedores();
                obj.btnsave = false;
                $("#mProveedor").modal("hide");
            } else {
                toastr.error(res.data.mensaje);
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.opcDesactivar = (_id, estatus) => {
        mensaje = Number(estatus) == 1 ? "¿Estas seguro de Desactivar al proveedor?" : "¿Estas seguro de Activar al proveedor?";
        if (confirm(mensaje)) {
            var marca = {
                opc: estatus == 1 ? "disabled" : "enabled",
                _id: _id
            }
            $http({
                method: 'POST',
                url: url,
                data: { marca },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.marca) {
                        formData.append(m, data.marca[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.getProvedores();
                    toastr.success(res.data.mensaje);
                } else {
                    toastr.error(res.data.mensaje);
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });

        }
    }

    angular.element(document).ready(function () {
        var fileInput1 = document.getElementById('txtfile');
        fileInput1.addEventListener('change', function (e) {
            var file = fileInput1.files[0];
            var imageType = /image.*/;
            if (file) {
                if (file.size <= 512000) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        obj.img = reader.result;
                        obj.$apply();
                    }
                    reader.readAsDataURL(file);
                } else {
                    toastr.warning("Error la Imagen supera los 512 KB");
                    return;
                }
            } else {
                return
            }
        });
        obj.getProvedores();
    });
}
