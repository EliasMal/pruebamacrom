'use strict';

const urlLogin = "./Ajax/login.php";
const urlHome = "../asset/Modulo/Home/Ajax/Home.php";

tsuruVolks.controller('loginCtrl', ['$scope', '$http', loginCtrl]);

function loginCtrl($scope, $http) {
    var obj = $scope;

    // =========================================================
    // INICIALIZACIÓN DE VARIABLES Y BANDERAS
    // =========================================================
    obj.login = {};
    obj.data = { opc: "" };
    
    // Banderas de estado UI
    obj.dataflag = true;
    obj.loginError = false;
    obj.mensajeError = "";
    
    // Banderas de Seguridad (Bloqueo de intentos)
    obj.cuentaBloqueada = false;
    obj.intentosRestantes = null;
    obj.tiempoRestante = 0;
    
    // Banderas de Mantenimiento
    var mantenimiento;
    obj.modoMantenimientoActivo = false; 
    obj.forzarLogin = false;

    // =========================================================
    // FUNCIÓN PRINCIPAL
    // =========================================================
    obj.btnlogin = async function() {
        if (!obj.login.user || !obj.login.password) {
            obj.loginError = true;
            obj.mensajeError = "Por favor, ingresa tu usuario y contraseña.";
            toastr.warning(obj.mensajeError);
            return;
        }

        obj.dataflag = false;
        try {
            const res = await $http({
                method: 'POST',
                url: urlLogin,
                data: { login: obj.login }
            });

            if (res.data.Bandera == 1) {
                $scope.$evalAsync(() => { obj.loginError = false; });
                
                localStorage.setItem('session', JSON.stringify(res.data.session));
                localStorage.setItem('ultimoAcceso', res.data.session.ultimoAcceso);
                toastr.success(res.data.mensaje + obj.login.user);
                
                setTimeout(() => {
                    window.location.href = "../asset/";
                }, 500);

            } else {
                const textoError = res.data.mensaje || "Usuario o contraseña incorrectos";
                
                $scope.$evalAsync(() => {
                    obj.loginError = true;
                    obj.mensajeError = textoError;
                    obj.cuentaBloqueada = (res.data.bloqueado == 1);
                    obj.intentosRestantes = res.data.intentos_restantes ?? null;
                    obj.tiempoRestante = res.data.tiempo_restante ?? 0;
                    obj.dataflag = true;
                });
                
                toastr.error(textoError);
            }
        } catch (error) {
            toastr.error("Error: no se realizó la conexión con el servidor");
            $scope.$evalAsync(() => { obj.dataflag = true; });
        }
    };

    // =========================================================
    // FUNCIONES DE SEGUNDO PLANO (Background Checks)
    // =========================================================
    
    // Consulta si el Sistema está en Mantenimiento
    obj.usrCON = () => {
        obj.data.opc = "loginM";
        $http({
            method: 'POST',
            url: urlHome,
            data: { home: obj.data }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                mantenimiento = res.data.Usuarios;
                obj.modoMantenimientoActivo = (mantenimiento == 1) ? true : false;
            } else {
                if (res.data.mensaje && res.data.mensaje.trim() !== "") {
                    toastr.error(res.data.mensaje);
                }
            }
        }, function errorCallback(res) {
            console.error("Error de conexión al verificar mantenimiento");
        });
    };

    // Limpia basura de sesiones anteriores si detecta que no hay sesión activa
    obj.isonline = () => {
        obj.data.opc = "isonline";
        $http({
            method: 'POST',
            url: urlHome,
            data: { home: obj.data }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                localStorage.clear();
            } else {
                if (res.data.mensaje && res.data.mensaje.trim() !== "") {
                    toastr.error(res.data.mensaje);
                }
            }
        }, function errorCallback(res) {
            console.error("Error de conexión en isonline");
        });
    };
    
    // =========================================================
    // EJECUCIÓN CARGAR LA PÁGINA
    // =========================================================
    angular.element(document).ready(function() {
        obj.usrCON();
        
        setTimeout(() => {
            if (localStorage) {
                obj.isonline();
            }
        }, 100);
    });
}