var url = "./Modulo/Configuracion/Modelos/Ajax/Modelos.php";

tsuruVolks
    .controller('ModelosCrtl', ["$scope", "$http", ModelosCrtl])
    ;

function ModelosCrtl($scope, $http) {
    var obj = $scope
    obj.noModelos = 0
    obj.modelo = {};
    obj.modeloanio = {};
    obj.modeloanios;
    obj.Modelos;
    obj.Marcas;
    obj.nuevo = true;
    obj.img = "Images/boxed-bg.jpg";
    obj._idModelotemp;
    obj.dominio
    obj.paginador = { page: 0, limit: 10 }
    obj.txtfind = "";


    obj.btnAumentar = () => {
        obj.paginador.page += obj.paginador.limit
        obj.getModelos(obj.paginador.page, obj.paginador.limit);
    }

    obj.btnDisminuir = () => {
        obj.paginador.page -= obj.paginador.limit
        if (obj.paginador.page < 0) {
            obj.paginador.page = 0
        }
        obj.getModelos(obj.paginador.page, obj.paginador.limit);
    }

    obj.getModelos = (skip = 0, limit = 10) => {
        setTimeout(() => {
            $http({
                method: 'POST',
                url: url,
                data: { modelo: { opc: "buscar", find: obj.txtfind, skip: skip, limit: limit, historico: obj.historico ? 0 : 1, tipo: "Modelos" } },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.modelo) {
                        formData.append(m, data.modelo[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.noModelos = res.data.Data.NoModelos;
                    obj.Modelos = res.data.Data.Modelos;
                    obj.dominio = res.data.dominio;
                }


            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }, 100);
    }

    obj.getMarcas = () => {
        $http({
            method: 'POST',
            url: url,
            data: { marca: { opc: "buscar", historico: obj.historico ? 0 : 1, tipo: "Marcas" } },
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
                obj.Marcas = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getAnios = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Anios", _idModelo: obj._idModelotemp } },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) {
                    formData.append(m, data.modelo[m]);
                }
                //formData.append("file",data.file);
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.modeloanios = res.data.data;
                $("#mAnios").modal("show");

            } else {
                toastr.error(res.data.mensaje);
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnNuevoModelo = () => {
        obj.nuevo = true;
        $("#mModelos").modal("show");
        obj.modelo = {};
        obj.modelo.opc = "new";
        obj.img = "Images/boxed-bg.jpg";
    }

    obj.btnCrearModelo = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: obj.modelo },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) {
                    formData.append(m, data.modelo[m]);
                }
                //formData.append("file",data.file);
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                toastr.success(res.data.mensaje);
                obj.getModelos();
                $("#mModelos").modal("hide");
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnEditar = (modelo) => {
        obj.nuevo = false;
        obj.modelo = angular.copy(modelo);
        obj.modelo.opc = "edit";
        console.log(obj.modelo);
        obj.img = modelo.foto ? obj.dominio + "/images/Marcas/" + modelo._idMarca + ".png" : "Images/boxed-bg.jpg";
        $("#mModelos").modal("show");
    }

    obj.btnEditarModelo = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: obj.modelo },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) {
                    formData.append(m, data.modelo[m]);
                }
                //formData.append("file",data.file);
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                toastr.success(res.data.mensaje);
                obj.getModelos();
                $("#mModelos").modal("hide");
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.opcDesactivar = (_id) => {
        if (confirm("Estas seguro de Desactivar el modelo?")) {

            $http({
                method: 'POST',
                url: url,
                data: { modelo: { opc: "disabled", _id: _id } },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.modelo) {
                        formData.append(m, data.modelo[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.getModelos();
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
        if (confirm("Estas seguro de Activar el modelo?")) {

            $http({
                method: 'POST',
                url: url,
                data: { modelo: { opc: "enabled", _id: _id } },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.modelo) {
                        formData.append(m, data.modelo[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.getModelos();
                    toastr.success(res.data.mensaje);
                } else {
                    toastr.error(res.data.mensaje);
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }

    obj.btnAnios = (vehiculo) => {
        obj.modelo = angular.copy(vehiculo);

        obj._idModelotemp = vehiculo._id;
        obj.getAnios();
    }

    obj.opcEliminar = (modelo) => {
        if (confirm("¿Estas seguro de eliminar el modelo?")) {
            obj.modeloanio = modelo;
            obj.modeloanio.opc = "deleteanio";
            obj.sendAnios();
        }
    }

    obj.sendAnios = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: obj.modeloanio },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) {
                    formData.append(m, data.modelo[m]);
                }

                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.getAnios(obj._idModelotemp);
                toastr.success(res.data.mensaje);
            } else {
                toastr.error(res.data.mensaje);
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnAgregarAnio = () => {
        obj.modeloanio.Anio = prompt("Modelo del vehiculo:");

        if (obj.modeloanio.Anio.length != 0) {
            obj.modeloanio._idModelo = obj._idModelotemp;
            obj.modeloanio.opc = "newanios";
            obj.sendAnios();
        }
    }

    obj.btnEditarAnio = (anio) => {
        let modelo = prompt("Modelo del vehiculo", anio.Anio);
        if (modelo.length != 0) {
            if (confirm("Estas seguro de editar el modelo")) {
                obj.modeloanio = anio;
                obj.modeloanio.Anio = modelo
                obj.modeloanio.opc = "editanio";
                obj.sendAnios();
            }
        }
    }

    obj.getImagenOne = () => {
        let id = obj.Marcas.find(marcas => marcas._id === obj.modelo._idMarca);
        obj.img = id.foto ? obj.dominio + "/images/Marcas/" + id._id + ".png" : "Images/boxed-bg.jpg";
    }

    obj.getImagen = (objeto) => {
        return objeto.foto ? obj.dominio + "/images/Marcas/" + objeto._idMarca + ".png" : "Images/boxed-bg.jpg";
    }
    angular.element(document).ready(function () {
        $(".numeric").numeric();
        //        var fileInput1 = document.getElementById('txtfile');
        //        fileInput1.addEventListener('change', function(e) {
        //            var file = fileInput1.files[0];
        //            var imageType = /image.*/;
        //            if (file) {
        //                if (file.size <= 512000) {
        //                    var reader = new FileReader();
        //                    reader.onload = function(e) {
        //                        obj.img = reader.result;
        //                        obj.$apply();
        //                    }
        //                    reader.readAsDataURL(file);
        //                } else {
        //                    toastr.warning("Error la Imagen supera los 512 KB");
        //                    return;
        //                }
        //            } else {
        //                return
        //            }
        //        });
        obj.getModelos(obj.paginador.page, obj.paginador.limit);
        obj.getMarcas();

    });
}

