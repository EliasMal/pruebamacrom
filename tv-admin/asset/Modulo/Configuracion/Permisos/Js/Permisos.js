'use strict';

var urlPermisos = "./Modulo/Configuracion/Permisos/Ajax/Permisos.php";
const Toast = Swal.mixin({
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

const confirmarPermisoAccion = (titulo, texto, icono, btnText, btnColor, accion) => {
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

tsuruVolks.controller('PermisosCtrl', ["$scope", "$http", function($scope, $http) {
    var obj = $scope;
    
    obj.rolesDisponibles = [];
    obj.rolSeleccionado = ''; 
    obj.listaModulos = [];
    obj.nuevoRol = '';

    obj.cargarRoles = () => {
        $http.post(urlPermisos, { permisos: { opc: "get_roles" } })
        .then(res => {
            if(res.data.Bandera === 1){
                obj.rolesDisponibles = res.data.Roles;
                if(!obj.rolesDisponibles.includes('root')) obj.rolesDisponibles.unshift('root'); // Asegurar root
                
                if(!obj.rolSeleccionado && obj.rolesDisponibles.length > 0) {
                    obj.rolSeleccionado = obj.rolesDisponibles[0];
                    obj.cargarPermisos();
                }
            }
        });
    };

    obj.crearRol = () => {
        if(!obj.nuevoRol || obj.nuevoRol.trim() === '') {
            Toast.fire({ icon: 'warning', title: 'Escribe el nombre del nuevo rol.' });
            return;
        }
        
        let rolLimpio = obj.nuevoRol.trim();
        
        if(!obj.rolesDisponibles.includes(rolLimpio)) {
            obj.rolesDisponibles.push(rolLimpio);
            obj.rolSeleccionado = rolLimpio;
            obj.nuevoRol = '';
            obj.cargarPermisos();
            Toast.fire({ icon: 'success', title: `Rol '${rolLimpio}' listo. ¡Enciende sus permisos para guardarlo!` });
        } else {
            Toast.fire({ icon: 'info', title: 'Ese rol ya existe.' });
        }
    };

    obj.eliminarRol = () => {
        if(obj.rolSeleccionado === 'root' || obj.rolSeleccionado === 'Admin') {
            Toast.fire({ icon: 'warning', title: 'Seguridad: No puedes eliminar los roles principales.' });
            return;
        }

        confirmarPermisoAccion(
            '¿Eliminar rol permanentemente?',
            `¿Estás seguro de que deseas eliminar el rol '${obj.rolSeleccionado}'?\n\nEsto le quitará el acceso al panel a todos los usuarios que tengan este rol.`,
            'error',
            '<i class="fas fa-trash-alt"></i> Sí, eliminar rol',
            '#dc3545',
            () => {
                $http.post(urlPermisos, { 
                    permisos: { 
                        opc: "delete_rol", 
                        rol_seleccionado: obj.rolSeleccionado
                    } 
                }).then(res => {
                    if(res.data.Bandera === 1){
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Rol eliminado correctamente' });
                        obj.rolSeleccionado = '';
                        obj.cargarRoles();
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje });
                    }
                }).catch(() => {
                    Toast.fire({ icon: 'error', title: 'Error de conexión al intentar eliminar.' });
                });
            }
        );
    };
    
    obj.cargarPermisos = () => {
        $http.post(urlPermisos, { permisos: { opc: "get_modulos", rol_seleccionado: obj.rolSeleccionado } })
        .then(res => {
            if(res.data.Bandera === 1){
                obj.listaModulos = res.data.Modulos.map(mod => {
                    mod.tiene_permiso = mod.tiene_permiso == 1;
                    return mod;
                });
            }
        });
    };

    obj.cambiarPermiso = (modulo) => {
        $http.post(urlPermisos, { 
            permisos: { 
                opc: "toggle", 
                rol_seleccionado: obj.rolSeleccionado, 
                id_modulo: modulo.id_modulo, 
                estado: modulo.tiene_permiso 
            } 
        }).then(res => {
            if(res.data.Bandera === 1){
                Toast.fire({ icon: 'success', title: res.data.mensaje || 'Permiso actualizado' });
                if(modulo.tiene_permiso) obj.cargarRoles(); 
            } else {
                modulo.tiene_permiso = !modulo.tiene_permiso;
                Toast.fire({ icon: 'error', title: 'No se pudo actualizar el permiso' });
            }
        }).catch(() => {
            modulo.tiene_permiso = !modulo.tiene_permiso;
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
    };

    angular.element(document).ready(() => obj.cargarRoles());
}]);