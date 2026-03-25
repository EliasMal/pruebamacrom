'use strict'

var urlPerfil = "./Modulo/Configuracion/Perfil/Ajax/Perfil.php";

if (typeof window.Toast === 'undefined') {
    window.Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

var confirmarPerfilAccion = (titulo, texto, icono, btnText, btnColor, accion) => {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        showCancelButton: true,
        confirmButtonColor: btnColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: btnText,
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) accion();
    });
};

tsuruVolks.controller('PerfilCtrl', ["$scope", "$http", PerfilCtrl]);

function PerfilCtrl($scope, $http){
    var obj = $scope;
    obj.tipoUsuario = [
        {value:"root", descripcion:"Root"}, 
        {value:"admin", descripcion:"Administrador"},
        {value:"Web", descripcion:"Web"},
        {value:"user", descripcion:"Usuario"}
    ];
    obj.perfil = {};
    obj.perfilOriginal = {};
    obj.img = "Images/usuarios/nouser.png";
    obj.newpass = "";
    obj.tieneFotoCustom = false;

    obj.btnChangepass = () => { 
        obj.newpass = "";
        $("#changapass").modal("show"); 
    }

    obj.getNewpass = () => {
        obj.newpass = Math.random().toString(36).substring(2, 10);
    }

    obj.gettipousuario = function(tipousuario){
        if (tipousuario != undefined){
            var id = obj.tipoUsuario.find(tipo => tipo.value === tipousuario);
            return id ? id.descripcion : tipousuario;
        }
    }

    obj.btnRegresar = () => { location.href = document.referrer; }

    obj.getUsuario = () => {
        $http.post(urlPerfil, {usuarios:{opc:"get"}}).then(function(res){
            if(res.data.Bandera == 1){
                obj.perfil = res.data.Data;
                obj.perfilOriginal = angular.copy(res.data.Data); 
                
                obj.tieneFotoCustom = res.data.img;
                obj.img = res.data.img ? "Images/usuarios/" + res.data.Data.Username + ".png" : "Images/usuarios/nouser.png";
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje });
            }
        }, function(res){
            Toast.fire({ icon: 'error', title: 'Error al conectar con el servidor.' });
        });
    }

    obj.btnGuardarpass = function(){
        if(!obj.newpass) { 
            Toast.fire({ icon: 'warning', title: 'Genera o escribe una contraseña primero' }); 
            return; 
        }
        
        $http.post(urlPerfil, {usuarios:{opc:"pass", id: obj.perfil.id_seguridad, pass: obj.newpass}}).then(function(res){
            if(res.data.Bandera == 1){
                $("#changapass").modal("hide");
                Toast.fire({ icon: 'success', title: res.data.mensaje || 'Contraseña actualizada' });
                obj.newpass = "";
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje });
            }
        }, function(res){
            Toast.fire({ icon: 'error', title: 'Error al guardar la contraseña.' });
        });
    }

    obj.btnEliminarFoto = () => {
        confirmarPerfilAccion(
            '¿Eliminar foto de perfil?',
            'Volverás a tener la imagen por defecto.',
            'warning',
            '<i class="fas fa-trash-alt"></i> Sí, eliminarla',
            '#dc3545',
            () => {
                $http.post(urlPerfil, {usuarios: {opc: "delete_foto", Username: obj.perfil.Username}}).then(function(res){
                    if(res.data.Bandera == 1){
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Foto eliminada' });
                        obj.img = "Images/usuarios/nouser.png";
                        obj.tieneFotoCustom = false;
                        
                        document.getElementById('txtavatar').value = "";
                        obj.perfil.file = undefined;
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje });
                    }
                }, function() {
                    Toast.fire({ icon: 'error', title: 'Error de conexión' });
                });
            }
        );
    }

    obj.btnGuardarPerfil = () => {
        let noHayCambiosDeTexto = angular.equals(obj.perfil, obj.perfilOriginal);
        let noHayFotoNueva = (obj.perfil.file === undefined || obj.perfil.file === null);

        if(noHayCambiosDeTexto && noHayFotoNueva) {
            Toast.fire({ icon: 'info', title: 'No se detectaron cambios para guardar.' });
            return;
        }

        confirmarPerfilAccion(
            '¿Actualizar perfil?',
            'Se guardarán los cambios realizados en tu cuenta.',
            'question',
            '<i class="fas fa-save"></i> Sí, guardar',
            '#28a745',
            () => {
                obj.perfil.opc = "save"; 

                $http({
                    method: 'POST',
                    url: urlPerfil,
                    data: {usuarios: obj.perfil},
                    headers: { 'Content-Type': undefined },
                    transformRequest: function(data){
                        var formData = new FormData();
                        for(var m in data.usuarios){
                            formData.append(m, data.usuarios[m]);
                        }
                        return formData;
                    }
                }).then(function(res){
                    if(res.data.Bandera == 1){
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Perfil actualizado' });
                        
                        delete obj.perfil.opc;
                        obj.perfilOriginal = angular.copy(obj.perfil);
                        
                        if(!noHayFotoNueva) {
                            obj.tieneFotoCustom = true;
                            obj.perfil.file = undefined;
                            document.getElementById('txtavatar').value = "";
                        }
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje });
                    }
                }, function(res){
                    Toast.fire({ icon: 'error', title: 'Error al conectar con el servidor.' });
                });
            }
        );
    }

    angular.element(document).ready(function(){
        obj.getUsuario();

        $(".archivos").on("change", function(e){
            var file = this.files[0];
            if(file){
                if(file.size <= 1024000){ 
                    var reader = new FileReader();
                    reader.onload = () => {
                        obj.img = reader.result;
                        obj.$apply();
                    } 
                    reader.readAsDataURL(file);
                } else {
                    Toast.fire({ icon: 'warning', title: 'La imagen no debe superar 1 MB' });
                    this.value = ""; 
                }
            }
        });
    });
}