var url_session = "./modulo/home/Ajax/session.php";
var url = "./modulo/home/Ajax/home.php";
var urlLogin = "./modulo/Login/Ajax/Login.php";

tsuruVolks.controller('CabeceraCtrl', ["$scope", "$http", "$sce", "vcRecaptchaService", CabeceraCtrl])
    .controller('FooterCtrl', ["$scope", "$http", FooterCtrl])
    .directive('convertToString', function () {
        return {
            require: 'ngModel',
            link: function ($scope, element, attrs, ngModel) {
                ngModel.$parsers.push(function (value) {
                    return parseFloat(value);
                });
                ngModel.$formatters.push(function (value) {
                    return '' + value;
                });
            }
        };
    });

function CabeceraCtrl($scope, $http, $sce, vcRecaptchaService) {
    var obj = $scope;
    obj.session = $_SESSION;
    obj.user = (obj.session.autentificacion != undefined && obj.session.autentificacion == 1) ? true : false;
    obj.Numproducts = obj.session.CarritoPrueba ? Object.keys(obj.session.CarritoPrueba).length : 0;
    obj.login = {};
    obj.Costumer = {};
    obj.tipoPago = {
        value: "",
        estatus: false
    };
    obj.Banner = [];
    obj.Menu = {};
    obj.mod;
    obj.url = "";
    obj.msgContacto = false;
    obj.flagenvio = false;
    obj.cotizacion;
    obj.Data = {};
    obj.dataBanners;
    obj.dataCarrousel;
    
    toastr.options = {
        "progressBar": true,
        "closeButton": true
    };

    obj.getImagen = (id) => {
        var url = "images/refacciones/";
        return url + id + ".webp";
    };
    
    obj.getImagenCate = (e) => {
        return "images/Categorias/" + e._id + ".png";
    };

    obj.recapchatKey = "6Le-C64UAAAAAMlSQyH3lu6aXLIkzgewZlVRgEam";
    obj.Contacto = {};

    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            e.NewUrlName = e["_producto"].replaceAll(" ","-");
            e.NewUrlName = e.NewUrlName.replaceAll(",","");
            e.NewUrlName = e.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g,"");
            e.NewAltName = e["_producto"].replaceAll(",","");
        });
    };

    obj.subtotal = () => {
        obj.Costumer.Subtotal = 0;
        
        // OPTIMIZACIÓN: for...of y validación de seguridad
        if (obj.Data && obj.Data.Carrito) {
            for (const producto of obj.Data.Carrito) {
                if (producto.RefaccionOferta == '1') {
                    obj.Costumer.Subtotal += (producto.Cantidad * producto.Precio2);
                } else {
                    obj.Costumer.Subtotal += (producto.Cantidad * producto.Precio);
                }
            }
        }

        // OPTIMIZACIÓN: URL dinámica en lugar de hardcodeada
        setTimeout(function () {
            if(window.location.search.includes("?mod=Compras") && obj.Data.Carrito && obj.Data.Carrito.length == 0 && $_SESSION["CarritoPrueba"] && Object.keys($_SESSION["CarritoPrueba"]).length > 0){
                location.reload();
            }
        }, 400);
        
        return obj.Costumer.Subtotal;
    };

    obj.actualizarSession = (Refaccion, opc) => {
        /*opc? true = elimina la variable de la session, false= no aplica nada*/
        $http({
            method: 'POST',
            url: url_session,
            data: { modelo: Refaccion }
        }).then(function successCallback(res) {
            if (opc) {
                location.reload();
            }
        }, function errorCallback(res) {
            console.error(res);
            toastr.error("Error: no se realizó la conexión con el servidor");
        });
    };

    obj.btnEliminarRefaccion = (Refaccion) => {
        Swal.fire({
            title: "¿Deseas Eliminar la Refacción del carrito?",
            showCancelButton: true,
            confirmButtonText: "Eliminar"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    showConfirmButton: false,
                    title: "¡Eliminado!",
                    text: "El artículo fue eliminado",
                    icon: "success"
                });
                Refaccion.erase = 1;
                Refaccion.borrar = Refaccion.Clave;
                Refaccion.n = Object.keys($_SESSION["CarritoPrueba"]).length;
                obj.actualizarSession(Refaccion, true);
            }
        });
    };

    obj.textoBusqueda = "";

    obj.buscarConEnter = (evento) => {
        if (evento.keyCode === 13) {
            obj.general_search();
        }
    };

    obj.general_search = () => {
        const value = obj.textoBusqueda.trim();
        if (!value) return;
        
        const query = encodeURIComponent(value);
        window.location.href = `/?mod=catalogo&pag=1&prod=${query}`;
    };

    obj.getCategorias = async () => {
        try {
            const res = await $http({
                method: 'POST',
                url: url,
                data: { modelo: { opc: "buscar", tipo: "Categorias", home: true } }
            });

            if (res && res.data && res.data.Bandera == 1) {
                $scope.$evalAsync(() => {
                    obj.Data = res.data.Data;
                    if(obj.Data && obj.Data.Carrito) {
                        obj.eachRefacciones(obj.Data.Carrito);
                    }
                });
            }
        } catch (error) {
            toastr.error("Error: no se realizó la conexión con el servidor");
            console.error(error);
        }
    };

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
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                // OPTIMIZACIÓN: Evitar el switch repetitivo
                const categoria = res.data.categoria;
                if (['Principal', 'Catalogos', 'Compras', 'Nosotros'].includes(categoria)) {
                    obj.dataBanners = res.data.Data;
                } else if (categoria === 'Carrousel') {
                    obj.dataCarrousel = res.data.Data;
                }
            } else {
                toastr.error(res.data.mensaje);
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizó la conexión con el servidor");
        });
    };

    obj.RefaccionDetalles = (_id, newurl) => {
        window.open(`?mod=catalogo&opc=detalles&_id=${_id}-${newurl}`, "_self");
    };

    obj.btnLogout = () => {
        obj.login.opc = "out";

        $http({
            method: 'POST',
            url: urlLogin,
            data: { Login: obj.login }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                // OPTIMIZACIÓN: Rutas relativas y dinámicas
                if (window.location.search.includes("?mod=Compras") || window.location.search.includes("?mod=Profile")) {
                    location.href = "?mod=home";
                } else {
                    location.reload();
                }

                // OPTIMIZACIÓN: Respetar la preferencia del modo oscuro
                const modoOscuroGuardado = localStorage.getItem('darkmode');
                localStorage.clear();
                if (modoOscuroGuardado) {
                    localStorage.setItem('darkmode', modoOscuroGuardado);
                }
            } else {
                toastr.error(res.data.mensaje);
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizó la conexión con el servidor");
        });
    };

    obj.btnPerfil = () => {
        location.href = "?mod=Profile&opc=Direcciones";
    };

    obj.enviarContacto = () => {
        if (vcRecaptchaService.getResponse() === "") {
            alert("Verifica que eres humano");
        } else {
            obj.Contacto.recapRespond = vcRecaptchaService.getResponse();

            $http({
                method: 'POST',
                url: "./modulo/Contacto/Ajax/Contacto.php",
                data: obj.Contacto
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.msgContacto = true;
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizó la conexión con el servidor");
            });
        }
    };

    angular.element(document).ready(function () {
        obj.getCategorias();
        
        // OPTIMIZACIÓN: Manejo de URLs dinámicas para evitar hardcodeo de dominio
        const urlParams = new URLSearchParams(window.location.search);
        const moduloActual = urlParams.get('mod');

        if (!moduloActual || moduloActual === 'home') {
            obj.getBanners({ opc: "get", Categoria: "Principal", Estatus: 1 });
            obj.getBanners({ opc: "get", Categoria: "Carrousel", Estatus: 1 });
        } else if (moduloActual === 'catalogo') {
            obj.getBanners({ opc: "get", Categoria: "Catalogos", Estatus: 1 });
        } else if (moduloActual === 'ProcesoCompra') { 
            obj.getBanners({ opc: "get", Categoria: "Compras", Estatus: 1 });
        } else if (moduloActual === 'nosotros') {
            obj.getBanners({ opc: "get", Categoria: "Nosotros", Estatus: 1 });
        }
    });
}

function FooterCtrl($scope, $http) {
    var obj = $scope;
    obj.categorias = [];

    obj.getCategorias = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Categorias" } },
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
                obj.categorias = res.data.Data.Categorias;
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizó la conexión con el servidor");
        });
    };

    angular.element(document).ready(function () {
        obj.getCategorias();
    });
}