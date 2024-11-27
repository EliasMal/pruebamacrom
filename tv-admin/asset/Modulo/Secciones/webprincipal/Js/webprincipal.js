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

    if (localStorage.getItem("TabActive")) {
        var tabActive = document.getElementById(localStorage.getItem("TabActive"));
        var TabpaneActive = document.getElementById("Tab" + localStorage.getItem("TabActive"));
        TabpaneActive.classList.add("active");
        tabActive.classList.add("active");
        tabActive.classList.add("show");
    }

    obj.btnsubirimagen = () => {
        var info = document.querySelectorAll(".tab-pane");
        info.forEach(element => {
            if (element.classList.contains('active')) {
                obj.imagen["Categoria"] = element.id;
            }
        });
        if (obj.imagen.file != undefined) {
            if (obj.imagen.Disenio != "") {
                obj.setImagenes(obj.imagen);
            } else {
                toastr.error("No has seleccionado si es Escritorio o Movil");
            }
        } else {
            toastr.error("No has seleccionado una imagen para subir");
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
                }

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
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
                        if (obj.databannerPrincipal.Escritorio[0]) {
                            var foto = new Image();
                            foto.src = "https://macromautopartes.com/images/Banners/" + obj.databannerPrincipal.Escritorio[0].imagen;
                            obj.databannerPrincipal.Escritorio[0].width = foto.width;
                            obj.databannerPrincipal.Escritorio[0].height = foto.height;
                            obj.databannerPrincipal.Escritorio[0].formato = foto["src"].split(".");
                            obj.databannerPrincipal.Escritorio[0].formato = obj.databannerPrincipal.Escritorio[0].formato[2];
                        }
                        break;
                    case 'Promociones':
                        obj.promociones = res.data.Data;
                        obj.promociones.disabled = res.data.Disabled;
                        if (obj.promociones.Escritorio[0]) {
                            var foto = new Image();
                            foto.src = "https://macromautopartes.com/images/Banners/" + obj.promociones.Escritorio[0].imagen;
                            obj.promociones.Escritorio[0].width = foto.width;
                            obj.promociones.Escritorio[0].height = foto.height;
                            obj.promociones.Escritorio[0].formato = foto["src"].split(".");
                            obj.promociones.Escritorio[0].formato = obj.promociones.Escritorio[0].formato[2];
                        }
                        break;
                    case 'Catalogos':
                        obj.catalogos = res.data.Data;
                        obj.catalogos.disabled = res.data.Disabled;
                        if (obj.catalogos.Escritorio[0]) {
                            var foto = new Image();
                            foto.src = "https://macromautopartes.com/images/Banners/" + obj.catalogos.Escritorio[0].imagen;
                            obj.catalogos.Escritorio[0].width = foto.width;
                            obj.catalogos.Escritorio[0].height = foto.height;
                            obj.catalogos.Escritorio[0].formato = foto["src"].split(".");
                            obj.catalogos.Escritorio[0].formato = obj.catalogos.Escritorio[0].formato[2];
                        }

                        break;
                    case 'Compras':
                        obj.compras = res.data.Data;
                        obj.compras.disabled = res.data.Disabled;
                        if (obj.compras.Escritorio[0]) {
                            var foto = new Image();
                            foto.src = "https://macromautopartes.com/images/Banners/" + obj.compras.Escritorio[0].imagen;
                            obj.compras.Escritorio[0].width = foto.width;
                            obj.compras.Escritorio[0].height = foto.height;
                            obj.compras.Escritorio[0].formato = foto["src"].split(".");
                            obj.compras.Escritorio[0].formato = obj.compras.Escritorio[0].formato[2];
                        }

                        break;
                    case 'Nosotros':
                        obj.nosotros = res.data.Data;
                        obj.nosotros.disabled = res.data.Disabled;
                        if (obj.nosotros.Escritorio[0]) {
                            var foto = new Image();
                            foto.src = "https://macromautopartes.com/images/Banners/" + obj.nosotros.Escritorio[0].imagen;
                            obj.nosotros.Escritorio[0].width = foto.width;
                            obj.nosotros.Escritorio[0].height = foto.height;
                            obj.nosotros.Escritorio[0].formato = foto["src"].split(".");
                            obj.nosotros.Escritorio[0].formato = obj.nosotros.Escritorio[0].formato[2];
                        }

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
    });
}