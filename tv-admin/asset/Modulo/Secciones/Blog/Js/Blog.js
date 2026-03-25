'use strict';

const urlBlog = './Modulo/Secciones/Blog/Ajax/Blog.php';
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

var confirmarAccionBlog = (titulo, texto, icono, btnColor, btnText, accion) => {
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
    .controller('BlogCtrl', ["$scope", "$http", BlogCtrl])
    .controller('BlogeditCtrl', ["$scope", "$http", BlogeditCtrl]);
    

function BlogCtrl($scope, $http) {
    var obj = $scope;
    obj.hoy = moment().format("YYYY-MM-DD");
    obj.paginador = { page: 0, limit: 10 };
    
    obj.Blog = { Noentradas: 0, entradas: [] };

    obj.entrada = {
        Titulo: null, Contenido: null, Fecha: null,
        imagen: { placeholder: "Ingresa una imagen del cintillo" },
        imagendestacada: { placeholder: "Ingresa una imagen destacada miniatura" },
        Publicar: false, Estatus: false, opc: "new"
    };

    obj.btnAumentar = () => {
        obj.paginador.page += obj.paginador.limit;
        obj.getEntradas(obj.paginador.page, obj.paginador.limit);
    };

    obj.btnDisminuir = () => {
        obj.paginador.page -= obj.paginador.limit;
        if (obj.paginador.page < 0) obj.paginador.page = 0;
        obj.getEntradas(obj.paginador.page, obj.paginador.limit);
    };

    obj.btnNuevaEntrada = () => { window.location.href = "?mod=Blog&opc=newEntrada"; };
    obj.btnEditarEntrada = (id) => { window.location.href = "?mod=Blog&opc=editEntrada&id=" + id; };

    obj.btnEliminarEntrada = (id) => {
        confirmarAccionBlog(
            '¿Eliminar entrada?',
            'Esta acción dará de baja la publicación del blog permanentemente.',
            'error',
            '#dc3545',
            '<i class="fas fa-trash-alt"></i> Sí, eliminar',
            () => obj.getEntradas(0, 10, "delete", id)
        );
    };
    
    obj.toggleVisibilidad = (dato) => {
        let nuevoEstado = dato.Publicar ? 0 : 1;
        let accionTexto = nuevoEstado ? 'publicar' : 'ocultar';
        let colorBtn = nuevoEstado ? '#28a745' : '#ffc107';
        confirmarAccionBlog(
            `¿${nuevoEstado ? 'Publicar' : 'Ocultar'} esta entrada?`,
            `La publicación pasará a estar ${nuevoEstado ? 'visible en la tienda' : 'oculta como borrador'}.`,
            'info',
            colorBtn,
            `<i class="fas ${nuevoEstado ? 'fa-eye' : 'fa-eye-slash'}"></i> Sí, ${accionTexto}`,
            () => {
                $http({
                    method: 'POST',
                    url: urlBlog,
                    data: { Blog: { opc: "togglePublicar", id: dato._id, estado: nuevoEstado } },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.Blog) formData.append(m, data.Blog[m]);
                        return formData;
                    }
                }).then(function (res) {
                    if (res.data.Bandera) {
                        Toast.fire({ icon: 'success', title: res.data.mensaje || `Entrada ${accionTexto}a correctamente` });
                        dato.Publicar = !dato.Publicar; 
                        dato.ClassPublicar = dato.Publicar ? 'bg-success' : 'bg-secondary';
                        dato.faPublicar = dato.Publicar ? 'fa-eye' : 'fa-eye-slash';
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje });
                    }
                }, function() {
                    Toast.fire({ icon: 'error', title: 'Error de conexión' });
                });
            }
        );
    };

    obj.getEntradas = (skip = 0, limit = 10, opc = "get", id = null) => {
        let titleSearch = obj.find ? obj.find : "";
        
        $http({
            method: 'POST',
            url: urlBlog,
            data: { Blog: { opc: opc, skip: skip, limit: limit, id: id, search: titleSearch } },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                for (var m in data.Blog) formData.append(m, data.Blog[m]);
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera) {
                obj.Blog.Noentradas = res.data.Data.NoRegistrados;
                obj.Blog.entradas = res.data.Data.Registros;
                if(opc == 'delete') Toast.fire({ icon: 'success', title: res.data.mensaje || 'Entrada eliminada' });
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje });
            }
        }, function () {
            Toast.fire({ icon: 'error', title: 'Error de conexión con el servidor.' });
        });
    };

    obj.btnCrearEntrada = () => {
        let tituloValidar = obj.entrada.Titulo || obj.entrada.titulo || obj.entrada.Title || "";

        if(tituloValidar.trim() === '') {
            Toast.fire({ icon: 'warning', title: 'El título de la entrada es obligatorio' });
            return;
        }

        confirmarAccionBlog(
            '¿Guardar nueva entrada?',
            'El artículo será guardado en el blog.',
            'question',
            '#28a745',
            '<i class="fas fa-save"></i> Sí, crear',
            () => {
                obj.entrada.Estatus = true;
                $http({
                    method: 'POST',
                    url: urlBlog,
                    data: { Blog: obj.entrada },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.Blog) formData.append(m, data.Blog[m]);
                        for (let m in data.Blog.imagen) formData.append(m + "1", data.Blog.imagen[m]);
                        for (let m in data.Blog.imagendestacada) formData.append(m + "2", data.Blog.imagendestacada[m]);
                        return formData;
                    }
                }).then(function (res) {
                    if (res.data.Bandera) {
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Entrada creada con éxito' });
                        setTimeout(() => window.location.href = '?mod=Blog', 1500);
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje });
                    }
                }, function() {
                    Toast.fire({ icon: 'error', title: 'Error de conexión' });
                });
            }
        );
    };

    angular.element(document).ready(function () {
        $(".archivos").on("change", function (e) {
            let reader = new FileReader();
            let file = this.files[0];
            
            if (file) {
                if (file.size <= 1048576) {
                    reader.readAsDataURL(file);
                    if (this.id == "imgcintillo") obj.entrada.imagen.filename = file.name;
                    else obj.entrada.imagendestacada.filename = file.name;
                    
                    obj.$apply();
                    reader.onload = () => {
                        if (this.id == "imgcintillo") $("#previewimgcintillo").attr('src', reader.result);
                        else $("#previewimgminiatura").attr('src', reader.result);
                    };
                } else {
                    Toast.fire({ icon: 'warning', title: 'La imagen es muy pesada. Máximo 1MB.' });
                    this.value = "";
                }
            }
        });
        obj.getEntradas();
    });
}

function BlogeditCtrl($scope, $http) {
    var obj = $scope;
    obj.id;
    obj.entrada;
    obj.Imagen = { placeholder: "Ingresa una imagen destacada para el area del cintillo" };
    obj.imagenDestacada = { placeholder: "Ingresa una imagen destacada para el area de miniaturas" };
    obj.dominio = "";

    obj.SendData = ($opc = null, $id = null, $data = null, file = null, file2 = null) => {
        $http({
            method: 'POST',
            url: urlBlog,
            data: { opc: $opc, id: $id, data: $data, file: file, file2: file2 },
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                if (data.opc == "save") {
                    for (var m in data.data) formData.append(m, data.data[m]);
                    formData.append('opc', data.opc);
                    formData.append('id', data.id);
                    if(data.file) formData.append('file1', data.file);
                    if(data.file2) formData.append('file2', data.file2);
                } else {
                    for (var m in data) formData.append(m, data[m]);
                }
                return formData;
            }
        }).then(function (res) {
            if (res.data.Bandera) {
                if ($opc == "getOne") {
                    obj.entrada = res.data.Data;
                    obj.dominio = res.data.dominio;
                } else {
                    Toast.fire({ icon: 'success', title: res.data.mensaje || 'Cambios guardados' });
                    setTimeout(() => window.location.href = '?mod=Blog', 1500);
                }
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje });
            }
        }, function() {
            if ($opc != "getOne") Toast.fire({ icon: 'error', title: 'Error de conexión al guardar' });
        });
    };

    obj.btnEditarEntrada = () => {
        let tituloValidar = obj.entrada.Titulo || obj.entrada.titulo || obj.entrada.Title || "";
        
        if(tituloValidar.trim() === '') {
            Toast.fire({ icon: 'warning', title: 'El título no puede quedar vacío.' });
            return;
        }

        confirmarAccionBlog(
            '¿Guardar cambios?',
            'El blog se actualizará con la nueva información.',
            'info',
            '#007bff',
            '<i class="fas fa-save"></i> Guardar',
            () => {
                if (obj.Imagen.file) obj.entrada.Imagen = obj.Imagen.filename;
                if (obj.imagenDestacada.file) obj.entrada.imagendestacada = obj.imagenDestacada.filename;
                obj.SendData("save", obj.id, obj.entrada, obj.Imagen.file, obj.imagenDestacada.file);
            }
        );
    };

    angular.element(document).ready(function () {
        $(".archivos").on("change", function (e) {
            let reader = new FileReader();
            let file = this.files[0];
            
            if (file) {
                if (file.size <= 1048576) {
                    reader.readAsDataURL(file);
                    if (this.id == "imgcintillo") obj.Imagen.filename = file.name;
                    else obj.imagenDestacada.filename = file.name;
                    
                    obj.$apply();
                    reader.onload = () => {
                        if (this.id == "imgcintillo") $("#previewimgcintillo").attr('src', reader.result);
                        else $("#previewimgminiatura").attr('src', reader.result);
                    };
                } else {
                    Toast.fire({ icon: 'warning', title: 'La imagen es muy pesada. Máximo 1MB.' });
                    this.value = "";
                }
            }
        });
        obj.SendData("getOne", obj.id);
    });
}