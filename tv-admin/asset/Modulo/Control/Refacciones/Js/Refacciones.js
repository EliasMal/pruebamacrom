'use strict';
var urlRefacciones = "./Modulo/Control/Refacciones/Ajax/Refacciones.php";
var urlGaleria = "./Modulo/Control/Refacciones/Ajax/Galeria.php";
var url_seicom = "https://volks.dyndns.info:444/service.asmx/consulta_art";

if (typeof window.Toast === 'undefined') {
    window.Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

var confirmarRefaccionAccion = (titulo, texto, icono, btnText, btnColor, accion) => {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        showCancelButton: true,
        confirmButtonColor: btnColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: btnText,
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) accion();
    });
};

tsuruVolks
    .controller('RefaccionesCtrl', ["$scope", "$http", RefaccionesCtrl])
    .controller('RefaccionesNewCtrl', ["$scope", "$http", RefaccionesNewCtrl])
    .controller('RefaccionesEditCtrl', ["$scope", "$http","$sce", RefaccionesEditCtrl])
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
    obj.Numreg = 0;
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

    if (window.location.href.includes("&")) {
        const mylink = window.location.href.split("&");
        const orden = mylink[1];
        
        if (orden == "pub") {
            obj.publicados = false;
        } else {
            obj.propertyName = orden;
            obj.ordentype = "desc";
        }
    }

    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            obj.getSeicom(e.Clave).then(token => {
                e.agotado = token;
                if(e.stock != 0){
                    e.agotado = false;
                }
            })
        })
    }

    obj.sortby = (linkInfo) => {
        window.location.href = "?mod=Refacciones&" + linkInfo;
    }
    
    obj.DefaultRefacciones = () => {
        window.location = "?mod=Refacciones";
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
                console.error("Error Seicom:", res);
            });
            
            if (result) {
                const xml = $(result.data).find("string");
                let json = JSON.parse(xml.text());
                return json.Table.map(e => e.existencia).reduce((a, b) => a + b, 0) == 0 ? true : false;
            }
        } catch (error) {
            console.error(error);
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
        // Buscador por clave única
        if (obj.buscar && /^[0-9]*$/.test(obj.buscar) && obj.buscar.length > 3) {
            $http({
                method: 'POST',
                url: urlRefacciones,
                data: { modelo: { opc: "buscar", tipo: "Refaccion", id: obj.buscar } },
                headers: {'Content-Type': undefined},
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.OneRefaccion = res.data.data.ClaveUnica;
                    obj.SinRefaccion = (obj.OneRefaccion == null) ? obj.buscar : null;
                }
            });
        } else {
            obj.OneRefaccion = "";
            obj.SinRefaccion = null;
        }

        // Buscador General
        setTimeout(() => {
            $http({
                method: 'POST',
                url: urlRefacciones,
                data: { modelo: { opc: "buscar", tipo: "Refacciones", buscar: obj.buscar, publicados: obj.publicados, historico: obj.historico, skip: $skip, limit: $limit, orden: obj.propertyName, ordentype: obj.ordentype } },
                headers: {'Content-Type': undefined},
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
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
                Toast.fire({ icon: 'error', title: 'Error de conexión con el servidor' });
            });
        }, 100);
    }

    obj.configPages = function () {
        obj.pages = [];
        var totalPages = Math.ceil(obj.Numreg / obj.pageSize);
        var ini = Math.max(1, obj.currentPage - 4);
        var fin = Math.min(totalPages, ini + 9);
        
        if (fin - ini < 9) ini = Math.max(1, fin - 9);

        for (var i = ini; i <= fin; i++) {
            obj.pages.push({ no: i });
        }
    };

    obj.nextPage = () => {
        obj.currentPage++;
        obj.getRefacciones(obj.currentPage * obj.pageSize, obj.pageSize);
    }

    obj.lastPage = () => {
        obj.currentPage--;
        obj.getRefacciones(obj.currentPage * obj.pageSize, obj.pageSize);
    }

    obj.setPage = function (index) {
        obj.currentPage = index - 1;
        obj.getRefacciones(obj.currentPage * obj.pageSize, obj.pageSize);
    };

    angular.element(document).ready(function () {
        obj.getRefacciones();
    });
}

function RefaccionesNewCtrl($scope, $http) {
    var obj = $scope;
    obj.img = "/images/refacciones/motor.webp";
    obj.refaccion = {
        opc: "new", Color: "#FFFFFF", Estatus: true, RefaccionNueva: false, 
        RefaccionOferta: false, RefaccionLiquidacion: false, Publicar: false, Enviogratis: false, 
        Kit: false, Alto: 0, Largo: 0, Ancho: 0, 
        Peso: 0, stock: 0, Precio1: 0.0, Precio2: 0.0, precio_manual: false 
    };
    obj.backgroudimg = { "background-color": obj.refaccion.Color };
    obj.categorias = []; obj.Marcas = []; obj.Vehiculos = []; obj.Modelos = []; obj.Proveedor = [];
    obj.habilitado = false;
    obj.precioSeicomBase = 0;

    function trunc(x, posiciones = 0) {
        var s = x.toString();
        var decimalLength = s.indexOf('.') + 1;
        var numStr = s.substr(0, decimalLength + posiciones);
        return Number(numStr);
    }

    let timeoutBusqueda = null;
    obj.buscarClaveSeicom = () => {
        if(timeoutBusqueda) clearTimeout(timeoutBusqueda);
        
        timeoutBusqueda = setTimeout(() => {
            if(obj.refaccion.Clave && obj.refaccion.Clave.toString().length >= 3) {
                obj.getArticulovolks();
                Toast.fire({ icon: 'info', title: 'Buscando datos en Seicom...' });
            }
        }, 800);
    }

    obj.getArticulovolks = () => {
        if(obj.refaccion.Kit != true && obj.refaccion.Clave) {
            $http({
                method: 'POST',
                url: url_seicom,
                data: "articulo=" + obj.refaccion.Clave,
                headers: { 'Content-Type': "application/x-www-form-urlencoded" },
                transformResponse: function (data) { return $.parseXML(data); }
            }).then(function successCallback(res) {
                var xml = $(res.data);
                var jsonText = xml.find("string").text();
                
                if(jsonText){
                    var existenciasData = JSON.parse(jsonText);
                    
                    if(existenciasData.Table && existenciasData.Table.length > 0) {
                        let datosSeicom = existenciasData.Table[0];
                        obj.precioSeicomBase = parseFloat(datosSeicom.precio_5);

                        let nombreReal = datosSeicom.descripcion_producto || datosSeicom.descripcion;
                        if(nombreReal) obj.refaccion.Producto = nombreReal;

                        if(!obj.refaccion.precio_manual) {
                            obj.calcularPrecioCarga(); 
                            let exisTotales = 0;
                            existenciasData.Table.forEach(function (e) {
                                exisTotales += parseInt(e.existencia);
                            });
                            obj.refaccion.stock = exisTotales;
                        }
                    }
                }
            }).catch(function(err) {
                console.error("Error Seicom:", err);
            });
        }
    }

    obj.calcularPrecioCarga = () => {
        if(!obj.refaccion.precio_manual && obj.precioSeicomBase) {
            var pCalc = obj.precioSeicomBase * 1.16;
            obj.refaccion.Precio1 = trunc(pCalc, 2);
        }
    }

    obj.cambioPrecioManual = () => {
        if(!obj.refaccion.precio_manual) {
            if(obj.precioSeicomBase) {
                var pCalc = obj.precioSeicomBase * 1.16;
                obj.refaccion.Precio1 = trunc(pCalc, 2);
                Toast.fire({ icon: 'info', title: 'Precio calculado desde Seicom' });
            }
        }
    }

    obj.btnGuardarRefaccion = () => {
        confirmarRefaccionAccion(
            '¿Guardar nueva refacción?',
            'Asegúrate de que la clave y el precio sean correctos.',
            'question',
            '<i class="fas fa-save"></i> Sí, guardar',
            '#28a745',
            () => {
                $http({
                    method: 'POST',
                    url: urlRefacciones,
                    data: { modelo: obj.refaccion },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                        return formData;
                    }
                }).then(function(res) {
                    obj.habilitado = true;
                    Swal.fire('¡Guardado!', 'La refacción se creó exitosamente.', 'success');
                });
            }
        );
    }

    obj.getCategorias = () => {
        $http.post(urlRefacciones, { modelo: { opc: "buscar", tipo: "Categorias" } }, {
            headers: {'Content-Type': undefined},
            transformRequest: function (data) {
                var fd = new FormData();
                for (var m in data.modelo) { fd.append(m, data.modelo[m]); }
                return fd;
            }
        }).then(res => { if(res.data.Bandera == 1) obj.categorias = res.data.data; });
    }

    obj.getMarcas = () => {
        $http.post(urlRefacciones, { modelo: { opc: "buscar", tipo: "Marcas" } }, {
            headers: {'Content-Type': undefined},
            transformRequest: function (data) {
                var fd = new FormData();
                for (var m in data.modelo) { fd.append(m, data.modelo[m]); }
                return fd;
            }
        }).then(res => { if(res.data.Bandera == 1) obj.Marcas = res.data.data; });
    }

    obj.getVehiculos = () => {
        $http.post(urlRefacciones, { modelo: { opc: "buscar", tipo: "Vehiculos", _idMarca: obj.refaccion._idMarca } }, {
            headers: {'Content-Type': undefined},
            transformRequest: function (data) {
                var fd = new FormData();
                for (var m in data.modelo) { fd.append(m, data.modelo[m]); }
                return fd;
            }
        }).then(res => { if(res.data.Bandera == 1) obj.Vehiculos = res.data.data; });
    }

    obj.getModelos = () => {
        $http.post(urlRefacciones, { modelo: { opc: "buscar", tipo: "Modelos", _idVehiculo: obj.refaccion.Modelo } }, {
            headers: {'Content-Type': undefined},
            transformRequest: function (data) {
                var fd = new FormData();
                for (var m in data.modelo) { fd.append(m, data.modelo[m]); }
                return fd;
            }
        }).then(res => { if(res.data.Bandera == 1) obj.Modelos = res.data.data; });
    }

    obj.getProveedores = () => {
        $http.post(urlRefacciones, { modelo: { opc: "buscar", tipo: "proveedores" } }, {
            headers: {'Content-Type': undefined},
            transformRequest: function (data) {
                var fd = new FormData();
                for (var m in data.modelo) { fd.append(m, data.modelo[m]); }
                return fd;
            }
        }).then(res => { if(res.data.Bandera == 1) obj.Proveedor = res.data.data; });
    }

    obj.btnRegresar = () => { window.location.href = "?mod=Refacciones"; }

    angular.element(document).ready(function () {
        var fileInput = document.getElementById('txtfile');
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                var file = fileInput.files[0];
                if (file && file.size <= 512000) {
                    var reader = new FileReader();
                    reader.onload = function (e) { obj.img = reader.result; obj.$apply(); }
                    reader.readAsDataURL(file);
                } else {
                    Toast.fire({ icon: 'warning', title: 'La Imagen supera los 512 KB' });
                }
            });
        }
        obj.getCategorias(); obj.getMarcas(); obj.getProveedores();
        $(".numeric").numeric();
        $('.calendario').datepicker({ format: 'yyyy-mm-dd', startDate: '-3d' });
    });
}

function RefaccionesEditCtrl($scope, $http, $sce) {
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
    
    obj.mapMarcas = {};
    obj.mapCategorias = {};
    obj.mapModelos = {};
    obj.mapAnios = {};
    obj.mapProveedores = {};

    function trunc(x, posiciones = 0) {
        var s = x.toString();
        var decimalLength = s.indexOf('.') + 1;
        var numStr = s.substr(0, decimalLength + posiciones);
        return Number(numStr);
    }

    obj.formatearDiferencias = function(textoFila) {
        if (!textoFila || textoFila === "{}") return $sce.trustAsHtml("<i>Sin cambios registrados</i>");
        
        const etiquetas = {
            "_idMarca": "Marca",
            "_idCategoria": "Categoría",
            "Modelo": "Vehículo",
            "Anios": "Año/Versión",
            "id_proveedor": "Proveedor",
            "Precio1": "Precio Público",
            "stock": "Existencia",
            "precio_manual": "Precio Manual",
            "Publicar": "Visible en Web"
        };

        try {
            let limpio = textoFila.replace(/&quot;|"|{|}/g, "");
            let formateado = limpio.split(',').map(item => {
                let partes = item.split(':');
                if(partes.length === 2) {
                    let llave = partes[0].trim();
                    let valor = partes[1].trim();
                    
                    if(llave === "_idMarca") valor = obj.mapMarcas[valor] || valor;
                    else if(llave === "_idCategoria") valor = obj.mapCategorias[valor] || valor;
                    else if(llave === "Modelo") valor = obj.mapModelos[valor] || valor;
                    else if(llave === "Anios") valor = obj.mapAnios[valor] || valor;
                    else if(llave === "id_proveedor") valor = obj.mapProveedores[valor] || valor;
                    else if(valor === "true") valor = "Activado";
                    else if(valor === "false") valor = "Desactivado";

                    let nombreReal = etiquetas[llave] || llave;
                    
                    return `<span class="badge badge-light border mb-1 mr-1 text-sm font-weight-normal">
                                <b class="text-danger">${nombreReal}:</b> ${valor}
                            </span>`;
                }
                return item;
            }).join(' ');
            return $sce.trustAsHtml(formateado);
        } catch(e) {
            return $sce.trustAsHtml(textoFila.replace(/&quot;|{|}/g, ""));
        }
    };

    let timeoutBusqueda = null;
    obj.buscarClaveSeicom = () => {
        if(timeoutBusqueda) clearTimeout(timeoutBusqueda);
        
        timeoutBusqueda = setTimeout(() => {
            if(obj.refaccion.Clave && obj.refaccion.Clave.length >= 3) {
                obj.getArticulovolks();
                Toast.fire({ icon: 'info', title: 'Buscando existencias en Seicom...' });
            }
        }, 800);
    }

    obj.btnGuardarVehiculo = function() {
        if(!obj.vehiculo.id_Marca_RefaccionVehiculo || !obj.vehiculo.id_Modelo_RefaccionVehiculo) {
            Toast.fire({ icon: 'warning', title: 'Por favor, selecciona Marca y Modelo.' });
            return;
        }

        let payload = {
            opc: "buscar",
            tipo: "AgregarVehiculo",
            clave: obj.refaccion.Clave,
            id_imagen: obj.refaccion._id,
            idmarca: obj.vehiculo.id_Marca_RefaccionVehiculo,
            idmodelo: obj.vehiculo.id_Modelo_RefaccionVehiculo,
            generacion: obj.vehiculo.generacion || '',
            ainicial: String(obj.vehiculo.ainicial || '').replace(/\D/g, ''),
            afinal: String(obj.vehiculo.afinal || '').replace(/\D/g, ''),
            motor: obj.vehiculo.motor || '',
            transmision: obj.vehiculo.transmision || '',
            especificaciones: obj.vehiculo.especificaciones || ''
        };

        $http({
            method: 'POST',
            url: urlRefacciones,
            data: { modelo: payload },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                return formData;
            }
        }).then(function successCallback(res) {
            if(res.data && res.data.Bandera == 1) {
                Toast.fire({ icon: 'success', title: res.data.mensaje || 'Vehículo guardado' });
                $('#mdlVehiculo').modal('hide'); 
                obj.vehiculo = {}; 
                obj.refaccion.id = obj.refaccion._id; 
                obj.getRefaccion(); 
            } else {
                Toast.fire({ icon: 'error', title: "Error: " + res.data.mensaje });
            }
        }, function errorCallback(res) {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
    };

    obj.btnBorrarRvehiculo = (RV = null) => { 
        confirmarRefaccionAccion(
            '¿Eliminar vehículo compatible?',
            'Esta acción quitará este auto de la lista de compatibilidades.',
            'warning',
            '<i class="fas fa-trash-alt"></i> Sí, eliminar',
            '#dc3545',
            () => {
                let idActual = obj.refaccion._id;
                $http({
                    method: 'POST',
                    url: urlRefacciones,
                    data: { modelo: { 
                        opc: "buscar", 
                        tipo: "EliminarVehiculo", 
                        idcompatibilidad: RV.idcompatibilidad, 
                        id_imagen: idActual
                    }},
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                        return formData;
                    }
                }).then(function successCallback(res) {
                    Toast.fire({ icon: 'success', title: 'Vehículo eliminado correctamente' });
                    obj.refaccion.id = idActual;
                    obj.getRefaccion(); 
                }, function errorCallback(res) {
                    Toast.fire({ icon: 'error', title: 'Error al eliminar el vehículo' });
                });
            }
        );
    }

   obj.getArticulovolks = () => {
        if(obj.refaccion.Kit != true && obj.refaccion.Clave){
            $http({
                method: 'POST',
                url: url_seicom,
                data: "articulo=" + obj.refaccion.Clave,
                headers: { 'Content-Type': "application/x-www-form-urlencoded" },
                transformResponse: function (data) { return $.parseXML(data); }
            }).then(function successCallback(res) {
                var xml = $(res.data);
                var jsonText = xml.find("string").text();
                
                if(jsonText){
                    obj.existencias = JSON.parse(jsonText);
                    obj.exisTotales = 0;

                    if(obj.existencias.Table && obj.existencias.Table.length > 0) {
                        let datosSeicom = obj.existencias.Table[0];
                        obj.precioSeicomBase = parseFloat(datosSeicom.precio_5);

                        obj.existencias.Table.forEach(function (e) {
                            obj.exisTotales += parseInt(e.existencia);
                        });

                        if(!obj.refaccion.precio_manual) {
                            obj.calcularPrecioCarga(); 
                        }
                    }
                }
            }).catch(function(err) {
                console.error("Error de conexión con Seicom:", err);
            });
        }
    }

    obj.calcularPrecioCarga = () => {
        if(!obj.refaccion.precio_manual && obj.precioSeicomBase) {
            var pCalc = obj.precioSeicomBase * 1.16;
            obj.refaccion.Precio1 = trunc(pCalc, 2);
        }
    }

    obj.cambioPrecioManual = () => {
        if(!obj.refaccion.precio_manual) {
            if(obj.precioSeicomBase) {
                var pCalc = obj.precioSeicomBase * 1.16;
                obj.refaccion.Precio1 = trunc(pCalc, 2);
                Toast.fire({ icon: 'info', title: 'Precio actualizado desde Seicom' });
            } else {
                Toast.fire({ icon: 'warning', title: 'No se encontró precio en Seicom' });
            }
        } else {
            Toast.fire({ icon: 'success', title: 'Precio manual activado' });
        }
    }

    obj.getRefaccion = () => {
        $http({
            method: 'POST',
            url: urlRefacciones,
            data: { modelo: { opc: "buscar", tipo: "Refaccion", id: obj.refaccion.id } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.dominio = res.data.dominio;
                
                obj.Vehiculos = res.data.data.ListaVehiculos;
                obj.Modelos = res.data.data.ListaAnios;
                
                obj.refaccion = res.data.data.Refaccion;
                
                obj.categorias = res.data.data.Categorias;
                obj.actividad = res.data.data.Actividad;
                obj.Marcas = res.data.data.Marcas;
                obj.Compatibilidad = res.data.data.Compatibilidad;
                obj.Proveedor = res.data.data.Proveedores;
                obj.refaccion.opc = "edit";

                if(obj.Marcas) obj.Marcas.forEach(m => obj.mapMarcas[m._id] = m.Marca);
                if(obj.categorias) obj.categorias.forEach(c => obj.mapCategorias[c._id] = c.Categoria);
                if(obj.Vehiculos) obj.Vehiculos.forEach(v => obj.mapModelos[v._id] = v.Modelo);
                if(obj.Modelos) obj.Modelos.forEach(a => obj.mapAnios[a._id] = a.Anio);
                if(obj.Proveedor) obj.Proveedor.forEach(p => obj.mapProveedores[p._id] = p.Proveedor);
                
                obj.backgroudimg = { "background-color": obj.refaccion.color }
                obj.img = obj.refaccion.imagen ? obj.dominio + '/images/refacciones/' + obj.refaccion._id + '.png' : obj.dominio + '/images/refacciones/' + obj.refaccion._id + '.webp';
                obj.getArticulovolks();
                localStorage.setItem("Datos", JSON.stringify(obj.refaccion));
                obj.getGaleria();

                if(obj.actividad && obj.actividad.length > 0) {
                    obj.actividad.forEach(act => {
                        if(!act.datosdiff || act.datosdiff === "{}" || act.datosdiff === "") {
                            act.datos_limpios = "Sin cambios registrados";
                        } else {
                            let texto = act.datosdiff.replace(/&quot;|{|}/g, "");
                            act.datos_limpios = texto.replace(/,/g, " | "); 
                        }
                    });
                }
            }
        }, function errorCallback(res) {
            Toast.fire({ icon: 'error', title: 'Error al obtener los datos de la refacción' });
        });
    }

    obj.getCategorias = () => {
        $http({
            method: 'POST',
            url: urlRefacciones,
            data: { modelo: { opc: "buscar", tipo: "Categorias" } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera) { obj.categorias = res.data.data; }
        }, function errorCallback(res) {
            Toast.fire({ icon: 'error', title: 'Error al cargar categorías' });
        });
    }

    obj.getMarcas = () => {
        $http({
            method: 'POST',
            url: urlRefacciones,
            data: { modelo: { opc: "buscar", tipo: "Marcas" } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) { obj.Marcas = res.data.data; }
        }, function errorCallback(res) {
            Toast.fire({ icon: 'error', title: 'Error al cargar marcas' });
        });
    }

    obj.getVehiculos = (id = null) => {
        $http({
            method: 'POST',
            url: urlRefacciones,
            data: { modelo: { opc: "buscar", tipo: "Vehiculos", _idMarca: id } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) { obj.Vehiculos = res.data.data; }
        }, function errorCallback(res) {
            Toast.fire({ icon: 'error', title: 'Error al cargar vehículos' });
        });
    }

    obj.getModelos = (id = null) => {
        $http({
            method: 'POST',
            url: urlRefacciones,
            data: { modelo: { opc: "buscar", tipo: "Modelos", _idVehiculo: id } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) { obj.Modelos = res.data.data; }
        }, function errorCallback(res) {
            Toast.fire({ icon: 'error', title: 'Error al cargar modelos' });
        });
    }

    obj.getProveedores = () => {
        $http({
            method: 'POST',
            url: urlRefacciones,
            data: { modelo: { opc: "buscar", tipo: "proveedores" } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) { obj.Proveedor = res.data.data; }
        }, function errorCallback(res) {
            Toast.fire({ icon: 'error', title: 'Error al cargar proveedores' });
        });
    }

    obj.btnRegresar = () => { window.location.href = "?mod=Refacciones"; }
    obj.btnEditarRefaccion = () => { obj.habilitado = false; }

    obj.btnSaveRefaccion = () => {
        confirmarRefaccionAccion(
            '¿Guardar los cambios?',
            'Se guardarán los cambios efectuados en la refacción.',
            'info',
            '<i class="fas fa-save"></i> Sí, guardar cambios',
            '#007bff',
            () => {
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
                    url: urlRefacciones,
                    data: { modelo: obj.refaccion },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                        return formData;
                    }
                }).then(function successCallback(res) {
                    obj.habilitado = true;
                    Swal.fire('¡Actualizado!', 'Los cambios han sido guardados.', 'success').then(() => {
                        location.reload();
                    });
                }, function errorCallback(res) {
                    Toast.fire({ icon: 'error', title: 'Error al guardar los cambios' });
                });
            }
        );
    }

    obj.btnEliminarRefaccionCompleta = () => {
        confirmarRefaccionAccion(
            '¿Eliminar refacción permanentemente?',
            'Esta acción es irreversible. Se borrará la pieza, su galería, sus vehículos compatibles y su historial.',
            'error',
            '<i class="fas fa-trash-alt"></i> Sí, eliminar todo',
            '#dc3545',
            () => {
                $http({
                    method: 'POST',
                    url: urlRefacciones,
                    data: { modelo: { opc: "delete", id: obj.refaccion._id, Clave: obj.refaccion.Clave } },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.modelo) { formData.append(m, data.modelo[m]); }
                        return formData;
                    }
                }).then(function successCallback(res) {
                    
                    if(typeof res.data === 'object' && res.data.Bandera == 1){
                        Swal.fire('¡Eliminada!', 'La refacción ha sido borrada del sistema.', 'success').then(() => {
                            window.location.href = "?mod=Refacciones";
                        });
                    } else {
                        let errorMsg = res.data.mensaje || 'Error en el servidor.';
                        Toast.fire({ icon: 'error', title: errorMsg });
                        console.error("Respuesta cruda del servidor:", res.data); 
                    }
                    
                }, function errorCallback(res) {
                    Toast.fire({ icon: 'error', title: 'Error de conexión al intentar eliminar.' });
                });
            }
        );
    }

    /* Galería */
    obj.getImagen = (e) => {
        return e.imagen ? obj.dominio + '/images/galeria/' + e._id + '.webp' : obj.dominio + '/images/galeria/' + e._id + '.png';
    }

    obj.btnEliminarImagen = (_id) => {
        confirmarRefaccionAccion(
            '¿Eliminar imagen?',
            'La imagen se borrará permanentemente de la galería.',
            'error',
            '<i class="fas fa-trash-alt"></i> Sí, borrarla',
            '#dc3545', // Rojo
            () => {
                obj.setImagenes({ opc: "erase", id: _id, id_refaccion: obj.refaccion._id });
            }
        );
    }

    obj.getGaleria = () => {
        if(!obj.refaccion._id) return;

        $http({
            method: 'POST',
            url: urlGaleria,
            data: { galeria: { opc: "get", id: obj.refaccion._id } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.galeria) {
                    formData.append(m, data.galeria[m]);
                }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data && res.data.Bandera == 1) {
                obj.dataGaleria = res.data.Data;
            }
        }, function errorCallback(res) {
            console.error("Error al cargar galería:", res);
        });
    }

    obj.setImagenes = (Galeria) => {
        $http({
            method: 'POST',
            url: urlGaleria,
            data: { galeria: Galeria },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.galeria) { formData.append(m, data.galeria[m]); }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                $("#Mcategoria").modal('hide');
                Toast.fire({ icon: 'success', title: res.data.mensaje || 'Operación exitosa en galería' });
                obj.getGaleria();
            } else { 
                Toast.fire({ icon: 'error', title: res.data.mensaje }); 
            }
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
            Toast.fire({ icon: 'warning', title: 'No has seleccionado una imagen' }); 
        }
    }

    obj.btnNuevoVehiculo = () => { obj.vehiculo = {}; $("#mdlVehiculo").modal('show'); }

    angular.element(document).ready(function () {
        var fileInput1 = document.getElementById('txtfile');
        if(fileInput1){
            fileInput1.addEventListener('change', function (e) {
                var file = fileInput1.files[0];
                if (file) {
                    if (file.size <= 512000) {
                        obj.refaccion.file = file;
                        var reader = new FileReader();
                        reader.onload = function (e) { obj.img = reader.result; obj.$apply(); }
                        reader.readAsDataURL(file);
                    } else { 
                        Toast.fire({ icon: 'warning', title: 'La Imagen supera los 512 KB' }); 
                    }
                }
            });
        }

        $(".archivos").on("change", function (e) {
            var file = this.files[0];
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
                    Toast.fire({ icon: 'warning', title: 'La Imagen supera 1 MB' }); 
                }
            }
        });

        obj.getRefaccion();
        $(".numeric").numeric();
        $('.calendario').datepicker({ format: 'yyyy-mm-dd', startDate: '-3d' });
        obj.session = JSON.parse(localStorage.getItem('session'));
        obj.isAdmin = obj.session.rol === "Admin" || obj.session.rol === "root" ? true : false;
    });
}