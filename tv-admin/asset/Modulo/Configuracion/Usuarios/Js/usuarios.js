'use strict';

var urlBuscarUsuarios = "./Modulo/Configuracion/Usuarios/Ajax/Buscar.php";
var urlUsuariosDatos = "./Modulo/Configuracion/Usuarios/Ajax/Usuarios.php";

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

var confirmarUsuarioAccion = (titulo, texto, icono, btnText, btnColor, accion) => {
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

tsuruVolks
    .controller('UsuariosCtrl', UsuariosCtrl)
    .controller('UsuariosNewCtrl', UsuariosNewCtrl)
    .controller('UsuariosEditCtrl', ['$scope', '$http', UsuariosEditCtrl]);

function UsuariosCtrl($scope, $http) {
    var obj = $scope;
    obj.usuarios = {};
    obj.historico = false;
    obj.listaRoles = [];
    obj.Rol_Usuario = "";

    obj.cargarRoles = function () {
        $http.post(urlBuscarUsuarios, { usuarios: { opc: "get_roles" } }).then(function (res) {
            if (res.data.Bandera == 1) obj.listaRoles = res.data.Roles;
        });
    }
    obj.cargarRoles();

    obj.actualizarRolRapido = function(user) {
        $http({
            method: 'POST',
            url: urlUsuariosDatos,
            data: { usuarios: { opc: "update_rol_rapido", id_usuario: user._id, nuevo_rol: user.Tipo_usuario } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.usuarios) { formData.append(m, data.usuarios[m]); }
                return formData;
            }
        }).then(function(res) {
            if (res.data.Bandera == 1) {
                Toast.fire({ icon: 'success', title: res.data.mensaje || 'Rol actualizado' });
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje || 'Error al cambiar rol' });
                obj.getUsuarios();
            }
        }, function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
            obj.getUsuarios();
        });
    }

    obj.btnNuevoUsuario = function () { window.location.href = "?mod=usuarios&opc=new"; };
    obj.opcEditar = function (id) { window.location.href = "?mod=usuarios&opc=edit&id=" + id; }

    obj.opcDesactivar = function (_id) {
        confirmarUsuarioAccion(
            '¿Desactivar usuario?',
            'El usuario ya no podrá ingresar al sistema.',
            'warning',
            '<i class="fas fa-user-slash"></i> Sí, desactivar',
            '#dc3545',
            () => {
                $http({ method: 'POST', url: urlBuscarUsuarios, data: { usuarios: { opc: "borrar", id: _id } } })
                .then(function (res) {
                    if (res.data.Bandera == 1) {
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Usuario desactivado' });
                        obj.getUsuarios();
                    } else {
                        Toast.fire({ icon: 'error', title: 'No se pudo desactivar el usuario' });
                    }
                }, function() {
                    Toast.fire({ icon: 'error', title: 'Error de conexión' });
                });
            }
        );
    }

    obj.opcActivar = function (_id) {
        $http({ method: 'POST', url: urlBuscarUsuarios, data: { usuarios: { opc: "activar", id: _id } } })
        .then(function (res) {
            if (res.data.Bandera == 1) {
                Toast.fire({ icon: 'success', title: 'Usuario reactivado' });
                obj.getUsuarios();
            } else {
                Toast.fire({ icon: 'error', title: 'No se pudo activar el usuario' });
            }
        });
    }

    obj.opcEliminar = function (user) {
        Swal.fire({
            title: "¿ELIMINAR DEFINITIVAMENTE?",
            html: `Estás a punto de borrar al usuario <b>${user.Nombre}</b>.<br>Se borrará su cuenta, credenciales y fotografía.<br>¡ESTA ACCIÓN NO SE PUEDE DESHACER!`,
            icon: "error",
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> SÍ, ELIMINAR',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $http({ method: 'POST', url: urlBuscarUsuarios, data: { usuarios: { opc: "eliminar_permanente", id: user._id } } })
                .then(function (res) {
                    if (res.data.Bandera == 1) {
                        Toast.fire({ icon: 'success', title: res.data.mensaje });
                        obj.getUsuarios();
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje });
                    }
                });
            }
        });
    };

    obj.getUsuarios = function () {
        setTimeout(() => {
            $http({ method: 'POST', url: urlBuscarUsuarios, data: { usuarios: { opc: "buscar", historico: obj.historico ? 0 : 1 } } })
            .then(function (res) {
                if (res.data.Bandera == 1) {
                    obj.usuarios = res.data.Data;
                    obj.Rol_Usuario = res.data.Rol_Usuario;
                }
            });
        }, 100);
    }
    
    obj.getUsuarios();
}

function UsuariosNewCtrl($scope, $http) {
    var obj = $scope;
    obj.usuario = { opc: "new" };
    obj.img = "Images/boxed-bg.jpg";
    obj.disabled = false;
    obj.verpass = false;
    obj.tipoUsuario = [];

    $http.post(urlBuscarUsuarios, { usuarios: { opc: "get_roles" } }).then(function (res) {
        if (res.data.Bandera == 1) obj.tipoUsuario = res.data.Roles;
    });

    obj.btnCrearUsuario = function () {
        if (!obj.usuario.Nombre || !obj.usuario.Username || !obj.usuario.Email) {
            Toast.fire({ icon: 'warning', title: 'Llena los campos obligatorios' });
            return;
        }

        $http({
            method: 'POST', url: urlUsuariosDatos, data: { usuarios: obj.usuario },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.usuarios) { formData.append(m, data.usuarios[m]); }
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera == 1) {
                obj.disabled = true;
                obj.verpass = true;
                $("#usuario").html(res.data.Username);
                Toast.fire({ icon: 'success', title: res.data.mensaje || 'Usuario creado exitosamente' });
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje || 'Error al guardar el usuario' });
            }
        }, function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
    }

    obj.btnNuevoUsuario = function () { obj.usuario = { opc: "new" }; obj.disabled = false; obj.verpass = false; }
    obj.btnRegresar = function () { window.location.href = '?mod=usuarios'; }
    obj.btnverPass = function () { $("#passuser").modal("show"); }

    obj.gettipousuario = function (tipousuario) {
        if (tipousuario && obj.tipoUsuario.length > 0) {
            var id = obj.tipoUsuario.find(tipo => tipo.value === tipousuario);
            return id ? id.descripcion : tipousuario;
        }
    }

    angular.element(document).ready(function () {
        var fileInput1 = document.getElementById('txtavatar');
        if(fileInput1) {
            fileInput1.addEventListener('change', function (e) {
                var file = fileInput1.files[0];
                if (file) {
                    if (file.size <= 512000) {
                        var reader = new FileReader();
                        reader.onload = function (e) { obj.img = reader.result; obj.$apply(); }
                        reader.readAsDataURL(file);
                    } else {
                        Toast.fire({ icon: 'warning', title: 'La Imagen supera los 512 KB' });
                        fileInput1.value = "";
                    }
                }
            });
        }
    });
}

function UsuariosEditCtrl($scope, $http) {
    var obj = $scope;
    obj.usuario = {};
    obj.verpass = true;
    obj.disabled = true;
    obj.tipoUsuario = [];
    obj.img;
    obj.newpass;

    $http.post(urlBuscarUsuarios, { usuarios: { opc: "get_roles" } }).then(function (res) {
        if (res.data.Bandera == 1) obj.tipoUsuario = res.data.Roles;
    });

    obj.getUsuario = function () {
        $http({ method: 'POST', url: urlBuscarUsuarios, data: { usuarios: { opc: "edit", id: obj.usuario.id } } })
        .then(function (res) {
            if (res.data.Bandera == 1) {
                obj.usuario = res.data.Data;
                obj.usuario.opc = "save";
                obj.img = res.data.img ? "Images/usuarios/" + res.data.Data.Username + ".png" : "Images/boxed-bg.jpg";
            }
        });
    }

    obj.btnEditarUsuario = function () { obj.disabled = false; }
    obj.btnRegresar = function () { window.location.href = '?mod=usuarios'; }

    obj.btnGuardarcambios = function () {
        confirmarUsuarioAccion(
            '¿Guardar cambios?',
            'Se actualizará la información del perfil del usuario.',
            'question',
            '<i class="fas fa-save"></i> Sí, guardar',
            '#28a745',
            () => {
                $http({
                    method: 'POST', url: urlUsuariosDatos, data: { usuarios: obj.usuario },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.usuarios) { formData.append(m, data.usuarios[m]); }
                        return formData;
                    }
                }).then(function (res) {
                    if (res.data.Bandera == 1) {
                        obj.disabled = true;
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Cambios guardados' });
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje || 'Error al guardar cambios' });
                    }
                }, function() {
                    Toast.fire({ icon: 'error', title: 'Error de conexión' });
                });
            }
        );
    }

    obj.btnChangepass = function () { 
        obj.newpass = "";
        $("#changapass").modal("show"); 
    }
    
    obj.getNewpass = function () { 
        obj.newpass = Math.random().toString(36).substring(2, 8); 
    }

    obj.btnGuardarpass = function () {
        if(!obj.newpass) {
            Toast.fire({ icon: 'warning', title: 'Primero genera o escribe una contraseña' });
            return;
        }

        confirmarUsuarioAccion(
            '¿Actualizar Contraseña?',
            `La nueva contraseña para ${obj.usuario.Nombre} será guardada.`,
            'info',
            '<i class="fas fa-key"></i> Sí, actualizar',
            '#007bff', // Azul
            () => {
                $http({
                    method: 'POST', url: urlBuscarUsuarios,
                    data: { usuarios: { opc: "pass", id: obj.usuario.idseguridad, pass: obj.newpass, email: obj.usuario.email, nombre: obj.usuario.Nombre, username: obj.usuario.username } }
                }).then(function (res) {
                    if (res.data.Bandera == 1) {
                        $("#changapass").modal("hide");
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Contraseña actualizada' });
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje || 'Error al actualizar' });
                    }
                }, function() {
                    Toast.fire({ icon: 'error', title: 'Error de conexión' });
                });
            }
        );
    }

    obj.gettipousuario = function (tipousuario) {
        if (tipousuario && obj.tipoUsuario.length > 0) {
            var id = obj.tipoUsuario.find(tipo => tipo.value === tipousuario);
            return id ? id.descripcion : tipousuario;
        }
    }

    angular.element(document).ready(function () {
        var fileInput1 = document.getElementById('txtavatar');
        if(fileInput1) {
            fileInput1.addEventListener('change', function (e) {
                var file = fileInput1.files[0];
                if (file) {
                    if (file.size <= 512000) {
                        var reader = new FileReader();
                        reader.onload = function (e) { obj.img = reader.result; obj.$apply(); }
                        reader.readAsDataURL(file);
                    } else {
                        Toast.fire({ icon: 'warning', title: 'La Imagen supera los 512 KB' });
                        fileInput1.value = "";
                    }
                }
            });
        }
        obj.getUsuario();
    });
}