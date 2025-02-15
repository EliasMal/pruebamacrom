const url = "./Modulo/Control/Refacciones/Ajax/Refacciones.php";
const urlGaleria = "./Modulo/Control/Refacciones/Ajax/Galeria.php";
const urlVehiculos = "./Modulo/Control/Refacciones/Ajax/Vehiculos.php";
const url_seicom = "https://volks.dyndns.info:444/service.asmx/consulta_art";

tsuruVolks
    .controller('RefaccionesCtrl', ["$scope", "$http", RefaccionesCtrl])
    .controller('RefaccionesNewCtrl', ["$scope", "$http", RefaccionesNewCtrl])
    .controller('RefaccionesEditCtrl', ["$scope", "$http", RefaccionesEditCtrl])
    .filter('startFromGrid', function () {
        return function (input, start) {
            start = +start;
            return input.slice(start);
        }
    });

function RefaccionesCtrl($scope, $http) {
    var obj = $scope;
    obj.buscar = "";
    obj.refacciones = [];
    obj.Numreg;
    obj.currentPage = 0;
    obj.historico = false;
    obj.publicados = true;
    obj.pageSize = 20;
    obj.pages = [];
    obj.propertyName = 'Producto';
    obj.ordentype = "asc";

    if (localStorage.getItem("Datos")) {
        localStorage.removeItem("Datos");
    }

    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            obj.getSeicom(e.Clave).then(token => {
                e.agotado = token
            })
        })
    }

    obj.sortby = (linkInfo) => {
        window.location.href = "?mod=Refacciones&" + linkInfo;
    }
    obj.DefaultRefacciones = () => {
        window.location = "?mod=Refacciones";
    }
    if (window.location.href.includes("&")) {
        const mylink = window.location.href.split("&");
        const orden = mylink[1];
        const mybutton = document.getElementsByClassName(orden)[0];
        if (orden == "pub") {
            obj.publicados = false;
        } else {
            obj.propertyName = orden;
            obj.ordentype = "desc";
            mybutton.className += " filtro__activo";
        }
    } else {
        const mybutton = document.getElementsByClassName("productoAZ")[0];
        mybutton.className += " filtro__activo";
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

    obj.btnAgregarRefaccion = () => {
        window.location.href = "?mod=Refacciones&opc=new";
    }

    obj.btnEditarRefaccion = (_id) => {
        window.location.href = "?mod=Refacciones&opc=edit&id=" + _id;
    }

    obj.clickRefaccionUnica = () => {
        window.location.href = "?mod=Refacciones&opc=edit&id=" + obj.OneRefaccion._id;
    }

    obj.getRefacciones = ($skip = 0, $limit = obj.pageSize) => {
        var valInp = document.getElementById("txtbuscar");
        valInp = parseInt(valInp.value);
        const regex = /^[0-9]*$/;
        if (regex.test(valInp)) {
            $http({
                method: 'POST',
                url: url,
                data: { modelo: { opc: "buscar", tipo: "Refaccion", id: valInp } },
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
                    obj.OneRefaccion = res.data.data.ClaveUnica;
                    if (obj.OneRefaccion == null) {
                        obj.SinRefaccion = valInp;
                    } else {
                        obj.SinRefaccion = null;
                    }
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        } else {
            obj.OneRefaccion = "";
        }

        setTimeout(() => {
            $http({
                method: 'POST',
                url: url,
                data: { modelo: { opc: "buscar", tipo: "Refacciones", buscar: obj.buscar, publicados: obj.publicados, historico: obj.historico, skip: $skip, limit: $limit, orden: obj.propertyName, ordentype: obj.ordentype } },
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
                    obj.dominio = res.data.dominio;
                    obj.refacciones = res.data.data.refacciones;
                    obj.Numreg = res.data.data.totalrefacciones;
                    obj.configPages();
                    obj.eachRefacciones(obj.refacciones);
                }

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }, 100);
    }

    obj.configPages = function () {
        obj.pages.length = 0;
        var ini = obj.currentPage - 4;
        var fin = obj.currentPage + 5;

        if (ini < 1) {
            ini = 1;
            if (Math.ceil(obj.Numreg / obj.pageSize) > 10)
                fin = 10;
            else
                fin = Math.ceil(obj.Numreg / obj.pageSize);
        } else {

            if (ini >= Math.ceil(obj.Numreg / obj.pageSize) - 10) {
                ini = Math.ceil(obj.Numreg / obj.pageSize) - 10;
                fin = Math.ceil(obj.Numreg / obj.pageSize);
            }
        }
        if (ini < 1) ini = 1;
        for (var i = ini; i <= fin; i++) {
            obj.pages.push({
                no: i
            });
        }
    };

    obj.nextPage = () => {
        obj.currentPage = obj.currentPage + 1;
        obj.getRefacciones(obj.currentPage * obj.pageSize, obj.pageSize)

    }

    obj.lastPage = () => {
        obj.currentPage = obj.currentPage - 1;
        obj.getRefacciones(obj.currentPage * obj.pageSize, obj.pageSize)
    }

    obj.setPage = function (index) {
        obj.currentPage = index - 1;

        obj.getRefacciones(obj.currentPage * obj.pageSize, obj.pageSize)

    };
    angular.element(document).ready(function () {
        obj.getRefacciones();

    });
}

function RefaccionesNewCtrl($scope, $http) {
    var obj = $scope;

    obj.img = "/images/refacciones/motor.webp";
    obj.refaccion = {};
    obj.refaccion.Color = "#FFFFFF";
    obj.backgroudimg = { "background-color": obj.refaccion.color }
    obj.categorias = [];
    obj.Marcas = [];
    obj.Vehiculos = [];
    obj.Modelos = [];
    obj.Proveedor = [];
    obj.refaccion.opc = "new";
    obj.refaccion.Estatus = true;
    obj.refaccion.Nuevo = false;
    obj.refaccion.Oferta = false;
    obj.refaccion.Alto = 0;
    obj.refaccion.Largo = 0;
    obj.refaccion.Ancho = 0;
    obj.refaccion.Peso = 0;
    obj.refaccion.Precio1 = 0.0;
    obj.refaccion.Precio2 = 0.0;
    obj.habilitado = false;

    obj.getColorMarca = () => {
        var id = obj.Marcas.find(marca => marca._id === obj.refaccion.Marca);
        obj.refaccion.Color = id.Color;
    }

    obj.getArticulovolks = () => {
        $http({
            method: 'POST',
            url: "https://volks.dyndns.info:444/service.asmx/consulta_art",
            data: "articulo=" + obj.refaccion.Clave,
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
                obj.exisTotales += parseInt(e.existencia);
            })


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }


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
                obj.categorias = res.data.data;
                obj.dominio = res.data.dominio;
                obj.img = obj.dominio + obj.img;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getMarcas = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Marcas" } },
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
                obj.Marcas = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getVehiculos = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Vehiculos", _idMarca: obj.refaccion.Marca } },
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
                obj.Vehiculos = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
        obj.getColorMarca();
    }

    obj.getModelos = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Modelos", _idVehiculo: obj.refaccion.Vehiculo } },
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
                obj.Modelos = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnGuardarRefaccion = () => {
        if (confirm("¿Estas seguro de guardar la Refaccion?")) {
            obj.refaccion.opc = "new";
            $http({
                method: 'POST',
                url: url,
                data: { modelo: obj.refaccion },
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
                obj.habilitado = true;
                toastr.success('Refacción Guardada');


            }, function errorCallback(res) {
                console.log("ERROR: ", res);
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }

    obj.btnNuevaRefaccion = () => {
        obj.refaccion = {};
        obj.refaccion.opc = "new";
        obj.refaccion.Color = "#FFFFFF";
        obj.refaccion.Estatus = true;
        obj.refaccion.Nuevo = false;
        obj.refaccion.Oferta = false;
        obj.refaccion.Alto = 0;
        obj.refaccion.Largo = 0;
        obj.refaccion.Ancho = 0;
        obj.refaccion.Peso = 0;
        obj.refaccion.Precio1 = 0.0;
        obj.refaccion.Precio2 = 0.0;
        obj.habilitado = false;
        obj.img = obj.dominio + "/images/refacciones/motor.wepb";
        document.getElementById("txtfile").value = "";
        $("#txtclave").focus();
    }

    obj.btnRegresar = () => {
        window.location.href = "?mod=Refacciones";
    }

    obj.getProveedores = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "proveedores" } },
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
                obj.Proveedor = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
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
        obj.getCategorias();
        obj.getProveedores();
        $(".numeric").numeric();
        $('.calendario').datepicker({
            format: 'yyyy-mm-dd',
            startDate: '-3d'
        });
    });
}

function RefaccionesEditCtrl($scope, $http) {
    var obj = $scope;
    obj.img = "";
    obj.imgGaleria = ""
    obj.refaccion = {};
    obj.refaccion.Color = "#FFFFFF";
    obj.habilitado = true;
    obj.existencias = [];
    obj.exisTotales = 0;
    obj.backgroudimg;
    obj.session;
    obj.Galeria = { placeholder: "Selecciona una imagen", name: "", opc: "" };
    obj.dataGaleria = []
    obj.vehiculo = {};
    obj.Rvehiculo = [];
    obj.arrayAnios = [];


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
            if (res.data.Bandera) {
                obj.categorias = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getMarcas = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Marcas" } },
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
                obj.Marcas = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getCompatibilidad = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Compatibilidad" } },
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
                obj.Compatibilidad = res.data.data;
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getActividad = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Actividad" } },
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
                obj.actividad = res.data.data;
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnBorrarRvehiculo = (RV = null) => {  //Prueba Eliminar Vehiculo de Compatibilidad
        if (confirm("¿Esta seguro de eliminar la compatibilidad?")) {
            $http({
                method: 'POST',
                url: url,
                data: { modelo: { opc: "buscar", tipo: "EliminarVehiculo", idcompatibilidad: RV.idcompatibilidad, clave: RV.clave, _id: RV.id_imagen, diferencias:'Elimino vehiculo de compatibilidades'} },
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
                toastr.success("Vehiculo eliminado");
                location.reload();

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }

    obj.getVehiculos = (id = null) => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Vehiculos", _idMarca: id } },
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
                obj.Vehiculos = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getModelos = (id = null) => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Modelos", _idVehiculo: id } },
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
                obj.Modelos = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    function trunc(x, posiciones = 0) { /*Funcion para truncar numeros decimales a solo 2 digitos despus del punto*/
        var s = x.toString()
        var l = s.length
        var decimalLength = s.indexOf('.') + 1
        var numStr = s.substr(0, decimalLength + posiciones)
        return Number(numStr)
    }

    obj.getArticulovolks = () => {
        $http({
            method: 'POST',
            url: "https://volks.dyndns.info:444/service.asmx/consulta_art",
            data: "articulo=" + obj.refaccion.Clave,
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
                obj.exisTotales += parseInt(e.existencia);

                obj.refaccion.Precio1 = parseFloat(e.precio_5 * 1.16);
                obj.refaccion.Precio1 = trunc(obj.refaccion.Precio1, 2);
            })


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getProveedores = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "proveedores" } },
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
        }).then(function successCallcalendarioback(res) {
            if (res.data.Bandera == 1) {
                obj.Proveedor = res.data.data;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getRefaccion = () => {
        $http({
            method: 'POST',
            url: url,
            data: { modelo: { opc: "buscar", tipo: "Refaccion", id: obj.refaccion.id } },
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
                obj.dominio = res.data.dominio;
                obj.refaccion = res.data.data.Refaccion;
                obj.categorias = res.data.data.Categorias;
                obj.actividad = res.data.data.Actividad;
                obj.Marcas = res.data.data.Marcas;
                obj.Compatibilidad = res.data.data.Compatibilidad;
                obj.Proveedor = res.data.data.Proveedores;
                obj.refaccion.opc = "edit";
                obj.backgroudimg = { "background-color": obj.refaccion.color }
                obj.img = obj.refaccion.imagen ? obj.dominio + '/images/refacciones/' + obj.refaccion._id + '.png' : obj.dominio + '/images/refacciones/' + obj.refaccion._id + '.webp';
                obj.getArticulovolks();
                localStorage.setItem("Datos", JSON.stringify(obj.refaccion));
                var datos = JSON.parse(localStorage.getItem('Datos'));
                obj.getGaleria();

                let actividadKeys = Object.keys(obj.actividad);
                for (var i = 0; i < actividadKeys.length; i++) {
                    obj.actividad[actividadKeys[i]].datosdiff = obj.actividad[actividadKeys[i]].datosdiff.replaceAll(/&quot;|{|}/g, "");
                    obj.actividad[actividadKeys[i]].datosdiff = obj.actividad[actividadKeys[i]].datosdiff.replaceAll(":", ": ");
                    setTimeout(function () {
                        let textoejemplo = document.querySelectorAll(".datosdiff_txt");
                        for (let i = 0; i < textoejemplo.length; i++) {
                            textoejemplo[i].innerHTML = "<b>" + textoejemplo[i].innerHTML;
                            textoejemplo[i].innerHTML = textoejemplo[i].innerHTML.replaceAll(",", "<br><b>");
                            textoejemplo[i].innerHTML = textoejemplo[i].innerHTML.replaceAll(":", "</b>: ");
                        }
                    }, 1);
                }
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnRegresar = () => {
        window.location.href = "?mod=Refacciones";
    }

    obj.btnEditarRefaccion = () => {
        obj.habilitado = false;
    }

    obj.btnSaveRefaccion = () => {
        obj.getColorMarca();

        if (confirm("¿Estas seguro de guardar los cambios?")) {
            obj.refaccion.Rvehiculo = JSON.stringify(obj.Rvehiculo);
            var datos = JSON.parse(localStorage.getItem('Datos'));
            let keys = Object.keys(obj.refaccion); let datoskeys = Object.keys(datos); let datosdiff = {};
            for (let i = 0; i < keys.length; i++) {
                if (keys[i] == datoskeys[i]) {
                    if (obj.refaccion[keys[i]] != datos[keys[i]]) {
                        datosdiff[keys[i]] = obj.refaccion[keys[i]];
                        if (keys[i] == 'Descripcion' || keys[i] == 'Producto') {
                            datosdiff[keys[i]] = obj.refaccion[keys[i]].replaceAll(",", "");
                        }
                    }
                }
            }
            obj.refaccion.diferencias = JSON.stringify(datosdiff);

            $http({
                method: 'POST',
                url: url,
                data: { modelo: obj.refaccion },
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
                obj.habilitado = true;
                toastr.success('Completado');
                location.reload();
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }

    }

    obj.getColorMarca = () => {
        var id = obj.Marcas.find(marca => marca._id === obj.refaccion._idMarca);
        obj.refaccion.color = id.Color;
    }


    /*Inicia seccioin de la galeria */
    obj.setImagenes = (Galeria) => {
        $http({
            method: 'POST',
            url: urlGaleria,
            data: { galeria: Galeria },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.galeria) {
                    formData.append(m, data.galeria[m]);
                }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                $("#Mcategoria").modal('hide');
                obj.getGaleria();

            } else {
                toastr.error(res.data.mensaje);
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getImagen = (e) => {
        return e.imagen ? obj.dominio + '/images/galeria/' + e._id + '.png' : obj.dominio + '/images/galeria/' + e._id + '.webp';
    }

    obj.btnEliminarImagen = (_id) => {
        if (confirm("¿Estas seguro de eliminar la imagen de la galeria?")) {
            obj.setImagenes({ opc: "erase", id: _id, id_refaccion: obj.refaccion._id });
        }
    }

    obj.getGaleria = () => {
        $http({
            method: 'POST',
            url: urlGaleria,
            data: { galeria: { opc: "get", id: obj.refaccion._id } },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.galeria) {
                    formData.append(m, data.galeria[m]);
                }
                return formData;
            }
        }).then(function successCallback(res) {

            if (res.data.Bandera == 1) {
                obj.dataGaleria = res.data.Data;

            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnNuevaCategoria = () => {
        obj.Galeria = { placeholder: "Selecciona una imagen", name: "", opc: "new", id_refaccion: obj.refaccion._id };
        obj.imgGaleria = obj.dominio + "/images/refacciones/motor.webp"
        $("#Mcategoria").modal('show');
    }

    obj.btnsubirimagen = () => {

        if (obj.Galeria.file != undefined) {
            obj.setImagenes(obj.Galeria);
        } else {
            toastr.error("No has seleccionado una imagen para subir")
        }
    }

    obj.btnNuevoVehiculo = () => {
        obj.vehiculo = {};
        $("#mdlVehiculo").modal('show');
    }

    obj.btnAddVehicle = () => {

        let $result = obj.Marcas.find(e => e._id === obj.vehiculo.id_Marca_RefaccionVehiculo)
        obj.vehiculo.Agencia = $result.Marca
        $result = obj.Vehiculos.find(e => e._id === obj.vehiculo.id_Modelo_RefaccionVehiculo)
        obj.vehiculo.Vehiculo = $result.Modelo;
        $result = obj.Modelos.find(e => e._id === obj.vehiculo.id_generacion_RefaccionVehiculo)
        obj.vehiculo.generacion = $result.Anio

        $http({
            method: 'POST',
            url: urlVehiculo,
            data: { Vehiculo: obj.vehiculo },
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.dataGaleria = res.data.Data;
                obj.Rvehiculo.push(obj.vehiculo)
                obj.vehiculo = {};
                console.log(obj.Rvehiculo);
                $("#mdlVehiculo").modal('hide');

            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });


    }

    obj.btnEditarRvehiculo = (data) => {
        obj.vehiculo = angular.copy(data);
        $("#mdlVehiculo").modal('show');
    }

    obj.btnEliminarRvehiculo = (data) => {

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

        $(".archivos").on("change", function (e) {

            var file = this.files[0];
            console.log(file)
            if (file) {
                if (file.size <= 1024000) {
                    var reader = new FileReader();
                    reader.onload = () => {
                        obj.Galeria.name = file.name;
                        obj.Galeria.Categoria = this.id;
                        obj.imgGaleria = reader.result;
                        obj.$apply();
                    }
                    reader.readAsDataURL(file);

                } else {
                    toastr.warning("Error la Imagen supera los 1 MB");
                    return;
                }
            } else {
                return;
            }
        })
        obj.getRefaccion();

        $(".numeric").numeric();
        $('.calendario').datepicker({
            format: 'yyyy-mm-dd',
            startDate: '-3d'
        });
        obj.session = JSON.parse(localStorage.getItem('session'))
        obj.isAdmin = obj.session.rol === "Admin" || obj.session.rol === "root" ? true : false;
    });
}
