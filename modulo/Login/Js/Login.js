var urlLogin = "./modulo/Login/Ajax/Login.php";
var urlRegistro = "./modulo/Login/Ajax/Registro.php";
const url_seicom = "https://volks.dyndns.info:444/service.asmx/consulta_art";
var url = "./modulo/home/Ajax/home.php";
var url_session = "./modulo/home/Ajax/session.php";

tsuruVolks.controller('LoginCtrl', ["$scope", "$http", LoginCtrl]);

// Protección de ruta (Redirección si ya está logueado)
if (window.location.search.includes("?mod=login") || window.location.search.includes("?mod=register")) {
    if (localStorage.getItem('iduser') != undefined) {
        location.href = "?mod=home";
    }
}

function LoginCtrl($scope, $http) {
    var obj = $scope;
    
    // Inicialización de variables de scope
    obj.login = {};
    obj.Registro = {};
    obj.SeiData = {}; // Mantengo esto por si lo usas en la vista, aunque ya no dependemos de él para la lógica
    obj.dataflag = true;
    obj.loginError = false;
    obj.intentosRestantes = null;
    obj.cuentaBloqueada = false;
    obj.tiempoRestante = 0;
    obj.mensajeError = "";

    // --- 1. FUNCIÓN MATEMÁTICA SEGURA ---
    function trunc(x, posiciones = 0) {
        // Se usa matemática real para evitar que un número sin decimales rompa el string
        const factor = Math.pow(10, posiciones);
        return Math.trunc(x * factor) / factor;
    }

    // --- 2. LOGIN ASÍNCRONO SEGURO ---
    obj.btnLogin = async function () {
        // 1. FAIL FAST FRONTEND: Si están vacíos, mostramos error y cortamos aquí mismo.
        if (!obj.login.user || !obj.login.password) {
            $scope.$evalAsync(() => {
                obj.loginError = true;
                obj.mensajeError = "Por favor, ingresa tu correo y contraseña.";
            });
            return; // Retorna y cancela todo, ¡no tocamos PHP!
        }
        obj.login.opc = "in";
        obj.dataflag = false; // Bloquea el botón mientras carga

        try {
            const res = await $http({
                method: 'POST',
                url: urlLogin,
                data: { Login: obj.login }
            });

            if (res.data.Bandera == 1) {
                $scope.$evalAsync(() => { obj.loginError = false; });
                
                // ESPERAMOS a que termine TODO el proceso del carrito antes de avanzar
                await obj.prodCarrito(res);
                
                localStorage.setItem('session', JSON.stringify(res.data.session));
                localStorage.setItem('iduser', res.data.session.iduser);
                
                // Validación de seguridad por si no hay domicilio registrado aún
                if (res.data.session.id_domicilio) {
                    localStorage.setItem('_id_domicilio', res.data.session.id_domicilio._id);
                }

                // Ahora sí, redireccionamos con seguridad
                if (document.referrer.includes("?mod=catalogo")) {
                    location.href = document.referrer;
                } else {
                    location.href = "?mod=home";
                }

            } else {
                $scope.$evalAsync(() => {
                    obj.loginError = true;
                    obj.mensajeError = res.data.mensaje || "Usuario o contraseña incorrectos";
                    obj.cuentaBloqueada = res.data.bloqueado == 1 ? true : false;
                    obj.intentosRestantes = res.data.intentos_restantes ?? null;
                    obj.tiempoRestante = res.data.tiempo_restante ?? 0;
                    obj.dataflag = true; // Liberamos el botón
                });
            }
        } catch (error) {
            console.error(error);
            toastr.error("Error en el servidor");
            $scope.$evalAsync(() => { obj.dataflag = true; });
        }
    };

    // --- 3. ACTUALIZACIÓN DE CARRITO SIN CONDICIÓN DE CARRERA ---
    obj.prodCarrito = async function (res) {
        const carrito = res.data.session.CarritoPrueba || [];
        
        // Mapeamos cada producto en una promesa aislada
        const promesas = carrito.map(async (el) => {
            const seiData = await obj.getSeicom(el.Clave);
            
            // Si el servicio falla o no trae info, saltamos este producto
            if (!seiData || !seiData.Table) return;

            let count_prod = 0;
            let promesasInternas = []; // Para agrupar los updates de cada producto

            seiData.Table.forEach(prd => {
                count_prod += prd.existencia;
                let NewPrecio = parseFloat(prd.precio_5 * 1.16);
                NewPrecio = trunc(NewPrecio, 2);
                
                if (el.Precio != NewPrecio) {
                    promesasInternas.push(
                        $http({
                            method: 'POST',
                            url: url,
                            data: { modelo: { opc: "ActPrecio", refaccion: el.Clave, NewPrecio: NewPrecio, home: true } }
                        })
                    );
                }
            });

            if (el.Existencias != count_prod || el.Cantidad > count_prod) {
                promesasInternas.push(
                    $http({
                        method: 'POST',
                        url: url,
                        data: { modelo: { opc: "ActExistencias", refaccion: el.Clave, NewExistencia: count_prod, home: true, Cant: el.Cantidad } }
                    })
                );
            }

            if (count_prod == 0) {
                let Refaccion = {
                    erase: 1,
                    borrar: el.Clave,
                    n: carrito.length
                };
                promesasInternas.push(
                    $http({
                        method: 'POST',
                        url: url_session,
                        data: { modelo: Refaccion }
                    })
                );
            }
            
            // Ejecutamos todos los updates de este producto
            return Promise.all(promesasInternas);
        });

        // Esperamos a que TODOS los productos del carrito terminen su proceso
        await Promise.all(promesas);
    };

    // --- 4. PETICIÓN A SEICOM AISLADA ---
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
            });

            if (result && result.data) {
                const xml = $(result.data).find("string");
                const json = JSON.parse(xml.text());
                return json; // Retornamos la info directo para no cruzar datos en variables globales
            }
        } catch (error) {
            console.error("Error al consultar SEICOM para " + clave, error);
            return null;
        }
    };

    // --- 5. OLVIDÉ MI CONTRASEÑA ---
    obj.btnolvide = () => {
        obj.dataflag = false; // Angular se encarga de bloquear el botón en la vista
        obj.login.opc = "forgot";
        
        $http({
            method: 'POST',
            url: urlLogin,
            data: { Login: obj.login }
        }).then(function successCallback(res) {
            if (res.data.Olvidado == 1) {
                toastr.success(res.data.mensaje);
            } else {
                console.log(res);
                toastr.error("Error en el servidor");
            }
            obj.dataflag = true;
        }, function errorCallback(res) {
            console.log(res);
            toastr.error("Error en el servidor");
            obj.dataflag = true;
        });
    };

    // --- 6. REGISTRO ---
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
                    obj.dataflag = true;
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizó la conexión con el servidor");
                obj.dataflag = true;
            });
        } else {
            toastr.error("Error: Las contraseñas no coinciden");
        }
    };

    obj.chkModalAviso = () => {
        if (obj.Registro.Aviso) {
            $("#ModalAviso").modal("show");
        }
    };

    obj.btnAceptoAvisoPrivasidad = () => {
        $("#ModalAviso").modal("hide");
    };
}

// =========================================================
// SCRIPTS FUERA DEL CONTROLADOR (Manejo de UI de Contraseña)
// =========================================================

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

document.addEventListener("DOMContentLoaded", () => {
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
                    if(iconPass) { iconPass.textContent = "❌"; iconPass.style.color = "#e74c3c"; }
                    break;
                case 2:
                    strengthBar.style.width = "50%";
                    strengthBar.style.background = "#f1c40f";
                    if(iconPass) { iconPass.textContent = "⚠️"; iconPass.style.color = "#f1c40f"; }
                    break;
                case 3:
                case 4:
                    strengthBar.style.width = strength === 3 ? "75%" : "100%";
                    strengthBar.style.background = "#27ae60";
                    if(iconPass) { iconPass.textContent = "✔️"; iconPass.style.color = "#27ae60"; }
                    break;
            }
        }

        function checkMatch(){
            if(confirmInput.value.length === 0){
                matchStatus.textContent = "";
                if(iconConfirm) iconConfirm.textContent = "";
                return;
            }

            if(passInput.value === confirmInput.value){
                matchStatus.textContent = "Las contraseñas coinciden";
                matchStatus.className = "match-status match-yes";
                if(iconConfirm) { iconConfirm.textContent = "✔️"; iconConfirm.style.color = "#27ae60"; }
            } else {
                matchStatus.textContent = "Las contraseñas no coinciden";
                matchStatus.className = "match-status match-no";
                if(iconConfirm) { iconConfirm.textContent = "❌"; iconConfirm.style.color = "#e74c3c"; }
            }
        }
    }
});