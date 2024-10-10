
var url = "./Ajax/login.php";
tsuruVolks.controller('loginCtrl',['$scope','$http', loginCtrl]);

function loginCtrl($scope,$http){
    var obj = $scope;
    obj.login = {};
    obj.data = {
        opc:""
   }
   var mantenimiento;

    obj.btnlogin = function(){
        $http({
            method: 'POST',
                url: url,
                data: {login: obj.login}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    localStorage.setItem('session', JSON.stringify(res.data.session))
                    localStorage.setItem('ultimoAcceso', res.data.session.ultimoAcceso)
                    toastr.success(res.data.mensaje + obj.login.user);
                    window.location.href="../asset/";
                }else{
                    toastr.error(res.data.mensaje);
                }
                
                
            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
        
    }

    obj.usrCON =()=>{
        obj.data.opc= "loginM";
        $http({
            method: 'POST',
                url: "../asset/Modulo/Home/Ajax/Home.php",
                data: {home:obj.data}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    mantenimiento = res.data.Usuarios;
                    if(mantenimiento == 1){
                        document.querySelector(".alerta-mantenimiento").style.display = "flex";
                    }
                }else{
                    toastr.error(res.data.mensaje);
                }
                
            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.isonline =()=>{
        obj.data.opc= "isonline";
        $http({
            method: 'POST',
                url: "../asset/Modulo/Home/Ajax/Home.php",
                data: {home:obj.data}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    localStorage.clear();
                }else{
                    toastr.error(res.data.mensaje);
                }
                
                
            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }
    
    angular.element(document).ready(function(){
        obj.usrCON();
        setTimeout(() => {
            if(localStorage){
                obj.isonline();
            }
        }, 100);
    });
}


