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
    /*variables del paginador*/
    obj.currentPage = 0;
    obj.pages = [];
    obj.pageSize = 20;
    obj.Trefacciones = 0;

    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            e.NewUrlName = e["Producto"].replaceAll(" ","-");
            e.NewUrlName = e.NewUrlName.replaceAll(",","");
            e.NewUrlName = e.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g,"");
            e.NewAltName = e["Producto"].replaceAll(",","");
            if(e.stock == 0){
                e.agotado = true;
            }
        })
    }

    obj.getCategorias = async () => {
        obj.refaccion.tipo = "Categorias";
        obj.refaccion.x = 0;
        obj.refaccion.y = obj.pageSize;
        if (window.location.href.includes("%20")) {
            obj.refaccion.producto = next_prod.replaceAll("%20", " ");
        } else {
            obj.refaccion.producto = next_prod;
        }
        if (window.location.href.includes("%2520")) {
            obj.refaccion.producto = next_prod.replaceAll("%2520", " ");
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
        
        if(obj.refaccion.producto != "" || obj.refaccion.marca != ""){
            obj.refaccion.orden = "Producto";
            obj.refaccion.tipodeorden = "ASC";
        }else{
            obj.refaccion.orden = "dateCreated";
            obj.refaccion.tipodeorden = "DESC";
        }
        $http({
            method: 'GET',
            url: url_catalogo,
            params: obj.refaccion
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.categorias = res.data.Data.Categorias;
                obj.Marcas = res.data.Data.Marcas;

                obj.Refacciones = res.data.Data.Refacciones;
                obj.Trefacciones = res.data.Data.Trefacciones;

                obj.currentPage = next_url - 1;
                obj.configPages();
                obj.eachRefacciones(obj.Refacciones);
            }

            obj.currentPage = next_url - 1;
            obj.configPages();
            obj.getPaginador(obj.currentPage * obj.pageSize, obj.pageSize);

            if (next_marca != "") {
                obj.refaccion.vehiculo = next_vehi;
                obj.getVehiculos();
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });

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
                    obj.eachRefacciones(obj.Refacciones);
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

    angular.element(document).ready(function () {
        obj.getCategorias();
        document.querySelector('title').textContent = "Refacciones | MacromAutopartes";
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
    
    obj.btndisminuir = () => {
        obj.Refaccion.cantidad = obj.Refaccion.cantidad != 1 ? obj.Refaccion.cantidad - 1 : 1
    }

    obj.btnaumentar = () => {
        if (obj.Refaccion.cantidad < obj.Refaccion.datos.stock) {
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
                obj.Refaccion.datos.NewAltName = obj.Refaccion.datos.Producto.replaceAll(",","");
                newPageTitle = obj.Refaccion.datos.NewAltName;
                obj.Refaccion.datos.NewUrlName = obj.Refaccion.datos["Producto"].replaceAll(" ","-");
                obj.Refaccion.datos.NewUrlName = obj.Refaccion.datos.NewUrlName.replaceAll(",","");
                document.querySelector('title').textContent = newPageTitle;
                obj.Refaccion.datos.NewUrlName = obj.Refaccion.datos.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g,"");
                if(window.location.href.includes(obj.Refaccion.datos.NewUrlName)){
                }else{
                    window.location.href = window.location.href+"-"+obj.Refaccion.datos.NewUrlName;
                }
                obj.Activa = obj.Refaccion.datos.stock != 0 ? true : false;
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
            e.NewUrlName = e["Producto"].replaceAll(" ","-");
            e.NewUrlName = e.NewUrlName.replaceAll(",","");
            e.NewUrlName = e.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g,"");
            e.NewAltName = e["Producto"].replaceAll(",","");
            if(e.stock == 0){
                e.agotado = true;
            }
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
