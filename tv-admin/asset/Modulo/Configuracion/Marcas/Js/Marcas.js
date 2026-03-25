'use strict';

var urlMarcas = "./Modulo/Configuracion/Marcas/Ajax/Marcas.php";

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

var confirmarAccionMarcas = (titulo, texto, icono, btnColor, btnText, accion) => {
    Swal.fire({
        title: titulo, text: texto, icon: icono,
        showCancelButton: true, confirmButtonColor: btnColor, cancelButtonColor: '#6c757d',
        confirmButtonText: btnText, cancelButtonText: 'Cancelar', reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) accion();
    });
};

tsuruVolks.controller('MarcasCrtl', ["$scope", "$http", MarcasCrtl]);

function MarcasCrtl($scope, $http) {
    var obj = $scope;
    obj.noMarcas = 0;
    obj.marca = {};
    obj.nuevo = true;
    obj.btnsave = false;
    obj.img = "Images/boxed-bg.jpg";
    obj.paginador = { page: 0, limit: "10" };
    obj.txtfind = "";

    obj.btnAumentar = () => {
        let currentPage = parseInt(obj.paginador.page, 10);
        let currentLimit = parseInt(obj.paginador.limit, 10);
        
        if ((currentPage + currentLimit) < obj.noMarcas) {
            obj.paginador.page = currentPage + currentLimit;
            obj.getMarcas();
        }
    };

    obj.btnDisminuir = () => {
        let currentPage = parseInt(obj.paginador.page, 10);
        let currentLimit = parseInt(obj.paginador.limit, 10);
        
        obj.paginador.page = currentPage - currentLimit;
        
        if (obj.paginador.page < 0) {
            obj.paginador.page = 0;
        }
        obj.getMarcas();
    };

    obj.getMarcas = (skip = null, limit = null) => {
        let _skip = skip !== null ? skip : obj.paginador.page;
        let _limit = limit !== null ? limit : obj.paginador.limit;

        setTimeout(() => {
            $http({
                method: 'POST',
                url: urlMarcas,
                data: { marca: { 
                    opc: "buscar", 
                    find: obj.txtfind, 
                    historico: obj.historico ? 0 : 1, 
                    skip: _skip, 
                    limit: _limit 
                } },
                headers: { 'Content-Type': undefined },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.marca) formData.append(m, data.marca[m]);
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.noMarcas = res.data.Data.noRegistros;
                    obj.marcas = res.data.Data.Registros;
                    obj.dominio = res.data.dominio;
                }
            }, function errorCallback(res) {
                Toast.fire({ icon: 'error', title: 'Error de conexión con el servidor' });
            });
        }, 100);
    };

    obj.btnNuevaMarca = () => {
        obj.nuevo = true;
        obj.marca = { opc: "new", Color: "#000000" }; 
        obj.img = "Images/boxed-bg.jpg";
        document.getElementById("txtfile").value = "";
        $('.custom-file-label').html('Seleccionar imagen...');
        $("#mMarcas").modal("show");
    };

    obj.btnCrearMarca = () => {
        if(!obj.marca.Marca || obj.marca.Marca.trim() === '') {
            Toast.fire({ icon: 'warning', title: 'El nombre de la Agencia es obligatorio' });
            return;
        }

        obj.btnsave = true;
        $http({
            method: 'POST', url: urlMarcas,
            data: { marca: obj.marca },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.marca) formData.append(m, data.marca[m]);
                return formData;
            }
        }).then(function successCallback(res) {
            obj.btnsave = false;
            if (res.data.Bandera == 1) {
                $("#mMarcas").modal("hide");
                obj.getMarcas();
                Toast.fire({ icon: 'success', title: res.data.mensaje || 'Agencia creada' });
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje || 'Error al crear la agencia' });
            }
        }, function errorCallback(res) {
            obj.btnsave = false;
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
    };

    obj.btnEditar = (marca) => {
        obj.nuevo = false;
        obj.marca = angular.copy(marca); 
        obj.marca.opc = "edit";
        obj.img = obj.marca.foto ? obj.dominio + "/images/Marcas/" + obj.marca._id + ".png" : "Images/boxed-bg.jpg";
        document.getElementById("txtfile").value = "";
        $('.custom-file-label').html('Cambiar imagen...');
        $("#mMarcas").modal("show");
    };

    obj.btnEditarMarca = () => {
        if(!obj.marca.Marca || obj.marca.Marca.trim() === '') {
            Toast.fire({ icon: 'warning', title: 'El nombre no puede quedar vacío' });
            return;
        }

        confirmarAccionMarcas(
            '¿Guardar cambios?',
            'Se actualizará la información de la agencia.',
            'info', '#007bff', '<i class="fas fa-save"></i> Guardar',
            () => {
                obj.btnsave = true;
                $http({
                    method: 'POST', url: urlMarcas,
                    data: { marca: obj.marca },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.marca) formData.append(m, data.marca[m]);
                        return formData;
                    }
                }).then(function successCallback(res) {
                    obj.btnsave = false;
                    if (res.data.Bandera == 1) {
                        $("#mMarcas").modal("hide");
                        obj.getMarcas();
                        Toast.fire({ icon: 'success', title: res.data.mensaje });
                    } else { Toast.fire({ icon: 'error', title: res.data.mensaje }); }
                }, function errorCallback(res) {
                    obj.btnsave = false;
                    Toast.fire({ icon: 'error', title: 'Error de conexión' });
                });
            }
        );
    };

    obj.getImagen = (foto, id) => {
        return foto ? obj.dominio + "/images/Marcas/" + id + ".png" : "Images/boxed-bg.jpg";
    };

    obj.toggleEstatus = (marca) => {
        let opcNueva = marca.Estatus == 1 ? "disabled" : "enabled";
        let textoToast = marca.Estatus == 1 ? "Agencia desactivada" : "Agencia activada";

        $http({
            method: 'POST', url: urlMarcas,
            data: { marca: { opc: opcNueva, _id: marca._id } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.marca) formData.append(m, data.marca[m]);
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera == 1) {
                marca.Estatus = marca.Estatus == 1 ? 0 : 1;
                Toast.fire({ icon: 'success', title: textoToast });
            } else { Toast.fire({ icon: 'error', title: res.data.mensaje }); }
        }, () => Toast.fire({ icon: 'error', title: 'Error de conexión' }));
    };

    obj.btnEliminar = (_id) => {
        confirmarAccionMarcas(
            '¿Eliminar Agencia permanentemente?',
            'Esta acción no se puede deshacer. Se borrará la agencia por completo.',
            'error', '#dc3545', '<i class="fas fa-trash-alt"></i> Sí, eliminar',
            () => {
                $http({
                    method: 'POST', url: urlMarcas,
                    data: { marca: { opc: "delete", _id: _id } },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.marca) formData.append(m, data.marca[m]);
                        return formData;
                    }
                }).then(function (res) {
                    if (res.data.Bandera == 1) {
                        obj.getMarcas();
                        Toast.fire({ icon: 'success', title: res.data.mensaje });
                    } else { Toast.fire({ icon: 'error', title: res.data.mensaje }); }
                }, () => Toast.fire({ icon: 'error', title: 'Error de conexión' }));
            }
        );
    };

    angular.element(document).ready(function () {
        var fileInput1 = document.getElementById('txtfile');
        if(fileInput1) {
            fileInput1.addEventListener('change', function (e) {
                var file = fileInput1.files[0];
                if (file) {
                    if (file.size <= 512000) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            obj.img = reader.result;
                            obj.$apply();
                        }
                        reader.readAsDataURL(file);
                        $(this).next('.custom-file-label').html(file.name); 
                    } else {
                        Toast.fire({ icon: 'warning', title: 'La imagen es muy pesada. Máximo 512 KB.' });
                        this.value = "";
                        $(this).next('.custom-file-label').html('Seleccionar imagen...');
                    }
                }
            });
        }
        obj.getMarcas();
    });
}