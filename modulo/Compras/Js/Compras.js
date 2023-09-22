'use strict'

var url_getusercampras = "./modulo/Compras/Ajax/Compras.php";
const urlProfile = "./modulo/Profile/Ajax/Profile.php";
var urlCostumer = "./modulo/ProcesoCompra/Ajax/ProcesoCompra.php";
var url_session ="./modulo/home/Ajax/session.php";
var urlhome = "./modulo/home/Ajax/home.php";
const url_monedero = "./tv-admin/asset/Modulo/Control/Monedero/Ajax/Monedero.php";
//const urlSkydropx = "https://api-demo.skydropx.com/v1/quotations"
const urlSkydropx = "https://api.skydropx.com/v1/quotations";
//const token = "Token token=SuOZQz5IrqceQbJmBqQfAo4PMQvNKMCh2PtXOKMfKM0t";
const token = "Token token=fInE1ArT8CJfaR2wkznA5hXSNCMSXs7vitsCFeM98Pct";

tsuruVolks.controller('ComprasCtrl', ["$scope","$http","$sce", ComprasCtrl])
.directive('convertToString', function() {
    return {
        require: 'ngModel',
        link: function($scope, element, attrs, ngModel) {
                ngModel.$parsers.push(function(value) {
                        return parseFloat(value);
                });
                ngModel.$formatters.push(function(value) {
                        return '' + value;
                });
        }
    };
}); 

function ComprasCtrl($scope, $http , $sce){
    var obj = $scope;
    obj.session = $_SESSION;
    obj.Costumer = {Cenvio:{costo:0}, aviso:false ,facturacion:0, profile:{}, metodoPago:"", cart: $_SESSION.CarritoPrueba, dataFacturacion: {}, dataDomicilio: {}, descuento:0};
    obj.cenvio = 0
    obj.Numproducts = obj.session.CarritoPrueba? Object.keys(obj.session.CarritoPrueba).length:0;
    obj.factflag = false;
    obj.monedero = {Importe:0, aplicado:false};
    obj.cotizador = [];
    obj.flag = false;
    obj.requiredEnvio = false;
    obj.dataCotizador = { 
        zip_from: "28000", 
        zip_to: "60174", 
        parcel: { 
            weight: 0, //peso
            height: 0, //altura
            width: 0, //ancho
            length: 0 } //largo
        }
    
            obj.RefaccionDetalles = (_id)=>{
        window.open("?mod=catalogo&opc=detalles&_id="+_id,"_self");
    }

    obj.subtotal = ()=>{
        obj.Costumer.Subtotal = 0
        for(let e in obj.session.CarritoPrueba){
            obj.Costumer.Subtotal += (obj.session.CarritoPrueba[e].Cantidad * obj.session.CarritoPrueba[e].Precio);
        }
        return obj.Costumer.Subtotal;
    }

    obj.Ttotal = ()=>{
        if(obj.Costumer.Cenvio.Envio == 'L'){
            obj.Costumer.Cenvio.Costo =  obj.dataCotizador.parcel.weight == 0? 0 : obj.cenvio;
            obj.requiredEnvio = true;
        }
        obj.total = obj.Costumer.Subtotal + parseFloat(obj.Costumer.Cenvio.Costo)-obj.Costumer.descuento;
        return obj.total;
    }
    //prueba cupon local
obj.cupon = () =>{
    let inpCupon = document.getElementById("inpCupon").value;
    var incpn = document.getElementById("inpCupon"); 
    var acrcupo = localStorage.getItem("acrcupon");
    if(inpCupon == obj.session.cupon && obj.session.acreditacion == 0 && acrcupo != 1){
        obj.Costumer.Subtotal= (obj.Costumer.Subtotal*(10/100));
        obj.Costumer.descuento = obj.Costumer.Subtotal;
        btncupon.disabled = true;
        incpn.disabled = true;
        btncupon.style.borderColor="#00ff00";
        btncupon.style.backgroundColor="#ccc";
        
    } else{
        btncupon.style.borderColor="#de0007";
        incpn.style.borderColor="#de0007";
    }

}
//fin de prueba cupon local


    obj.aplicarMonedero = ()=>{
        if(obj.monedero.Importe>0){
            obj.Costumer.descuento = (obj.monedero.Importe - obj.total) >= 0? obj.total: obj.monedero.Importe
            obj.monedero.aplicado = true
            obj.monedero.Importe -= obj.total;
            if(obj.monedero.Importe<=0){
                obj.monedero.Importe = 0;
            }
        }
        
    }

    obj.btnAgregar = (p)=>{
        
        if(p.Cantidad < p.Existencias){
            p.Cantidad++; 
            obj.Ttotal();
            obj.actualizarSession(p,false);
        }
        
        
        //obj.getCotizacionEnvio();
    }

    obj.btnQuitar = (p)=>{
    
        p.Cantidad--;
        obj.Ttotal();
        obj.actualizarSession(p);
        if (p.Cantidad<=1) {
            p.Cantidad=1;
        }
    }

     // Ventana modal
var modal = document.getElementById("ventanaModal");
var modal1 = document.getElementById("ventanaModal1");
var modal2 = document.getElementById("ventanaModal2");
var modal3 = document.getElementById("ventanaModal3");
var modal4 = document.getElementById("ventanaModal4");

// Botón que abre el modal
var boton = document.getElementById("abrirModal");
var boton1 = document.getElementById("abrirModal1");
var boton2 = document.getElementById("abrirModal2");
var boton3 = document.getElementById("abrirModal3");
var boton4 = document.getElementById("abrirModal4");

// Hace referencia al elemento <span> que tiene la X que cierra la ventana
var span = document.getElementsByClassName("cerrar")[0];
var span1 = document.getElementsByClassName("cerrar1")[0];
var span2 = document.getElementsByClassName("cerrar2")[0];
var span3 = document.getElementsByClassName("cerrar3")[0];
var span4 = document.getElementsByClassName("cerrar4")[0];
// Cuando el usuario hace click en el botón, se abre la ventana
boton.addEventListener("click",function() {
  modal.style.display = "block";
});
boton1.addEventListener("click",function() {
  modal1.style.display = "block";
});
boton2.addEventListener("click",function() {
  modal2.style.display = "block";
});
boton3.addEventListener("click",function() {
  modal3.style.display = "block";
});
boton4.addEventListener("click",function() {
  modal4.style.display = "block";
});

// Si el usuario hace click en la x, la ventana se cierra
span.addEventListener("click",function() {
  modal.style.display = "none";
});
span1.addEventListener("click",function() {
  modal1.style.display = "none";
});
span2.addEventListener("click",function() {
  modal2.style.display = "none";
});
span3.addEventListener("click",function() {
  modal3.style.display = "none";
});
span4.addEventListener("click",function() {
  modal4.style.display = "none";
});
// Si el usuario hace click fuera de la ventana, se cierra.
window.addEventListener("click",function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
});
window.addEventListener("click",function(event) {
  if (event.target == modal1) {
    modal1.style.display = "none";
  } else if (event.target == modal2) {
     modal2.style.display = "none";   
  }else if (event.target == modal3) {
     modal3.style.display = "none";   
  }else if (event.target == modal4){
    modal4.style.display = "none";
  }
});
    
 var btnfacomp = document.getElementById('btnfacomp');
 btnfacomp.disabled = true;
 var tandcheck = document.getElementById('aviso');

 tandcheck.addEventListener('click', function(){
    if (tandcheck.checked) {
        btnfacomp.disabled = false; 
    } else {
         btnfacomp.disabled = true; 
    }
 });


    obj.getImagen = (id)=>{
        var url = "images/refacciones/";
        return  url+id+".webp";
    }

    obj.comprobarDatosFacturacion = (value)=>{
        obj.factflag = value == 1? true: false;
    }

    obj.btndireccionguardadas = (pag) => {
        localStorage.setItem("pag",pag);
        location.href = "?mod=Profile&opc="+pag;
    }
    obj.changeCenvio = (Tarifa) =>{
        id = obj.Costumer.Cenvio.Costos.find(e => e.Tarifa === Tarifa);
    }

    obj.getDataUser = (data) => {
        $http({
            method: 'POST',
            url: url_getusercampras,
            data: {Compras: data}
        }).then(function successCallback(res) {
            
            if(res.data.Bandera == 1){
                obj.Costumer.profile = res.data.Data.datauser;
                obj.Costumer.dataDomicilio = res.data.Data.datadomicilio
                obj.Costumer.dataFacturacion = res.data.Data.datafacturacion;
                obj.Costumer.Cenvio = res.data.Data.Cenvio;
                obj.cenvio = res.data.Data.Cenvio.Costo;
                //obj.Costumer.Cenvio.Costo = res.data.Data.Cenvio.Envio == "N"? res.data.Data.Cenvio.Costos[0].Tarifa: res.data.Data.Cenvio.Costo
                localStorage.setItem("id",obj.Costumer.profile.id)
                localStorage.setItem("id_rfc",obj.Costumer.dataFacturacion._id)
                obj.Ttotal();
            }else{
                toastr.error(res.data.mensaje)
            }
        }, function errorCallback(res) {res.data.Data.Cenvio
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    //Comprobacion Datos Facturación
        obj.Costumer.facturacion = localStorage.getItem("facturaSi");
        if (obj.Costumer.facturacion == 0) {
            facturaNo.style.display="none";
        }else{
            facturaNo.style.display="block";
        }  


        /*if (obj.Costumer.facturacion == "") {
            dfacturanota.style.display="block !important";
        }*/

          obj.facturaNo = ()=>{
            obj.Costumer.facturacion = 0;
            localStorage.setItem("facturaSi", obj.Costumer.facturacion);
            location.reload(); 
        }
    //Termina Comprobacion Datos Facturación

    obj.btnPagar = () => {
        let aut = false;
        if(obj.Costumer.dataDomicilio.Bandera){
            if((obj.Costumer.facturacion=="1" && obj.Costumer.dataFacturacion.Bandera) || obj.Costumer.facturacion=="0"){
                if(obj.requiredEnvio){
                        obj.Costumer.opc="buy2";
                        obj.Costumer.Cenvio.Servicio =  obj.dataCotizador.parcel.weight == 0? "ENVIO GRATIS":obj.Costumer.Cenvio.Servicio ;
                        obj.ProcesarCompra(obj.Costumer);
                }else{
                    toastr.error("Error: Debes de seleccionar el costo del envio");
                }
            }else {
                toastr.error("Error: no hay datos de facturacion predeterminados");
            }
            
        }else{
            toastr.error("Error no tienes una direccion de envio predeterminada");
        }
        let inpCupon = document.getElementById("inpCupon").value; 
        var acrcupo = localStorage.getItem("acrcupon");
        if(inpCupon == obj.session.cupon && obj.session.acreditacion == 0 && acrcupo != 1){
            localStorage.setItem("acrcupon", 1);
        }
    }
obj.metransfe = ()=>{
obj.Costumer.metodoPago = "Transferencia"
btntransfe.style.borderColor="#de0007";
btnefectivo.style.borderColor="#e6e6e6";
btncredito.style.borderColor="#e6e6e6";
}
obj.medeposito = ()=>{
obj.Costumer.metodoPago = "Deposito"
btntransfe.style.borderColor="#e6e6e6";
btnefectivo.style.borderColor="#de0007";
btncredito.style.borderColor="#e6e6e6";
}
obj.metarjeta = ()=>{
obj.Costumer.metodoPago = "Tarjeta"
btntransfe.style.borderColor="#e6e6e6";
btnefectivo.style.borderColor="#e6e6e6";
btncredito.style.borderColor="#de0007";
}
    obj.ProcesarCompra = (data) =>{
        
            $http({
                method: 'POST',
                url: urlCostumer,
                data: {Costumer: data}
            }).then(function successCallback(res) {
                if(res.data.Bandera == 1){
                    if(obj.total === 0 && obj.monedero.aplicado ){
                        location.href="?mod=ProcesoCompra&opc=paso3";
                    }else if(obj.Costumer.metodoPago === "Deposito" || obj.Costumer.metodoPago === "Transferencia" ){
                        obj.openDeposito(res.data.Data);
                        //location.href="?mod=ProcesoCompra&opc=paso3";
                    }else if(obj.Costumer.metodoPago ==="Tarjeta"){
                        obj.seturl(res.data.data[0]);
                    }
                    /*  */
                }else{
                    toastr.error("ERROR");
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
    }

    obj.seturl = (url)=>{
        obj.url = $sce.trustAsResourceUrl(url);
        location.href=obj.url;
    }

    obj.actualizarSession = (Refaccion,opc)=>{
        /*opc? true = elimina la variable de la session, false= no aplica nada*/
        $http({
            method: 'POST',
            url: url_session,
            data: {modelo: Refaccion}

        }).then(function successCallback(res) {
            if(opc){
               location.reload();
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.openDeposito= (id)=>{
        localStorage.setItem("id_pedido", id)
        var popUp = window.open("./Reportes/fichaDeposito/Controller.php");
        if(popUp == null || typeof(popUp)=='undefined'){
            alert("Por favor Deshabilita el bloqueador de ventanas emergentes");
        }
        location.href="?mod=ProcesoCompra&opc=paso3";
        
    }

    obj.btnEliminarRefaccion = (Refaccion)=>{
        if(confirm("¿Esta seguro de eliminar la refaccion del carrito?")){
            Refaccion.erase = 1;
            Refaccion.borrar = Refaccion.Clave;
            Refaccion.n = $_SESSION["CarritoPrueba"]["length"];

            obj.actualizarSession(Refaccion,true);
        }
    }

    obj.getMonedero = async (metodo, params)=>{
        
        try {
            const resultado  = await $http({
                method: metodo,
                url: url_monedero,
                params: params
            }).then(function successCallback(res){
                return res       
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
            
            if(resultado.data.Bandera==1){
                obj.monedero.Importe = resultado.data.Data.Monedero;
                $scope.$apply();
            }else{
                toastr.error(resultado.data.mensaje);  
            } 
        } catch (error) {
            toastr.error(error)
        }
    }

    obj.getFechaentrega = (dias)=>{
        const arrayDias = ["Domingo", "Lunes", "Martes", "Miercoles","Jueves","Viernes","Sabado","Domingo"];
        const arrayMes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"]
        const fecha = moment().add(dias, 'days')
        return `${arrayDias[fecha.day()]} ${fecha.format("DD")} ${arrayMes[fecha.month()]}`;
    }

    obj.btncotizar = ()=>{
        obj.flag = true
        obj.btnCotizacion();
        $("#mdlcotizar").modal('show');
    }

    obj.selectenvio = (params)=>{
        obj.Costumer.Cenvio.paqueteria = params.provider;
        obj.Costumer.Cenvio.Costo = params.total_pricing;
        obj.Costumer.Cenvio.enviodias = params.days;
        obj.Costumer.Cenvio.Servicio = params.service_level_name;
        obj.requiredEnvio = true;
        $("#mdlcotizar").modal('hide');
    }
    
    obj.empaquetar = ()=>{
        Object.values(obj.session.CarritoPrueba).forEach(e =>{
            if(!e.Enviogratis){
                obj.dataCotizador.parcel.weight += parseFloat(e.Peso)
                obj.dataCotizador.parcel.width =  parseInt(e.Ancho > obj.dataCotizador.parcel.width? e.Ancho: obj.dataCotizador.parcel.width);
                obj.dataCotizador.parcel.height += parseInt(e.Alto)
                obj.dataCotizador.parcel.length = parseInt(e.Largo > obj.dataCotizador.parcel.length? e.Largo:  obj.dataCotizador.parcel.length);
            }
        })
        obj.requiredEnvio = obj.dataCotizador.parcel.weight != 0? false: true;
        
    }

    obj.eliminarPaqueterias = (data)=>{
       let arrayPaq = ["CARSSA", "SKYDROPX", "AMPM", "SANDEX", "ESTAFETA", "UPS"];
       arrayPaq.forEach(e=>{
            data = data.filter(paqueteria => paqueteria.provider != e)
       })
       return data
    }

    obj.btnCotizacion = async()=>{
        try {
            obj.dataCotizador.zip_to = obj.Costumer.dataDomicilio.data.Codigo_postal;
            const result = await $http({
                method: 'POST',
                    url: urlSkydropx,
                    data: obj.dataCotizador,
                    headers:{
                        'Authorization': token,
                        'Content-Type':  "application/json"                    
                    }
                }).then(function successCallback(res){
                    return res
                    
                }, function errorCallback(res){
                    console.error(res)
                    console.log("Error: no se realizo la conexion con el servidor");
                    
            }); 
            obj.cotizador = obj.eliminarPaqueterias(result.data);
            obj.flag = false
            
            $scope.$apply();
        } catch (error) {
            return false;
        }
    }

    angular.element(document).ready(function () {
        if(obj.session.autentificacion==undefined && obj.session.autentificacion!=1){
            location.href = "?mod=login";
        }else{
            let params = {opc:"Monedero", idCliente: localStorage.getItem("iduser")}
            obj.getDataUser({opc:"get",username:obj.session.usr})
            obj.getMonedero('GET',params)
            obj.empaquetar();
        }
    });
}

tsuruVolks.controller("ProfileCtrl",["$scope","$http", ProfileCtrl]);

function ProfileCtrl($scope, $http){
    var obj = $scope;
    obj.No_Pedidos=0;
    obj.session = $_SESSION;
    obj.pag = "";
    obj.profile = {};
    obj.msjprofiledisplay = false;
    obj.Facturacion = {dataFacturacion: [], usocfdi: [], estados: [], Actividad:[{valor: "Persona Fisica"}, {valor: "Persona Moral"}]};
    obj.Carrito ={carrito:[]};
    obj.Mispedidos = [];
    obj.dataFactura = {}
    obj.wizard = {preparacion:false, transito: false, proceso: false, entregado: false}
    obj.comprobantefile = {};
    obj.paginador = {currentPage : 0, pages:[], pageSize:5}
    obj.paginador2 = {page:0, limit:15}
    obj.monedero = {Importe:0, detalles:[],totalrecords:0}

    /* Paginacion */
    obj.configPages = ()=>{
        obj.paginador.pages.length=0;
        var ini = obj.paginador.currentPage - 4;
        var fin = obj.paginador.currentPage + 5;
        if(ini < 1){
            ini = 1;
            if (Math.ceil(obj.No_Pedidos / obj.paginador.pageSize) > 10)
                fin = 10;
            else
                fin = Math.ceil(obj.No_Pedidos / obj.paginador.pageSize);
        }else{
            if (ini >= Math.ceil(obj.No_Pedidos / obj.paginador.pageSize) - 10) {
                ini = Math.ceil(obj.No_Pedidos / obj.paginador.pageSize) - 10;
                fin = Math.ceil(obj.No_Pedidos / obj.paginador.pageSize);
            }
        }
        if (ini < 1) ini = 1;
        for (var i = ini; i <= fin; i++) {
            obj.paginador.pages.push({
                no: i, p: (obj.paginador.pageSize * i) - obj.paginador.pageSize
            });
        }
    }

    obj.nextPage = ()=>{
        obj.paginador.currentPage = obj.paginador.currentPage + 1;
        obj.configPages();
        obj.sendMispedidos("buscar",{_id: localStorage.getItem("iduser")},obj.paginador.currentPage * obj.paginador.pageSize,obj.paginador.pageSize )
    }
    
    obj.lastPage = ()=>{
        obj.paginador.currentPage = obj.paginador.currentPage - 1;
        obj.configPages();
        obj.sendMispedidos("buscar",{_id: localStorage.getItem("iduser")},obj.paginador.currentPage * obj.paginador.pageSize,obj.paginador.pageSize )
    }

    obj.setPage = function(a) {
        obj.paginador.currentPage = a.no - 1;
        obj.configPages();
        obj.sendMispedidos("buscar",{_id: localStorage.getItem("iduser")},a.p,obj.paginador.pageSize )
    };

    /* Termina Paginacion */
    
    obj.btnMenulinks = (opc='') => {
        if(opc!=""){
            location.href = "?mod=Compras";
        }else{
            location.href = "?mod=Profile";
        }
        
        //localStorage.setItem("pag",opc);
    }



    //Inicia Verificador de Agregar nueva Dirección.
    obj.inputvalidireccion = ()=>{
        validateEmptyDire(inpaddncalle.value, inpaddnumext.value, inpaddnumint.value, inpaddcol.value, inpaddcp.value, inpaddcity.value, inpaddest.value, inpaddtel.value);
    }

    function validateEmptyDire(inpaddncalle, inpaddnumext, inpaddnumint, inpaddcol, inpaddcp, inpaddcity, inpaddest, inpaddtel){
         if (inpaddncalle.length == 0) {
            divaddcalle.style.borderColor="#de0007";
            lbladdcalle.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            divaddcalle.style.borderColor="#666";
            lbladdcalle.style.color="black";
        }

        if (inpaddnumext.length == 0 && inpaddnumint.length == 0) {
            divaddnumext.style.borderColor="#de0007";
            lbladdnumext.style.color="#de0007";
            //
            divaddnumint.style.borderColor="#de0007";
            lbladdnumint.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            divaddnumext.style.borderColor="#666";
            lbladdnumext.style.color="black";
            //
            divaddnumint.style.borderColor="#666";
            lbladdnumint.style.color="black";
        }

        if (inpaddcol.length == 0) {
            divaddcol.style.borderColor="#de0007";
            lbladdcol.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            divaddcol.style.borderColor="#666";
            lbladdcol.style.color="black";
        }

        if (inpaddcp.length == 0) {
            divaddcp.style.borderColor="#de0007";
            lbladdcp.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            divaddcp.style.borderColor="#666";
            lbladdcp.style.color="black";
        }

        if (inpaddcity.length == 0) {
            divaddcity.style.borderColor="#de0007";
            lbladdcity.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            divaddcity.style.borderColor="#666";
            lbladdcity.style.color="black";
        }

        if (inpaddest.length == 0) {
            divaddest.style.borderColor="#de0007";
            lbladdest.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            divaddest.style.borderColor="#666";
            lbladdest.style.color="black";
        }

        if (inpaddtel.length == 0) {
            divaddtel.style.borderColor="#de0007";
            lbladdtel.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            divaddtel.style.borderColor="#666";
            lbladdtel.style.color="black";
        }

        if (inpaddncalle.length > 0 && inpaddcol.length > 0 && inpaddcp.length > 0 && inpaddcity.length > 0 && inpaddest.length > 0 && inpaddtel.length > 0) {
            alertvalid.style.display="none";
            if(confirm("¿Estas seguro de guardar el domicilio?")){
            obj.SendDirecciones('add', obj.dataDireccion);
        }

        }else{ 
            alertvalid.style.display="block";
        }

    }

    //Termina Verificador de Agregar Nueva Dirección.



    //Inicia Verificador de Agregar nueva factura.
    obj.inputvalidfactura = ()=>{
        validateEmptyFact(primerInput.value, segundoInput.value, terceroInput.value, cuartoInput.value, quintoInput.value, sextoInput.value, novenoInput.value);
    }

    function validateEmptyFact(valueInput,valueInput1,valueInput2,valueInput3,valueInput4,valueInput5,valueInput6,novenoInput){
        if (valueInput.length == 0) {
            dveline1.style.borderColor="#de0007";
            Agrfc.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            dveline1.style.borderColor="#666";
            Agrfc.style.color="black";
        }
        //2
        if (valueInput1.length == 0) {
            dveline.style.borderColor="#de0007";
            Agrs.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            dveline.style.borderColor="#666";
            Agrs.style.color="black";
        }
        //3
        if (valueInput2.length == 0) {
            dveline3.style.borderColor="#de0007";
            Agrdm.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            dveline3.style.borderColor="#666";
            Agrdm.style.color="black";
        }
        //4
        if (valueInput3.length == 0) {
            dveline4.style.borderColor="#de0007";
            Agrcol.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            dveline4.style.borderColor="#666";
            Agrcol.style.color="black";
        }
        //5
         if (valueInput4.length == 0) {
            dveline5.style.borderColor="#de0007";
            Agrcp.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            dveline5.style.borderColor="#666";
            Agrcp.style.color="black";
        }
        //6
         if (valueInput5.length == 0) {
            dveline6.style.borderColor="#de0007";
            Agrciu.style.color="#de0007";
           // toastr.error("Error: no se han llenado todos los espacios requeridos");
        } else{
            dveline6.style.borderColor="#666";
            Agrciu.style.color="black";
        }
        //7
        //9
        /*if (novenoInput == null) {
            alert("Selecciona Actividad");
            agraem.style.color="#de0007";
        }else{
            alert("Camaron");
        }*/
        //comprobacion
        if (valueInput.length > 0 && valueInput1.length > 0 && valueInput2.length > 0 && valueInput3.length > 0 && valueInput4.length > 0 && valueInput5.length > 0) {
            alertvalid1.style.display="none";
            if(confirm("¿Estas seguro de dar de alta estos datos?")){
            obj.sendFacturacion('add', obj.dataFacturacion)
        }
           
        }else{
            alertvalid1.style.display="block";
        }

    }
    //Termina Verificador de Agregar neuva Factura.


    /*Seccion para el modulo de mis pedidos */
    obj.setWizard = (estatus)=>{
        switch(estatus){
            case 2:
                obj.wizard.preparacion = true;
            break;
            case 3:
                obj.wizard.preparacion = true;
                obj.wizard.transito = true;
            break;
            case 4:
                obj.wizard.preparacion = true;
                obj.wizard.transito = true;
                obj.wizard.proceso = true;
            break;
            case 5:
                obj.wizard.preparacion = true;
                obj.wizard.transito = true;
                obj.wizard.proceso = true;
                obj.wizard.entregado = true;
            break;
        }
    }
    obj.sendMispedidos = (opc="buscar", data=null, x=0, y=obj.paginador.pageSize) =>{
        $http({
            method: 'POST',
            url: urlProfile,
            data: {profile: {opc: opc, tipo: obj.pag, data: data, x:x, y:y}}
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                if(opc=="buscar"){
                    obj.No_Pedidos = res.data.Data.No_pedidos;
                    res.data.Data.Pedidos.forEach( e => {
                        e.Acreditado = parseInt(e.Acreditado);
                        e.class = obj.getcolorEstatus(e.Acreditado);
                    })
                    obj.configPages();
                    obj.Mispedidos = res.data.Data.Pedidos;
                   
                }
               
                if(opc=="details"){
                    obj.Mispedidos = res.data.Data;
                    obj.Mispedidos.Acreditado = parseInt(obj.Mispedidos.Acreditado); 
                    obj.setWizard(obj.Mispedidos.Acreditado);
                    
                }
                if(opc=="DeleteComp"){
                    if(res.data.Bandera === 1){
                        obj.Mispedidos.isFileComprobante = false;
                        angular.element('#comprobante').val(null)
                        toastr.success(res.data.mensaje);
                    }else{
                        toastr.error(res.data.mensaje);
                    }
                    
                }
            }else{
                toastr.error(res.data.mensaje);    
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnDeleteComp = (e)=>{
        e.preventDefault();     
        if(confirm("Estas seguro de eliminar el comprobante de pago")){
            obj.sendMispedidos("DeleteComp",{_idpedido: localStorage.getItem("_idPedido")});
        }
    }

    obj.btnverMispedidos = (_idPedido)=>{
        localStorage.setItem("_idPedido",_idPedido);
        obj.btnMenulinks("Mispedidos_view");
    }

    obj.getcolorEstatus = (estatus)=>{
        let classEstatus = "";
        switch(estatus){
            case 0:
                classEstatus = "badge-secondary";
                break;
            case 1:
            case 5:
                classEstatus = "badge-success";
                break;
            case 2:
            case 3:
            case 4:
                classEstatus = "badge-warning";
            break;
            case 6:
                classEstatus = "badge-danger";
            break;
        }
        return classEstatus;
    }

    obj.showModalFile = (data)=>{
        obj.dataFactura = {
            Rfc: data.Rfc,
            Razon: data.Razonsocial,
            usoCFDI: data.Descripcion,
            xml: "Public/Facturas/" + data.archivoxml,
            pdf: "Public/Facturas/" + data.archivopdf
        }
        $("#Mdlfiles").modal('show');
    } 
    
    obj.btnDownloadZip = () =>{
        let zip = new JSZip();
        let promise = new Blob([$.get(obj.dataFactura.xml)],{type: "text/plain;charset=utf-8"});
        
         zip.file(obj.dataFactura.xml,promise)
         promise = new Blob([$.get(obj.dataFactura.pdf)],{type: "text/plain;charset=utf-8"});
         zip.file(obj.dataFactura.pdf,promise)

    }

    obj.btnuploadComprobante = ()=>{
        $http({
            method: 'POST',
            url: urlComprobante,
            data: {profile: {opc: "upload", _idpedido: localStorage.getItem("_idPedido"), file: obj.comprobantefile}},
            headers:{
                'Content-Type': undefined
            },
            transformRequest: function(data){
                var formData = new FormData();
                for(var m in data.profile){
                    formData.append(m, data.profile[m]);
                }
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera === 1) {
                obj.Mispedidos.isFileComprobante = true;
                obj.Mispedidos.comprobante = res.data.Data.comprobante;
                toastr.success(res.data.mensaje);
            }else{
                toastr.error(res.data.mensaje);    
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnComprobanteCompra = (_idPedido)=>{
        localStorage.setItem("_idPedido",_idPedido);
        window.open("./Reportes/ComprobantePago/Controller.php");
        // window.open("./Reportes/ComprobantePago/Html/ComproPago.php");
    }

    obj.btnRegresarviewPedidos = ()=>{
       window.location.href="?mod=Profile&opc=Mispedidos";
    }

    obj.btnCancelarPedido = ()=>{
        if(confirm("¿Estas seguro de cancelar el pedido?")){
            obj.sendMispedidos("CancelPedido",{_idpedido: localStorage.getItem("_idPedido")})
        }
    }
    /*Finaliza seccion mis pedidos */

    /* variables seccion direcciones */
    obj.dataDireccion = {Estatus: 1, Predeterminado: 0}
    obj.dataFacturacion = {Estatus: 1, Predeterminado: 0}

    /* seccion para modulo Session y seguridad */
    obj.btnGuardarSession = (press) => {
        if(confirm("Estas seguro de Actualizar los datos")){
           if(press == 1){
                obj.sendProfile("profile");
           }else if(press == 2){
                if(obj.profile.Nuevapass === obj.profile.Confirmarpass){
                    obj.msjprofiledisplay =false;
                    obj.sendProfile("password")
                }else{
                    obj.msjprofiledisplay =true;
                    toastr.error("Las contraseñas no coinciden");
                }
           }
        }
    }

    obj.sendProfile = (opc)=> {
        $http({
            method: 'POST',
            url: urlProfile,
            data: {profile: {opc: opc, tipo: obj.pag,  data: obj.profile}}
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                toastr.success(res.data.mensaje);
            }else{
                toastr.error(res.data.mensaje);    
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.getDatosCliente = () =>{
        $http({
            method: 'POST',
            url: urlProfile,
            data: {profile: {opc: "buscar", tipo: obj.pag}}
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.profile = res.data.Data;
                if(!localStorage.getItem("iduser")){
                    localStorage.setItem("iduser",obj.profile._id)
                }
            }else{
                toastr.error("Error: ");    
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    /* Termina el modulo de Session y Seguridad */
    /* Inicia modulo de Direcciones */
    obj.addDirecciones = () =>{
        obj.btnMenulinks('Direcciones_add');
    }

    obj.SendDirecciones = (opc, data) =>{
        $http({
            method: 'POST',
            url: urlProfile,
            data: {profile: {opc: opc, tipo: obj.pag,  data: data}}
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                
                switch(opc){
                    case 'add':
                    case 'save':
                        toastr.success(res.data.mensaje);
                        setTimeout(()=>{
                            obj.btnMenulinks("Direcciones")
                        },500);
                    break;
                    case 'delete':
                    case 'set':
                        toastr.success(res.data.mensaje);
                        obj.getDatosCliente();
                    break;
                    case 'edit':
                        obj.dataDireccion = res.data.Data;
                        obj.Facturacion.estados = res.data.Data2;
                        obj.Carrito.carrito = res.data.Data3;
                    break;
                }
                
                
            }else{
                toastr.error(res.data.mensaje);    
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }
    
    obj.btnGuardarDireccion = (opc) =>{
        if(confirm("¿Estas seguro de guardar el domicilio?")){
            obj.SendDirecciones(opc, obj.dataDireccion)
        }
    }

    obj.btndescartarDomicilio = (id) =>{
        if(confirm("¿Estas seguro de Eliminar el domicilio?")){
            obj.SendDirecciones("delete", {id:id});
            location.reload();
        }
    }

    obj.btneditDomicilio = (id) =>{
        //obj.btnMenulinks("Direcciones_edit");
        localStorage.setItem("_id_domicilio",id);
        //location.href = "?mod=Profile&opc=Direcciones_edit&id="+id;
    }

    obj.btnPredeterminado = (id) => {
        obj.SendDirecciones("set", {id_domicilio:id, id: localStorage.getItem("id")});
        location.reload();
        localStorage.setItem("_id_domicilio",id);
    }

    /* Termina modulo de direcciones */

    /* Inicia modulo de Datos de Facturacion */
    obj.addFacturacion = () =>{
        location.href = "?mod=Profile&opc=Facturacion_add";
        localStorage.setItem("pag","Facturacion_add")
    }

    obj.btnEditadatosFacturacion = (id) =>{
        localStorage.setItem("id_rfc",id);
        obj.btnMenulinks('Facturacion_edit');
        //obj.sendFacturacion(opc, {_id:id})
    }

    obj.sendFacturacion = (opc="buscar", data=null ) => {
        $http({
            method: 'POST',
            url: urlProfile,
            data: {profile: {opc: opc, tipo: obj.pag,  data: data}}
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                
                switch(opc){
                    case 'add':
                    case 'save':
                        toastr.success(res.data.mensaje);
                        setTimeout(()=>{
                            obj.btnMenulinks("Facturacion")
                        },500);
                    break;
                    case 'pre':
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
                        obj.Carrito.carrito = res.data.Data3;
                    break;
                    default:
                        
                        obj.Facturacion.dataFacturacion = res.data.Data;
                    break;
                }
                
                
            }else{
                toastr.error(res.data.mensaje);    
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    

    obj.btnGuardarnuevosdatos = (opc) => {
        if(confirm("¿Estas seguro de dar de alta estos datos?")){
            obj.sendFacturacion(opc, obj.dataFacturacion)
        }
    }


    obj.btnEliminardatosfacturacion = (id, opc) => {
        if(confirm("¿Estas seguro de eliminar los datos?")){
            obj.sendFacturacion(opc, {_id:id})
            location.reload();
        }
    }

    obj.btnEditardatosfacturacion = (opc) => {
        if(confirm("¿Estas seguro de modificar los datos de facturación?")){
            obj.sendFacturacion(opc, obj.Facturacion.dataFacturacion);
        }
    }
    obj.btnFacpredetermiando = (id, opc) => {
            obj.sendFacturacion(opc, {_id: id});
            obj.Costumer.facturacion = 1;
            localStorage.setItem("facturaSi", obj.Costumer.facturacion);
    }
    /* Finaliza modulo de Datos de Facturacion */

    /* Comienza modulo de monedero*/
    obj.sendMonedero = async function (metodo,params=null){
        try {
            const resultado  = await $http({
                method: metodo,
                url: urlMonedero,
                params: params
            }).then(function successCallback(res){
                return res       
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if(resultado.data.Bandera==1){
                switch(params.opc){
                    case 'Detalles':
                        obj.monedero.Importe = resultado.data.Data.Monedero;
                        obj.monedero.detalles = resultado.data.Data.History;
                        obj.monedero.totalrecords = resultado.data.Data.NoMonedero;
                    break;
                    case 'history':
                        obj.monedero.detalles = resultado.data.Data.History;
                        obj.monedero.totalrecords = resultado.data.Data.NoMonedero;
                    break;
                }
                
                $scope.$apply();
            }else{
                toastr.error(resultado.data.mensaje);  
            } 
        } catch (error) {
            toastr.error(error)
        }    
    }

    obj.btnNext = ()=>{
        obj.paginador2.page += obj.paginador2.limit
        let params = {opc:"history", idCliente: localStorage.getItem("iduser"), page:obj.paginador2.page, limit: obj.paginador2.limit }
        obj.sendMonedero('GET',params)
       
    }

    obj.bntPrevios = ()=>{
        obj.paginador2.page -= obj.paginador2.limit
        if(obj.paginador2.page<0){
            obj.paginador2.page = 0
        }
        let params = {opc:"history", idCliente: localStorage.getItem("iduser"), page: obj.paginador2.page, limit: obj.paginador2.limit}
        obj.sendMonedero('GET',params)
        
    }
    /* */

    angular.element(document).ready(function () {
        if((obj.session.autentificacion == undefined && obj.session.autentificacion!=1)){
            localStorage.clear();
            location.href = "?mod=login";
        }
        
        
        if(!localStorage.getItem("iduser")){
            obj.getDatosCliente(); 
        }
        
        switch(obj.pag){
            case 'Session':
            case 'Direcciones':
                obj.getDatosCliente();
            break;
            case 'Direcciones_add':
                obj.dataDireccion.id = localStorage.getItem("iduser");
            break;
            case 'Direcciones_edit':
                obj.dataDireccion.id_domicilio = localStorage.getItem('_id_domicilio');
                obj.SendDirecciones('edit',obj.dataDireccion);
            break;
            case 'Facturacion':
                obj.sendFacturacion("buscar",{_id_cliente:localStorage.getItem("iduser")});
            break
            case 'Facturacion_add':
                obj.sendFacturacion("new");
                obj.dataFacturacion._id_cliente = localStorage.getItem("iduser");
            break;
            case 'Facturacion_edit':
                obj.sendFacturacion('edit',{_id:localStorage.getItem("id_rfc")})
            break
            case 'Mispedidos':
                obj.sendMispedidos("buscar",{_id: localStorage.getItem("iduser")})
            break
            case 'Mispedidos_view':
                obj.sendMispedidos("details",{_idpedido: localStorage.getItem("_idPedido")});
            break;  
            case 'Monedero':
                
                let $params = {opc:"Detalles", idCliente: localStorage.getItem("iduser")}
                obj.sendMonedero('GET',$params);
                break;
        }
        $(".numero").numeric();
    });
}