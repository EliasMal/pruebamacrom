var url = "./Ajax/login.php";
tsuruVolks.controller('loginCtrl',['$scope','$http', loginCtrl]);

function loginCtrl($scope, $http){
    var obj = $scope;
    obj.login = {};
    obj.data = { opc:"" };
    var mantenimiento;

    obj.dataflag = true;
    obj.loginError = false;
    obj.intentosRestantes = null;
    obj.cuentaBloqueada = false;
    obj.tiempoRestante = 0;
    obj.mensajeError = "";

    obj.btnlogin = async function(){
        if (!obj.login.user || !obj.login.password) {
            obj.loginError = true;
            obj.mensajeError = "Por favor, ingresa tu usuario y contraseña.";
            return;
        }

        obj.dataflag = false;
        
        try {
            const res = await $http({
                method: 'POST',
                url: url,
                data: {login: obj.login}
            });

            if(res.data.Bandera == 1){
                $scope.$evalAsync(() => { obj.loginError = false; });
                localStorage.setItem('session', JSON.stringify(res.data.session));
                localStorage.setItem('ultimoAcceso', res.data.session.ultimoAcceso);
                toastr.success(res.data.mensaje + obj.login.user);
                
                setTimeout(() => {
                    window.location.href = "../asset/";
                }, 500);
            } else {
                $scope.$evalAsync(() => {
                    obj.loginError = true;
                    obj.mensajeError = res.data.mensaje || "Usuario o contraseña incorrectos";
                    obj.cuentaBloqueada = res.data.bloqueado == 1 ? true : false;
                    obj.intentosRestantes = res.data.intentos_restantes ?? null;
                    obj.tiempoRestante = res.data.tiempo_restante ?? 0;
                    obj.dataflag = true;
                });
                toastr.error(obj.mensajeError);
            }
        } catch (error) {
            toastr.error("Error: no se realizó la conexión con el servidor");
            $scope.$evalAsync(() => { obj.dataflag = true; });
        }
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


