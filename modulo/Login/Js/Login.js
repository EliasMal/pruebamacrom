/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
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
    obj.login = {};
    obj.Registro = {};
    obj.SeiData = {};
    obj.dataflag = true;
    var Refaccion = {};
    var count_prod;

    obj.btnLogin = function () {
        obj.login.opc = "in";
        $http({
            method: 'POST',
            url: urlLogin,
            data: { Login: obj.login }

        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
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
                toastr.error(res.data.mensaje);
                alertvali.style.display = "block";
            }
        }, function errorCallback(res) {
            toastr.error("Error: el usuario no existe");
        });
    }

    obj.prodCarrito = function (res) {
        res.data.session.CarritoPrueba.forEach(el => {
            obj.getSeicom(el.Clave).then(token => {
                count_prod = 0;
                obj.SeiData.Table.forEach(prd => {
                    count_prod = count_prod + prd.existencia;
                });
                if (el.Existencias != count_prod) {
                    $http({
                        method: 'POST',
                        url: url,
                        data: { modelo: { opc: "ActExistencias", refaccion: el.Clave, NewExistencia: count_prod, home: true } },
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
                toastr.error("Error: el usuario no existe");
                butonolv.classList.remove("non-active");
            }
        }, function errorCallback(res) {
            obj.dataflag = true;
            toastr.error("Error: el usuario no existe");
            butonolv.classList.remove("non-active");
        });
    }

    obj.btnRegistrar = (form) => {
        obj.dataflag = false;
        butonolv.classList.add("non-active");
        obj.Registro.FechaCreacion = new Date();
        obj.Registro.FechaModificacion = new Date();
        obj.Registro.ultimoaccesso = new Date();
        obj.Registro.inicioacceso = new Date();
        obj.Registro.Estatus = 1;
        if (obj.Registro.pass === obj.Registro.Cpass) {
            $http({
                method: 'POST',
                url: urlRegistro,
                data: { Registro: obj.Registro }

            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.dataflag = true;
                    butonolv.classList.remove("non-active");
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

    var inprpass = document.querySelector("#inprpass");
    var inprcpass = document.querySelector("#inprcpass");
    document.querySelectorAll(".pass_ver").forEach(el => {
        el.addEventListener("click", e => {
            if (el.classList.contains('pss')) {
                inprpass.toggleAttribute("type");
                el.classList.toggle("fa-eye-slash");
                if (inprpass.getAttribute("type") != null) {
                    inprpass.setAttribute("type", "password");
                }
            } else if (el.classList.contains('cpss')) {
                inprcpass.toggleAttribute("type");
                el.classList.toggle("fa-eye-slash");
                if (inprcpass.getAttribute("type") != null) {
                    inprcpass.setAttribute("type", "password");
                }
            }
        });
    });

    obj.btnAceptoAvisoPrivasidad = () => {
        $("#ModalAviso").modal("hide");
    }

    // angular.element(document).ready(function () {
        

    // });

}
