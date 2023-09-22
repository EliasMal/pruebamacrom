'use strict'

const urlProfile = "./modulo/Profile/Ajax/Profile.php";
const urlComprobante = "./modulo/Profile/Ajax/uploadfile.php";
const urlMonedero = "./tv-admin/asset/Modulo/Control/Monedero/Ajax/Monedero.php";

tsuruVolks.controller("ProfileCtrl",["$scope","$http", ProfileCtrl]);

function ProfileCtrl($scope, $http){
    var obj = $scope;
    obj.No_Pedidos=0;
    obj.session = $_SESSION;
    obj.pag = "";
    obj.profile = {};
    obj.msjprofiledisplay = false;
    obj.Facturacion = {dataFacturacion: [], usocfdi: [], Actividad:[{valor: "Persona Fisica"}, {valor: "Persona Moral"}]};
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
        console.log(obj.paginador, ini, fin,obj.No_Pedidos)
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
            location.href = "?mod=Profile&opc="+opc;
        }else{
            location.href = "?mod=Profile";
        }
        
        //localStorage.setItem("pag",opc);
    }
    
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
                console.log(obj.Mispedidos);
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
                classEstatus = "orange";
                break;
            case 1:
            case 5:
                classEstatus = "success";
                break;
            case 2:
            case 3:
            case 4:
                classEstatus = "yellow";
            break;
            case 6:
                classEstatus = "red";
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
        console.log(obj.dataFactura)
        $("#Mdlfiles").modal('show');
    } 
    
    obj.btnDownloadZip = () =>{
        let zip = new JSZip();
        let promise = new Blob([$.get(obj.dataFactura.xml)],{type: "text/plain;charset=utf-8"});
        
         zip.file(obj.dataFactura.xml,promise)
         promise = new Blob([$.get(obj.dataFactura.pdf)],{type: "text/plain;charset=utf-8"});
         console.log(promise)
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
                console.log(obj.profile, "Entro a este dato");
                if(!localStorage.getItem("iduser")){
                    localStorage.setItem("iduser",obj.profile._id)
                }else{
                    console.log(localStorage.getItem("iduser"));
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
            obj.SendDirecciones("delete", {id:id})
        }
    }

    obj.btneditDomicilio = (id) =>{
        obj.btnMenulinks("Direcciones_edit");
        localStorage.setItem("_id_domicilio",id);
        //location.href = "?mod=Profile&opc=Direcciones_edit&id="+id;
    }

    obj.btnPredeterminado = (id) => {
        if(confirm("¿EStas seguro de Establecer como predterminado este domicilio?")){
            obj.SendDirecciones("set", {id_domicilio:id, id: localStorage.getItem("id")})
        }
    }

    /* Termina modulo de direcciones */

    /* Inicia modulo de Datos de Facturacion */
    obj.addFacturacion = () =>{
        location.href = "?mod=Profile&opc=Facturacion_add";
        localStorage.setItem("pag","Facturacion_add")
    }

    obj.btnEditadatosFacturacion = (id) =>{
        localStorage.setItem("id_rfc",id)
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
        }
    }

    obj.btnEditardatosfacturacion = (opc) => {
        if(confirm("¿Estas seguro de modificar los datos de facturación?")){
            obj.sendFacturacion(opc, obj.Facturacion.dataFacturacion);
        }
    }

    obj.btnFacpredetermiando = (id, opc) => {
        if(confirm("¿Estas seguro de activar los datos como predeterminado?")){
            obj.sendFacturacion(opc, {_id: id});
        }
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
            console.log(resultado.data);
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
                console.log(obj.monedero);
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

        console.log(obj.pag)
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
                obj.SendDirecciones('edit',obj.dataDireccion)
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
                console.log("Entro",localStorage.getItem("id_rfc"));
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