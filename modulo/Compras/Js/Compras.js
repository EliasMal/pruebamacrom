'use strict'

var url_getusercampras = "./modulo/Compras/Ajax/Compras.php";
const urlProfile = "./modulo/Profile/Ajax/Profile.php";
var urlCostumer = "./modulo/ProcesoCompra/Ajax/ProcesoCompra.php";
var url_session = "./modulo/home/Ajax/session.php";
var urlhome = "./modulo/home/Ajax/home.php";
const url_seicom = "https://volks.dyndns.info:444/service.asmx/consulta_art";
//const urlSkydropx = "https://api-demo.skydropx.com/v1/quotations"
const urlSkydropx = "https://api.skydropx.com/v1/quotations";
//const token = "Token token=SuOZQz5IrqceQbJmBqQfAo4PMQvNKMCh2PtXOKMfKM0t";
const token = "Token token=fInE1ArT8CJfaR2wkznA5hXSNCMSXs7vitsCFeM98Pct";

tsuruVolks.controller('ComprasCtrl', ["$scope", "$http", "$sce", ComprasCtrl])
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

function ComprasCtrl($scope, $http, $sce) {
    var obj = $scope;
    obj.session = $_SESSION;
    obj.Costumer = { Cenvio: { costo: 0 }, aviso: false, facturacion: 0, profile: {}, metodoPago: "", cart: $_SESSION.CarritoPrueba, dataFacturacion: {}, dataDomicilio: {}, descuento: 0, DiaEstimado: null};
    obj.cenvio = 0
    obj.Numproducts = obj.session.CarritoPrueba ? Object.keys(obj.session.CarritoPrueba).length : 0;
    obj.factflag = false;
    obj.cotizador = [];
    obj.flag = false;
    obj.dataflag = false;
    obj.requiredEnvio = false;
    var count_prod;
    var count_sucursales = "";
    obj.sucursales = { Colima: 0, Manzanillo: 0, Tecoman: 0, VillaDeAlvarez: 0 };
    obj.SeiData = {};
    obj.dataCotizador = {
        zip_from: "28000",
        zip_to: "60174",
        parcel: {
            weight: 0, //peso
            height: 0, //altura
            width: 0, //ancho
            length: 0 //largo
        }
    }
    obj.eachRefacciones = (array) => {
        array.forEach(e => {
            e.NewUrlName = e["_producto"].replaceAll(" ","-");
            e.NewUrlName = e.NewUrlName.replaceAll(",","");
            e.NewUrlName = e.NewUrlName.normalize('NFD').replace(/[\u0300-\u036f]/g,"");
            e.NewAltName = e["_producto"].replaceAll(",","");
        });
    }
    //funcion en preparacion, Estado: En espera.
    obj.prodCarrito = function () {
        for (let el in $_SESSION.CarritoPrueba) {
            obj.getSeicom($_SESSION.CarritoPrueba[el].Clave).then(token => {
                count_prod = 0;
                obj.SeiData.Table.forEach(prd => {
                    prd.em_nombre = prd.em_nombre.replaceAll("(MACROM AUTOPARTES)", "");
                    if (prd.em_nombre.includes("BODEGA") || prd.em_nombre.includes("VENTAS INTERNET")) {

                    } else {

                        if (parseInt(prd.existencia) >= parseInt($_SESSION.CarritoPrueba[el].Cantidad)) {

                            count_sucursales += prd.em_nombre.trim() + ".";

                        }
                    }
                    count_prod = count_prod + prd.existencia;
                });

                if (count_sucursales == "") {
                    count_sucursales = "Sin sucursales con stock suficiente";
                }
                if (count_sucursales.includes("VILLA DE ALVAREZ")) {
                    obj.sucursales.VillaDeAlvarez++;
                }
                if (count_sucursales.includes("MANZANILLO")) {
                    obj.sucursales.Manzanillo++;
                }
                if (count_sucursales.includes("COLIMA")) {
                    obj.sucursales.Colima++;
                }
                if (count_sucursales.includes("TECOMAN")) {
                    obj.sucursales.Tecoman++;
                }
                console.log("Puede recoger en sucursales: ", count_sucursales);
                console.log(el,"-----------------------------------");  
                count_sucursales = "";
            });
            
        }
        console.log("Costumer: ",obj.sucursales);
        for (let suc in obj.sucursales) {
            if (obj.sucursales[suc] == obj.Numproducts) {
                console.log(obj.sucursales[suc], " tiene stock disponible de las piezas");
            }
        }

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
                obj.SeiData = json;
                return json.Table.map(e => e.existencia).reduce((a, b) => a + b, 0) == 0 ? true : false;
            }
        } catch (error) {
            toastr.error(error)
        }

    }
    //funcion en preparacion, Estado: En espera.

    obj.RefaccionDetalles = (_id, newurl) => {
        window.open("?mod=catalogo&opc=detalles&_id=" + _id + "-" + newurl, "_self");
    }

    obj.subtotal = () => {
        obj.Costumer.Subtotal = 0
        for (let e in obj.session.CarritoPrueba) {
            if (obj.session.CarritoPrueba[e].RefaccionOferta == '1') {
                obj.Costumer.Subtotal += (obj.session.CarritoPrueba[e].Cantidad * obj.session.CarritoPrueba[e].Precio2);
            } else {
                obj.Costumer.Subtotal += (obj.session.CarritoPrueba[e].Cantidad * obj.session.CarritoPrueba[e].Precio);
            }

        }
        return obj.Costumer.Subtotal;
    }

    obj.Ttotal = () => {
        if (obj.Costumer.Cenvio.Envio == 'L') {
            obj.Costumer.Cenvio.Costo = obj.dataCotizador.parcel.weight == 0 ? 0 : obj.cenvio;
            obj.requiredEnvio = true;
        }
        obj.total = obj.Costumer.Subtotal + parseFloat(obj.Costumer.Cenvio.Costo) - obj.Costumer.descuento;
        return obj.total;
    }

    obj.cupon = () => {
        let inpCupon = document.getElementById("inpCupon").value;
        var incpn = document.getElementById("inpCupon");
        var cupon__alert = document.getElementById('alert--cupon');
        var valor_cpn = 10;

        if (obj.Costumer.profile.cupon_nombre != null && obj.Costumer.profile.cupon_nombre != "") {
            const cpn = obj.Costumer.profile.cupon_nombre.split(",");

            cpn.forEach(function (element) {

                if (element.includes("=")) {
                    const cpn_valor = element.split("=");
                    if (inpCupon == cpn_valor[0]) {
                        valor_cpn = cpn_valor[1];
                        inpCupon = element;
                    }
                }

                if (inpCupon == element) {
                    obj.Costumer.Subtotal = (obj.Costumer.Subtotal * (valor_cpn / 100));
                    obj.Costumer.descuento = obj.Costumer.Subtotal; //Descuento envio para prueba credito
                    obj.Costumer.valor_cpn = valor_cpn;
                    btncupon.disabled = true;
                    incpn.disabled = true;
                    btncupon.style.borderColor = "#00ff00";
                    btncupon.style.backgroundColor = "#ccc";
                    cupon__alert.innerHTML = "";
                    obj.Costumer.usercpn = element;
                } else if (inpCupon == "" || inpCupon != element) {
                    cupon__alert.className += " cupon--alert-active";
                    if (incpn.disabled != true) {
                        cupon__alert.innerHTML = "Ingresa un cupón valido";
                    }
                } else {
                    btncupon.style.backgroundColor = "#ccc";
                    btncupon.style.cursor = "default";
                    btncupon.disabled = true;
                    cupon__alert.innerHTML = "Este cupón YA ha sido utilizado"
                    cupon__alert.className += " cupon--alert-active";
                    incpn.disabled = true;
                }

            });
        } else {
            cupon__alert.className += " cupon--alert-active";
            if (incpn.disabled != true) {
                cupon__alert.innerHTML = "Ingresa un cupón valido";
            }
        }
    }

    obj.btnAgregar = (Refaccion) => {
        if (parseInt(Refaccion.Cantidad) < parseInt(Refaccion.Existencias)) {
            Refaccion.Cantidad++;
            obj.Ttotal();
            Refaccion.upd = 1;
            Refaccion.updCLV = Refaccion.Clave;
            Refaccion.n = $_SESSION["CarritoPrueba"]["length"];
            obj.actualizarSession(Refaccion, true);
        }

    }

    obj.btnQuitar = (Refaccion) => {
        Refaccion.Cantidad--;
        obj.Ttotal();
        Refaccion.upd = 1;
        Refaccion.updCLV = Refaccion.Clave;
        Refaccion.n = $_SESSION["CarritoPrueba"]["length"];
        if (Refaccion.Cantidad < 1) {
            obj.btnEliminarRefaccion(Refaccion);
            Refaccion.Cantidad = 1;
        } else {
            obj.actualizarSession(Refaccion, true);
        }

    }

    var btnfacomp = document.querySelector(".pagar--button");
    btnfacomp.disabled = true;
    var tandcheck = document.getElementById('aviso');

    tandcheck.addEventListener('click', function () {
        if (tandcheck.checked) {
            btnfacomp.disabled = false;
        } else {
            btnfacomp.disabled = true;
        }
    });

    var closecotizar = document.getElementById("cotizarclose");
    closecotizar.addEventListener("click", function () {
        $("#mdlcotizar").modal('hide');
    });

    obj.getImagen = (id) => {
        //var url = "https://macromautopartes.com/images/refacciones/";
        var url = "images/refacciones/";
        return url + id + ".webp";
    }

    obj.comprobarDatosFacturacion = (value) => {
        obj.factflag = value == 1 ? true : false;
    }

    obj.btndireccionguardadas = (pag) => {
        localStorage.setItem("pag", pag);
        location.href = "?mod=Profile&opc=" + pag;
    }
    obj.changeCenvio = (Tarifa) => {
        id = obj.Costumer.Cenvio.Costos.find(e => e.Tarifa === Tarifa);
    }

    obj.getDataUser = (data) => {
        $http({
            method: 'POST',
            url: url_getusercampras,
            data: { Compras: data }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.Costumer.profile = res.data.Data.datauser;
                obj.Costumer.dataDomicilio = res.data.Data.datadomicilio
                obj.Costumer.dataFacturacion = res.data.Data.datafacturacion;
                obj.Costumer.Cenvio = res.data.Data.Cenvio;
                obj.cenvio = res.data.Data.Cenvio.Costo;
                localStorage.setItem("id", obj.Costumer.profile.id)
                localStorage.setItem("id_rfc", obj.Costumer.dataFacturacion._id)
                obj.Ttotal();
                obj.dataflag = true;
                // obj.prodCarrito(); funcion en preparacion, Estado: En espera.
            } else {
                toastr.error(res.data.mensaje)
            }
        }, function errorCallback(res) {
            res.data.Data.Cenvio;
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    if ($_SESSION.facturacion == null) {
        obj.Costumer.facturacion = 0;
    } else if ($_SESSION.facturacion == 1) {
        obj.Costumer.facturacion = 1;
    }

    var butccompra = document.querySelector(".clip");
    var butcompra = document.querySelector(".confirmar--pago");
    obj.btnPagar = () => {
        butcompra.disabled = true;
        butccompra.classList.add("animationclip");
        let aut = false;
        if (obj.Costumer.dataDomicilio.Bandera) {
            if ((obj.Costumer.facturacion == "1" && obj.Costumer.dataFacturacion.Bandera) || obj.Costumer.facturacion == "0") {
                if (obj.requiredEnvio) {
                    obj.Costumer.opc = "buy2";
                    obj.Costumer.Cenvio.Servicio = obj.dataCotizador.parcel.weight == 0 ? "ENVIO GRATIS" : obj.Costumer.Cenvio.Servicio;
                    if (obj.Costumer.profile.cupon_nombre != null && obj.Costumer.profile.cupon_nombre != "") {
                        const cpn = obj.Costumer.profile.cupon_nombre.split(",");
                        obj.Costumer.usercpn = cpn.filter(Discpn => Discpn != obj.Costumer.usercpn);
                        obj.Costumer.usercpn = obj.Costumer.usercpn.toString();
                    }
                    obj.Costumer.Medidas = obj.dataCotizador.parcel;
                    obj.ProcesarCompra(obj.Costumer);
                } else {
                    toastr.error("Error: Debes de seleccionar el costo del envio");
                    butccompra.classList.remove("animationclip");
                    butcompra.disabled = false;
                }
            } else {
                toastr.error("Error: no hay datos de facturacion predeterminados");
                butccompra.classList.remove("animationclip");
                butcompra.disabled = false;
            }

        } else {
            toastr.error("Error no tienes una direccion de envio predeterminada");
            butccompra.classList.remove("animationclip");
            butcompra.disabled = false;
        }
    }

    obj.metransfe = () => {
        obj.Costumer.metodoPago = "Transferencia"
        btntransfe.style.borderColor = "var(--primario)";
        btnefectivo.style.borderColor = "var(--gris-light)";
        btncredito.style.borderColor = "var(--gris-light)";
    }
    obj.medeposito = () => {
        obj.Costumer.metodoPago = "Deposito"
        btntransfe.style.borderColor = "var(--gris-light)";
        btnefectivo.style.borderColor = "var(--primario)";
        btncredito.style.borderColor = "var(--gris-light)";
    }
    obj.metarjeta = () => {
        obj.Costumer.metodoPago = "Tarjeta"
        btntransfe.style.borderColor = "var(--gris-light)";
        btnefectivo.style.borderColor = "var(--gris-light)";
        btncredito.style.borderColor = "var(--primario)";
    }

    obj.ProcesarCompra = (data) => {
        $http({
            method: 'POST',
            url: urlCostumer,
            data: { Costumer: data }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                butccompra.classList.remove("animationclip");
                butcompra.disabled = false;
                if (obj.total === 0) {
                    location.href = "?mod=ProcesoCompra&opc=paso3";
                } else if (obj.Costumer.metodoPago === "Deposito" || obj.Costumer.metodoPago === "Transferencia") {
                    obj.openDeposito(res.data.Data);
                    //location.href="?mod=ProcesoCompra&opc=paso3";
                } else if (obj.Costumer.metodoPago === "Tarjeta") {
                    obj.seturl(res.data.data[0]);
                }
            } else {
                setTimeout(() => {
                    butccompra.classList.remove("animationclip");
                    butcompra.classList.remove("btn-danger");
                    butcompra.classList.add("btn-warning");
                    butcompra.innerHTML = '<i class="fas fa-exclamation-triangle">¡Elige metodo de pago!</i>';
                    toastr["info"]("Recuerda seleccionar un metodo de pago.");
                    setTimeout(() => {
                        butcompra.disabled = false;
                        butcompra.innerHTML = "Confirmar Pago";
                        butcompra.classList.remove("btn-warning");
                        butcompra.classList.add("btn-danger");
                    }, 3000);
                }, 2000);
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
            butccompra.classList.remove("animationclip");
            butcompra.disabled = false;
        });
    }

    obj.seturl = (url) => {
        obj.url = $sce.trustAsResourceUrl(url);
        location.href = obj.url;
    }

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
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.openDeposito = (id) => {
        localStorage.setItem("id_pedido", id)
        var popUp = window.open("./Reportes/fichaDeposito/Controller.php");
        if (popUp == null || typeof (popUp) == 'undefined') {
            alert("Por favor Deshabilita el bloqueador de ventanas emergentes");
        }
        location.href = "?mod=ProcesoCompra&opc=paso3";
    }

    obj.btnEliminarRefaccion = (Refaccion) => {
        Swal.fire({
            title: "¿Deseas Eliminar la Refaccion del carrito?",
            showCancelButton: true,
            confirmButtonText: "Eliminar",
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    showConfirmButton: false,
                    title: "¡Eliminado!",
                    text: "El articulo fue eliminado",
                    icon: "success"
                });
                Refaccion.erase = 1;
                Refaccion.borrar = Refaccion.Clave;
                Refaccion.n = $_SESSION["CarritoPrueba"]["length"];
                obj.actualizarSession(Refaccion, true);
            }
        });
    }

    obj.getFechaentrega = (dias) => {
        const arrayDias = ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"];
        const arrayMes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"]
        const fecha = moment().add(dias, 'days')
        return `${arrayDias[fecha.day()]} ${fecha.format("DD")} ${arrayMes[fecha.month()]}`;
    }

    obj.btncotizar = () => {
        obj.flag = true
        obj.btnCotizacion();
        $("#mdlcotizar").modal('show');
    }

    obj.selectenvio = (params) => {
        obj.Costumer.Cenvio.paqueteria = params.provider;
        obj.Costumer.Cenvio.Costo = params.newtotal;
        obj.Costumer.Cenvio.CostoSnIva = params.total_pricing;
        obj.Costumer.Cenvio.enviodias = params.days;
        obj.Costumer.DiaEstimado = obj.getFechaentrega(obj.Costumer.Cenvio.enviodias);
        obj.Costumer.Cenvio.Servicio = params.service_level_name;
        obj.requiredEnvio = true;
        $("#mdlcotizar").modal('hide');
    }

    obj.empaquetar = () => {
        var TotalVolumen = 0;
        var TotalVolumen2 = 0;
        Object.values(obj.session.CarritoPrueba).forEach(e => {
            obj.dataCotizador.parcel.length = parseInt(e.Largo > obj.dataCotizador.parcel.length ? e.Largo : obj.dataCotizador.parcel.length);
            obj.dataCotizador.parcel.width = parseInt(e.Ancho > obj.dataCotizador.parcel.width ? e.Ancho : obj.dataCotizador.parcel.width);
            e.Volumen = (e.Largo * e.Ancho * e.Alto) * e.Cantidad;
            TotalVolumen = e.Volumen + TotalVolumen;
        });
        Object.values(obj.session.CarritoPrueba).forEach(e => {
            if (!e.Enviogratis) {
                obj.dataCotizador.parcel.weight += parseFloat(e.Peso * e.Cantidad);
                obj.dataCotizador.parcel.width = parseInt(e.Ancho > obj.dataCotizador.parcel.width ? e.Ancho : obj.dataCotizador.parcel.width);
                obj.dataCotizador.parcel.height = parseInt(e.Alto > obj.dataCotizador.parcel.height ? e.Alto : obj.dataCotizador.parcel.height);
                obj.dataCotizador.parcel.length = parseInt(e.Largo > obj.dataCotizador.parcel.length ? e.Largo : obj.dataCotizador.parcel.length);
            }

        });
        obj.dataCotizador.parcel.length += parseFloat(2); obj.dataCotizador.parcel.width += parseFloat(2); obj.dataCotizador.parcel.height += parseFloat(2);
        TotalVolumen2 = (obj.dataCotizador.parcel.length * obj.dataCotizador.parcel.width * obj.dataCotizador.parcel.height);
        
        if(TotalVolumen >= 100000 && TotalVolumen < 150000){
            obj.dataCotizador.parcel.length = parseFloat(80); obj.dataCotizador.parcel.width = parseFloat(40); obj.dataCotizador.parcel.height = parseFloat(45);
        }else{
            while(TotalVolumen > TotalVolumen2) {
                if(obj.dataCotizador.parcel.width < 40){
                    obj.dataCotizador.parcel.width += parseFloat(2);
                }

                if(obj.dataCotizador.parcel.width > 40 && obj.dataCotizador.parcel.length > 80 && obj.dataCotizador.parcel.height < 45){
                    obj.dataCotizador.parcel.height += parseFloat(2);
                }

                obj.dataCotizador.parcel.length += parseFloat(2);

                TotalVolumen2 = (obj.dataCotizador.parcel.length * obj.dataCotizador.parcel.width * obj.dataCotizador.parcel.height);
            }
        }
        
        obj.requiredEnvio = obj.dataCotizador.parcel.weight != 0 ? false : true;
    }

    obj.eliminarPaqueterias = (data) => {
        let arrayPaq = ["CARSSA", "SKYDROPX", "JTEXPRESS", "SANDEX", "QUIKEN", "SENDEX", "UPS", "TRACUSA", "TRESGUERRAS"];
        arrayPaq.forEach(e => {
            data = data.filter(paqueteria => paqueteria.provider != e)
        });
        return data
    }

    obj.btnCotizacion = async () => {
        try {
            obj.dataCotizador.zip_to = obj.Costumer.dataDomicilio.data.Codigo_postal;
            const result = await $http({
                method: 'POST',
                url: urlSkydropx,
                data: obj.dataCotizador,
                headers: {
                    'Authorization': token,
                    'Content-Type': "application/json"
                }
            }).then(function successCallback(res) {
                return res

            }, function errorCallback(res) {
                console.error(res)
                console.log("Error: no se realizo la conexion con el servidor");
            });
            obj.cotizador = obj.eliminarPaqueterias(result.data);
            obj.cotizador.forEach(e => {
                e.newtotal = (parseFloat(e.total_pricing)+4.64)+ parseFloat(e.total_pricing*(3.2/100));
            });
            obj.flag = false;

            $scope.$apply();
        } catch (error) {
            return false;
        }
    }

    angular.element(document).ready(function () {
        if (obj.session.autentificacion == undefined && obj.session.autentificacion != 1) {
            location.href = "?mod=login";
        } else {
            obj.getDataUser({ opc: "get", username: obj.session.usr })
            obj.empaquetar();
        }
        obj.eachRefacciones(obj.session.CarritoPrueba);
    });
}

tsuruVolks.controller("ProfileCtrl", ["$scope", "$http", ProfileCtrl]);

function ProfileCtrl($scope, $http) {
    var obj = $scope;
    obj.session = $_SESSION;
    obj.pag = "";
    obj.profile = {};
    obj.Facturacion = { dataFacturacion: [], usocfdi: [], estados: [], Actividad: [{ valor: "Persona Fisica" }, { valor: "Persona Moral" }] };
    obj.Mispedidos = [];

    obj.btnMenulinks = (opc = '') => {
        if (opc != "") {
            location.href = "?mod=Compras";
        } else {
            location.href = "?mod=Profile";
        }
    }

    obj.facturaNo = () => {
        obj.sendFacturacion('none', obj.dataFacturacion)
    }

    obj.inputvalidireccion = () => { //Inicia Verificador de Agregar nueva Dirección.
        let agregar = []; for (var j = 0; j <= 7; j++) {
            agregar[j] = document.getElementById("agregar_" + (j + 1)).value;
        }
        let agregar_div = []; for (var j = 0; j <= 7; j++) {
            agregar_div[j] = document.getElementById("agregar_div" + (j + 1));
        }
        let agregar_lbl = []; for (var j = 0; j <= 7; j++) {
            agregar_lbl[j] = document.getElementById("agregar_lbl" + (j + 1));
        }

        validateEmptyDire(agregar, agregar_div, agregar_lbl);
    }

    function validateEmptyDire(agregar, agregar_div, agregar_lbl) {
        var contador = 0;
        for (var i = 0; i <= 7; i++) {
            if (agregar[i].length == 0) {
                agregar_div[i].style.borderColor = "var(--primario)";
                agregar_lbl[i].style.color = "var(--primario)";
                alertvalid.style.display = "block";
            } else if (agregar[i].length >= 1) {
                agregar_lbl[i].style.color = "var(--negro)";
                agregar_div[i].style.borderColor = "var(--gris-ligth)";

                if (agregar[i].length >= 1) {
                    contador++;
                }
            }
        }
        if (contador == 7) {
            alertvalid.style.display = "none";
            Swal.fire({
                title: "¿Deseas Guardar Domicilio?",
                showDenyButton: true,
                confirmButtonText: "Guardar",
                denyButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ showConfirmButton: false, title: "¡Domicilio Guardado!", icon: "success" });
                    obj.SendDirecciones('add', obj.dataDireccion);
                } else if (result.isDenied) {
                    Swal.fire("Domicilio no guardado", "", "error");
                }
            });
        }
    } //Termina Verificador de Agregar Nueva Dirección.

    obj.inputvalidfactura = () => { //Inicia Verificador de Agregar nueva factura.
        let agregar = []; for (var j = 1; j <= 6; j++) {
            agregar[j] = document.getElementById("Fagregar_" + j).value;
        }
        let agregar_div = []; for (var j = 1; j <= 6; j++) {
            agregar_div[j] = document.getElementById("Fagregar_div" + j);
        }
        let agregar_lbl = []; for (var j = 1; j <= 6; j++) {
            agregar_lbl[j] = document.getElementById("Fagregar_lbl" + j);
        }
        validateEmptyFact(agregar, agregar_div, agregar_lbl);
    }

    function validateEmptyFact(agregar, agregar_div, agregar_lbl) {
        var contador = 0;
        for (var i = 1; i <= 6; i++) {
            if (agregar[i].length == 0 || agregar[i].value == "") {
                agregar_div[i].style.borderColor = "var(--primario)";
                agregar_lbl[i].style.color = "var(--primario)";
                alertvalid1.style.display = "block";
            } else if (agregar[i].length > 1) {
                agregar_lbl[i].style.color = "var(--negro)";
                agregar_div[i].style.borderColor = "var(--gris-ligth)";

                if (agregar[i].length > 1) {
                    contador++;
                }
            }
        }
        if (contador == 6) {
            alertvalid1.style.display = "none";
            Swal.fire({
                title: "¿Deseas Guardar Estos Datos?",
                showDenyButton: true,
                confirmButtonText: "Guardar",
                denyButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ showConfirmButton: false, title: "¡Datos Guardados!", icon: "success" });
                    obj.sendFacturacion('add', obj.dataFacturacion)
                } else if (result.isDenied) {
                    Swal.fire("Los Datos No Fueron Guardados!", "", "error");
                }
            });
        }

    }//Termina Verificador de Agregar nueva Factura.

    /* variables seccion direcciones */
    obj.dataDireccion = { Estatus: 1, Predeterminado: 0 }
    obj.dataFacturacion = { Estatus: 1, Predeterminado: 0 }

    obj.sendProfile = (opc) => {
        $http({
            method: 'POST',
            url: urlProfile,
            data: { profile: { opc: opc, tipo: obj.pag, data: obj.profile } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                toastr.success(res.data.mensaje);
            } else {
                toastr.error(res.data.mensaje);
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getDatosCliente = () => {
        $http({
            method: 'POST',
            url: urlProfile,
            data: { profile: { opc: "buscar", tipo: obj.pag } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.profile = res.data.Data;
                obj.profile.arrayDomicilios.forEach(function (el) {
                    if (el.Predeterminado == 1) {
                        localStorage.setItem("_id_domicilio", el._id);
                    }
                });
                if (!localStorage.getItem("iduser")) {
                    localStorage.setItem("iduser", obj.profile._id)
                }
            } else {
                toastr.error("Error: ");
            }
        }, function errorCallback(res) {
            toastr.info("Info: No se actualizarón elementos necesarios, Actualizar pagina si está no se actualiza sola en los proximos 5segundos.");
            setTimeout(() => {
                location.reload();
            }, 3000);
        });
    }

    /* Termina el modulo de Session y Seguridad */
    /* Inicia modulo de Direcciones */
    obj.addDirecciones = () => {
        obj.btnMenulinks('Direcciones_add');
    }

    obj.SendDirecciones = (opc, data) => {
        $http({
            method: 'POST',
            url: urlProfile,
            data: { profile: { opc: opc, tipo: obj.pag, data: data } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                switch (opc) {
                    case 'add':
                    case 'save':
                        toastr.success(res.data.mensaje);
                        setTimeout(() => {
                            obj.btnMenulinks("Direcciones")
                        }, 100);
                        break;
                    case 'delete':
                    case 'set':
                        toastr.success(res.data.mensaje);
                        obj.getDatosCliente();
                        break;
                    case 'edit':
                        obj.dataDireccion = res.data.Data;
                        obj.Facturacion.estados = res.data.Data2;
                        break;
                }

            } else {
                toastr.error(res.data.mensaje);
            }
        }, function errorCallback(res) {
            toastr.info("No se encontro ningun Domicilio registrado");
        });
    }

    obj.btnGuardarDireccion = (opc) => {
        Swal.fire({
            title: "¿Deseas Guardar Domicilio?",
            showDenyButton: true,
            confirmButtonText: "Guardar",
            denyButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ showConfirmButton: false, title: "¡Guardado!", icon: "success" });
                obj.SendDirecciones(opc, obj.dataDireccion)
            } else if (result.isDenied) {
                Swal.fire("Domicilio no guardado", "", "error");
            }
        });
    }

    obj.btndescartarDomicilio = (id) => {
        Swal.fire({
            title: "¿Deseas Eliminar Domicilio?",
            showDenyButton: true,
            confirmButtonText: "Eliminar",
            denyButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ showConfirmButton: false, title: "Eliminado!", icon: "success" });
                obj.SendDirecciones("delete", { id: id });
                location.reload();
            }
        });
    }

    obj.btneditDomicilio = (id) => {
        //obj.btnMenulinks("Direcciones_edit");
        localStorage.setItem("_id_domicilio", id);
        //location.href = "?mod=Profile&opc=Direcciones_edit&id="+id;
    }

    obj.btnPredeterminado = (id) => {
        obj.SendDirecciones("set", { id_domicilio: id, id: localStorage.getItem("id") });
        location.reload();
        localStorage.setItem("_id_domicilio", id);
    }

    /* Termina modulo de direcciones */

    /* Inicia modulo de Datos de Facturacion */
    obj.addFacturacion = () => {
        location.href = "?mod=Profile&opc=Facturacion_add";
        localStorage.setItem("pag", "Facturacion_add")
    }

    obj.btnEditadatosFacturacion = (id) => {
        localStorage.setItem("id_rfc", id);
        obj.btnMenulinks('Facturacion_edit');
        //obj.sendFacturacion(opc, {_id:id})
    }

    obj.sendFacturacion = (opc = "buscar", data = null) => {
        $http({
            method: 'POST',
            url: urlProfile,
            data: { profile: { opc: opc, tipo: obj.pag, data: data } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {

                switch (opc) {
                    case 'add':
                    case 'save':
                        toastr.success(res.data.mensaje);
                        setTimeout(() => {
                            obj.btnMenulinks("Facturacion")
                        }, 500);
                        break;
                    case 'pre':
                        obj.btnMenulinks("Facturacion");
                        break;
                    case 'none':
                        obj.btnMenulinks("Facturacion");
                        break;
                    case 'del':
                        toastr.success(res.data.mensaje);
                        obj.sendFacturacion();
                        break;
                    case 'edit':
                        obj.Facturacion.dataFacturacion = res.data.Data.RFC;
                        obj.Facturacion.usocfdi = res.data.Data.usoCFDI;

                        break;
                    case 'new':
                        obj.Facturacion.usocfdi = res.data.Data;
                        obj.Facturacion.estados = res.data.Data2;
                        break;
                    default:
                        obj.Facturacion.dataFacturacion = res.data.Data;
                        break;
                }


            } else {
                toastr.error(res.data.mensaje);
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }


    obj.btnGuardarnuevosdatos = (opc) => {
        Swal.fire({
            title: "¿Deseas Guardar Estos Datos?",
            showDenyButton: true,
            confirmButtonText: "Guardar",
            denyButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ showConfirmButton: false, title: "Guardado!", icon: "success" });
                obj.sendFacturacion(opc, obj.dataFacturacion)
            } else if (result.isDenied) {
                Swal.fire("Datos Facturación no guardados", "", "error");
            }
        });
    }

    obj.btnEliminardatosfacturacion = (id, opc) => {
        Swal.fire({
            title: "¿Deseas Eliminar Estos Datos?",
            showDenyButton: true,
            confirmButtonText: "Eliminar",
            denyButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ showConfirmButton: false, title: "Eliminado!", icon: "success" });
                obj.sendFacturacion(opc, { _id: id })
                location.reload();
            }
        });
    }

    obj.btnEditardatosfacturacion = (opc) => {
        Swal.fire({
            title: "¿Deseas Guardar Los Cambios en los Datos?",
            showDenyButton: true,
            confirmButtonText: "Guardar",
            denyButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ showConfirmButton: false, title: "¡Cambios Guardados!", icon: "success" });
                obj.sendFacturacion(opc, obj.Facturacion.dataFacturacion);
            } else if (result.isDenied) {
                Swal.fire("¡Cambios No Guardados!", "", "error");
            }
        });
    }

    obj.btnFacpredetermiando = (id, opc) => {
        obj.sendFacturacion(opc, { _id: id });
    }
    /* Finaliza modulo de Datos de Facturacion */

    /* */

    angular.element(document).ready(function () {
        if ((obj.session.autentificacion == undefined && obj.session.autentificacion != 1)) {
            localStorage.clear();
            location.href = "?mod=login";
        }

        if (!localStorage.getItem("iduser")) {
            obj.getDatosCliente();
        }

        switch (obj.pag) {
            case 'Session':
            case 'Direcciones':
                obj.getDatosCliente();
                break;
            case 'Direcciones_add':
                obj.dataDireccion.id = obj.session.iduser;
                break;
            case 'Direcciones_edit':
                obj.dataDireccion.id_domicilio = localStorage.getItem('_id_domicilio');
                setTimeout(() => {
                    obj.SendDirecciones('edit', obj.dataDireccion);
                }, 100);
                break;
            case 'Facturacion':
                obj.sendFacturacion("buscar", { _id_cliente: localStorage.getItem("iduser") });
                break
            case 'Facturacion_add':
                obj.sendFacturacion("new");
                obj.dataFacturacion._id_cliente = localStorage.getItem("iduser");
                break;
            case 'Facturacion_edit':
                obj.sendFacturacion('edit', { _id: localStorage.getItem("id_rfc") })
                break
            case 'Mispedidos':
                obj.sendMispedidos("buscar", { _id: localStorage.getItem("iduser") })
                break
            case 'Mispedidos_view':
                obj.sendMispedidos("details", { _idpedido: localStorage.getItem("_idPedido") });
                break;
        }
        $(".numero").numeric();
    });
}