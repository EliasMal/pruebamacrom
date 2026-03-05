var urlLogin = "./modulo/Login/Ajax/Login.php";
var urlRegistro = "./modulo/Login/Ajax/Registro.php";
const url_seicom = "https://volks.dyndns.info:444/service.asmx/consulta_art";
var url = "./modulo/home/Ajax/home.php";
var url_session = "./modulo/home/Ajax/session.php";

tsuruVolks.controller('LoginCtrl', ["$scope", "$http", LoginCtrl]);
if (window.location.href.includes("?mod=login") || window.location.href.includes("?mod=register")) {
    if (localStorage.getItem('iduser') != undefined) {
        location.href = "?mod=home";
    }
}
function LoginCtrl($scope, $http) {
    var obj = $scope;
    obj.intentosRestantes = null;
    obj.cuentaBloqueada = false;    
    obj.login = {};
    obj.Registro = {};
    obj.SeiData = {};
    obj.dataflag = true;
    var Refaccion = {};
    var count_prod;
    obj.loginError = false;
    obj.intentosRestantes = null;
    obj.cuentaBloqueada = false;
    obj.tiempoRestante = 0;

    obj.btnLogin = function () {
        obj.login.opc = "in";
        $http({
            method: 'POST',
            url: urlLogin,
            data: { Login: obj.login }

        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                $scope.loginError = false;
                obj.prodCarrito(res);
                localStorage.setItem('session', JSON.stringify(res.data.session));
                localStorage.setItem('iduser', res.data.session.iduser);
                localStorage.setItem('_id_domicilio', res.data.session.id_domicilio._id);
                setTimeout(() => {
                    if (document.referrer.includes("?mod=catalogo")) {
                        location.href = document.referrer;
                    } else {
                        location.href = "?mod=home";
                    }
                }, 100);

            } else {
                obj.loginError = true;

                obj.cuentaBloqueada = res.data.bloqueado == 1 ? true : false;
                obj.intentosRestantes = res.data.intentos_restantes ?? null;
                obj.tiempoRestante = res.data.tiempo_restante ?? 0;
            }
        }, function errorCallback(res) {
            console.log(res);
            toastr.error("Error en el servidor");
        });
    }

    obj.prodCarrito = function (res) {
        res.data.session.CarritoPrueba.forEach(el => {
            obj.getSeicom(el.Clave).then(token => {
                count_prod = 0;
                obj.SeiData.Table.forEach(prd => {
                    count_prod = count_prod + prd.existencia;
                    NewPrecio = parseFloat(prd.precio_5 * 1.16);
                    NewPrecio = trunc(NewPrecio, 2);
                    if(el.Precio != NewPrecio){
                        $http({
                            method: 'POST',
                            url: url,
                            data: { modelo: { opc: "ActPrecio", refaccion: el.Clave, NewPrecio: NewPrecio, home: true } },
                        }).then(function successCallback(res) {
                            console.log("Update Exitoso");
                        }, function errorCallback(res) {
                            toastr.error("Error: no se realizo la conexion con el servidor");
                        });
                    }
                });
                if (el.Existencias != count_prod || el.Cantidad > count_prod) {
                    $http({
                        method: 'POST',
                        url: url,
                        data: { modelo: { opc: "ActExistencias", refaccion: el.Clave, NewExistencia: count_prod, home: true, Cant: el.Cantidad } },
                    }).then(function successCallback(res) {
                        console.log("Update Exitoso");
                    }, function errorCallback(res) {
                        toastr.error("Error: no se realizo la conexion con el servidor");
                    });
                }
                if (count_prod == 0) {
                    Refaccion.erase = 1;
                    Refaccion.borrar = el.Clave;
                    Refaccion.n = res.data.session.CarritoPrueba["length"];
                    $http({
                        method: 'POST',
                        url: url_session,
                        data: { modelo: Refaccion }
                    }).then(function successCallback(res) {
                    }, function errorCallback(res) {
                        toastr.error("Error: no se realizo la conexion con el servidor");
                    });
                }
            });

        });

    }

    function trunc(x, posiciones = 0) { /*Funcion para truncar numeros decimales a solo 2 digitos despus del punto*/
        var s = x.toString()
        var l = s.length
        var decimalLength = s.indexOf('.') + 1
        var numStr = s.substr(0, decimalLength + posiciones)
        return Number(numStr)
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
    var butonolv = document.querySelector(".btnolvide");
    obj.btnolvide = () => {
        obj.dataflag = false;
        butonolv.classList.add("non-active");
        obj.login.opc = "forgot";
        $http({
            method: 'POST',
            url: urlLogin,
            data: { Login: obj.login }

        }).then(function successCallback(res) {
            if (res.data.Olvidado == 1) {
                toastr.success(res.data.mensaje);
                obj.dataflag = true;
                butonolv.classList.remove("non-active");
    
            } else {
                obj.dataflag = true;
                console.log(res);
                toastr.error("Error en el servidor");
                butonolv.classList.remove("non-active");
            }
        }, function errorCallback(res) {
            obj.dataflag = true;
            console.log(res);
            toastr.error("Error en el servidor");
            butonolv.classList.remove("non-active");
        });
    }

    obj.btnRegistrar = (form) => {
        obj.Registro.FechaCreacion = new Date();
        obj.Registro.FechaModificacion = new Date();
        obj.Registro.ultimoaccesso = new Date();
        obj.Registro.inicioacceso = new Date();
        obj.Registro.Estatus = 1;
        if (obj.Registro.pass === obj.Registro.Cpass) {
            obj.dataflag = false;
            $http({
                method: 'POST',
                url: urlRegistro,
                data: { Registro: obj.Registro }

            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.dataflag = true;
                    location.href = "?mod=home";
                } else {
                    toastr.error(res.data.mensaje);
                }


            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        } else {
            toastr.error("Error: Las contraseñas no coinciden");
        }
    }

    obj.chkModalAviso = () => {
        if (obj.Registro.Aviso) {
            $("#ModalAviso").modal("show");
        }

    }

    obj.btnAceptoAvisoPrivasidad = () => {
        $("#ModalAviso").modal("hide");
    }

    // angular.element(document).ready(function () {
        

    // });

}

function togglePassword(id, btn){
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');

    if(input.type === "password"){
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

const passInput = document.getElementById("inprpass");
const confirmInput = document.getElementById("inprcpass");
const strengthBar = document.getElementById("strength-bar");
const matchStatus = document.getElementById("match-status");
const iconPass = document.getElementById("icon-pass");
const iconConfirm = document.getElementById("icon-confirm");

if(passInput){

    passInput.addEventListener("input", function(){
        updateStrength();
        checkMatch();
    });

    confirmInput.addEventListener("input", checkMatch);

    function updateStrength(){
        const val = passInput.value;
        let strength = 0;

        if(val.length >= 8) strength++;
        if(val.match(/[a-z]/) && val.match(/[A-Z]/)) strength++;
        if(val.match(/[0-9]/)) strength++;
        if(val.match(/[\W]/)) strength++;

        switch(strength){
            case 0:
            case 1:
                strengthBar.style.width = "25%";
                strengthBar.style.background = "#e74c3c";
                iconPass.textContent = "❌";
                iconPass.style.color = "#e74c3c";
                break;
            case 2:
                strengthBar.style.width = "50%";
                strengthBar.style.background = "#f1c40f";
                iconPass.textContent = "⚠️";
                iconPass.style.color = "#f1c40f";
                break;
            case 3:
            case 4:
                strengthBar.style.width = strength === 3 ? "75%" : "100%";
                strengthBar.style.background = "#27ae60";
                iconPass.textContent = "✔️";
                iconPass.style.color = "#27ae60";
                break;
        }
    }

    function checkMatch(){
        if(confirmInput.value.length === 0){
            matchStatus.textContent = "";
            iconConfirm.textContent = "";
            return;
        }

        if(passInput.value === confirmInput.value){
            matchStatus.textContent = "Las contraseñas coinciden";
            matchStatus.className = "match-status match-yes";
            iconConfirm.textContent = "✔️";
            iconConfirm.style.color = "#27ae60";
        } else {
            matchStatus.textContent = "Las contraseñas no coinciden";
            matchStatus.className = "match-status match-no";
            iconConfirm.textContent = "❌";
            iconConfirm.style.color = "#e74c3c";
        }
    }
}
