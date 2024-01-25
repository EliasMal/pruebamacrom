/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var urlLogin = "./modulo/Login/Ajax/Login.php";
var urlRegistro = "./modulo/Login/Ajax/Registro.php";
tsuruVolks.controller('LoginCtrl', ["$scope", "$http", LoginCtrl]);

function LoginCtrl($scope, $http) {
    var obj = $scope;
    obj.login = {};
    obj.Registro = {};
    obj.Banner = [];
    obj.btnLogin = () => {
        obj.login.opc = "in";
        $http({
            method: 'POST',
            url: urlLogin,
            data: { Login: obj.login }

        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                localStorage.setItem('session', JSON.stringify(res.data.session))
                localStorage.setItem('iduser', res.data.session.iduser)
                localStorage.setItem("acrcupon", 0);
                location.href = "?mod=home";
            } else {
                toastr.error(res.data.mensaje);
                alertvali.style.display = "block";
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnolvide = () => {
        obj.login.opc = "forgot";
        $http({
            method: 'POST',
            url: urlLogin,
            data: { Login: obj.login }

        }).then(function successCallback(res) {
            if (res.data.Olvidado == 1) {
                toastr.success(res.data.mensaje);
            } else {
                toastr.error("Error: el usuario no existe");
            }
        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnRegistrar = (form) => {

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
                console.log(el);
                el.classList.toggle("fa-eye-slash");
                if (inprpass.getAttribute("type") != null) {
                    inprpass.setAttribute("type", "password");
                }
            } else if (el.classList.contains('cpss')) {
                inprcpass.toggleAttribute("type");
                console.log(el);
                el.classList.toggle("fa-eye-slash");
                if (inprcpass.getAttribute("type") != null) {
                    inprcpass.setAttribute("type", "password");
                }
            }
        });
    });

    obj.getBanners = (data) => {
        $http({
            method: 'POST',
            url: "./tv-admin/asset/Modulo/Secciones/webprincipal/Ajax/webprincipal.php",
            data: { imagen: data },
            headers: {
                'Content-Type': undefined
            },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.imagen) {
                    formData.append(m, data.imagen[m]);
                }
                //formData.append("file",data.file);

                return formData;
            }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.Banner = res.data.Data;

            } else {
                toastr.error(res.data.mensaje);
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnAceptoAvisoPrivasidad = () => {
        $("#ModalAviso").modal("hide");
    }

    angular.element(document).ready(function () {
        obj.getBanners({ opc: "get", Categoria: obj.mod, Estatus: 1 });
    });

}
