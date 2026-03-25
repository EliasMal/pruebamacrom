'use strict';

var urlModelos = "./Modulo/Configuracion/Modelos/Ajax/Modelos.php";

if (typeof window.Toast === 'undefined') {
    window.Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false,
        timer: 3000, timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

var confirmarAccionModelos = (titulo, texto, icono, btnColor, btnText, accion) => {
    Swal.fire({
        title: titulo, text: texto, icon: icono,
        showCancelButton: true, confirmButtonColor: btnColor, cancelButtonColor: '#6c757d',
        confirmButtonText: btnText, cancelButtonText: 'Cancelar', reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) accion();
    });
};

tsuruVolks.controller('ModelosCrtl', ["$scope", "$http", ModelosCrtl]);

function ModelosCrtl($scope, $http) {
    var obj = $scope;
    obj.noModelos = 0;
    obj.modelo = {};
    obj.modeloanio = {};
    obj.modeloanios = [];
    obj.Modelos = [];
    obj.Marcas = [];
    obj.nuevo = true;
    obj.img = "Images/boxed-bg.jpg";
    obj._idModelotemp;
    obj.dominio = "";
    obj.paginador = { page: 0, limit: "10" };
    obj.txtfind = "";

    obj.btnAumentar = () => {
        let currentPage = parseInt(obj.paginador.page, 10);
        let currentLimit = parseInt(obj.paginador.limit, 10);
        
        if ((currentPage + currentLimit) < obj.noModelos) {
            obj.paginador.page = currentPage + currentLimit;
            obj.getModelos();
        }
    };

    obj.btnDisminuir = () => {
        let currentPage = parseInt(obj.paginador.page, 10);
        let currentLimit = parseInt(obj.paginador.limit, 10);
        
        obj.paginador.page = currentPage - currentLimit;
        if (obj.paginador.page < 0) {
            obj.paginador.page = 0;
        }
        obj.getModelos();
    };

    obj.getModelos = (skip = null, limit = null) => {
        let _skip = skip !== null ? skip : parseInt(obj.paginador.page, 10);
        let _limit = limit !== null ? limit : parseInt(obj.paginador.limit, 10);

        setTimeout(() => {
            $http({
                method: 'POST', url: urlModelos,  
                data: { modelo: { 
                    opc: "buscar", 
                    find: obj.txtfind, 
                    skip: _skip, 
                    limit: _limit, 
                    historico: obj.historico ? 0 : 1, 
                    tipo: "Modelos" 
                } },
                headers: { 'Content-Type': undefined },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.modelo) formData.append(m, data.modelo[m]);
                    return formData;
                }
            }).then(function (res) {
                if (res.data.Bandera == 1) {
                    obj.noModelos = res.data.Data.NoModelos;
                    obj.Modelos = res.data.Data.Modelos;
                    obj.dominio = res.data.dominio;
                }
            }, () => Toast.fire({ icon: 'error', title: 'Error de conexión' }));
        }, 100);
    };

    obj.getMarcas = () => {
        $http({
            method: 'POST', url: urlModelos,  
            data: { marca: { opc: "buscar", historico: obj.historico ? 0 : 1, tipo: "Marcas" } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.marca) formData.append(m, data.marca[m]);
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera == 1) obj.Marcas = res.data.data;
        });
    };

    obj.btnNuevoModelo = () => {
        obj.nuevo = true;
        obj.modelo = { opc: "new" };
        obj.img = "Images/boxed-bg.jpg";
        $("#mModelos").modal("show");
    };

    obj.btnCrearModelo = () => {
        if(!obj.modelo.Modelo || !obj.modelo._idMarca) {
            Toast.fire({ icon: 'warning', title: 'Llena los campos obligatorios' });
            return;
        }
        $http({
            method: 'POST', url: urlModelos,  
            data: { modelo: obj.modelo },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) formData.append(m, data.modelo[m]);
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera == 1) {
                Toast.fire({ icon: 'success', title: res.data.mensaje });
                obj.getModelos();
                $("#mModelos").modal("hide");
            } else { Toast.fire({ icon: 'error', title: res.data.mensaje }); }
        }, () => Toast.fire({ icon: 'error', title: 'Error de conexión' }));
    };

    obj.btnEditar = (modelo) => {
        obj.nuevo = false;
        obj.modelo = angular.copy(modelo);
        obj.modelo.opc = "edit";
        obj.img = modelo.foto ? obj.dominio + "/images/Marcas/" + modelo._idMarca + ".png" : "Images/boxed-bg.jpg";
        $("#mModelos").modal("show");
    };

    obj.btnEditarModelo = () => {
        if(!obj.modelo.Modelo || !obj.modelo._idMarca) {
            Toast.fire({ icon: 'warning', title: 'Nombre y Marca son obligatorios' });
            return;
        }
        $http({
            method: 'POST', url: urlModelos,  
            data: { modelo: obj.modelo },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) formData.append(m, data.modelo[m]);
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera == 1) {
                Toast.fire({ icon: 'success', title: res.data.mensaje });
                obj.getModelos();
                $("#mModelos").modal("hide");
            } else { Toast.fire({ icon: 'error', title: res.data.mensaje }); }
        }, () => Toast.fire({ icon: 'error', title: 'Error de conexión' }));
    };

    obj.toggleEstatus = (modelo) => {
        let opcNueva = modelo.Estatus == 1 ? "disabled" : "enabled";
        let textoToast = modelo.Estatus == 1 ? "Vehículo desactivado" : "Vehículo activado";
        $http({
            method: 'POST', url: urlModelos,  
            data: { modelo: { opc: opcNueva, _id: modelo._id } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) formData.append(m, data.modelo[m]);
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera == 1) {
                modelo.Estatus = modelo.Estatus == 1 ? 0 : 1;
                Toast.fire({ icon: 'success', title: textoToast });
            } else { Toast.fire({ icon: 'error', title: res.data.mensaje }); }
        }, () => Toast.fire({ icon: 'error', title: 'Error de conexión' }));
    };

    obj.btnEliminar = (_id) => {
        confirmarAccionModelos(
            '¿Eliminar Vehículo?',
            'Se borrará el modelo y sus generaciones. No se puede deshacer.',
            'error', '#dc3545', '<i class="fas fa-trash-alt"></i> Sí, eliminar',
            () => {
                $http({
                    method: 'POST', url: urlModelos,  
                    data: { modelo: { opc: "delete", _id: _id } },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.modelo) formData.append(m, data.modelo[m]);
                        return formData;
                    }
                }).then(function (res) {
                    if (res.data.Bandera == 1) {
                        obj.getModelos();
                        Toast.fire({ icon: 'success', title: res.data.mensaje });
                    } else { Toast.fire({ icon: 'error', title: res.data.mensaje }); }
                }, () => Toast.fire({ icon: 'error', title: 'Error de conexión' }));
            }
        );
    };

    obj.getAnios = () => {
        $http({
            method: 'POST', url: urlModelos,  
            data: { modelo: { opc: "buscar", tipo: "Anios", _idModelo: obj._idModelotemp } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) formData.append(m, data.modelo[m]);
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera == 1) {
                obj.modeloanios = res.data.data;
                $("#mAnios").modal("show");
            }
        });
    };

    obj.btnAnios = (vehiculo) => {
        obj.modelo = angular.copy(vehiculo);
        obj._idModelotemp = vehiculo._id;
        obj.getAnios();
    };

    obj.sendAnios = () => {
        $http({
            method: 'POST', url: urlModelos,  
            data: { modelo: obj.modeloanio },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.modelo) formData.append(m, data.modelo[m]);
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera == 1) {
                obj.getAnios(obj._idModelotemp);
                Toast.fire({ icon: 'success', title: res.data.mensaje });
            } else { Toast.fire({ icon: 'error', title: res.data.mensaje }); }
        }, () => Toast.fire({ icon: 'error', title: 'Error de conexión' }));
    };

    obj.btnAgregarAnio = () => {
        Swal.fire({
            title: 'Nueva Generación', text: 'Ingresa el año o periodo (Ej. 2015-2018):', input: 'text',
            showCancelButton: true, confirmButtonText: 'Guardar', confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed && result.value.trim() !== '') {
                obj.modeloanio = { Anio: result.value.trim(), _idModelo: obj._idModelotemp, opc: "newanios" };
                obj.sendAnios();
            }
        });
    };

    obj.btnEditarAnio = (anio) => {
        Swal.fire({
            title: 'Editar Generación', input: 'text', inputValue: anio.Anio,
            showCancelButton: true, confirmButtonText: 'Actualizar', confirmButtonColor: '#007bff'
        }).then((result) => {
            if (result.isConfirmed && result.value.trim() !== '') {
                obj.modeloanio = angular.copy(anio);
                obj.modeloanio.Anio = result.value.trim();
                obj.modeloanio.opc = "editanio";
                obj.sendAnios();
            }
        });
    };

    obj.btnEliminarAnio = (modelo) => {
        confirmarAccionModelos('¿Eliminar Generación?', 'Se borrará de la lista.', 'warning', '#dc3545', 'Eliminar', () => {
            obj.modeloanio = modelo; obj.modeloanio.opc = "deleteanio"; obj.sendAnios();
        });
    };

    obj.getImagenOne = () => {
        if(!obj.Marcas || obj.Marcas.length === 0) return;
        let id = obj.Marcas.find(marcas => marcas._id == obj.modelo._idMarca);
        if(id) obj.img = id.foto ? obj.dominio + "/images/Marcas/" + id._id + ".png" : "Images/boxed-bg.jpg";
    };

    obj.getImagen = (objeto) => {
        return objeto.foto ? obj.dominio + "/images/Marcas/" + objeto._idMarca + ".png" : "Images/boxed-bg.jpg";
    };

    angular.element(document).ready(function () {
        obj.getModelos();
        obj.getMarcas();
    });
}