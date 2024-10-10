var url = "./Modulo/Secciones/webprincipal/Ajax/webprincipal.php";

tsuruVolks
    .controller('WebCtrl', ["$scope", "$http", WebCtrl]);

function WebCtrl($scope, $http) {
    var obj = $scope;
    obj.imagen = {
        placeholder: "Agrega una imagen",
        Categoria: "",
        Estatus: 0,
        opc: "set",
        Disenio: ""
    };
    obj.databannerPrincipal = [];
    obj.promociones = [];
    obj.catalogos = [];
    obj.compras = [];
    obj.nosotros = [];
    obj.contacto = [];
    obj.session = [];
    obj.blog = [];
    obj.dominio = "";

    obj.btnsubirimagen = () => {
        if (obj.imagen.file != undefined) {
            obj.setImagenes(obj.imagen);
        } else {
            toastr.error("No has seleccionado una imagen para subir")
        }
    }


    obj.btnDesactivar = (id, categoria) => {
        if (confirm("Estas seguro de desactivar la imagen")) {
            $http({
                method: 'POST',
                url: url,
                data: { imagen: { opc: "off", _id: id, Categoria: categoria } },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.imagen) {
                        formData.append(m, data.imagen[m]);
                    }
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    toastr.success('Imagen eliminada correctamente');
                    obj.getImagenes({ opc: "get", Categoria: res.data.categoria, Estatus: 1 })
                    location.reload();
                } else {
                    toastr.error(res.data.mensaje);
                }

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });

        }
    }

    obj.changePred = (id, categoria) => {
        if (confirm("¿Deseas colocar esta imagen como la que se mostrara en la pagina?")) {
            $http({
                method: 'POST',
                url: url,
                data: { imagen: { opc: "act", _id: id, Categoria: categoria } },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.imagen) {
                        formData.append(m, data.imagen[m]);
                    }
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    toastr.success('Imagen reemplazada correctamente');
                    obj.getImagenes({ opc: "get", Categoria: res.data.categoria, Estatus: 1 })
                    location.reload();
                } else {
                    toastr.error(res.data.mensaje);
                    console.log(res);
                }

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
                console.log(res);
            });

        }
    }

    obj.setImagenes = (data) => {
        $http({
            method: 'POST',
            url: url,
            data: { imagen: data },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.imagen) {
                    formData.append(m, data.imagen[m]);
                }
                //formData.append("file",data.file);

                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                toastr.success('Imagen subida correctamente');
                obj.imagen = { placeholder: "Agregar una imagen", Categoria: "", Estatus: 1, opc: "set" };
                delete (obj.imagen.name);
                obj.getImagenes({ opc: "get", Categoria: res.data.categoria, Estatus: 1 })
                location.reload();
            } else {
                toastr.error(res.data.mensaje);
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }


    obj.getImagenes = (data) => {
        $http({
            method: 'POST',
            url: url,
            data: { imagen: data },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.imagen) {
                    formData.append(m, data.imagen[m]);
                }
                //formData.append("file",data.file);

                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.dominio = res.data.dominio;
                switch (res.data.categoria) {
                    case 'Principal':
                        obj.databannerPrincipal = res.data.Data;
                        obj.databannerPrincipal.disabled = res.data.Disabled;
                        console.log(obj.databannerPrincipal);
                        break;
                    case 'Promociones':
                        obj.promociones = res.data.Data;
                        obj.promociones.disabled = res.data.Disabled;
                        break;
                    case 'Catalogos':
                        obj.catalogos = res.data.Data;
                        obj.catalogos.disabled = res.data.Disabled;
                        break;
                    case 'Compras':
                        obj.compras = res.data.Data;
                        obj.compras.disabled = res.data.Disabled;
                        break;
                    case 'Nosotros':
                        obj.nosotros = res.data.Data;
                        obj.nosotros.disabled = res.data.Disabled;
                        break;
                    case 'Contacto':
                        obj.contacto = res.data.Data;
                        obj.contacto.disabled = res.data.Disabled;
                        break;
                    case 'Session':
                        obj.session = res.data.Data;
                        obj.session.disabled = res.data.Disabled;
                        break;
                    case 'Blog':
                        obj.blog = res.data.Data;
                        obj.blog.disabled = res.data.Disabled;
                        break;
                }

            } else {
                toastr.error(res.data.mensaje);
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }



    angular.element(document).ready(function () {

        $(".archivos").on("change", function (e) {
            var file = this.files[0];

            if (file) {
                if (file.size <= 1024000) {
                    obj.imagen.name = file.name;
                    obj.imagen.Categoria = this.id;
                    obj.$apply();

                } else {
                    toastr.warning("Error la Imagen supera los 1 MB");
                    return;
                }
            } else {
                return;
            }
        })
        obj.getImagenes({ opc: "get", Categoria: "Principal", Estatus: 1 });
        obj.getImagenes({ opc: "get", Categoria: "Promociones", Estatus: 1 });
        obj.getImagenes({ opc: "get", Categoria: "Catalogos", Estatus: 1 });
        obj.getImagenes({ opc: "get", Categoria: "Compras", Estatus: 1 });
        obj.getImagenes({ opc: "get", Categoria: "Nosotros", Estatus: 1 });
        obj.getImagenes({ opc: "get", Categoria: "Contacto", Estatus: 1 });
        obj.getImagenes({ opc: "get", Categoria: "Session", Estatus: 1 });
        obj.getImagenes({ opc: "get", Categoria: "Blog", Estatus: 1 });

    });
}