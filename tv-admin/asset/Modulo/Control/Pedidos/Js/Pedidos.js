var url = "./Modulo/Control/Pedidos/Ajax/Pedidos.php";
var url_detalles = "./Modulo/Control/Pedidos/Ajax/PedidosDetalles.php";

tsuruVolks
    .controller('PedidosCtrl', ["$scope", "$http", PedidosCtrl])
    .controller('PedidosDetallesCtrl', ["$scope", "$http", PedidosDetallesCtrl]);

function PedidosCtrl($scope, $http) {

    var obj = $scope;
    obj.No_Pedidos = 0;
    obj.Pedidos = [];
    obj.Acreditados = false;
    obj.historico = false;
    obj.paginador = { currentPage: 0, pages: [], pageSize: 10 }
    obj.autorizacion = false;

    obj.configPages = () => {
        obj.paginador.pages.length = 0;
        var ini = obj.paginador.currentPage - 4;
        var fin = obj.paginador.currentPage + 5;
        if (ini < 1) {
            ini = 1;
            if (Math.ceil(obj.No_Pedidos / obj.paginador.pageSize) > 10)
                fin = 10;
            else
                fin = Math.ceil(obj.No_Pedidos / obj.paginador.pageSize);
        } else {
            if (ini >= Math.ceil(obj.No_Pedidos / obj.paginador.pageSize) - 10) {
                ini = Math.ceil(obj.No_Pedidos / obj.paginador.pageSize) - 10;
                fin = Math.ceil(obj.No_Pedidos / obj.paginador.pageSize);
            }
        }
        if (ini < 1) ini = 1;
        for (var i = ini; i <= fin; i++) {
            obj.paginador.pages.push({
                no: i, p: (obj.paginador.pageSize * i) - obj.paginador.pageSize
            });
        }
    }

    obj.nextPage = () => {
        obj.paginador.currentPage = obj.paginador.currentPage + 1;
        obj.configPages();
        obj.getPedidos(obj.paginador.currentPage * obj.paginador.pageSize, obj.paginador.pageSize)
    }

    obj.lastPage = () => {
        obj.paginador.currentPage = obj.paginador.currentPage - 1;
        obj.configPages();
        obj.getPedidos(obj.paginador.currentPage * obj.paginador.pageSize, obj.paginador.pageSize)
    }

    obj.setPage = function (a) {
        obj.paginador.currentPage = a.no - 1;
        obj.configPages();
        obj.getPedidos(a.p, obj.paginador.pageSize)
    };

    obj.getPedidos = (x = 0, y = obj.paginador.pageSize) => {
        Pace.restart();
        setTimeout(() => {
            $http({
                method: 'POST',
                url: url,
                data: { pedidos: { opc: "get", Acreditados: obj.Acreditados ? 1 : 0, x: x, y: y, find: obj.find, Historico: obj.historico } }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.No_Pedidos = res.data.No_pedidos;
                    obj.Pedidos = res.data.Pedidos;
                    obj.Pedidos.forEach((e) => {
                        e.class = obj.getcolorEstatus(e.Acreditado)
                    })
                    obj.configPages();
                } else {
                    toastr.error(res.data.mensaje);
                }

            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }, 100);
    }

    obj.getcolorEstatus = (estatus) => {
        let classEstatus = "";
        switch (estatus) {
            case '0':
                classEstatus = "badge-secondary";
                break;
            case '1':
            case '5':
                classEstatus = "badge-success";
                break;
            case '2':
            case '3':
            case '4':
                classEstatus = "badge-warning";
                break;
            case '6':
                classEstatus = "badge-danger";
                break;
        }
        return classEstatus;
    }

    obj.btnView = (id) => {
        location.href = "?mod=Pedidos&opc=detalles&id=" + id;
    }

    angular.element(document).ready(function () {
        if (obj.autorizacion) {
            obj.getPedidos();
        } else {
            location.href = "?mod=home"
        }
    });
}

function PedidosDetallesCtrl($scope, $http) {
    var obj = $scope;
    obj.id = "";
    obj.Pedido = {};
    obj.Detalles = [];
    obj.Tarjeta = {};
    obj.params = {}
    obj.flagCancelado = false;
    obj.autorizacion = false;

    obj.xml = {
        placeholder: "Agrega el archivo xml"
    };
    obj.pdf = {
        placeholder: "Agrega el archivo pdf"
    }
    obj.estatus = ["Por Acreditar", "Acreditado", "En preparacion", "En transito", "En proceso de Entrega", "Entregado", "Cancelado"];

    obj.getOnePedido = (id) => {
        $http({
            method: 'POST',
            url: url,
            data: { pedidos: { opc: "getOne", id: id } }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.Pedido = res.data.Pedido;
                obj.Pedido.Importe = parseFloat(obj.Pedido.Importe)
                obj.Pedido.cenvio = parseFloat(obj.Pedido.cenvio)
                obj.flagCancelado = obj.Pedido.Acreditado != 6 ? true : false;
                obj.Detalles = res.data.Detalles;
                obj.Tarjeta = res.data.Tarjeta;
                if((obj.Pedido.Largo == "" && obj.Pedido.Ancho == "") || ((obj.Pedido.Largo == null && obj.Pedido.Ancho == null))){
                    obj.Pedido.SD = "sin datos";
                }
            } else {
                toastr.error(res.data.mensaje);
            }

        }, function errorCallback(res) {
            toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    obj.btnGuardarCambios = () => {
        if (confirm("Estas seguro de guardar los cambios")) {
            obj.Pedido.opc = 'save';
            $http({
                method: 'POST',
                url: url_detalles,
                data: { pedido: obj.Pedido },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.pedido) {
                        formData.append(m, data.pedido[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.flagCancelado = obj.Pedido.Acreditado != 6 ? true : false;
                    obj.habilitado = true;
                    toastr.success(res.data.mensaje);
                    obj.getOnePedido(obj.Pedido._idPedidos);
                }


            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });
        }
    }

    obj.btnCancelarArticulo = (idDetalle, importe) => {
        const pedido = {}

        if (confirm("¿Estas seguro de cancelar el articulo?")) {
            obj.Pedido.opc = 'deleteArtic'
            pedido.opc = 'deleteArtic';
            pedido.idDetalle = idDetalle;

            $http({
                method: 'POST',
                url: url_detalles,
                data: { pedido: pedido },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.pedido) {
                        formData.append(m, data.pedido[m]);
                    }
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    obj.Pedido.Importe -= importe;
                    obj.Detalles = obj.Detalles.filter(e => e._id != idDetalle)
                } else {
                    toastr.error(res.data.mensaje);
                }
            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });

        }
    }

    obj.btnClose = (element, tipo, file) => {

        if (confirm("Estas seguro de eliminar el archivo")) {
            let data = {
                _idPedido: obj.Pedido._idPedidos,
                opc: "deletefile",
                tipo: tipo,
                file: file
            }
            $http({
                method: 'POST',
                url: url_detalles,
                data: { pedido: data },
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    for (var m in data.pedido) {
                        formData.append(m, data.pedido[m]);
                    }
                    //formData.append("file",data.file);
                    return formData;
                }
            }).then(function successCallback(res) {
                if (res.data.Bandera == 1) {
                    //obj.habilitado = true;
                    $(element).alert('close');
                    toastr.success(res.data.mensaje);
                    obj.getOnePedido(obj.Pedido._idPedidos);
                }


            }, function errorCallback(res) {
                toastr.error("Error: no se realizo la conexion con el servidor");
            });

        }
    }

    obj.getcolorEstatus = (estatus) => {
        let classEstatus = "";
        switch (estatus) {
            case '0':
                classEstatus = "badge-secondary";
                break;
            case '1':
            case '5':
                classEstatus = "badge-success";
                break;
            case '2':
            case '3':
            case '4':
                classEstatus = "badge-warning";
                break;
            case '6':
                classEstatus = "badge-danger";
                break;
        }
        return classEstatus;
    }

    angular.element(document).ready(function () {
        if (obj.autorizacion) {
            $(".archivos").on("change", function (e) {
                var file = this.files[0];
                console.log(this);
                if (file) {
                    if (file.size <= 1024000) {
                        console.log(this.id)
                        if (this.id == "xml") {
                            obj.xml.name = file.name;
                            obj.xml.Categoria = this.id;
                        } else if (this.id == "pdf") {
                            obj.pdf.name = file.name;
                            obj.pdf.Categoria = this.id
                        }


                        obj.$apply();

                    } else {
                        toastr.warning("Error la Imagen supera los 1 MB");
                        return;
                    }
                } else {
                    return;
                }
            })
            obj.getOnePedido(obj.id);
        } else {
            location.href = "?mod=home"
        }
    });
}