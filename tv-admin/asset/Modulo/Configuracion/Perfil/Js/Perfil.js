'use strict'

var url = "./Modulo/Configuracion/Perfil/Ajax/Perfil.php";
var url2 = "./Modulo/Configuracion/Perfil/Ajax/Perfil_data.php";
tsuruVolks
        .controller('PerfilCtrl', ["$scope", "$http", PerfilCtrl]);

function PerfilCtrl($scope, $http){
    
    var obj = $scope;
    obj.tipoUsuario = [{value:"root",descripcion:"Root"}, {value:"admin",descripcion:"Administrador"},{value:"Web",descripcion:"Web"},{value:"user",descripcion:"Usuario"}];
    obj.perfil;
    obj.img;
    obj.newpass;

    obj.btnChangepass = () => {
        $("#changapass").modal("show");
    }

    obj.getNewpass = () => {
        obj.newpass = Math.random().toString(36).substring(2, 8);
    }

    obj.gettipousuario = function(tipousuario){
        
        if (tipousuario != undefined){
            var id = obj.tipoUsuario.find(tipo=> tipo.value === tipousuario);
            
            return id.descripcion;
        }
    }

    obj.btnRegresar =() =>{
        location.href = document.referrer;
    }

    obj.getUsuario = () => {
        $http({
            method: 'POST',
                url: url,
                data: {usuarios:{opc:"get"}}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.perfil = res.data.Data;
                    //obj..opc = "save";
                    obj.img = res.data.img? "Images/usuarios/"+res.data.Data.Username+".png":"Images/usuarios/nouser.png";
                    console.log(obj.perfil);
                }else{
                    toastr.error(res.data.mensaje);
                }


            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnGuardarpass = function(){
        $http({
            method: 'POST',
                url: url,
                data: {usuarios:{opc:"pass",id: obj.perfil.id_seguridad, pass: obj.newpass}}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    $("#changapass").modal("hide");
                    toastr.success(res.data.mensaje);
                }else{
                    toastr.error(res.data.mensaje);
                }
            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnGuardarPerfil = () => {
        if(confirm("¿Estas seguro de Actualizar los datos del perfil?")){
            $http({
                method: 'POST',
                    url: url2,
                    data: {usuarios:obj.perfil},
                    headers:{
                        'Content-Type': undefined
                    },
                    transformRequest: function(data){
                        var formData = new FormData();
                        for(var m in data.usuarios){
                            formData.append(m, data.usuarios[m]);
                        }
                        return formData;
                    }
                }).then(function successCallback(res){
                    if(res.data.Bandera == 1){
                        toastr.success(res.data.mensaje);
                    }else{
                        toastr.error("Error: No se ha guardado el usuario");
                    }
                }, function errorCallback(res){
                    toastr.error("Error: no se realizo la conexion con el servidor");
                });
        }
    }


    angular.element(document).ready(function(){
        obj.getUsuario();

        $(".archivos").on("change",function(e){
            var file = this.files[0];
            if(file){
                if(file.size <= 1024000){
                    var reader = new FileReader();
                    reader.onload = () => {
                        obj.img = reader.result;
                        obj.$apply();
                    } 
                    reader.readAsDataURL(file);
                }else {
                    toastr.warning("Error la Imagen supera los 1 MB");
                    return;
                }
            }else{
                return;
            }
        })
    })
}