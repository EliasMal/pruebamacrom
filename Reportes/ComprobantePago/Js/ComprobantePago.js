'use strict';

var urlComprobantePago = "Ajax/ComprobantePago.php";
var RptsuruVolks = angular.module('RptsuruVolks',[]);

RptsuruVolks.controller('ComprobantePagoCtrl', ["$scope","$http", ComprobantePagoCtrl]);

function ComprobantePagoCtrl($scope,$http){
    var obj = $scope;
    obj.session = {};
   // var esta = new Array ("Por Acreditar", "Acreditado", "En preparacion", "En transito", "En proceso de Entrega", "Entregado", "Cancelado");
    obj.wizard = {preparacion:false, transito: false, proceso: false, entregado: false}

    obj.sendData = (data)=>{
        $http({
            method: 'POST',
            url: urlComprobantePago,
            data: {ficha: data}
        }).then(function successCallback(res) {
            console.log(res.data);
            if(res.data.Bandera == 1){
                obj.Comprobante = res.data.Data;
                console.log(obj.Comprobante);
                if(obj.Comprobante.Acreditado != 6){
                    var pedidoEstado = document.getElementById('estadoPedido');
                    pedidoEstado.innerHTML = "Pagado";
                    pedidoEstado.style.color="green";
                    pedidoEstado.classList.remove("text-danger");
                } else if(obj.Comprobante.Acreditado == 6){
                    var pedidoEstado = document.getElementById('estadoPedido');
                    pedidoEstado.innerHTML = "Cancelado";
                }
            }else{
                //toastr.error(res.data.mensaje)
            }
        }, function errorCallback(res) {
            //toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    document.querySelectorAll('.printbutton').forEach(function(element) {
        element.addEventListener('click', function() {
            this.style.display="none";
            print();
            setTimeout(1000);
            this.style.display="block";
        });
    });



    angular.element(document).ready(function () {
        obj.session = JSON.parse(localStorage.getItem('session'));
        if(obj.session == "null" && obj.session.autentificacion==undefined && obj.session.autentificacion!=1){
            localStorage.clear();
            location.href = "../../";
        }else{
            if(localStorage.getItem("_idPedido")){
                obj.sendData({id: localStorage.getItem("_idPedido")});
            }else{
                localStorage.clear();
                location.href = "../../";
            }
        }
        
    });
}