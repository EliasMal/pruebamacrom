
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
        Swal.fire({
            title: "¿Deseas Eliminar la Refaccion del carrito?",
            showCancelButton: true,
            confirmButtonText: "Eliminar",
          }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire("Eliminado!", "", "Correctamente");
                Refaccion.erase = 1;
                Refaccion.borrar = Refaccion.Clave;
                Refaccion.n = $_SESSION["CarritoPrueba"]["length"];
                obj.actualizarSession(Refaccion,true);

            }
          });
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
                localStorage.clear();
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

    angular.element(document).ready(function () {
        obj.getCategorias();
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
