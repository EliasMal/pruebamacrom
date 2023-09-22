
var url_session ="./modulo/home/Ajax/session.php";
var url = "./modulo/home/Ajax/home.php";
var urlLogin = "./modulo/Login/Ajax/Login.php";

var urlcotizar = "./modulo/Compras/Ajax/Compras.php";

tsuruVolks.controller('CabeceraCtrl', ["$scope","$http","$sce","vcRecaptchaService", CabeceraCtrl])
.controller('FooterCtrl', ["$scope","$http", FooterCtrl])
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

function CabeceraCtrl($scope,$http, $sce,vcRecaptchaService){
    var obj = $scope;
    obj.session = $_SESSION;
    obj.user = (obj.session.autentificacion!=undefined && obj.session.autentificacion==1)? true:false;
    obj.Numproducts = obj.session.CarritoPrueba? Object.keys(obj.session.CarritoPrueba).length:0;
    obj.login = {};
    obj.Costumer = {};
    obj.tipoPago={
        value:"",
        estatus:false
    };
    obj.Banner = [];
    obj.mod;
    obj.url = "";
    obj.msgContacto = false;
    obj.flagenvio = false;
    obj.cotizacion;
    obj.Data = {};

    toastr.options = {
        "progressBar": true,
    }
    
    obj.getImagen = (id)=>{
        var url = "images/refacciones/";
        return  url+id+".webp";
    }
    obj.recapchatKey = "6Le-C64UAAAAAMlSQyH3lu6aXLIkzgewZlVRgEam";
    obj.Contacto = {};

     
       obj.subtotal = ()=>{
        obj.Costumer.Subtotal = 0
        for(let e in obj.Data.Carrito){
            obj.Costumer.Subtotal += (obj.Data.Carrito[e].Cantidad * obj.Data.Carrito[e].Precio);
        }
        return obj.Costumer.Subtotal;
        }

    obj.slcenvio = ()=>{
        //obj.session.Cenvio.costo = parseFloat( obj.session.Cenvio.costo);
        id = obj.cotizacion.find(e => e.Tarifa === parseFloat(obj.session.Cenvio.costo))
        obj.session.Cenvio.Servicio = id.Servicio;
        
        obj.actualizarSession(obj.session.Cenvio,false);
        
    }
    
    //eliminar refaccion.

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
    
    obj.btnEliminarRefaccion = (Refaccion)=>{
        if(confirm("¿Esta seguro de eliminar la refaccion del carrito?")){
            Refaccion.erase = 1;
            Refaccion.borrar = Refaccion.Clave;
            Refaccion.n = $_SESSION["CarritoPrueba"]["length"];
            obj.actualizarSession(Refaccion,true);
        }
    }
    //eliminar refaccion

    obj.getCategorias = async()=>{
        try {
            const result = await $http({
                method: 'POST',
                url: url,
                data: {modelo: {opc: "buscar", tipo: "Categorias", home:true}},
            }).then(function successCallback(res) {
                return res
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
            if(result){
                if (result.data.Bandera == 1) {
                    obj.Data = result.data.Data;
                }
            }  
            $scope.$apply();
        } catch (error) {
            toastr.error(error)
        }
        
    }

    obj.btnLogin = ()=>{
        location.href="?mod=login";
    }
    obj.btnRegister = ()=>{
        location.href="?mod=register";
    }
    
        obj.RefaccionDetalles = (_id)=>{
        window.open("?mod=catalogo&opc=detalles&_id="+_id,"_self");
    }

    obj.btnLogout= ()=>{
        obj.login.opc="out";
        
        $http({
            method: 'POST',
            url: urlLogin,
            data: {Login: obj.login}

        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                location.href = "?mod=home";
            }else{
                toastr.error(res.data.mensaje);
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnPerfil = () => {
       location.href="?mod=Profile&opc=Direcciones";
    }


    /* botones del proceso de la compra*/
    obj.btnProcesarCompra = ()=>{
        if(obj.session.Cenvio.costo != 0){
            location.href="?mod=ProcesoCompra";
        }else{
            
            toastr.warning("No has elegido el costo de envio", "Importante");
            
        }
        
    }
    
    obj.btnPaso2 = ()=>{
        location.href="?mod=ProcesoCompra&opc=paso2";
    }
    
    obj.btnPaso3 = ()=>{
        obj.tipoPago.opc = "buy";
        //window.open("./Reportes/fichaDeposito/Controller.php?_id=19");
         $http({
            method: 'POST',
            url: urlCostumer,
            data: {Costumer: obj.tipoPago}

        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                if(obj.tipoPago.value==="Deposito"){
                    obj.openDeposito(res.data.Data);
                    //location.href="?mod=ProcesoCompra&opc=paso3";
                }else if(obj.tipoPago.value==="Tarjeta"){
                    obj.seturl(res.data.data[0]);
                }
            }else{
                toastr.error(res.data.mensaje);

            }
            //$scope.$apply();
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });  
    }
    obj.openDeposito= (id)=>{
        
        var popUp = window.open("./Reportes/fichaDeposito/Controller.php?_id="+id);
        if(popUp == null || typeof(popUp)=='undefined'){
            alert("Por favor Deshabilita el bloqueador de ventanas emergentes");
        }
            location.href="?mod=ProcesoCompra&opc=paso3";
        
    }


    /* obj.seturl = (url)=>{
        obj.url = $sce.trustAsResourceUrl(url);
        location.href=obj.url;
    } */

    obj.sendPost = (data)=>{
        $http({
            method: 'POST',
            url: "https://wppsandbox.mit.com.mx/gen",
            data: 'xml='+ data,
            headers:{
                'cache-control': 'no-cache',
                'Content-Type':  "application/x-www-form-urlencoded"
                                
            },

        }).then(function successCallback(res) {
            
        }, function errorCallback(res) {
            //toastr.error("Error: no se realizo la conexion con el servidor");
        });  
    }

    obj.btnAcreditar = ()=>{
        location.href="?mod=ProcesoCompra&opc=paso3";
    }
    
    obj.btnHome = ()=>{
        location.href="?mod=home";
    }
    
    
    obj.opcTipopago = ()=>{
        obj.tipoPago.estatus = true;
        
    }
    
    obj.getBanners = (data)=>{
        $http({
            method: 'POST',
                url: "./tv-admin/asset/Modulo/Secciones/webprincipal/Ajax/webprincipal.php",
                data: {imagen:data},
                headers:{
                    'Content-Type': undefined
                },
                transformRequest: function(data){
                    var formData = new FormData();
                    for(var m in data.imagen){
                        formData.append(m, data.imagen[m]);
                    }
                    //formData.append("file",data.file);
                    
                    return formData;
                }
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.Banner = res.data.Data;
                    
                }else{
                    toastr.error(res.data.mensaje);
                }
    
            }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
            });
    }

    obj.enviarContacto = ()=>{
        
        if(vcRecaptchaService.getResponse() === ""){
            alert("Verifica que eres humano");
        }else{
            obj.Contacto.recapRespond = vcRecaptchaService.getResponse();
           
            $http({
                method: 'POST',
                    url: "./modulo/Contacto/Ajax/Contacto.php",
                    data: obj.Contacto
                }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        obj.msgContacto = true;
                    }
        
                }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
                });
        }
    }

    obj.getCotizacionEnvio = ()=>{
        $http({
            method: 'POST',
                url: urlcotizar,
                data: {opc: "cotizar"}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.cotizacion = res.data.Data;
                }
    
            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    angular.element(document).ready(function () {
        /* if(obj.user){
            obj.getCustomer();
        } */
        if(obj.session.Cenvio != undefined){
            if(obj.mod ==="Compras" && obj.session.Cenvio.Envio == "N"){
                obj.flagenvio = true;
                obj.getCotizacionEnvio();
             }else{
                 obj.flagenvio = false;
             }
        }
        
        obj.getCategorias();

        obj.getBanners({opc:"get", Categoria: obj.mod, Estatus:1});
        if(obj.mod === "ProcesoCompra" && obj.session.autentificacion==undefined && obj.session.autentificacion!=1){
            location.href = "?mod=login";
        }
    });
}

function FooterCtrl($scope,$http){
    var obj = $scope;
    obj.categorias = [];
    
    obj.getCategorias = ()=>{
        $http({
            method: 'POST',
            url: url,
            data: {modelo: {opc: "buscar", tipo: "Categorias"}},
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) {
                    formData.append(m, data.modelo[m]);
                }
                //formData.append("file",data.file);
                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.categorias = res.data.Data.Categorias;
            }


        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }
    
    

    angular.element(document).ready(function () {
        obj.getCategorias();
        
        
    });
}
