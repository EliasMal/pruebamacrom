const url_catalogo = "./modulo/Catalogo/Ajax/Catalogo.php";
var url_session = "./modulo/home/Ajax/session.php";
const url_seicom = "https://volks.dyndns.info:444/service.asmx/consulta_art";

tsuruVolks
    .controller('catalogosCtrl', ["$scope", "$http", catalogosCtrl])
    .controller("catalogosDetallesCtrl", ["$scope", "$http", catalogosDetallesCtrl])
    .filter('startFromGrid', function () {
        return function (input, start) {
            start = +start;
            return input.slice(start);
        }
    });

function catalogosCtrl($scope, $http) {
    var obj = $scope;
    obj.refaccion = {
        opc: "Buscar",
        tipo: "",
        categoria: "",
        marca: "",
        vehiculo: "",
        anio: "",
        producto: "",
        x: 0,
        y: 0
    }
    obj.categorias = [];
    obj.Marcas = [];
    obj.Vehiculos = [];
    obj.Modelos = [];
    obj.Refacciones = [];
    obj.catalogos = [];
    /*variables del paginador*/
    obj.currentPage = 0;
    obj.pages = [];
    obj.pageSize = 20;
    obj.Trefacciones = 0;

    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            obj.getSeicom(e.Clave).then(token => {
                e.agotado = token
            })
        })
    }

    obj.getSeicom = async (clave) => {
        try {
            const result = await $http({
                method: 'GET',
                url: url_seicom,
                params: { articulo: clave },
                headers: { 'Content-Type': "application/x-www-form-urlencoded" },
                transformResponse: function (data) {
                    return $.parseXML(data);
                }

            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error(res);
            });
            if (result) {
                const xml = $(result.data).find("string");
                let json = JSON.parse(xml.text());
                return json.Table.map(e => e.existencia).reduce((a, b) => a + b, 0) == 0 ? true : false;
            }
        } catch (error) {
            toastr.error(error)
        }

    }

    obj.getCategorias = async () => {
        obj.refaccion.tipo = "Categorias";
        obj.refaccion.x = 0;
        obj.refaccion.y = obj.pageSize;
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });

            if (result) {
                if (result.data.Bandera == 1) {
                    obj.categorias = result.data.Data.Categorias;
                    obj.Marcas = result.data.Data.Marcas;

                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;

                    obj.currentPage = next_url - 1;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones);
                }
                $scope.$apply();
            }
            obj.currentPage = next_url - 1;
            obj.configPages();
            obj.getPaginador(obj.currentPage * obj.pageSize, obj.pageSize);
            
            if (window.location.href.includes("%20")) {
                obj.refaccion.producto = next_prod.replaceAll("%20", " ");
            } else {
                obj.refaccion.producto = next_prod;
            }
            if (obj.refaccion.producto.includes("%C3%B1")) {
                obj.refaccion.producto = obj.refaccion.producto.replaceAll("%C3%B1", "ñ");
            }
            if (obj.refaccion.producto.includes("%C3%BC")) {
                obj.refaccion.producto = obj.refaccion.producto.replaceAll("%C3%BC", "ü");
            }
            if (next_marca.includes("?%20string:")) {
                obj.refaccion.marca = "";
            } else {
                obj.refaccion.marca = next_marca;
            }

            if (next_marca != "") {
                obj.refaccion.vehiculo = next_vehi;
                obj.getVehiculos();
            }

        } catch (error) {
            toastr.error(error);
        }

    }

    obj.getVehiculos = async () => {
        obj.refaccion.tipo = "Vehiculos";
        obj.refaccion.x = 0;
        obj.refaccion.y = obj.pageSize;
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if (result) {
                if (result.data.Bandera == 1) {
                    obj.Vehiculos = result.data.Data.Vehiculos;

                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;

                    obj.currentPage = next_url - 1;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones);
                } else {
                    toastr.error(result.data.Mensaje);
                }
                $scope.$apply();
            }
            obj.currentPage = next_url - 1;
            obj.configPages();
            obj.getPaginador(obj.currentPage * obj.pageSize, obj.pageSize);

            if (next_vehi != "") {
                obj.refaccion.anio = next_mdl;
                obj.getModelos();
            }

        } catch (error) {
            toastr.error(error);
        }
    }

    obj.getModelos = async () => {
        obj.refaccion.tipo = "Modelos";
        obj.refaccion.x = 0;
        obj.refaccion.y = obj.pageSize;
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if (result) {
                if (result.data.Bandera == 1) {
                    obj.Modelos = result.data.Data.Modelos;
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    obj.currentPage = next_url - 1;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones)
                } else {
                    toastr.error(result.data.Mensaje);
                }
            }
            $scope.$apply();
            obj.currentPage = next_url - 1;
            obj.configPages();
            obj.getPaginador(obj.currentPage * obj.pageSize, obj.pageSize);

        } catch (error) {

        }
    }

    obj.getAnios = async () => {
        obj.refaccion.tipo = "Anios";
        obj.refaccion.x = 0;
        obj.refaccion.y = obj.pageSize;
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if (result) {
                if (result.data.Bandera == 1) {
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    obj.currentPage = next_url - 1;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones)
                } else {
                    toastr.error(result.data.Mensaje);
                }
            }
            $scope.$apply();
            obj.currentPage = next_url - 1;
            obj.configPages();
            obj.getPaginador(obj.currentPage * obj.pageSize, obj.pageSize);

        } catch (error) {
            toastr.error(error);
        }

    }

    obj.getRefaccion = async (x = 0, y = obj.pageSize) => {
        obj.refaccion.tipo = "Refaccion";
        obj.refaccion.x = 0;
        obj.refaccion.y = obj.pageSize;
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if (result) {
                if (result.data.Bandera == 1) {
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    obj.currentPage = next_url - 1;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones);
                    console.log(obj.Refacciones);
                    window.location.href = "?mod=catalogo&pag=" + 1 + "&prod=" + obj.refaccion.producto + "&cate=" + next_cate + "&armadora=" + obj.refaccion.marca + "&mdl=" + obj.refaccion.vehiculo + "&[a]=" + obj.refaccion.anio;

                } else {
                    toastr.error(result.data.Mensaje);
                }
            }

            $scope.$apply();
        } catch (error) {
            toastr.error(error);
        }

    }

    var catalogo_buscador = document.querySelector("#prod_input");
    catalogo_buscador.addEventListener("keydown", e => {
        if (catalogo_buscador.value != "" && e.keyCode === 13) {
            window.location.href = "?mod=catalogo&pag=" + 1 + "&prod=" + catalogo_buscador.value + "&cate=" + next_cate + "&armadora=" + obj.refaccion.marca + "&mdl=" + obj.refaccion.vehiculo + "&[a]=" + obj.refaccion.anio;
        } else if (catalogo_buscador.value == "" && e.keyCode === 13) {
            window.location.href = "?mod=catalogo&pag=1&prod=&cate=T&armadora=&mdl=&[a]=";
        }
    });

    obj.getPaginador = async (x = 0, y = obj.pageSize) => {
        obj.refaccion.tipo = "Paginacion";
        obj.refaccion.x = x;
        obj.refaccion.y = y;
        try {
            const result = await $http({
                method: 'GET',
                url: url_catalogo,
                params: obj.refaccion
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if (result) {
                if (result.data.Bandera) {
                    obj.Refacciones = result.data.Data.Refacciones;
                    obj.Trefacciones = result.data.Data.Trefacciones;
                    obj.configPages();
                    obj.eachRefacciones(obj.Refacciones)
                } else {
                    toastr.error(result.data.Mensaje);
                }
            }
            $scope.$apply();
        } catch (error) {
            toastr.error(error);
        }

    };

    const mylink = window.location.href.split("&");
    const next_url = mylink[1].split("=")[1];
    const next_prod = mylink[2].split("=")[1];
    const next_cate = mylink[3].split("=")[1];
    const next_marca = mylink[4].split("=")[1];
    const next_vehi = mylink[5].split("=")[1];
    const next_mdl = mylink[6].split("=")[1];

    obj.configPages = function () {
        obj.pages.length = 0;
        var ini = obj.currentPage - 4;
        var fin = obj.currentPage + 5;

        if (ini < 1) {
            ini = 1;
            if (Math.ceil(obj.Trefacciones / obj.pageSize) > 10)
                fin = 10;
            else
                fin = Math.ceil(obj.Trefacciones / obj.pageSize);
        } else {

            if (ini >= Math.ceil(obj.Trefacciones / obj.pageSize) - 10) {
                ini = Math.ceil(obj.Trefacciones / obj.pageSize) - 10;
                fin = Math.ceil(obj.Trefacciones / obj.pageSize);
            }
        }
        if (ini < 1) ini = 1;
        for (var i = ini; i <= fin; i++) {
            obj.pages.push({
                no: i
            });
        }
    };

    obj.setPage = function (index) {
        obj.currentPage = next_url - 1;
        obj.configPages();
        obj.getPaginador(obj.currentPage * obj.pageSize, obj.pageSize);
        window.location.href = "?mod=catalogo&pag=" + index + "&prod=" + obj.refaccion.producto + "&cate=" + next_cate + "&armadora=" + obj.refaccion.marca + "&mdl=" + obj.refaccion.vehiculo + "&[a]=" + obj.refaccion.anio;
    }

    obj.RefaccionDetalles = (_id) => {
        window.open("?mod=catalogo&opc=detalles&_id=" + _id, "_self");
    }

    obj.getBanners = (data) => {
        $http({
            method: 'POST',
            url: "./tv-admin/asset/Modulo/Secciones/webprincipal/Ajax/webprincipal.php",
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
                switch (res.data.categoria) {
                    case 'Catalogos':
                        obj.catalogos = res.data.Data;
                        //console.log(obj.catalogos);
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
        obj.getBanners({ opc: "get", Categoria: "Catalogos", Estatus: 1 });
        obj.getCategorias();
    });
}

function catalogosDetallesCtrl($scope, $http) {

    var obj = $scope;
    obj.session = $_SESSION;
    obj.btnEnabled = obj.session.autentificacion == undefined ? true : false;
    obj.Refaccion = {
        id: 0,
        opc: "OneRefaccion",
        datos: {},
        galeria: [],
        Existencias: 0,
        cantidad: 1,
        precio: 0
    };
    obj.RefaccionDetalles = (_id) => {
        window.open("?mod=catalogo&opc=detalles&_id=" + _id, "_self");
    }

    obj.Activa = false;
    obj.trunc = (x, posiciones = 0) => {
        var s = x.toString()
        var l = s.length
        var decimalLength = s.indexOf('.') + 1
        var numStr = decimalLength > 0 ? s.substr(0, decimalLength + posiciones) : s
        return Number(numStr)
    }

    obj.getSeicom = async (clave) => {
        try {
            const result = await $http({
                method: 'GET',
                url: url_seicom,
                params: { articulo: clave },
                headers: { 'Content-Type': "application/x-www-form-urlencoded" },
                transformResponse: function (data) {
                    return $.parseXML(data);
                }

            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error(res);
            });
            if (result) {
                const xml = $(result.data).find("string");
                let json = JSON.parse(xml.text());
                return json.Table.map(e => e.existencia).reduce((a, b) => a + b, 0) == 0 ? true : false;
            }
        } catch (error) {
            toastr.error(error)
        }

    }

    obj.getArticulovolks = () => {
        $http({
            method: 'POST',
            url: "https://volks.dyndns.info:444/service.asmx/consulta_art",
            data: "articulo=" + obj.Refaccion.datos.Clave,
            headers: {
                'Content-Type': "application/x-www-form-urlencoded"

            },
            transformResponse: function (data) {
                return $.parseXML(data);
            }
        }).then(function successCallback(res) {
            var xml = $(res.data);
            var json = xml.find("string");
            obj.existencias = JSON.parse(json.text());

            obj.existencias.Table.forEach(function (e) {
                obj.Refaccion.Existencias += parseInt(e.existencia);
                obj.Refaccion.precio = obj.trunc((e.precio_5 * 1.16), 2);
            })
            obj.Activa = obj.Refaccion.Existencias != 0 ? true : false;

        }, function errorCallback(res) {
            console.log("Error: no se realizo la conexion con el servidor");

        });
    }

    obj.btndisminuir = () => {
        obj.Refaccion.cantidad = obj.Refaccion.cantidad != 1 ? obj.Refaccion.cantidad - 1 : 1
    }

    obj.btnaumentar = () => {
        if (obj.Refaccion.cantidad < obj.Refaccion.Existencias) {
            obj.Refaccion.cantidad++
        }
    }

    obj.getRefaccion = () => {

        $http({
            method: 'GET',
            url: url_catalogo,
            params: { opc: "OneRefaccion", id: obj.Refaccion.id },
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.Refaccion.datos = res.data.Data.Refaccion;
                obj.Refaccion.galeria = res.data.Data.Galeria;
                obj.productos = res.data.Data.Productos;
                obj.Refaccion.compatibilidad = res.data.Data.Compatibilidad;
                obj.eachRefacciones(obj.productos);
                obj.getArticulovolks();
            }
            if (obj.Refaccion.galeria.length > 4) {
                document.querySelector(".detalles__visual--opciones").style.display = "grid";
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.Agregarcarrito = () => {
        $http({
            method: 'POST',
            url: url_session,
            data: { modelo: obj.Refaccion }

        }).then(function successCallback(res) {

            location.reload();
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });

    }

    obj.getImagen = (status, id) => {
        var url = "https://macromautopartes.com/images/refacciones/";
        //var url = "images/refacciones/";
        return status ? url + id + ".webp" : url + id + ".webp";
    }

    obj.getGaleria = (id) => {
        if (id != undefined) {
            url = "https://macromautopartes.com/images/galeria/" + id;
            //url = "images/galeria/" + id;
            return url;
        }
    }

    obj.btnDetallesRelacionados = (id) => {
        window.open("?mod=catalogo&opc=detalles&_id=" + id, "_self");
    }

    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            obj.getSeicom(e.Clave).then(token => {
                e.agotado = token
            })
        })

    }

    angular.element(document).ready(function () {
        obj.getRefaccion();
        setTimeout(() => {
            $('.slick2').slick({

                slidesToShow: 4,
                slidesToScroll: 4,
                infinite: true,
                dots: true,
                autoplay: true,
                autoplaySpeed: 5000,
                arrows: true,
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 4,
                            slidesToScroll: 4
                        }
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2,
                            arrows: false,
                            cssEase: 'linear'
                        }
                    }
                ]
            });
        }, 1000);
    });

}
