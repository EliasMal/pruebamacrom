'use strict';
var urlPedidos = "./Modulo/Control/Pedidos/Ajax/Pedidos.php";

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

tsuruVolks
    .controller('PedidosCtrl', ["$scope", "$http", PedidosCtrl])
    .controller('PedidosDetallesCtrl', ["$scope", "$http", PedidosDetallesCtrl]);

var confirmarPedidoAccion = (titulo, texto, icono, btnText, btnColor, accion) => {
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

function PedidosCtrl($scope, $http) {
    var obj = $scope;
    obj.No_Pedidos = 0;
    obj.Pedidos = [];
    obj.Acreditados = false;
    obj.historico = false;
    obj.paginador = { currentPage: 0, pages: [], pageSize: 10 };
    obj.autorizacion = false;

    obj.configPages = () => {
        obj.paginador.pages.length = 0;
        var ini = obj.paginador.currentPage - 4;
        var fin = obj.paginador.currentPage + 5;
        if (ini < 1) {
            ini = 1;
            fin = Math.ceil(obj.No_Pedidos / obj.paginador.pageSize) > 10 ? 10 : Math.ceil(obj.No_Pedidos / obj.paginador.pageSize);
        } else {
            if (ini >= Math.ceil(obj.No_Pedidos / obj.paginador.pageSize) - 10) {
                ini = Math.ceil(obj.No_Pedidos / obj.paginador.pageSize) - 10;
                fin = Math.ceil(obj.No_Pedidos / obj.paginador.pageSize);
            }
        }
        if (ini < 1) ini = 1;
        for (var i = ini; i <= fin; i++) {
            obj.paginador.pages.push({ no: i, p: (obj.paginador.pageSize * i) - obj.paginador.pageSize });
        }
    }

    obj.nextPage = () => {
        obj.paginador.currentPage++;
        obj.configPages();
        obj.getPedidos(obj.paginador.currentPage * obj.paginador.pageSize, obj.paginador.pageSize);
    }

    obj.lastPage = () => {
        obj.paginador.currentPage--;
        obj.configPages();
        obj.getPedidos(obj.paginador.currentPage * obj.paginador.pageSize, obj.paginador.pageSize);
    }

    obj.setPage = function (a) {
        obj.paginador.currentPage = a.no - 1;
        obj.configPages();
        obj.getPedidos(a.p, obj.paginador.pageSize);
    };

    obj.getPedidos = (x = 0, y = obj.paginador.pageSize) => {
        if(typeof Pace !== 'undefined') Pace.restart();
        
        $http.post(urlPedidos, { 
            pedidos: { opc: "get", Acreditados: obj.Acreditados ? 1 : 0, x: x, y: y, find: obj.find, Historico: obj.historico } 
        }).then(function (res) {
            if (res.data.Bandera == 1) {
                obj.No_Pedidos = res.data.No_pedidos;
                obj.Pedidos = res.data.Pedidos;
                obj.Pedidos.forEach((e) => {
                    e.class = obj.getcolorEstatus(e.Acreditado);
                });
                obj.configPages();
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje || 'Error al obtener pedidos' });
            }
        }, function (res) {
            Toast.fire({ icon: 'error', title: 'Error de conexión con el servidor' });
        });
    }

    obj.getcolorEstatus = (estatus) => {
        switch (String(estatus)) {
            case '0': return "badge-secondary";
            case '1': case '5': return "badge-success";
            case '2': case '3': case '4': return "badge-warning";
            case '6': return "badge-danger";
            default: return "badge-dark";
        }
    }

    obj.btnView = (id) => {
        location.href = "?mod=Pedidos&opc=detalles&id=" + id;
    }

    angular.element(document).ready(function () {
        if (obj.autorizacion) {
            obj.getPedidos();
        } else {
            location.href = "?mod=home";
        }
    });
}


function PedidosDetallesCtrl($scope, $http) {
    var obj = $scope;
    obj.id = "";
    obj.Pedido = {};
    obj.Detalles = [];
    obj.Tarjeta = {};
    obj.params = {};
    obj.flagCancelado = false;
    obj.autorizacion = false;

    obj.xml = { placeholder: "Agrega el archivo xml" };
    obj.pdf = { placeholder: "Agrega el archivo pdf" };
    obj.estatus = ["Por Acreditar", "Acreditado", "En preparacion", "En transito", "En proceso de Entrega", "Entregado", "Cancelado"];

    obj.abrirRefaccion = function(idParte) {
        window.location.href = '?mod=Refacciones&opc=edit&id=' + idParte;
    };

    obj.getOnePedido = (id) => {
        if (!id) {
            let params = new URLSearchParams(window.location.search);
            id = params.get('id');
            obj.id = id;
        }

        $http.post(urlPedidos, { pedidos: { opc: "getOne", id: id } }).then(function (res) {
            if (res.data.Bandera == 1) {
                obj.Pedido = res.data.Pedido;
                obj.Pedido.Importe = parseFloat(obj.Pedido.Importe);
                obj.Pedido.cenvio = parseFloat(obj.Pedido.cenvio);
                obj.flagCancelado = obj.Pedido.Acreditado != 6;
                obj.Detalles = res.data.Detalles;
                obj.Tarjeta = res.data.Tarjeta;
                if (!obj.Pedido.Largo && !obj.Pedido.Ancho) {
                    obj.Pedido.SD = "sin datos";
                }
            } else {
                Toast.fire({ icon: 'error', title: res.data.mensaje || 'No se encontró el pedido' });
            }
        }, function (res) {
            Toast.fire({ icon: 'error', title: 'Error al cargar detalles del pedido' });
        });
    }

    obj.btnGuardarCambios = () => {

        let nombrePaqueteria = obj.Pedido.Servicio ? obj.Pedido.Servicio.toLowerCase() : "";
        let esEnvioLocal = nombrePaqueteria.includes('local') || nombrePaqueteria.includes('repartidor') || nombrePaqueteria.includes('tienda') || nombrePaqueteria.includes('metropolitano');
        if (obj.Pedido.Acreditado == 3 && (!obj.Pedido.Guia || obj.Pedido.Guia.trim() === '') && !esEnvioLocal) {
            Toast.fire({ icon: 'warning', title: 'Ingresa un número de guía de la paquetería antes de guardar' });
            return;
        }

        confirmarPedidoAccion(
            '¿Guardar cambios?',
            'Se actualizará el estatus y la información del pedido.',
            'question',
            '<i class="fas fa-save"></i> Sí, guardar',
            '#28a745',
            () => {
                obj.Pedido.opc = 'save';
                $http({
                    method: 'POST',
                    url: urlPedidos,
                    data: { pedido: obj.Pedido },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.pedido) {
                            formData.append(m, data.pedido[m]);
                        }
                        return formData;
                    }
                }).then(function (res) {
                    if (res.data.Bandera == 1) {
                        obj.flagCancelado = obj.Pedido.Acreditado != 6;
                        obj.habilitado = true;
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Pedido actualizado' });
                        obj.getOnePedido(obj.Pedido._idPedidos);
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje });
                    }
                }, function (res) {
                    Toast.fire({ icon: 'error', title: 'Error de conexión al guardar' });
                });
            }
        );
    }

    obj.btnCancelarArticulo = (idDetalle, importe) => {
        confirmarPedidoAccion(
            '¿Cancelar artículo?',
            'El importe será descontado del total del pedido. Esta acción no se puede deshacer.',
            'warning',
            '<i class="fas fa-ban"></i> Sí, cancelar artículo',
            '#dc3545',
            () => {
                let pedido = { opc: 'deleteArtic', idDetalle: idDetalle };
                $http({
                    method: 'POST',
                    url: urlPedidos,
                    data: { pedido: pedido },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.pedido) {
                            formData.append(m, data.pedido[m]);
                        }
                        return formData;
                    }
                }).then(function (res) {
                    if (res.data.Bandera == 1) {
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Artículo cancelado' });
                        obj.Pedido.Importe -= importe;
                        obj.Detalles = obj.Detalles.filter(e => e._id != idDetalle);
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje });
                    }
                });
            }
        );
    }

    obj.btnClose = (element, tipo, file) => {
        confirmarPedidoAccion(
            '¿Eliminar archivo?',
            `Esta acción borrará el documento ${tipo.toUpperCase()} del servidor.`,
            'error',
            '<i class="fas fa-trash-alt"></i> Sí, eliminar',
            '#dc3545',
            () => {
                let data = {
                    _idPedido: obj.Pedido._idPedidos,
                    opc: "deletefile",
                    tipo: tipo,
                    file: file
                };
                $http({
                    method: 'POST',
                    url: urlPedidos,
                    data: { pedido: data },
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        for (var m in data.pedido) {
                            formData.append(m, data.pedido[m]);
                        }
                        return formData;
                    }
                }).then(function (res) {
                    if (res.data.Bandera == 1) {
                        $(element).alert('close');
                        Toast.fire({ icon: 'success', title: res.data.mensaje || 'Archivo eliminado' });
                        obj.getOnePedido(obj.Pedido._idPedidos);
                    } else {
                        Toast.fire({ icon: 'error', title: res.data.mensaje });
                    }
                });
            }
        );
    }

    angular.element(document).ready(function () {
        if (obj.autorizacion) {
            $(".archivos").on("change", function (e) {
                var file = this.files[0];
                if (file) {
                    if (file.size <= 1048576) { 
                        if (this.id == "xml") {
                            obj.xml.name = file.name;
                            obj.xml.Categoria = this.id;
                        } else if (this.id == "pdf") {
                            obj.pdf.name = file.name;
                            obj.pdf.Categoria = this.id;
                        }
                        obj.$apply();
                    } else {
                        Toast.fire({ icon: 'warning', title: 'El archivo supera el límite de 1 MB' });
                        this.value = "";
                    }
                }
            });
            obj.getOnePedido(obj.id);
        } else {
            location.href = "?mod=home";
        }
    });
}