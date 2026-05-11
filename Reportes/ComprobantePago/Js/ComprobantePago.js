'use strict';

var urlComprobantePago = "Ajax/ComprobantePago.php";
var RptsuruVolks = angular.module('RptsuruVolks',[]);

RptsuruVolks.controller('ComprobantePagoCtrl', ["$scope","$http", function($scope, $http){
    var obj = $scope;
    obj.session = {};
    obj.Comprobante = {};
    obj.wizard = {preparacion:false, transito: false, proceso: false, entregado: false};
    
    obj.estadoTexto = "";
    obj.estadoClase = "";

    obj.sendData = (data) => {
        $http({
            method: 'POST',
            url: urlComprobantePago,
            data: {ficha: data}
        }).then(function successCallback(res) {
            if(res.data.Bandera == 1 && res.data.Data){
                obj.Comprobante = res.data.Data;

                if(obj.Comprobante.Fecha){
                    let fechaLimpia = obj.Comprobante.Fecha.replace(/-/g, '/');
                    obj.Comprobante.fechaObj = new Date(fechaLimpia);
                }

                if(obj.Comprobante.Acreditado != 6){
                    obj.estadoTexto = "PAGADO";
                    obj.estadoClase = "text-success";
                } else {
                    obj.estadoTexto = "CANCELADO";
                    obj.estadoClase = "text-danger";
                }
            } else {
                console.warn("No se encontraron datos del pedido");
            }
        }, function errorCallback(res) {
            console.error("Error de conexión:", res);
        });
    };

    obj.imprimirComprobante = () => {
        window.print();
    };

    angular.element(document).ready(function () {
        let sessionRaw = localStorage.getItem('session');
        obj.session = sessionRaw ? JSON.parse(sessionRaw) : null;
        
        let idPedido = localStorage.getItem("_idPedido");

        if(!obj.session || obj.session.autentificacion != 1 || !idPedido){
            localStorage.clear();
            location.href = "../../";
        } else {
            obj.sendData({id: idPedido});
        }
    });
}]);