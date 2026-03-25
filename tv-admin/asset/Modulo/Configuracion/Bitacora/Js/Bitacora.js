var urlBitacora = "./Modulo/Configuracion/Bitacora/Ajax/Bitacora.php";

tsuruVolks.controller('BitacoraCtrl', ["$scope", "$http", "$sce", BitacoraCtrl]);

function BitacoraCtrl($scope, $http, $sce) {
    var obj = $scope;
    obj.logs = [];
    obj.busqueda = '';
    
    // Diccionarios para Catálogos Básicos
    obj.mapMarcas = {};
    obj.mapCategorias = {};
    obj.mapModelos = {};
    obj.mapAnios = {};
    obj.mapProveedores = {};
    obj.mapEnvios = {}; 
    obj.mapRefacciones = {};

    obj.cargarCatalogos = function() {
        const urlRef = "./Modulo/Control/Refacciones/Ajax/Refacciones.php";
        const urlEnvios = "./Modulo/Configuracion/Cenvios/Ajax/Cenvios.php";
        
        const configRef = {
            headers: {'Content-Type': undefined},
            transformRequest: data => { 
                var fd = new FormData(); 
                for (var m in data.modelo) fd.append(m, data.modelo[m]); 
                return fd; 
            }
        };

        $http.post(urlRef, { modelo: { opc: "buscar", tipo: "Marcas" } }, configRef).then(res => { if(res.data.Bandera == 1) res.data.data.forEach(m => obj.mapMarcas[m._id] = m.Marca); });
        $http.post(urlRef, { modelo: { opc: "buscar", tipo: "Categorias" } }, configRef).then(res => { if(res.data.Bandera == 1) res.data.data.forEach(c => obj.mapCategorias[c._id] = c.Categoria); });
        $http.post(urlRef, { modelo: { opc: "buscar", tipo: "Vehiculos" } }, configRef).then(res => { if(res.data.Bandera == 1) res.data.data.forEach(v => obj.mapModelos[v._id] = v.Modelo); });
        $http.post(urlRef, { modelo: { opc: "buscar", tipo: "Modelos" } }, configRef).then(res => { if(res.data.Bandera == 1) res.data.data.forEach(a => obj.mapAnios[a._id] = a.Anio); });
        $http.post(urlRef, { modelo: { opc: "buscar", tipo: "proveedores" } }, configRef).then(res => { if(res.data.Bandera == 1) res.data.data.forEach(p => obj.mapProveedores[p._id] = p.Proveedor); });
        $http.post(urlRef, { modelo: { opc: "buscar", tipo: "Refacciones", buscar: "", skip: 0, limit: 1000, historico: "false", publicados: "true", orden: "P._id", ordentype: "DESC" } }, configRef).then(res => { if(res.data.Bandera == 1) res.data.data.refacciones.forEach(r => obj.mapRefacciones[r._id] = r.Clave + " - " + r.Producto); });

        $http.post(urlEnvios, { opc: "getEnvios" }).then(res => { 
            if(res.data.Bandera == 1) {
                res.data.Envios.forEach(e => {
                    let destino = e.Municipio ? `${e.Municipio}, ${e.Estado}` : e.Estado;
                    obj.mapEnvios[e.id] = destino;
                });
            }
        });
    };

    obj.formatearDetalles = function(textoFila, modulo, accionLog) {
        if (!textoFila) return "";
        
        let textoLimpio = textoFila.replace(/&quot;/g, '"');
        let mod = modulo ? modulo.toLowerCase() : "";

        const etiquetasJSON = {
            "_idMarca": "Marca", "_idCategoria": "Categoría", "Modelo": "Vehículo",
            "Anios": "Año/Versión", "id_proveedor": "Proveedor", "Precio1": "Precio Público",
            "stock": "Existencia", "precio_manual": "Precio Manual"
        };

        try {
            let matchJSON = textoLimpio.match(/({.*})/);
            if (matchJSON) {
                let jsonStr = matchJSON[1];
                let restoTexto = textoLimpio.replace(jsonStr, '').trim(); 
                let limpio = jsonStr.replace(/"|{|}/g, ""); 
                
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

                        let nombreHumano = etiquetasJSON[llave] || llave;
                        
                        return `<span class="badge badge-light border mb-1 mr-1 text-sm text-left font-weight-normal">
                                    <b class="text-danger">${nombreHumano}:</b> <span class="text-dark">${valor}</span>
                                </span>`;
                    }
                    return item;
                }).join(' ');

                return $sce.trustAsHtml((restoTexto ? `<b>${restoTexto}</b><br>` : '') + formateado);
            }
        } catch(e) {}

        let catalogosBasicos = ["agencias", "marcas", "categorias", "vehículos", "vehiculos", "modelos", "proveedores", "cenvios", "envios"];
        let esCatalogoBasico = catalogosBasicos.some(c => mod.includes(c));

        if (esCatalogoBasico) {
            let regexTextoID = /(?:ID Envío|ID)\s*[:]?\s*(\d+)/i; 
            let matchTexto = textoLimpio.match(regexTextoID);
            let nombreTraducido = "";

            if (matchTexto) {
                let idCapturado = matchTexto[1];

                if (mod.includes("agencias") || mod.includes("marcas")) nombreTraducido = obj.mapMarcas[idCapturado];
                else if (mod.includes("categorias")) nombreTraducido = obj.mapCategorias[idCapturado];
                else if (mod.includes("vehículos") || mod.includes("vehiculos") || mod.includes("modelos")) nombreTraducido = obj.mapModelos[idCapturado];
                else if (mod.includes("proveedores")) nombreTraducido = obj.mapProveedores[idCapturado];
                else if (mod.includes("cenvios") || mod.includes("envios")) nombreTraducido = obj.mapEnvios[idCapturado];

                if (!nombreTraducido) {
                    let partes = textoLimpio.split(matchTexto[0]);
                    let resto = partes.join(' ').replace(/^[-,\s()]+|[-,\s()]+$/g, ''); 
                    resto = resto.replace(/(Proveedor|Vehículo|Agencia|Categoría|Generación) (creado|creada|editado|editada|activado|activada|desactivado|desactivada|eliminado|eliminada)[.\s]*/gi, '');
                    resto = resto.replace(/Registró nueva categoría[:\s]*/gi, '');
                    resto = resto.replace(/^[-,\s()]+|[-,\s()]+$/g, '');

                    if (resto.length > 1 && resto !== "S/C" && isNaN(resto)) {
                        nombreTraducido = resto; 
                    }
                }

                if (nombreTraducido) {
                    let textoPildora = `<b class="text-primary"><i class="fas fa-tag mr-1"></i> ${nombreTraducido}</b>`;
                    
                    let accion = accionLog ? accionLog.toUpperCase() : "";
                    let verbo = "Modificó"; 
                    if (accion.includes("ACTIVAR") && !accion.includes("DESACTIVAR")) verbo = "Activó";
                    else if (accion.includes("DESACTIVAR")) verbo = "Desactivó";
                    else if (accion.includes("NUEVA") || accion.includes("CREAR")) verbo = "Agregó";
                    else if (accion.includes("EDITAR")) verbo = "Editó";
                    else if (accion.includes("ELIMINAR") || accion.includes("BORRAR")) verbo = "Eliminó";

                    let entidad = "el registro";
                    if (mod.includes("agencias") || mod.includes("marcas")) entidad = "la agencia";
                    else if (mod.includes("categorias")) entidad = "la categoría";
                    else if (mod.includes("vehículos") || mod.includes("modelos")) entidad = "el vehículo";
                    else if (mod.includes("proveedores")) entidad = "el proveedor";
                    else if (mod.includes("cenvios") || mod.includes("envios")) entidad = "el destino";

                    let frasePrincipal = `${verbo} ${entidad}: ${textoPildora}`;
                    let infoExtra = "";

                    if (matchTexto) {
                        infoExtra = textoLimpio.replace(matchTexto[0], '').trim();
                        infoExtra = infoExtra.replace(/(Proveedor|Vehículo|Agencia|Categoría|Generación) (creado|creada|editado|editada|activado|activada|desactivado|desactivada|eliminado|eliminada)[.\s]*/gi, '');
                        infoExtra = infoExtra.replace(/Registró nueva categoría[:\s]*/gi, '');
                        infoExtra = infoExtra.replace(/^[-,\s()]+|[-,\s()]+$/g, '');

                        if (infoExtra.toLowerCase().includes(nombreTraducido.toLowerCase())) {
                            infoExtra = infoExtra.replace(new RegExp(nombreTraducido, 'ig'), '').trim();
                            infoExtra = infoExtra.replace(/^[-,\s()]+|[-,\s()]+$/g, ''); 
                        }
                    }

                    if (infoExtra && infoExtra.length > 2) {
                        return $sce.trustAsHtml(`${frasePrincipal} <span class="ml-2 text-muted font-weight-normal">- ${infoExtra}</span>`);
                    } else {
                        return $sce.trustAsHtml(frasePrincipal);
                    }
                }
            }
        }

        textoLimpio = textoLimpio.replace(/^[-,\s()]+|[-,\s()]+$/g, '');
        return $sce.trustAsHtml(textoLimpio);
    };

    obj.cargarBitacora = function () {
        $http.post(urlBitacora, { opc: "get_logs" }).then(res => {
            if (res.data.Bandera == 1) obj.logs = res.data.Data;
        });
    }

    // Inicialización
    obj.cargarCatalogos();
    setTimeout(() => { obj.cargarBitacora(); }, 500);
}