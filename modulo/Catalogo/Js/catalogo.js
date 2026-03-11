const url_catalogo = "./modulo/Catalogo/Ajax/Catalogo.php";
var url_session = "./modulo/home/Ajax/session.php";

tsuruVolks
    .controller('catalogosCtrl', ["$scope", "$http", catalogosCtrl])
    .controller("catalogosDetallesCtrl", ["$scope", "$http" ,"$rootScope", catalogosDetallesCtrl])
    .filter('startFromGrid', function () {
        return function (input, start) {
            start = +start;
            return input.slice(start);
        }
    });

    const getParams = () => {
        const params = {};
        window.location.search.replace("?", "").split("&").forEach(p => {
            if (!p) return;
            const [key, val] = p.split("=");
            params[key] = decodeURIComponent(val || "");
        });
        return params;
    };
    
    const URL_PARAMS = getParams();

function catalogosCtrl($scope, $http) {
    var obj = $scope;
    let next_url    = parseInt(URL_PARAMS.pag || 1);
    let next_prod   = decodeURIComponent(URL_PARAMS.prod || "");
    let next_cate   = decodeURIComponent(URL_PARAMS.cate || "");
    let next_marca  = decodeURIComponent(URL_PARAMS.armadora || "");
    let next_mdl    = decodeURIComponent(URL_PARAMS.mdl || "");
    let next_provee = decodeURIComponent(URL_PARAMS.proveedor || "");
    let next_dispo  = decodeURIComponent(URL_PARAMS.Disponibilidad || "");

    obj.refaccion = {
        opc: "Buscar",
        tipo: "",
        categoria: "",
        marca: "",
        vehiculo: "",
        anio: "",
        producto: "",
        proveedor:"",
        disponibilidad:"",
        x: 0,
        y: 0
    }
    obj.categorias = [];
    obj.Marcas = [];
    obj.Vehiculos = [];
    obj.Modelos = [];
    obj.Refacciones = [];
    obj.Proveedores = [];
    obj.Existencias = [];
    obj.Ofertas = [];
    obj.Nuevos = [];
    obj.etiquetasActivas = [];
    /*variables del paginador*/
    obj.currentPage = 0;
    obj.pages = [];
    obj.pageSize = 20;
    obj.Trefacciones = 0;
    obj.view = 20;

    const updateURL = (params = {}, resetPage = true) => {
        const query = new URLSearchParams(window.location.search);

        Object.keys(params).forEach(k => {
            if (!params[k]) {
                query.delete(k);
            } else {
                query.set(k, params[k]);
            }
        });

        if (resetPage) query.set("pag", 1);

        window.location.search = query.toString();
    };

    obj.quitarEtiqueta = (tag) => {
        // Desmarcamos el checkbox en el HTML
        const checkbox = document.querySelector(`input[type="checkbox"][name="${tag.name}"][value="${tag.value}"]`);
        if (checkbox) checkbox.checked = false;

        // Llamamos a tu filtro para que actualice la búsqueda
        applyFilter({ name: tag.name, value: tag.value, checked: false });
    };

    obj.tagsFiltro = {
        tagsDispo: next_dispo ? next_dispo.split(",") : [],
        tagsMarca: next_marca ? next_marca.split(",") : [],
        tagsCatego: next_cate ? next_cate.split(",") : [],
        tagsProvee: next_provee ? next_provee.split(",") : [],
        tagsVehiculo: next_mdl ? next_mdl.split(",") : []
    };

    const aplicarbutton = document.querySelector(".filtro__aplicar--button");
    const borrarbutton = document.querySelector(".filtro__quitar--button");
    const seleccionDiv = document.querySelector(".name__seleccionados");
    const miDiv = document.getElementById("tags__filtros");
    
    /* HELPERS DE FILTROS */
    
    function getDisponibilidadValue(label) {
        return label.replace(/\s+/g, "+");
    }

    function hasAnyFilter() {
        return Object.values(obj.tagsFiltro).some(v => v);
    }

    function toggleApplyIfNeeded() {
        if (hasAnyFilter()) toggleActionButtons();
    }

    function checkByValue(name, value) {

        const inputs = document.querySelectorAll(
            `input[type="checkbox"][name="${name}"]`
        );

        inputs.forEach(input => {
            const domValue = input.value.replace(/\+/g, " ");
            const tagValue = value.replace(/\+/g, " ");

            if (domValue === tagValue) {
                input.checked = true;
            }
        });
    }
    
    function normalizeDisponibilidad(val = "") {
        return val
            .replace(/\+/g, " ")
            .trim();
    }

    function hydrateFiltersFromURL() {
        setTimeout(() => {
            // MARCA
            if (next_marca) {
                next_marca.split(",").forEach(id => {
                    checkByValue("Marca", id);

                    const marca = obj.Marcas.find(m => m._idMarca == id);
                    if (!marca) return;

                    obj.etiquetasActivas.push({name: "Marca",value: id,label: marca.Marca});
                });
            }
            // CATEGORIA
            if (next_cate) {
                next_cate.split(",").forEach(id => {
                    checkByValue("Categoria", id);

                    const cat = obj.categorias.find(c => c._id == id);
                    if (!cat) return;

                    obj.etiquetasActivas.push({name: "Categoria",value: id,label: cat.Categoria});
                });
            }
            // VEHICULO
            if (next_mdl) {
                next_mdl.split(",").forEach(id => {
                    checkByValue("Vehiculo", id);

                    const veh = obj.Vehiculos.find(v => v._id == id);
                    if (!veh) return;

                    obj.etiquetasActivas.push({name: "Vehiculo",value: id,label: veh.Modelo});
                });
            }
            // PROVEEDOR
            if (next_provee) {
                next_provee.split(",").forEach(id => {
                    checkByValue("Proveedor", id);

                    const prov = obj.Proveedores.find(p => p.id_proveedor == id);
                    if (!prov) return;

                    obj.etiquetasActivas.push({name: "Proveedor",value: id,label: prov.Proveedor});
                });
            }
            // DISPONIBILIDAD
            if (next_dispo) {
                next_dispo.split(",").forEach(raw => {
                    const label = normalizeDisponibilidad(raw);
                    const value = getDisponibilidadValue(label);

                    checkByValue("Disponibilidad", value);
                    obj.etiquetasActivas.push({name: "Disponibilidad",value: value,label: label});
                });
            }

            toggleApplyIfNeeded();
            $scope.$applyAsync();
        }, 0);
    }

    function filterVehiculosByMarca() {

        if (!Array.isArray(obj.Vehiculos)) return;

        const marcasActivas = obj.tagsFiltro.tagsMarca;

        // vehículos que sí siguen siendo válidos
        const vehiculosValidos = obj.tagsFiltro.tagsVehiculo.filter(idVehiculo => {
            const veh = obj.Vehiculos.find(v => v._id == idVehiculo);
            return veh && marcasActivas.includes(String(veh._idMarca));
        });

        // detectar los que se van a eliminar
        const eliminados = obj.tagsFiltro.tagsVehiculo.filter(v => !vehiculosValidos.includes(v));

        // actualizar estado interno
        obj.tagsFiltro.tagsVehiculo = vehiculosValidos;

        // desmarcar checkboxes y eliminar tags
        eliminados.forEach(id => {
            const cb = document.querySelector(`input[name="Vehiculo"][value="${id}"]`);
            if (cb) cb.checked = false;

            const tag = document.getElementById(`tags__Vehiculo_${id}`);
            if (tag) tag.remove();
        });
    }

    function refreshURL() {
        obj.tagsFiltro.tagsDispo = [...new Set(obj.tagsFiltro.tagsDispo)];
        updateURL({
            armadora: (obj.tagsFiltro.tagsMarca || []).join(","),
            cate: (obj.tagsFiltro.tagsCatego || []).join(","),
            proveedor: (obj.tagsFiltro.tagsProvee || []).join(","),
            Disponibilidad: (obj.tagsFiltro.tagsDispo || []).join(","),
            mdl: (obj.tagsFiltro.tagsVehiculo || []).join(",")
        });
    }

    function applyCurrentFilters() {
        updateURL({
            armadora: obj.tagsFiltro.tagsMarca.join(","),
            cate: obj.tagsFiltro.tagsCatego.join(","),
            proveedor: obj.tagsFiltro.tagsProvee.join(","),
            Disponibilidad: obj.tagsFiltro.tagsDispo.join(","),
            mdl: obj.tagsFiltro.tagsVehiculo.join(",")
        });
    }

    const hasFilters = Object.values(obj.tagsFiltro).some(v => v);
    function toggleActionButtons() {
        const visible = miDiv.childElementCount > 0 || hasAnyFilter();

        aplicarbutton.classList.toggle("dis-none", !visible);
        borrarbutton.classList.toggle("dis-none", !visible);

        if (document.getElementById("BodyDark").classList.contains("desktop")) {
            seleccionDiv.classList.toggle("dis-none", !visible);
        }
    }
    if (hasFilters) toggleActionButtons();
    
    function lockMarcas(lock = true) {
        document.querySelectorAll('input[name="Marca"]').forEach(cb => {
            cb.disabled = lock;
            cb.closest("label").classList.toggle("disabled", lock);
        });
    }

    function applyFilter({ name, value, checked }) {
        const MULTISELECT = ["Marca", "Proveedor", "Vehiculo", "Disponibilidad", "Categoria"];
        const map = {
            "Disponibilidad": "tagsDispo",
            "Categoria": "tagsCatego",
            "Proveedor": "tagsProvee",
            "Marca": "tagsMarca",
            "Vehiculo": "tagsVehiculo"
        };

        const prop = map[name];
        if (!prop) return;

        if (MULTISELECT.includes(name)) {
        
            const finalValue = name === "Disponibilidad"
                ? getDisponibilidadValue(value)
                : value;

            if (checked) {
                if (!obj.tagsFiltro[prop].includes(finalValue)) {
                    obj.tagsFiltro[prop].push(finalValue);
                }
            } else {
                obj.tagsFiltro[prop] = obj.tagsFiltro[prop].filter(v => v !== finalValue);
            }
        
        }
        // dependencia crítica Marca → Vehiculo
        if (name === "Marca") {
            filterVehiculosByMarca();
            lockMarcas(obj.tagsFiltro.tagsMarca.length > 0);
        }

        if (checked) {
            const existe = obj.etiquetasActivas.find(t => t.name === name && t.value === value);
            if (!existe) {
                obj.etiquetasActivas.push({ name: name, value: value, label: value });
            }
        } else {
            obj.etiquetasActivas = obj.etiquetasActivas.filter(t => !(t.name === name && t.value === value));
        }
        
        // Le avisamos a Angular que repinte la vista.
        $scope.$applyAsync();

        refreshURL();
        toggleApplyIfNeeded();
    }

    obj.checkmark = ($event) => {
        // Extraemos el checkbox físico desde el evento del clic
        const el = $event.target; 
        
        if (!el || !el.name) return;
        applyFilter({name: el.name.trim(), value: el.value, checked: el.checked});
    };

    aplicarbutton.addEventListener("click", () => {
        window.location.href = "?mod=catalogo&pag=1";
    });
    borrarbutton.addEventListener("click", clearfilter =>{
        window.location.href = "?mod=catalogo&pag=1";
    });

    document.querySelector(".open__opciones--Filtros").addEventListener("click", openF => {
        const open__filtro = document.querySelector(".filtros");
        const open__orden = document.querySelector(".filtros__orden");
        const open__opcionesF = document.querySelector(".open__opciones--Filtros");
        const open__opcionesO = document.querySelector(".open__opciones--Orden");
        open__filtro.classList.toggle("dis-none");
        open__opcionesF.classList.toggle("gris");
        open__opcionesO.classList.remove("gris");
        open__orden.classList.add("dis-none");
    });

    document.querySelector(".open__opciones--Orden").addEventListener("click", openO => {
        const open__orden = document.querySelector(".filtros__orden");
        const open__filtro = document.querySelector(".filtros");
        const open__opcionesF = document.querySelector(".open__opciones--Filtros");
        const open__opcionesO = document.querySelector(".open__opciones--Orden");
        open__orden.classList.toggle("dis-none");
        open__opcionesO.classList.toggle("gris");
        open__opcionesF.classList.remove("gris");
        open__filtro.classList.add("dis-none");
    });

    obj.viewMore = () => {
        const viewbtn = document.getElementById("viewbtn");
        if (obj.view < obj.Proveedores.length) {
            obj.view = obj.view + 100;
        } else if (obj.view >= obj.Proveedores.length) {
            obj.view = 20;
            viewbtn.textContent = "Ver más";
        }
        if (obj.view >= obj.Proveedores.length) {
            viewbtn.textContent = "Ver menos";
        }
    }

    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            e.NewUrlName = e["Producto"].replaceAll(" ", "-");
            e.NewUrlName = e.NewUrlName.replaceAll(",", "");
            e.NewUrlName = e.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
            e.NewAltName = e["Producto"].replaceAll(",", "");
            if (e.stock == 0) {
                e.agotado = true;
            }
        })
    }

    function syncRefaccionFromTags() {
        obj.refaccion.marca          = obj.tagsFiltro.tagsMarca.join(",");
        obj.refaccion.categoria      = obj.tagsFiltro.tagsCatego.join(",") || "T";
        obj.refaccion.vehiculo       = obj.tagsFiltro.tagsVehiculo.join(",");
        obj.refaccion.proveedor      = obj.tagsFiltro.tagsProvee.join(",");
        obj.refaccion.disponibilidad = obj.tagsFiltro.tagsDispo.join(",");
    }

    obj.getCategorias = async () => {
        obj.refaccion.tipo = "Categorias";
        obj.refaccion.x = (next_url - 1) * obj.pageSize;
        obj.refaccion.y = obj.pageSize;
        obj.refaccion.producto = next_prod;
        syncRefaccionFromTags();

        if (obj.refaccion.producto.includes("%C3%B1")) {
            obj.refaccion.producto = obj.refaccion.producto.replaceAll("%C3%B1", "ñ");
        }
        if (obj.refaccion.producto.includes("%C3%BC")) {
            obj.refaccion.producto = obj.refaccion.producto.replaceAll("%C3%BC", "ü");
        }

        if (obj.refaccion.producto != "" || obj.refaccion.marca != "") {
            obj.refaccion.orden = "Producto";
            obj.refaccion.tipodeorden = "ASC";
        } else {
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
                obj.Proveedores = res.data.Data.Proveedores;
                obj.Vehiculos = res.data.Data.Vehiculos;
                obj.Proveedores.sort((a, b) => a._id - b._id);
                obj.Existencias = res.data.Data.Existencias;
                obj.Ofertas = res.data.Data.Ofertas;
                obj.Nuevos = res.data.Data.Nuevos;
                obj.Refacciones = res.data.Data.Refacciones;
                obj.Trefacciones = res.data.Data.Trefacciones;
                hydrateFiltersFromURL();
            }
            
            obj.currentPage = next_url - 1;
            obj.configPages();
            obj.getPaginador(obj.currentPage * obj.pageSize, obj.pageSize);

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        }).finally(function() {
            // 2. Apagamos el estado de carga sin importar si la petición fue exitosa o falló
            obj.cargando = false; 
        });
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
                console.log(result);
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
            window.location.href = "?mod=catalogo&pag=1" + "&prod=" + encodeURIComponent(catalogo_buscador.value) + "&cate=" + encodeURIComponent(next_cate || "") + "&armadora=" + encodeURIComponent(obj.refaccion.marca || "") + "&mdl=" + encodeURIComponent(obj.refaccion.vehiculo || "");
        } else if (catalogo_buscador.value == "" && e.keyCode === 13) {
            window.location.href = "?mod=catalogo&pag=1";
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
        const query = new URLSearchParams(window.location.search);
        if (obj.refaccion && typeof obj.refaccion.producto === "string") {
            query.set("prod", encodeURIComponent(obj.refaccion.producto));
        }
        query.set("pag", index);
        window.location.href = "?" + query.toString();
    };


    obj.RefaccionDetalles = (_id) => {
        window.open("?mod=catalogo&opc=detalles&_id=" + _id, "_self");
    }

    obj.init = function() {
        obj.cargando = true; 

        obj.refaccion = obj.refaccion || {};
        obj.refaccion.producto       = next_prod;
        obj.refaccion.categoria      = next_cate || "T";
        obj.refaccion.marca          = next_marca;
        obj.refaccion.vehiculo       = next_mdl;
        obj.refaccion.proveedor      = next_provee;
        obj.refaccion.disponibilidad = next_dispo;

        obj.currentPage = next_url - 1;

        // Actualizar el title del documento
        document.querySelector('title').textContent = "Refacciones | MacromAutopartes";

        // Llamamos a los datos inmediatamente
        obj.getCategorias();
    };

    // Ejecutamos la función en cuanto el controlador se carga
    obj.init();
}

function catalogosDetallesCtrl($scope, $http, $rootScope) {

    var obj = $scope;
    obj.cargando = true;
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

    // cada vez que el usuario teclea un número
    obj.validarCantidad = () => {
        if (obj.Refaccion.cantidad === undefined || obj.Refaccion.cantidad === null) return;

        let cantidadActual = parseInt(obj.Refaccion.cantidad);
        let stockMaximo = parseInt(obj.Refaccion.datos.stock);
        //si teclea un número mayor, JavaScript lo atrapa
        if (cantidadActual > stockMaximo) {
            obj.Refaccion.cantidad = stockMaximo; // Lo bajamos al máximo
            toastr.warning("Solo tenemos " + stockMaximo + " piezas en existencia.");
        }
    };

    //cuando el usuario da clic fuera del input
    obj.formatearCantidad = () => {
        // Si el usuario borró el número o intentó poner 0 o negativos
        if (!obj.Refaccion.cantidad || obj.Refaccion.cantidad < 1) {
            obj.Refaccion.cantidad = 1;
        }
    };

    obj.btndisminuir = () => {
        obj.Refaccion.cantidad = obj.Refaccion.cantidad != 1 ? obj.Refaccion.cantidad - 1 : 1
    }

    obj.btnaumentar = () => {
        if (obj.Refaccion.cantidad < obj.Refaccion.datos.stock) {
            obj.Refaccion.cantidad++;
        } else {
            // Si intenta dar clic en "+" y ya está en el límite, le avisamos
            toastr.warning("Solo tenemos " + obj.Refaccion.datos.stock + " piezas en existencia.");
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
                obj.Refaccion.datos.NewAltName = obj.Refaccion.datos.Producto.replaceAll(",", "");
                newPageTitle = obj.Refaccion.datos.NewAltName;
                obj.Refaccion.datos.NewUrlName = obj.Refaccion.datos["Producto"].replaceAll(" ", "-");
                obj.Refaccion.datos.NewUrlName = obj.Refaccion.datos.NewUrlName.replaceAll(",", "");
                document.querySelector('title').textContent = newPageTitle;
                obj.Refaccion.datos.NewUrlName = obj.Refaccion.datos.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g, "");

                const expectedIdParam = obj.Refaccion.id + "-" + obj.Refaccion.datos.NewUrlName;
                const currentParams = new URLSearchParams(window.location.search);

                if (currentParams.get('_id') !== expectedIdParam) {
                    currentParams.set('_id', expectedIdParam);
                    const newUrl = window.location.pathname + '?' + currentParams.toString();
                    window.history.replaceState(null, '', newUrl);
                }

                obj.Activa = obj.Refaccion.datos.stock != 0 ? true : false;
            }
            if (obj.Refaccion.galeria.length > 4) {
                document.querySelector(".detalles__visual--opciones").style.display = "grid";
            }
            $scope.$evalAsync(() => {
                setTimeout(() => {
                
                    if ($('.slick2').hasClass('slick-initialized')) {
                        $('.slick2').slick('unslick');
                    }
                
                    $('.slick2').slick({
                        slidesToShow: 4,
                        slidesToScroll: 4,
                        infinite: true,
                        dots: true,
                        arrows: true,
                        responsive: [
                            {
                                breakpoint: 1200,
                                settings: { slidesToShow: 4, slidesToScroll: 4 }
                            },
                            {
                                breakpoint: 992,
                                settings: { slidesToShow: 3, slidesToScroll: 3 }
                            },
                            {
                                breakpoint: 768,
                                settings: { slidesToShow: 2, slidesToScroll: 2 }
                            },
                            {
                                breakpoint: 576,
                                settings: { slidesToShow: 2, slidesToScroll: 2, arrows: false }
                            }
                        ]
                    });
                
                }, 0);
            });
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        }).finally(function() {
            obj.cargando = false;
        });
    }

    obj.Agregarcarrito = () => {
        // 1. Prevenir doble clic: Si ya se está agregando, no hacer nada.
        if (obj.agregando) return; 
        
        obj.agregando = true;
        $http({
            method: 'POST',
            url: url_session,
            data: { modelo: obj.Refaccion }

        }).then(function successCallback(res) {
            
            // Evaluamos la Bandera limpia que nos manda PHP
            if (res.data.Bandera == 1) {
                toastr.success(res.data.Mensaje); 
            } else {
                toastr.warning(res.data.Mensaje || "No se pudo agregar al carrito");
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        }).finally(function() {
            obj.agregando = false;
            
            $rootScope.$broadcast('carritoActualizado'); 
        });
    }

    obj.getImagen = (status, id) => {
        //let url = "images/refacciones/";
        let url = "https://macromautopartes.com/images/refacciones/";
        return status ? url + id + ".webp" : url + id + ".webp";
    }

    obj.getGaleria = (id) => {
        if (id != undefined) {
            //let url = "images/galeria/" + id;
            let url = "https://macromautopartes.com/images/galeria/" + id; 
            return url;
        }
    }

    obj.btnDetallesRelacionados = (id) => {
        window.open("?mod=catalogo&opc=detalles&_id=" + id, "_self");
    }

    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            e.NewUrlName = e["Producto"].replaceAll(" ", "-");
            e.NewUrlName = e.NewUrlName.replaceAll(",", "");
            e.NewUrlName = e.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
            e.NewAltName = e["Producto"].replaceAll(",", "");
            if (e.stock == 0) {
                e.agotado = true;
            }
        })

    }
    // 1. Extraemos el ID directamente de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const rawId = urlParams.get('_id'); // Esto lee: "12637-Terminal-Dir-..."

    // 2. Si existe un ID en la URL, lo separamos para quedarnos solo con el número
    if (rawId) {
        obj.Refaccion.id = rawId.split('-')[0]; 
    }

    // 3. Disparamos la petición a la base de datos de inmediato
    obj.getRefaccion();
}
