/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var url1 = "./Modulo/Configuracion/Usuarios/Ajax/Buscar.php";
var url2 = "./Modulo/Configuracion/Usuarios/Ajax/Usuarios.php";
tsuruVolks
        .controller('UsuariosCtrl', UsuariosCtrl)
        .controller('UsuariosNewCtrl', UsuariosNewCtrl)
        .controller('UsuariosEditCtrl',['$scope','$http',UsuariosEditCtrl] );
        


function UsuariosCtrl($scope,$http){
    var obj = $scope;
    obj.usuarios = {};
    obj.historico = false;
    
    obj.btnNuevoUsuario = function(){
//        $("#mdlUsuarios").modal("show");
        window.location.href="?mod=usuarios&opc=new";
    };
    
    obj.opcEditar = function (id){
        window.location.href="?mod=usuarios&opc=edit&id="+id;
    }
    
    obj.opcDesactivar = function (_id){
        if(confirm("¿Estas seguro de desactivar la cuenta?")){
            $http({
            method: 'POST',
                url: url1,
                data: {usuarios:{opc:"borrar",id:_id}}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    toastr.success(res.data.mensaje);
                     obj.getUsuarios();
                }


            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
        }
    }
    
    
    obj.getUsuarios = function(){
        $http({
            method: 'POST',
                url: url1,
                data: {usuarios:{opc:"buscar",historico: obj.historico? 0:1}}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.usuarios = res.data.Data;
                    console.log(obj.usuarios);
                }


            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }
    
     obj.getUsuarios();
    
}

function UsuariosNewCtrl ($scope,$http){
    var obj = $scope;
    obj.usuario = {};
    obj.img = "Images/boxed-bg.jpg";
    obj.usuario.opc="new";
    obj.disabled = true;
    obj.verpass = false;
    obj.tipoUsuario = [{value:"Admin",descripcion:"Administrador"},{value:"Web",descripcion:"Web"},{value:"user",descripcion:"Usuario"},
                       {value:"capturista", descripcion:"Capturista"},{value:"venta", descripcion:"Ventas"}];
    
    obj.btnCrearUsuario = function(){
        $http({
            method: 'POST',
                url: url2,
                data: {usuarios:obj.usuario},
                headers:{
                    'Content-Type': undefined
                },
                transformRequest: function(data){
                    var formData = new FormData();
                    for(var m in data.usuarios){
                        formData.append(m, data.usuarios[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.disabled = true
                    obj.verpass = true;
                    $("#usuario").html(res.data.Username);
                    //$("#password").html(res.data.password);
                    //$("#passuser").modal("show");
                    toastr.success(res.data.mensaje);
                }else{
                    toastr.error("Error: No se ha guardado el usuario");
                }


            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }
    
    obj.btnNuevoUsuario = function(){
        obj.usuario = {};
        obj.usuario.opc="new";
        obj.disabled = false;
    }
    
    obj.gettipousuario = function(tipousuario){
        
        if (tipousuario != undefined){
            var id = obj.tipoUsuario.find(tipo=> tipo.value === tipousuario);
            
            return id.descripcion;
        }
    }
    
    obj.btnRegresar = function(){
        window.location.href='?mod=usuarios';
    }
    
    obj.btnverPass = function(){
        $("#passuser").modal("show");
    }
    
    angular.element(document).ready(function(){
            var fileInput1 = document.getElementById('txtavatar');
            fileInput1.addEventListener('change', function(e) {
              var file = fileInput1.files[0];
              var imageType = /image.*/;
              if (file) {
                if (file.size <= 512000) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
                    obj.img = reader.result;
                    obj.$apply();
                    
                  }
                  
                  reader.readAsDataURL(file);
                } else {
                  toastr.warning("Error la Imagen supera los 512 KB");
                  return;
                }
              } else {
                return
              }
            });
        });
}

function UsuariosEditCtrl($scope,$http){
    var obj = $scope;
    obj.usuario={};
    obj.verpass = true;
    obj.disabled = true;
    obj.tipoUsuario = [{value:"Admin",descripcion:"Administrador"},{value:"Web",descripcion:"Web"},{value:"user",descripcion:"Usuario"},
                       {value:"capturista", descripcion:"Capturista"},{value:"venta", descripcion:"Ventas"}];
    obj.img;
    obj.newpass;
    obj.getUsuario = function(){
        $http({
            method: 'POST',
                url: url1,
                data: {usuarios:{opc:"edit",id: obj.usuario.id}}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.usuario = res.data.Data;
                    obj.usuario.opc = "save";
                    obj.img = res.data.img? "Images/usuarios/"+res.data.Data.Username+".png":"Images/boxed-bg.jpg";
                    console.log(obj.usuario);
                }else{
                    toastr.error(res.data.mensaje);
                }


            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }
    
    obj.btnEditarUsuario = function (){
        obj.disabled = false;
    }
    
    obj.gettipousuario = function(tipousuario){
        
        if (tipousuario != undefined){
            var id = obj.tipoUsuario.find(tipo=> tipo.value === tipousuario);
            
            return id.descripcion;
        }
    }
    
    obj.btnRegresar = function(){
        window.location.href='?mod=usuarios';
    }
    
    obj.btnGuardarcambios = function(){
        if(confirm("¿Estas seguro de guardar los cambios?")){
            
            $http({
            method: 'POST',
                url: url2,
                data: {usuarios:obj.usuario},
                headers:{
                    'Content-Type': undefined
                },
                transformRequest: function(data){
                    var formData = new FormData();
                    for(var m in data.usuarios){
                        formData.append(m, data.usuarios[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.disabled = true
//                    obj.verpass = true;
//                    $("#usuario").html(res.data.username);
//                    $("#password").html(res.data.password);
//                    $("#passuser").modal("show");
                    toastr.success(res.data.mensaje);
                }else{
                    toastr.error("Error: No se ha guardado el usuario");
                }


            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }
    
    obj.btnChangepass = function(){
        $("#changapass").modal("show");
    }
    
    obj.getNewpass = function(){
        obj.newpass = Math.random().toString(36).substring(2, 8);
    }
    
    obj.btnGuardarpass = function(){
        $http({
            method: 'POST',
                url: url1,
                data: {usuarios:{opc:"pass",id: obj.usuario.idseguridad, pass:obj.newpass, email:obj.usuario.email, 
                nombre:obj.usuario.Nombre, username:obj.usuario.username}}
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
    angular.element(document).ready(function(){
            var fileInput1 = document.getElementById('txtavatar');
            fileInput1.addEventListener('change', function(e) {
              var file = fileInput1.files[0];
              var imageType = /image.*/;
              if (file) {
                if (file.size <= 512000) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
                    obj.img = reader.result;
                    obj.$apply();
                    
                  }
                  
                  reader.readAsDataURL(file);
                } else {
                  toastr.warning("Error la Imagen supera los 512 KB");
                  return;
                }
              } else {
                return
              }
            });
            
            obj.getUsuario();  
        });
        
    
}
