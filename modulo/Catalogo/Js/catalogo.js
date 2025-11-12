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
    /*variables del paginador*/
    obj.currentPage = 0;
    obj.pages = [];
    obj.pageSize = 20;
    obj.Trefacciones = 0;
    obj.view = 20;
    obj.tagsFiltro = {
        tagsDispo: "",
        tagsMarca: "",
        tagsCatego: "",
        tagsProvee: "",
        tagsVehiculo: ""
    };

    let url_actual = window.location.href;
    const mylink = window.location.href.split("&");
    const next_url = mylink[1].split("=")[1];
    var next_prod="";var next_cate="";var next_marca="";
    var next_mdl="";var next_vehi="";var next_provee="";var next_dispo="";
    const aplicarbutton = document.querySelector(".filtro__aplicar--button");
    const borrarbutton = document.querySelector(".filtro__quitar--button");
    const seleccionDiv = document.querySelector(".name__seleccionados");
    const miDiv = document.getElementById("tags__filtros");

    checkmark = async (value, checkid) => {
        const checkbox = document.querySelector('input[id="' + checkid + '"]');
        const name = checkbox.getAttribute("name");
        if (checkbox.checked) {
            const contenedorTags = document.createElement("div");
            const nuevoDiv = document.createElement("div");
            const nuevoI = document.createElement("i");

            contenedorTags.className = "tags__contenedor";
            contenedorTags.setAttribute("name", name + "__tags");
            contenedorTags.id = "tags__contendor__" + checkbox.id;
            miDiv.appendChild(contenedorTags);
            const tagsDiv = document.getElementById(contenedorTags.id);

            tagsDiv.setAttribute("onclick", "desmark(id)");
            nuevoI.className = "fas fa-times cursorpnt";
            tagsDiv.appendChild(nuevoI);

            nuevoDiv.className = "Filtro_Seleccion " + checkbox.id;
            nuevoDiv.id = checkbox.id + "_" + name;
            nuevoDiv.innerHTML = checkbox.id;
            tagsDiv.appendChild(nuevoDiv);
            switch (name) {
                case "Disponibilidad":
                    if(obj.tagsFiltro.tagsDispo.length >= 1){
                        obj.tagsFiltro.tagsDispo += ","+checkid;
                    }else{
                        obj.tagsFiltro.tagsDispo += checkid;
                    }
                    if(url_actual.includes("Disponibilidad=")){
                        if(next_dispo.length < obj.tagsFiltro.tagsDispo.length){
                            url_actual = url_actual.replace("Disponibilidad="+next_dispo,"Disponibilidad="+obj.tagsFiltro.tagsDispo);
                            url_actual = url_actual.replace("pag="+next_url,"pag=1");
                            window.location.href = url_actual;
                        }
                    }else{
                        url_actual +="&Disponibilidad="+obj.tagsFiltro.tagsDispo;
                        url_actual = url_actual.replace("pag="+next_url,"pag=1");
                        window.location.href = url_actual;
                    }
                    break;
                case "Marca":
                    if(obj.tagsFiltro.tagsMarca.length >= 1){
                        obj.tagsFiltro.tagsMarca += ","+value;
                    }else{
                        obj.tagsFiltro.tagsMarca += value;
                    }
                    if(mylink[4].includes(next_marca) && mylink[4].includes(obj.tagsFiltro.tagsMarca)){
                    }else{
                        url_actual = url_actual.replace("armadora="+next_marca,"armadora="+obj.tagsFiltro.tagsMarca);
                        url_actual = url_actual.replace("pag="+next_url,"pag=1");
                        window.location.href = url_actual;
                    }

                    break;
                case "Vehiculo":
                    if(obj.tagsFiltro.tagsVehiculo.length >= 1){
                        obj.tagsFiltro.tagsVehiculo += ","+value;
                    }else{
                        obj.tagsFiltro.tagsVehiculo += value;
                    }
                    if(mylink[5].includes(next_mdl) && mylink[5].includes(obj.tagsFiltro.tagsVehiculo)){
                    }else{
                        url_actual = url_actual.replace("mdl="+next_mdl,"mdl="+obj.tagsFiltro.tagsVehiculo);
                        url_actual = url_actual.replace("pag="+next_url,"pag=1");
                        window.location.href = url_actual;
                    }
                    break;
                case "Categoria":
                    if(obj.tagsFiltro.tagsCatego.length >= 1){
                        obj.tagsFiltro.tagsCatego += ","+value;
                    }else{
                        obj.tagsFiltro.tagsCatego += value;
                    }

                    if(next_cate=="T"){
                        next_cate="";
                    }

                    if(mylink[3].includes(next_cate) && mylink[3].includes(obj.tagsFiltro.tagsCatego)){
                    }else{
                        url_actual = url_actual.replace("cate="+next_cate,"cate="+obj.tagsFiltro.tagsCatego);
                        url_actual = url_actual.replace("pag="+next_url,"pag=1");
                        window.location.href = url_actual;
                    }

                    break;
                case "Proveedor":
                    if(obj.tagsFiltro.tagsProvee.length >= 1){
                        obj.tagsFiltro.tagsProvee += ","+value;
                    }else{
                        obj.tagsFiltro.tagsProvee += value;
                    }
                    if(url_actual.includes("proveedor=")){
                        if(next_provee.length < obj.tagsFiltro.tagsProvee.length){
                            url_actual = url_actual.replace("proveedor="+next_provee,"proveedor="+obj.tagsFiltro.tagsProvee);
                            url_actual = url_actual.replace("pag="+next_url,"pag=1");
                            window.location.href = url_actual;
                        }
                    }else{
                        url_actual +="&proveedor="+obj.tagsFiltro.tagsProvee;
                        url_actual = url_actual.replace("pag="+next_url,"pag=1");
                        window.location.href = url_actual;
                    }
                    break;
            }
        } else {
            switch (name) {
                case "Disponibilidad":
                    let tagsArregloDispo = obj.tagsFiltro.tagsDispo.split(",");
                    let tagsArregloFiltroDispo = tagsArregloDispo.filter(elemento => elemento !== checkid);
                    tagsArregloFiltroDispo = tagsArregloFiltroDispo.toString();
                    obj.tagsFiltro.tagsDispo = tagsArregloFiltroDispo;
                    if(obj.tagsFiltro.tagsDispo == ""){
                        url_actual = url_actual.replace("&Disponibilidad="+next_dispo,"");
                        url_actual = url_actual.replace("pag="+next_url,"pag=1");
                        window.location.href = url_actual;
                    }else{
                        url_actual = url_actual.replace("Disponibilidad="+next_dispo,"Disponibilidad="+obj.tagsFiltro.tagsDispo);
                        url_actual = url_actual.replace("pag="+next_url,"pag=1");
                        window.location.href = url_actual;
                    }
                    break;
                case "Marca":
                    let valorEliminarMarca;
                    let tagsArregloVehiculoMarca = obj.tagsFiltro.tagsVehiculo.split(",");
                    let tagsArregloFiltroVehiculoMarca = tagsArregloVehiculoMarca;
                    obj.Marcas.forEach(m =>{
                        if(checkid == m.Marca){
                            valorEliminarMarca = m._idMarca;
                        }
                    });

                    if(obj.Vehiculos.length > 0){
                        obj.Vehiculos.forEach(v =>{
                            tagsArregloVehiculoMarca.forEach(md =>{
                                if(md == v._id && v._idMarca == valorEliminarMarca){

                                    tagsArregloFiltroVehiculoMarca = tagsArregloFiltroVehiculoMarca.filter(elemento => elemento !== md);
                                }
                            });
                        });
                        tagsArregloFiltroVehiculoMarca = tagsArregloFiltroVehiculoMarca.toString();
                        obj.tagsFiltro.tagsVehiculo = tagsArregloFiltroVehiculoMarca;
                        url_actual = url_actual.replace("mdl="+next_mdl,"mdl="+obj.tagsFiltro.tagsVehiculo);
                    }

                    console.log(url_actual);
                    let tagsArregloMarca = obj.tagsFiltro.tagsMarca.split(",");
                    let tagsArregloFiltroMarca = tagsArregloMarca.filter(elemento => elemento !== value);
                    tagsArregloFiltroMarca = tagsArregloFiltroMarca.toString();
                    obj.tagsFiltro.tagsMarca = tagsArregloFiltroMarca;
                    url_actual = url_actual.replace("armadora="+next_marca,"armadora="+obj.tagsFiltro.tagsMarca);
                    url_actual = url_actual.replace("pag="+next_url,"pag=1");
                    window.location.href = url_actual;
                    break;
                case "Vehiculo":
                    let tagsArregloVehiculo = obj.tagsFiltro.tagsVehiculo.split(",");
                    let tagsArregloFiltroVehiculo = tagsArregloVehiculo.filter(elemento => elemento !== value);
                    tagsArregloFiltroVehiculo = tagsArregloFiltroVehiculo.toString();
                    obj.tagsFiltro.tagsVehiculo = tagsArregloFiltroVehiculo;
                    url_actual = url_actual.replace("mdl="+next_mdl,"mdl="+obj.tagsFiltro.tagsVehiculo);
                    url_actual = url_actual.replace("pag="+next_url,"pag=1");
                    window.location.href = url_actual;

                    break;
                case "Categoria":
                    let tagsArregloCatego = obj.tagsFiltro.tagsCatego.split(",");
                    let tagsArregloFiltroCatego = tagsArregloCatego.filter(elemento => elemento !== value);
                    tagsArregloFiltroCatego = tagsArregloFiltroCatego.toString();
                    obj.tagsFiltro.tagsCatego = tagsArregloFiltroCatego;
                    url_actual = url_actual.replace("cate="+next_cate,"cate="+obj.tagsFiltro.tagsCatego);
                    url_actual = url_actual.replace("pag="+next_url,"pag=1");
                    window.location.href = url_actual;
                    break;
                case "Proveedor":
                    let tagsArregloProvee = obj.tagsFiltro.tagsProvee.split(",");
                    let tagsArregloFiltroProvee = tagsArregloProvee.filter(elemento => elemento !== value);
                    tagsArregloFiltroProvee = tagsArregloFiltroProvee.toString();
                    obj.tagsFiltro.tagsProvee = tagsArregloFiltroProvee;
                    if(obj.tagsFiltro.tagsProvee == ""){
                        url_actual = url_actual.replace("&proveedor="+next_provee,"");
                        url_actual = url_actual.replace("pag="+next_url,"pag=1");
                        window.location.href = url_actual;
                    }else{
                        url_actual = url_actual.replace("proveedor="+next_provee,"proveedor="+obj.tagsFiltro.tagsProvee);
                        url_actual = url_actual.replace("pag="+next_url,"pag=1");
                        window.location.href = url_actual;
                    }
                    break;
            }
            const borrarDiv = document.getElementById("tags__contendor__" + checkbox.id);
            borrarDiv.remove();
        }

        if (miDiv.childElementCount > 0) {
            aplicarbutton.classList.remove("dis-none");
            borrarbutton.classList.remove("dis-none");
            if(document.getElementById("BodyDark").classList.contains("desktop")){
                seleccionDiv.classList.remove("dis-none")
            }
        } else {
            aplicarbutton.classList.add("dis-none");
            borrarbutton.classList.add("dis-none");
            if(document.getElementById("BodyDark").classList.contains("desktop")){
                seleccionDiv.classList.add("dis-none")
            }
        }

    }

    aplicarbutton.addEventListener("click", clearfilter =>{
        window.location.href = "?mod=catalogo&pag=1&prod=&cate=&armadora=&mdl=&[a]=";
    });
    borrarbutton.addEventListener("click", clearfilter =>{
        window.location.href = "?mod=catalogo&pag=1&prod=&cate=&armadora=&mdl=&[a]=";
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

    desmark = async (desmarkid) => {
        var idelete = desmarkid.replace("tags__contendor__", "");
        const divdelete = document.getElementById(desmarkid);
        const checkdelete = document.getElementById(idelete);
        nametag = divdelete.getAttribute("name");
        const Newnametag = nametag.replace("__tags", "");
        console.log(Newnametag);
        if (idelete.includes("_") && Newnametag != "Disponibilidad") {
            idelete = idelete.replace("_", " ");
        }

        switch (Newnametag) {
            case "Disponibilidad":
                let tagsArregloDispo = obj.tagsFiltro.tagsDispo.split(",");
                let tagsArregloFiltroDispo = tagsArregloDispo.filter(elemento => elemento !== idelete);
                tagsArregloFiltroDispo = tagsArregloFiltroDispo.toString();
                obj.tagsFiltro.tagsDispo = tagsArregloFiltroDispo;
                if(obj.tagsFiltro.tagsDispo == ""){
                    url_actual = url_actual.replace("&Disponibilidad="+next_dispo,"");
                    url_actual = url_actual.replace("pag="+next_url,"pag=1");
                    window.location.href = url_actual;
                }else{
                    url_actual = url_actual.replace("Disponibilidad="+next_dispo,"Disponibilidad="+obj.tagsFiltro.tagsDispo);
                    url_actual = url_actual.replace("pag="+next_url,"pag=1");
                    window.location.href = url_actual;
                }
                break;
            case "Marca":
                let valorEliminarMarca;
                let tagsArregloVehiculoMarca = obj.tagsFiltro.tagsVehiculo.split(",");
                let tagsArregloFiltroVehiculoMarca = tagsArregloVehiculoMarca;
                obj.Marcas.forEach(m =>{
                    if(idelete == m.Marca){
                        valorEliminarMarca = m._idMarca;
                    }
                });

                if(obj.Vehiculos.length > 0){
                    obj.Vehiculos.forEach(v =>{
                        tagsArregloVehiculoMarca.forEach(md =>{
                            if(md == v._id && v._idMarca == valorEliminarMarca){

                                tagsArregloFiltroVehiculoMarca = tagsArregloFiltroVehiculoMarca.filter(elemento => elemento !== md);
                            }
                        });
                    });
                    tagsArregloFiltroVehiculoMarca = tagsArregloFiltroVehiculoMarca.toString();
                    obj.tagsFiltro.tagsVehiculo = tagsArregloFiltroVehiculoMarca;
                    url_actual = url_actual.replace("mdl="+next_mdl,"mdl="+obj.tagsFiltro.tagsVehiculo);
                }

                let tagsArregloMarca = obj.tagsFiltro.tagsMarca.split(",");
                let tagsArregloFiltroMarca = tagsArregloMarca.filter(elemento => elemento !== valorEliminarMarca);
                tagsArregloFiltroMarca = tagsArregloFiltroMarca.toString();
                obj.tagsFiltro.tagsMarca = tagsArregloFiltroMarca;
                url_actual = url_actual.replace("armadora="+next_marca,"armadora="+obj.tagsFiltro.tagsMarca);
                url_actual = url_actual.replace("pag="+next_url,"pag=1");
                window.location.href = url_actual;

                break;
            case "Vehiculo":
                let valorEliminarVehiculo;
                obj.Vehiculos.forEach(m =>{
                    if(idelete == m.Modelo){
                        valorEliminarVehiculo = m._id;
                    }
                });
                if(obj.Marcas.length == 1){
                    url_actual = url_actual.replace("armadora="+next_marca,"armadora="+obj.Marcas[0]._idMarca);
                }
                let tagsArregloVehiculo = obj.tagsFiltro.tagsVehiculo.split(",");
                let tagsArregloFiltroVehiculo = tagsArregloVehiculo.filter(elemento => elemento !== valorEliminarVehiculo);
                tagsArregloFiltroVehiculo = tagsArregloFiltroVehiculo.toString();
                obj.tagsFiltro.tagsVehiculo = tagsArregloFiltroVehiculo;
                url_actual = url_actual.replace("mdl="+next_mdl,"mdl="+obj.tagsFiltro.tagsVehiculo);
                url_actual = url_actual.replace("pag="+next_url,"pag=1");
                window.location.href = url_actual;

                break;
            case "Categoria":
               let valorEliminarCatego;
                obj.categorias.forEach(m =>{
                    if(idelete == m.Categoria){
                        valorEliminarCatego = m._id;
                    }
                });
                let tagsArregloCatego = obj.tagsFiltro.tagsCatego.split(",");
                let tagsArregloFiltroCatego = tagsArregloCatego.filter(elemento => elemento !== valorEliminarCatego);
                tagsArregloFiltroCatego = tagsArregloFiltroCatego.toString();
                obj.tagsFiltro.tagsCatego = tagsArregloFiltroCatego;
                url_actual = url_actual.replace("cate="+next_cate,"cate="+obj.tagsFiltro.tagsCatego);
                url_actual = url_actual.replace("pag="+next_url,"pag=1");
                window.location.href = url_actual;
                break;
            case "Proveedor":
                let valorEliminarProvee;
                obj.Proveedores.forEach(m =>{
                    if(idelete == m.Proveedor){
                        valorEliminarProvee = m.id_proveedor;
                    }
                });
                let tagsArregloProvee = obj.tagsFiltro.tagsProvee.split(",");
                let tagsArregloFiltroProvee = tagsArregloProvee.filter(elemento => elemento !== valorEliminarProvee);
                tagsArregloFiltroProvee = tagsArregloFiltroProvee.toString();
                obj.tagsFiltro.tagsProvee = tagsArregloFiltroProvee;
                if(obj.tagsFiltro.tagsProvee == ""){
                    url_actual = url_actual.replace("&proveedor="+next_provee,"");
                    url_actual = url_actual.replace("pag="+next_url,"pag=1");
                    window.location.href = url_actual;
                }else{
                    url_actual = url_actual.replace("proveedor="+next_provee,"proveedor="+obj.tagsFiltro.tagsProvee);
                    url_actual = url_actual.replace("pag="+next_url,"pag=1");
                    window.location.href = url_actual;
                }
                break;
        }
        divdelete.remove();
        checkdelete.checked = false;

        if (miDiv.childElementCount > 0) {
            aplicarbutton.classList.remove("dis-none");
            borrarbutton.classList.remove("dis-none");
            if(document.getElementById("BodyDark").classList.contains("desktop")){
                seleccionDiv.classList.remove("dis-none")
            }
        } else {
            aplicarbutton.classList.add("dis-none");
            borrarbutton.classList.add("dis-none");
            if(document.getElementById("BodyDark").classList.contains("desktop")){
                seleccionDiv.classList.add("dis-none")
            }
        }
    }

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

    obj.getCategorias = async () => {
        obj.refaccion.tipo = "Categorias";
        obj.refaccion.x = 0;
        obj.refaccion.y = obj.pageSize;
        obj.refaccion.producto = next_prod;
        obj.refaccion.proveedor = next_provee;
        obj.refaccion.vehiculo = next_mdl;
        obj.refaccion.disponibilidad = next_dispo;
        if(obj.refaccion.categoria == ""){
            obj.refaccion.categoria = "T";
        }
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
        console.log(obj.refaccion);

        $http({
            method: 'GET',
            url: url_catalogo,
            params: obj.refaccion
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                console.log(res);
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

                obj.currentPage = next_url - 1;
                obj.configPages();
                obj.eachRefacciones(obj.Refacciones);
            }

            obj.currentPage = next_url - 1;
            obj.configPages();
            obj.getPaginador(obj.currentPage * obj.pageSize, obj.pageSize);

            let steprovee;
            mylink.forEach(letprove =>{
                if(letprove.includes("proveedor")){
                   steprovee = letprove.split("=")[1];
                }
            })
            if(steprovee){
                if(steprovee.includes(",")){
                    let moverProvee = steprovee.split(",");
                    moverProvee.forEach(elprovee =>{
                        let index = obj.Proveedores.findIndex(x => x.id_proveedor === elprovee);
                        let valor;
                        valor = obj.Proveedores.splice(index,1);
                        obj.Proveedores.unshift(valor[0]);
                    });
                }else{
                     let index = obj.Proveedores.findIndex(x => x.id_proveedor === steprovee);
                    let valor;
                    valor = obj.Proveedores.splice(index,1);
                    obj.Proveedores.unshift(valor[0]);
                }
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
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
            window.location.href = "?mod=catalogo&pag=" + 1 + "&prod=" + catalogo_buscador.value + "&cate=" + next_cate + "&armadora=" + obj.refaccion.marca + "&mdl=" + obj.refaccion.vehiculo + "&[a]=" + obj.refaccion.anio;
        } else if (catalogo_buscador.value == "" && e.keyCode === 13) {
            window.location.href = "?mod=catalogo&pag=1&prod=&cate=&armadora=&mdl=&[a]=";
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

    mylink.forEach(myurl =>{
        switch(true){
            case myurl.includes("prod"):
                next_prod = myurl.split("=")[1];
                if(next_prod.includes("%20")){
                   next_prod = next_prod.replaceAll("%20"," ");
                } else if(next_prod.includes("%2520")){
                    next_prod = next_prod.replaceAll("%2520"," ");
                }
                obj.refaccion.producto = next_prod;
            break;
            case myurl.includes("cate"):
                next_cate = myurl.split("=")[1];
            break;
            case myurl.includes("armadora"):
                next_marca = myurl.split("=")[1];
                if (next_marca.includes("?%20string:")) {
                    obj.refaccion.marca = "";
                } else {
                    obj.refaccion.marca = next_marca;
                }
            break;
            case myurl.includes("mdl"):
                next_mdl = myurl.split("=")[1];
                if(next_mdl.includes("?%20string:")){
                    next_mdl = next_mdl.replaceAll("?","");
                    next_mdl = next_mdl.replaceAll("%20","");
                    next_mdl = next_mdl.replaceAll("string:","");
                }
            break;
            case myurl.includes("[a]"):
                next_vehi = myurl.split("=")[1];
            break;
            case myurl.includes("Disponibilidad"):
                next_dispo = myurl.split("=")[1];
                if(next_dispo.includes("%20")){
                    next_dispo = next_dispo.replace("%20","_");
                }
                console.log("DISPONIBILIDAD: ",next_dispo);
            break;
            case myurl.includes("proveedor"):
                next_provee = myurl.split("=")[1];
            break;
        }
    });

    setTimeout(() => {
        if(next_marca != ""){
            const individualMarca = next_marca.split(",");
            individualMarca.forEach(ind =>{
                obj.Marcas.forEach(m =>{
                    if(ind == m._idMarca){
                        const truebox = document.querySelector('input[id="' + m.Marca + '"]');
                        truebox.checked = true;
                        checkmark(m._idMarca,m.Marca);
                    }
                });
            });

        }
        if(next_cate != "" || next_cate != "T"){
            const individualCatego = next_cate.split(",");
            individualCatego.forEach(ind =>{
                obj.categorias.forEach(m =>{
                    if(ind == m._id){
                        const truebox = document.querySelector('input[id="' + m.Categoria + '"]');
                        truebox.checked = true;
                        checkmark(m._id,m.Categoria);
                    }
                });
            });
        }
        if(next_mdl != ""){
            const individualMdl = next_mdl.split(",");
            individualMdl.forEach(ind =>{
                obj.Vehiculos.forEach(m =>{
                    if(ind == m._id){
                        const truebox = document.querySelector('input[id="' + m.Modelo + '"]');
                        truebox.checked = true;
                        checkmark(m._id,m.Modelo);
                    }
                });
            });
        }
        if(next_provee != ""){
            const individualProvee = next_provee.split(",");
            individualProvee.forEach(ind =>{
                obj.Proveedores.forEach(m =>{
                    if(ind == m.id_proveedor){
                        const truebox = document.querySelector('input[id="' + m.Proveedor + '"]');
                        truebox.checked = true;
                        checkmark(m.id_proveedor,m.Proveedor);
                    }
                });
            });
        }

        if(next_dispo != ""){
            var dispo = "Ofertas,En_Existencia,Articulos_Nuevos,switch__existencia,switch__oferta,switch__Articulos_Nuevos";
            dispo = dispo.split(",");
            console.log("Objeto Creado:",dispo);
            const individualDispo = next_dispo.split(",");
            individualDispo.forEach(ind =>{
                dispo.forEach(m =>{
                    if(ind == m){
                        const truebox = document.querySelector('input[id="' + m + '"]');
                        truebox.checked = true;
                        checkmark(m,ind);
                    }
                });
            });
        }

    }, "500");

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
        url_actual = url_actual.replace("pag="+next_url,"pag="+index);
        window.location.href = url_actual;
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
                obj.Refaccion.datos.NewAltName = obj.Refaccion.datos.Producto.replaceAll(",", "");
                newPageTitle = obj.Refaccion.datos.NewAltName;
                obj.Refaccion.datos.NewUrlName = obj.Refaccion.datos["Producto"].replaceAll(" ", "-");
                obj.Refaccion.datos.NewUrlName = obj.Refaccion.datos.NewUrlName.replaceAll(",", "");
                document.querySelector('title').textContent = newPageTitle;
                obj.Refaccion.datos.NewUrlName = obj.Refaccion.datos.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
                if (window.location.href.includes(obj.Refaccion.datos.NewUrlName)) {
                } else {
                    window.location.href = window.location.href + "-" + obj.Refaccion.datos.NewUrlName;
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
            e.NewUrlName = e["Producto"].replaceAll(" ", "-");
            e.NewUrlName = e.NewUrlName.replaceAll(",", "");
            e.NewUrlName = e.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
            e.NewAltName = e["Producto"].replaceAll(",", "");
            if (e.stock == 0) {
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
