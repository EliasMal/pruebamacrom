var url = "./Modulo/Configuracion/Marcas/Ajax/Marcas.php";

tsuruVolks
    .controller('MarcasCrtl', ["$scope", "$http", MarcasCrtl]);

function MarcasCrtl($scope, $http) {
    var obj = $scope
    obj.noMarcas = 0;
    obj.marca = {};
    obj.nuevo = true;
    obj.btnsave = false;
    obj.img = "Images/boxed-bg.jpg";
    obj.paginador = { page: 0, limit: 10 }
    obj.txtfind = "";

    obj.btnAumentar = () => {
        obj.paginador.page += obj.paginador.limit
        obj.getMarcas(obj.paginador.page, obj.paginador.limit);
    }

    obj.btnDisminuir = () => {
        obj.paginador.page -= obj.paginador.limit
        if (obj.paginador.page < 0) {
            obj.paginador.page = 0
        }
        obj.getMarcas(obj.paginador.page, obj.paginador.limit);
    }

    obj.getMarcas = (skip = 0, limit = 10) => {
        setTimeout(() => {
            $http({
                method: 'POST',
                url: url,
                data: { marca: { opc: "buscar", find: obj.txtfind, historico: obj.historico ? 0 : 1, skip: skip, limit: limit } },
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
                    obj.noMarcas = res.data.Data.noRegistros;
                    obj.marcas = res.data.Data.Registros;
                    obj.dominio = res.data.dominio;
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }, 100);
    };

    obj.btnNuevaMarca = () => {
        obj.nuevo = true;
        $("#mMarcas").modal("show");
        obj.marca = {};
        obj.marca.opc = "new";
        obj.img = "Images/boxed-bg.jpg";
        document.getElementById("txtfile").value = "";
    }

    obj.btnCrearMarca = () => {
        obj.btnsave = true;
        $http({
            method: 'POST',
            url: url,
            data: { marca: obj.marca },
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
                $("#mMarcas").modal("hide");
                obj.getMarcas();
                obj.btnsave = false;
                toastr.success(res.data.mensaje);
            } else {
                toastr.error("Error: No se creo la categoria");
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnEditar = (marca) => {
        obj.nuevo = false;
        obj.marca = marca;
        obj.marca.opc = "edit"
        obj.img = obj.marca.foto ? obj.dominio + "/images/Marcas/" + obj.marca._id + ".png" : "Images/boxed-bg.jpg";

        $("#mMarcas").modal("show");
    }

    obj.btnEditarMarca = () => {

        if (confirm("Estas seguro de guardar los cambios?")) {
            obj.btnsave = true;
            $http({
                method: 'POST',
                url: url,
                data: { marca: obj.marca },
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

                    $("#mMarcas").modal("hide");
                    obj.getMarcas();
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

    obj.getImagen = (foto, id) => {
        return foto ? obj.dominio + "/images/Marcas/" + id + ".png" : "Images/boxed-bg.jpg";
    }

    obj.opcDesactivar = (_id) => {
        if (confirm("Estas seguro de guardar los cambios?")) {

            $http({
                method: 'POST',
                url: url,
                data: { marca: { opc: "disabled", _id: _id } },
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
                    obj.getMarcas();
                    toastr.success(res.data.mensaje);
                } else {
                    toastr.error(res.data.mensaje);
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }

    obj.opcActivar = (_id) => {
        if (confirm("Estas seguro de guardar los cambios?")) {

            $http({
                method: 'POST',
                url: url,
                data: { marca: { opc: "enabled", _id: _id } },
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
                    obj.getMarcas();
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
        obj.getMarcas(obj.paginador.page, obj.paginador.limit);
    });

}

